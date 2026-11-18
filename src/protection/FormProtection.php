<?php

namespace skeeks\modules\cms\form2\protection;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use skeeks\modules\cms\form2\components\Form2ProtectionSettings;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\Request;

/**
 * Protection of public forms of the form constructor.
 *
 * A submission is never silently dropped: one that fails a check is saved as «suspicious» with the codes of its
 * reasons, without notifications, without a CRM deal and without a conversion; an administrator can accept it in
 * the submission card. Configured per site in the project config:
 *
 * ```php
 * 'modules' => ['form2' => ['protection' => [
 *     'limits' => ['ip' => [3600 => 20]],      // replaces the defaults of this key
 *     'botCheck' => ['maxNumber' => 100000],   // or false to switch the bot check off
 * ]]],
 * ```
 *
 * See README «Protection of public forms».
 */
class FormProtection extends Component
{
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_SUSPICIOUS = 'suspicious';

    const REASON_HONEYPOT = 'honeypot';
    const REASON_NO_TOKEN = 'no_token';
    const REASON_BAD_TOKEN = 'bad_token';
    const REASON_EXPIRED_TOKEN = 'expired_token';
    const REASON_BOT_CHECK = 'bot_check';
    const REASON_BOT_CHECK_UNAVAILABLE = 'bot_check_unavailable';
    const REASON_LIMIT_PREFIX = 'limit_';

    /** @var bool false — no checks at all, every submission is accepted (the stamp still protects from duplicates) */
    public $enabled = true;

    /**
     * @var string|null HMAC key; by default derived from request->cookieValidationKey. Sites of one installation
     * share that key, so a stamp also names its site and form model: a stamp of one site never confirms another.
     */
    public $secret;

    /** @var int seconds a stamp is valid; the browser takes a new one before it expires */
    public $tokenTtl = 7200;

    /**
     * @var int a form sent sooner than this after the first interaction is marked in the details as fast. Only a
     * signal for the manager: a visitor with autofill is fast too, so it never makes a submission suspicious.
     */
    public $minFillSeconds = 3;

    /**
     * @var bool a submission without a stamp (a direct request, no browser script) is suspicious. During the update
     * of a site with long HTML caching (CDN) set it to false until pages cached before the update expire.
     */
    public $requireToken = true;

    /** @var array|BotCheckInterface|false the bot check; false switches it off */
    public $botCheck = ['class' => PowBotCheck::class];

    /** @var string what to do when the bot check service is unavailable: `suspicious` or `accept` */
    public $botCheckUnavailable = self::STATUS_SUSPICIOUS;

    /**
     * @var array limits of accepted submissions set in the project config: key => [seconds => max count]. Keys:
     * `phone` (the phone of a field of the format «Телефон»), `session` (the visitor session), `ip`. Normally the
     * limits are set by the site in the administration («Конструктор форм → Защита форм», Form2ProtectionSettings);
     * a key given here wins over those settings for that key, [] switches the key off. See limitsForSite().
     *
     * A submission over a limit is saved as suspicious.
     * The limits are approximate, a soft filter and not a quota: they count saved accepted submissions before the
     * new one is saved, so submissions sent at the same moment may pass together. Several people may share one IP
     * (offices, mobile operators), so the IP limit is soft.
     */
    public $limits = [];

    /** @var string the region of a phone written without a country code, for the limit key */
    public $phoneRegion = 'RU';

    /** @var string the name of the hidden trap field (kept for old cached pages and custom templates) */
    public $honeypotField = 'email_confirm';

    /** @var string the name of the hidden field with the stamp and the answer of the bot check */
    public $tokenField = 'form2_protect';

    private $_botCheck;

    /** The limits of a site that has not changed the settings (the defaults of Form2ProtectionSettings). */
    public static function defaultLimits(): array
    {
        return (new Form2ProtectionSettings(['overridePath' => []]))->limits();
    }

    /**
     * The effective limits of a site: its administration settings, then keys of the project config ($limits) over
     * them.
     */
    public function limitsForSite(?int $siteId): array
    {
        return array_merge(Form2ProtectionSettings::forSite($siteId)->limits(), (array)$this->limits);
    }

    public static function reasonLabels(): array
    {
        return [
            self::REASON_HONEYPOT => 'Заполнено скрытое поле-ловушка',
            self::REASON_NO_TOKEN => 'Отправлено без скрипта страницы (нет отметки формы)',
            self::REASON_BAD_TOKEN => 'Поддельная или чужая отметка формы',
            self::REASON_EXPIRED_TOKEN => 'Отметка формы устарела',
            self::REASON_BOT_CHECK => 'Не пройдена проверка на бота',
            self::REASON_BOT_CHECK_UNAVAILABLE => 'Проверка на бота была недоступна',
            self::REASON_LIMIT_PREFIX.'phone' => 'Слишком много заявок с этого телефона',
            self::REASON_LIMIT_PREFIX.'session' => 'Слишком много заявок от одного посетителя',
            self::REASON_LIMIT_PREFIX.'ip' => 'Слишком много заявок с этого IP-адреса',
        ];
    }

    public static function reasonLabel(string $code): string
    {
        return self::reasonLabels()[$code] ?? $code;
    }

    public function getBotCheck(): ?BotCheckInterface
    {
        if ($this->_botCheck === null) {
            if ($this->botCheck === false || $this->botCheck === null) {
                $this->_botCheck = false;
            } else {
                $config = $this->botCheck;
                if (is_array($config)) {
                    $config += ['class' => PowBotCheck::class];
                    if (is_a($config['class'], PowBotCheck::class, true) && !isset($config['secret'])) {
                        $config['secret'] = $this->getSecret();
                    }
                }
                $check = \Yii::createObject($config);
                if (!$check instanceof BotCheckInterface) {
                    throw new InvalidConfigException('form2 protection botCheck must implement '.BotCheckInterface::class);
                }
                $this->_botCheck = $check;
            }
        }

        return $this->_botCheck ?: null;
    }

    public function getSecret(): string
    {
        if ($this->secret) {
            return (string)$this->secret;
        }
        $key = '';
        if (\Yii::$app->has('request') && \Yii::$app->request instanceof Request) {
            $key = (string)\Yii::$app->request->cookieValidationKey;
        }
        if ($key === '') {
            throw new InvalidConfigException('form2 protection needs a secret or request->cookieValidationKey.');
        }

        return hash_hmac('sha256', 'skeeks-form2-protection', $key);
    }

    /**
     * What the browser gets before sending a form: the signed one-time stamp and the bot check task.
     */
    public function issueChallenge(Form2Form $form): array
    {
        $token = $this->createToken($form);
        $result = [
            'token' => $token,
            'ttl' => (int)$this->tokenTtl,
        ];
        if ($this->enabled && ($check = $this->getBotCheck())) {
            $result['check'] = $check->issue($token);
        }

        return $result;
    }

    /** A short identity of a form model class inside the stamp: forms of different models may share an id. */
    public static function modelKey(Form2Form $form): string
    {
        return substr(hash('sha256', get_class($form)), 0, 12);
    }

    public function createToken(Form2Form $form, ?int $time = null): string
    {
        $payload = $this->base64UrlEncode(json_encode([
            's' => (int)$form->cms_site_id,
            'm' => self::modelKey($form),
            'f' => (int)$form->id,
            't' => $time ?? time(),
            'n' => bin2hex(random_bytes(16)),
        ]));

        return $payload.'.'.hash_hmac('sha256', 'token|'.$payload, $this->getSecret());
    }

    /**
     * @return array|null ['s' => site, 'm' => model key, 'f' => form id, 't' => issued at, 'n' => one-time key] of
     *                    a correctly signed stamp
     */
    public function parseToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return null;
        }
        if (!hash_equals(hash_hmac('sha256', 'token|'.$parts[0], $this->getSecret()), $parts[1])) {
            return null;
        }
        $data = json_decode((string)$this->base64UrlDecode($parts[0]), true);
        if (!is_array($data) || !isset($data['s'], $data['m'], $data['f'], $data['t'], $data['n'])
            || !is_int($data['s']) || !is_string($data['m']) || !is_int($data['f']) || !is_int($data['t'])
            || !is_string($data['n']) || !preg_match('/^[a-f0-9]{32}$/', $data['n'])
        ) {
            return null;
        }

        return $data;
    }

    /** The stamp was issued for exactly this form of this site. */
    public static function stampMatches(array $stamp, Form2Form $form): bool
    {
        return $stamp['s'] === (int)$form->cms_site_id
            && $stamp['m'] === self::modelKey($form)
            && $stamp['f'] === (int)$form->id;
    }

    /**
     * Checks of the request itself, before the form values: the trap field, the stamp and the bot check.
     */
    public function checkRequest(Form2Form $form, Request $request): CheckResult
    {
        $result = new CheckResult();

        $raw = $request->post($this->tokenField);
        $payload = null;
        if (is_string($raw) && $raw !== '' && strlen($raw) < 8192) {
            $decoded = json_decode((string)base64_decode($raw, true), true);
            $payload = is_array($decoded) ? $decoded : null;
        }
        $token = $payload && isset($payload['token']) && is_string($payload['token']) ? $payload['token'] : '';
        $stamp = $token !== '' ? $this->parseToken($token) : null;
        $matches = $stamp && self::stampMatches($stamp, $form);

        if ($matches) {
            // The one-time key protects from duplicates even with the checks switched off.
            $result->submitKey = $stamp['n'];
        }

        if (!$this->enabled) {
            return $result;
        }

        $honeypot = $request->post($this->honeypotField);
        if (is_string($honeypot) && trim($honeypot) !== '') {
            $result->addReason(self::REASON_HONEYPOT);
        }

        if ($token === '') {
            if ($this->requireToken) {
                $result->addReason(self::REASON_NO_TOKEN);
            }
            return $result;
        }
        if (!$matches) {
            $result->addReason(self::REASON_BAD_TOKEN);
            return $result;
        }

        $age = time() - $stamp['t'];
        $result->data['fill_seconds'] = $age;
        if ($age < 0 || $age > (int)$this->tokenTtl) {
            $result->addReason(self::REASON_EXPIRED_TOKEN);
        } elseif ($age < (int)$this->minFillSeconds) {
            $result->data['fast_fill'] = true;
        }

        if ($check = $this->getBotCheck()) {
            $answer = $payload['check'] ?? null;
            $verdict = $check->verify($token, $answer);
            $result->data['bot_check'] = array_filter([
                'type' => $check->getType(),
                'status' => $verdict->status,
                'note' => $verdict->note,
            ]);
            if ($verdict->status === BotCheckResult::FAILED) {
                $result->addReason(self::REASON_BOT_CHECK);
            } elseif ($verdict->status === BotCheckResult::UNAVAILABLE
                && $this->botCheckUnavailable !== self::STATUS_ACCEPTED
            ) {
                $result->addReason(self::REASON_BOT_CHECK_UNAVAILABLE);
            }
        }

        return $result;
    }

    /**
     * Approximate limits by the phone, the session and the IP of a submission that is about to be saved (see
     * $limits). Needs the protection schema (Form2FormSend::isProtectionReady()).
     */
    public function checkLimits(CheckResult $result, Form2FormSend $send): void
    {
        if (!$this->enabled || !Form2FormSend::isProtectionReady()) {
            return;
        }
        $columns = [
            'phone' => 'phone',
            'session' => 'session_hash',
            'ip' => 'ip',
        ];
        $now = time();
        $limits = $this->limitsForSite($send->cms_site_id ? (int)$send->cms_site_id : null);
        foreach ($columns as $key => $column) {
            $value = (string)$send->getAttribute($column);
            $rules = $limits[$key] ?? [];
            if ($value === '' || !$rules) {
                continue;
            }
            foreach ($rules as $seconds => $max) {
                $count = (int)Form2FormSend::find()
                    ->andWhere([$column => $value])
                    ->andWhere(['cms_site_id' => $send->cms_site_id])
                    ->andWhere(['>', 'created_at', $now - (int)$seconds])
                    // Only accepted submissions count: junk of a bot with someone's phone must not block that person.
                    ->andWhere(['or', ['check_status' => null], ['check_status' => self::STATUS_ACCEPTED]])
                    ->count();
                if ($count >= (int)$max) {
                    $result->addReason(self::REASON_LIMIT_PREFIX.$key);
                    $result->data['limits'][$key] = ['seconds' => (int)$seconds, 'max' => (int)$max, 'count' => $count];
                    break;
                }
            }
        }
    }

    /**
     * The key of a phone for the limits: E.164 digits by libphonenumber, so «8 (900) 123-45-67» and
     * «+7 900 123-45-67» give one key. Null for a value that is not a valid number.
     */
    public function phoneKey(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        try {
            $util = PhoneNumberUtil::getInstance();
            $number = $util->parse($value, strtoupper((string)$this->phoneRegion));
            if (!$util->isValidNumber($number)) {
                return null;
            }

            return ltrim($util->format($number, PhoneNumberFormat::E164), '+');
        } catch (NumberParseException $e) {
            return null;
        }
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value)
    {
        $value = strtr($value, '-_', '+/');
        $value .= str_repeat('=', (4 - strlen($value) % 4) % 4);

        return base64_decode($value, true);
    }
}
