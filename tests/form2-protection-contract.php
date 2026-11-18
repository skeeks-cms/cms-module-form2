<?php
/**
 * The protection of public forms on the stand database, without saving anything: the signed one-time stamp bound to
 * a site, a form model and a form; the built-in bot check task; phone keys of the format «Телефон» by
 * libphonenumber; limit settings; the protection schema state.
 *
 * Usage (inside the php container of a site): FORM2_PROTECTION_TEST=local php tests/form2-protection-contract.php
 */
if (getenv('FORM2_PROTECTION_TEST') !== 'local') {
    throw new RuntimeException('Set FORM2_PROTECTION_TEST=local.');
}
define('ROOT_DIR', getenv('TEST_APP_ROOT') ?: '/app');
define('YII_ENV', 'dev');
define('YII_DEBUG', true);
require ROOT_DIR.'/vendor/skeeks/cms/bootstrap.php';
$config = new Yiisoft\Config\Config(new Yiisoft\Config\ConfigPaths(ROOT_DIR, 'config'), null, [Yiisoft\Config\Modifier\RecursiveMerge::groups('web', 'web-'.ENV, 'params', 'params-web-'.ENV)], 'params-web-'.ENV);
$c = $config->get($config->has('web-'.ENV) ? 'web-'.ENV : 'web');
$c['components']['request']['hostInfo'] = 'http://localhost';
$c['components']['user']['enableSession'] = false;
new yii\web\Application($c);
Yii::$app->errorHandler->unregister();

use skeeks\modules\cms\form2\components\Form2ProtectionSettings;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use skeeks\modules\cms\form2\models\Form2FormSendNotification;
use skeeks\modules\cms\form2\Module;
use skeeks\modules\cms\form2\protection\BotCheckResult;
use skeeks\modules\cms\form2\protection\FormProtection;
use skeeks\modules\cms\form2\protection\PowBotCheck;

$checks = 0;
$check = static function (bool $ok, string $message) use (&$checks) {
    if (!$ok) {
        throw new RuntimeException('Form2 protection: '.$message);
    }
    $checks++;
};

$protection = new FormProtection(['secret' => 'test-secret', 'limits' => ['ip' => [60 => 2]]]);

// Limits: the defaults of a site, the administration settings, a project config key over them.
$check(FormProtection::defaultLimits() === ['phone' => [86400 => 3], 'session' => [3600 => 5], 'ip' => [3600 => 3, 86400 => 10]], 'default limits: phone 3/day, session 5/hour, IP 3/hour and 10/day');
$settings = new Form2ProtectionSettings(['overridePath' => []]);
$settings->ipPerHour = 0;
$settings->phonePerDay = 0;
$check($settings->limits() === ['phone' => [], 'session' => [3600 => 5], 'ip' => [86400 => 10]], '0 switches a limit off');
$siteLimits = $protection->limitsForSite(null);
$check($siteLimits['ip'] === [60 => 2], 'a project config key wins over the settings');
$check(array_key_exists('phone', $siteLimits) && array_key_exists('session', $siteLimits), 'other keys come from the settings');
$bad = new Form2ProtectionSettings(['overridePath' => []]);
$bad->ipPerHour = -1;
$check(!$bad->validate(['ipPerHour']), 'a negative limit is refused');

// The stamp is bound to the site, the form model and the form.
$formA = new Form2Form(['id' => 7, 'cms_site_id' => 1]);
$formB = new Form2Form(['id' => 7, 'cms_site_id' => 2]);
$formC = new Form2Form(['id' => 8, 'cms_site_id' => 1]);
$token = $protection->createToken($formA);
$stamp = $protection->parseToken($token);
$check(is_array($stamp) && $stamp['f'] === 7 && $stamp['s'] === 1 && preg_match('/^[a-f0-9]{32}$/', $stamp['n']) === 1, 'a stamp is parsed');
$check(FormProtection::stampMatches($stamp, $formA), 'a stamp matches its form');
$check(!FormProtection::stampMatches($stamp, $formB), 'a stamp of site A does not confirm the same form id of site B');
$check(!FormProtection::stampMatches($stamp, $formC), 'a stamp does not confirm another form');
$otherModel = new class(['id' => 7, 'cms_site_id' => 1]) extends Form2Form {};
$check(!FormProtection::stampMatches($stamp, $otherModel), 'a stamp does not confirm a form of another model with the same id');
$check($protection->createToken($formA) !== $token, 'every stamp has its own one-time key');
[$payload, $signature] = explode('.', $token);
$check($protection->parseToken($payload.'.'.str_repeat('0', 64)) === null, 'a wrong signature is refused');
$forged = rtrim(strtr(base64_encode(json_encode(['s' => 2, 'm' => $stamp['m'], 'f' => 7, 't' => time(), 'n' => $stamp['n']])), '+/', '-_'), '=');
$check($protection->parseToken($forged.'.'.$signature) === null, 'a stamp moved to another site is refused');
$other = new FormProtection(['secret' => 'other-secret']);
$check($other->parseToken($token) === null, 'a stamp of another key is refused');

// The built-in task.
$pow = new PowBotCheck(['secret' => 'test-secret', 'maxNumber' => 2000]);
$task = $pow->issue($token);
$number = null;
for ($i = 0; $i <= 2000; $i++) {
    if (hash('sha256', $task['salt'].$i) === $task['challenge']) {
        $number = $i;
        break;
    }
}
$check($number !== null, 'the task has a solution within maxNumber');
$answer = ['salt' => $task['salt'], 'challenge' => $task['challenge'], 'signature' => $task['signature'], 'number' => $number];
$check($pow->verify($token, $answer)->status === BotCheckResult::PASSED, 'a solved task passes');
$check($pow->verify($token, ['number' => $number + 1] + $answer)->status === BotCheckResult::FAILED, 'a wrong number fails');
$check($pow->verify($protection->createToken($formA), $answer)->status === BotCheckResult::FAILED, 'a task of another stamp fails');
$check($pow->verify($token, null)->status === BotCheckResult::FAILED, 'no answer fails');
$check($pow->verify($token, ['challenge' => str_repeat('a', 64)] + $answer)->status === BotCheckResult::FAILED, 'a replaced challenge fails');

// Phone keys: libphonenumber, valid numbers only, one key for different spellings.
foreach ([
    '8 (900) 123-45-67' => '79001234567',
    '+7 900 123-45-67' => '79001234567',
    '+375 29 123-45-67' => '375291234567',
    '+354 551 2345' => '3545512345',
    '12345' => null,
    '+7 900 123-45' => null,
    'client@example.com' => null,
] as $input => $expected) {
    $check($protection->phoneKey($input) === $expected, 'phone key of '.$input);
}

// Phones are taken only from fields of the format «Телефон», never by a name.
$check(!method_exists(FormProtection::class, 'isPhoneField'), 'no phone detection by a field name');
$send = new Form2FormSend();
$check($send->phoneFormatValues(['pdphone' => '+7 900 123-45-67'], ['pdphone' => 'Телефон']) === [], 'a field without a form is not a phone by its name');

// Recipients as the unique index sees them; an interrupted delivery is an unknown result, not a retry.
$check(Form2FormSendNotification::recipientKey(' TEST@Example.invalid ') === Form2FormSendNotification::recipientKey('test@example.invalid'), 'recipients are compared case-insensitively');
$check(isset(Form2FormSendNotification::statusLabels()[Form2FormSendNotification::STATUS_UNKNOWN]), 'the status «Результат неизвестен» exists');
$model = file_get_contents(dirname(__DIR__).'/src/models/Form2FormSend.php');
$check(strpos($model, "'status' => Form2FormSendNotification::STATUS_UNKNOWN") !== false
    && strpos($model, "['status' => [Form2FormSendNotification::STATUS_PENDING, Form2FormSendNotification::STATUS_FAILED]]") !== false,
    'a stale «sending» row becomes unknown and only pending/failed rows are sent');
$check(strpos(file_get_contents(dirname(__DIR__).'/src/Module.php'), 'legacyLeadPhoneByName') === false
    && strpos($model, "preg_match('/телефон|phone|mobile/u'") === false, 'no phone of a deal by a field name');
$controller = file_get_contents(dirname(__DIR__).'/src/controllers/AdminFormSendController.php');
$check(strpos($controller, 'propertyValueHtml(') !== false && strpos($controller, '->getAttributeAsHtml(') === false, 'list columns escape visitor values');

foreach (array_keys(FormProtection::reasonLabels()) as $code) {
    $check(FormProtection::reasonLabel($code) !== $code, 'a label of reason '.$code);
}

// The module: one check of a public form.
$module = Module::instance();
$check($module->resolveFormModelClass('skeeks\cms\models\CmsUser') === null, 'only allowlisted form models');
$check($module->findPublicForm(Form2Form::class, 'abc', 1) === null, 'a non-numeric id is refused');
$anyForm = Form2Form::find()->one();
if ($anyForm) {
    $check($module->findPublicForm(Form2Form::class, $anyForm->id, (int)$anyForm->cms_site_id) !== null, 'a form of its site is found');
    $check($module->findPublicForm(Form2Form::class, $anyForm->id, (int)$anyForm->cms_site_id + 100000) === null, 'a form of another site is not found');
}

Form2FormSend::resetProtectionReady();
echo 'Protection schema: '.(Form2FormSend::isProtectionReady() ? 'complete' : 'not complete')."\n";
echo "Form2 protection contract: ok ({$checks} checks)\n";
