<?php
/**
 * The channel of a form (Form2Form::$lead_source_type, owner's decision 05.11.2026) on the stand database,
 * rolled back: a submission of a form with the channel «Визит» makes a deal with source_type=visit, the
 * submission finds its deal again, the form creation entry names the form; a form without the channel stays
 * «Форма»; the utm of the visit fill «Откуда узнали» the same way.
 *
 * Usage: FORM2_VISIT_TEST=local php tests/form2-visit-channel-stand.php (FORM2_FORM_ID=2)
 */
if (getenv('FORM2_VISIT_TEST') !== 'local') {
    throw new RuntimeException('Set FORM2_VISIT_TEST=local.');
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

use skeeks\cms\models\CmsLead;
use skeeks\cms\models\CmsLog;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;

$checks = 0;
$check = static function (bool $ok, string $message) use (&$checks) {
    $checks++;
    if (!$ok) {
        throw new RuntimeException('FAIL: '.$message);
    }
};
if (!defined(CmsLead::class.'::SOURCE_VISIT') || !(new Form2Form())->hasAttribute('lead_source_type')) {
    throw new RuntimeException('Update cms and form2 and run cms/migrate.');
}

$tx = Yii::$app->db->beginTransaction();
try {
    $form = Form2Form::findOne((int)(getenv('FORM2_FORM_ID') ?: 2));
    $check($form !== null, 'the test form exists');
    $form->emails = '';
    $form->lead_source_type = CmsLead::SOURCE_VISIT;
    $check($form->save(false, ['emails', 'lead_source_type']), 'the form gets the channel «Визит»');
    $check(isset(Form2Form::leadSourceTypeItems()[CmsLead::SOURCE_VISIT]), 'the channel is offered');
    $bad = clone $form;
    $bad->lead_source_type = 'nowhere';
    $check(!$bad->validate(['lead_source_type']), 'an unknown channel is refused');

    $send = static function (array $utm) use ($form) {
        $model = new Form2FormSend([
            'form_id' => (int)$form->id,
            'cms_site_id' => (int)$form->cms_site_id,
            'data_values' => ['name' => 'Проверка визита', 'phone' => '+7 999 '.random_int(100, 999).'-'.random_int(10, 99).'-'.random_int(10, 99)],
            'data_labels' => ['name' => 'Имя', 'phone' => 'Телефон'],
            'utms' => $utm,
            'page_url' => 'http://localhost/salon',
        ]);
        if (!$model->save(false)) {
            throw new RuntimeException('the submission is not saved');
        }

        return $model;
    };

    $visit = $send(['utm_source' => 'yandex', 'utm_medium' => 'cpc']);
    $lead = $visit->getCmsLead();
    $check($lead !== null && $lead->source_type === CmsLead::SOURCE_VISIT, 'a submission of the form makes a visit deal');
    $check($lead->isFormSubmission(), 'the visit deal is a form submission');
    $check($lead->referral && $lead->referral->code === 'yandex_direct', '«Откуда узнали» by the marks of the visit');
    $entry = CmsLog::find()->andWhere(['model_code' => $lead->skeeksModelCode, 'model_id' => (int)$lead->id, 'log_type' => CmsLog::LOG_TYPE_COMMENT])
        ->orderBy(['id' => SORT_ASC])->one();
    $check($entry && mb_strpos((string)$entry->comment, 'Отправлена форма «'.$form->name.'» №'.(int)$visit->id) === 0, 'the creation entry names the form');

    // The channel switched off later: the old submission still finds its deal, a new one is a form deal.
    $form->lead_source_type = null;
    $check($form->save(false, ['lead_source_type']), 'the channel is switched off');
    $visit->refresh();
    $check($visit->getCmsLead() && (int)$visit->getCmsLead()->id === (int)$lead->id, 'the old submission still finds its visit deal');
    $plain = $send([]);
    $check($plain->getCmsLead() && $plain->getCmsLead()->source_type === CmsLead::SOURCE_FORM, 'a form without the channel makes a form deal');

    echo "OK form2-visit-channel-stand: {$checks} checks\n";
} finally {
    if ($tx->isActive) {
        $tx->rollBack();
    }
}
