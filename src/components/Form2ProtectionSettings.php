<?php

namespace skeeks\modules\cms\form2\components;

use skeeks\cms\base\Component;
use skeeks\cms\models\CmsSite;
use skeeks\yii2\form\fields\NumberField;

/**
 * «Конструктор форм → Защита форм»: limits of accepted submissions for all forms of a site.
 *
 * Stored as component settings (cms_component_settings): the default site edits the default record, another site
 * its site record (references/component-settings.md of skeeks-cms-package-development). 0 switches a limit off.
 * A key of `modules.form2.protection.limits` in the project config still wins over these settings.
 */
class Form2ProtectionSettings extends Component
{
    /** Accepted submissions with one phone (a field of the format «Телефон») per day. */
    public $phonePerDay = 3;

    /** Accepted submissions from one visitor session per hour. */
    public $sessionPerHour = 5;

    /** Accepted submissions from one IP address per hour. */
    public $ipPerHour = 3;

    /** Accepted submissions from one IP address per day. */
    public $ipPerDay = 10;

    private static $_cache = [];

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['phonePerDay', 'sessionPerHour', 'ipPerHour', 'ipPerDay'], 'required'],
            [['phonePerDay', 'sessionPerHour', 'ipPerHour', 'ipPerDay'], 'integer', 'min' => 0, 'max' => 100000],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'phonePerDay' => 'С одного телефона — заявок в сутки',
            'sessionPerHour' => 'От одного посетителя — заявок в час',
            'ipPerHour' => 'С одного IP-адреса — заявок в час',
            'ipPerDay' => 'С одного IP-адреса — заявок в сутки',
        ]);
    }

    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'phonePerDay' => 'Телефон берётся только из полей с форматом «Телефон». 0 — без ограничения.',
            'sessionPerHour' => 'Посетитель — одна сессия браузера. 0 — без ограничения.',
            'ipPerHour' => 'С одного адреса могут писать несколько человек (офис, мобильный оператор), поэтому лимит по IP лучше не делать слишком строгим. 0 — без ограничения.',
            'ipPerDay' => '0 — без ограничения.',
        ]);
    }

    /** Own form id: the work timer form in the admin header is also «w0». */
    public function beginConfigForm()
    {
        return \skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab::begin([
            'id' => 'sx-form2-protection-settings',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false,
        ]);
    }

    public function getConfigFormFields()
    {
        return [
            'phonePerDay' => ['class' => NumberField::class],
            'sessionPerHour' => ['class' => NumberField::class],
            'ipPerHour' => ['class' => NumberField::class],
            'ipPerDay' => ['class' => NumberField::class],
        ];
    }

    /**
     * The limits in the format of FormProtection::$limits: key => [seconds => max]; a switched off limit is left out,
     * a key with no limits is [].
     */
    public function limits(): array
    {
        $pick = static function (array $rules): array {
            return array_filter($rules, static function ($max) {
                return (int)$max > 0;
            });
        };

        return [
            'phone' => $pick([86400 => (int)$this->phonePerDay]),
            'session' => $pick([3600 => (int)$this->sessionPerHour]),
            'ip' => $pick([3600 => (int)$this->ipPerHour, 86400 => (int)$this->ipPerDay]),
        ];
    }

    /**
     * The effective settings of a site, read as the administration of that site sees them (default → site). Cached
     * per site within a request; reset() after saving.
     */
    public static function forSite(?int $siteId): self
    {
        $key = (int)$siteId;
        if (!isset(self::$_cache[$key])) {
            $settings = new self();
            $site = $siteId ? CmsSite::findOne($siteId) : null;
            if ($site && !$site->is_default) {
                $settings->overridePath = [self::OVERRIDE_DEFAULT, self::OVERRIDE_SITE];
                $settings->cmsSite = $site;
            } else {
                $settings->overridePath = [self::OVERRIDE_DEFAULT];
            }
            try {
                $settings->refresh();
            } catch (\Throwable $e) {
                // Without the settings table (an old CMS, a broken install) the class defaults apply.
                \Yii::warning('Form2: protection settings are not readable: '.$e->getMessage(), 'form2');
            }
            self::$_cache[$key] = $settings;
        }

        return self::$_cache[$key];
    }

    public static function reset(): void
    {
        self::$_cache = [];
    }
}
