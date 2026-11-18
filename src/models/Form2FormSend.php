<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */

namespace skeeks\modules\cms\form2\models;

use skeeks\cms\helpers\Request;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\behaviors\HasRelatedProperties;
use skeeks\cms\models\behaviors\Serialize;
use skeeks\cms\models\behaviors\traits\HasRelatedPropertiesTrait;
use skeeks\cms\models\CmsLead;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\cms\services\CmsLeadService;
use skeeks\cms\models\StorageFile;
use skeeks\cms\models\User;
use skeeks\cms\relatedProperties\models\RelatedElementModel;
use skeeks\cms\relatedProperties\PropertyType;
use yii\helpers\Html;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeStorageFile;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText;
use skeeks\modules\cms\form2\Module;
use skeeks\modules\cms\form2\protection\FormProtection;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%form2_form_send}}".
 *
 * @property integer                 $id
 * @property integer                 $created_by
 * @property integer                 $updated_by
 * @property integer                 $created_at
 * @property integer                 $updated_at
 * @property integer                 $processed_at
 * @property integer                 $processed_by
 * @property string                  $data_values
 * @property string                  $data_labels
 * @property string                  $emails
 * @property int                     $cms_site_id
 * @property string                  $comment
 * @property string                  $phones
 * @property string                  $user_ids
 * @property string                  $email_message
 * @property string                  $phone_message
 * @property integer                 $status
 * @property integer                 $form_id
 * @property string                  $ip
 * @property string                  $page_url
 * @property string                  $data_server
 * @property string                  $data_session
 * @property string                  $data_cookie
 * @property string                  $data_request
 * @property string                  $additional_data
 * @property array                   $utms
 * @property string|null             $check_status  accepted, suspicious; null — before the protection
 * @property string|null             $check_reasons codes of the reasons, comma separated
 * @property string|null             $check_data    details of the checks (JSON)
 * @property string|null             $submit_key    the one-time key of the form stamp
 * @property string|null             $phone         the normalized main phone
 * @property string|null             $session_hash
 * @property string|null             $client_id     the Metrika ClientID
 * @property string|null             $user_agent
 *
 * @property User                    $processedBy
 * @property Form2Form               $form
 *
 * @property Form2FormSendProperty[] relatedElementProperties
 * @property Form2FormProperty[]     relatedProperties
 * @property CmsSite                 $cmsSite
 */
class Form2FormSend extends RelatedElementModel
{
    const STATUS_NEW = 0;
    const STATUS_PROCESSED = 5;
    const STATUS_EXECUTED = 10;

    /**
     * A submission was accepted — automatically when it passed the checks, or by hand from «suspicious». Fires
     * exactly once per submission, after the commit, never for a suspicious one. Project integrations with side
     * effects (SMS, CRM, webhooks) listen here instead of BackendController::_afterSave():
     * `Event::on(Form2FormSend::class, Form2FormSend::EVENT_ACCEPTED, fn ($e) => …$e->sender…)`.
     */
    const EVENT_ACCEPTED = 'form2Accepted';

    /** Columns of the protection schema (migration m261118_100000). */
    const PROTECTION_COLUMNS = ['check_status', 'check_reasons', 'check_data', 'submit_key', 'phone', 'session_hash', 'client_id', 'user_agent'];

    static public function getStatuses()
    {
        return [
            self::STATUS_NEW       => \Yii::t('skeeks/form2/app', 'New message'),
            self::STATUS_PROCESSED => \Yii::t('skeeks/form2/app', 'In progress'),
            self::STATUS_EXECUTED  => \Yii::t('skeeks/form2/app', 'Completed'),
        ];
    }


    use HasRelatedPropertiesTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form2_form_send}}';
    }


    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

            HasRelatedProperties::className() => [
                'class'                           => HasRelatedProperties::className(),
                'relatedElementPropertyClassName' => Form2FormSendProperty::className(),
                'relatedPropertyClassName'        => Form2FormProperty::className(),
                //'relatedPropertyEnumClassName'    => Form2FormPropertyEnum::className(),
            ],

            Serialize::className() =>
                [
                    'class'  => Serialize::className(),
                    'fields' => ['data_labels', 'data_values', 'data_server', 'data_session', 'data_cookie', 'additional_data', 'data_request'],
                ],

            HasJsonFieldsBehavior::className() =>
                [
                    'class'  => HasJsonFieldsBehavior::className(),
                    'fields' => ['utms'],
                ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'processed_by', 'processed_at', 'status', 'form_id', 'cms_site_id'], 'integer'],
            [['email_message', 'phone_message'], 'string'],
            [['data_labels', 'data_values', 'data_server', 'data_session', 'data_cookie', 'additional_data', 'data_request'], 'safe'],
            [['emails', 'phones', 'user_ids'], 'string'],
            [['ip'], 'string', 'max' => 45],
            [['page_url'], 'string', 'max' => 500],
            [['comment'], 'string'],
            [['utms'], 'safe'],
            [['status'], 'in', 'range' => array_keys(self::getStatuses())],

            // Only a short allowlist of the request: full $_SERVER, cookies, the session and the request are not
            // stored any more (session cookie, CSRF token, server environment). Old submissions keep their dumps.
            [
                'data_server',
                'default',
                'value' => function (self $model, $attribute) {
                    return self::requestServerAllowlist();
                },
            ],

            [
                'ip',
                'default',
                'value' => function (self $model, $attribute) {
                    return \Yii::$app->request->userIP;
                },
            ],
        ], $this->protectionRules());
    }

    /** The fields of $_SERVER kept with a submission. */
    public static function requestServerAllowlist(): array
    {
        $result = [];
        foreach (['REQUEST_METHOD', 'HTTP_USER_AGENT', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_REFERER', 'REMOTE_ADDR'] as $key) {
            if (isset($_SERVER[$key]) && is_scalar($_SERVER[$key])) {
                $result[$key] = mb_substr((string)$_SERVER[$key], 0, 500);
            }
        }

        return $result;
    }

    /**
     * The protection schema is complete: every column of m261118_100000, the unique index of submit_key and the
     * notification table of m261118_110000. Until then submissions work as before the protection: nothing reads or
     * writes the new columns, a suspicious request is not saved (see BackendController).
     */
    public static function isProtectionReady(): bool
    {
        $db = self::getDb();
        $key = $db->dsn.'|'.$db->tablePrefix;
        if (!array_key_exists($key, self::$_protectionReady)) {
            self::$_protectionReady[$key] = self::detectProtectionSchema();
        }

        return self::$_protectionReady[$key];
    }

    /** Forget the cached schema state (tests, migrations in the same process). */
    public static function resetProtectionReady(): void
    {
        self::$_protectionReady = [];
    }

    private static $_protectionReady = [];

    private static function detectProtectionSchema(): bool
    {
        try {
            $db = self::getDb();
            $schema = $db->getTableSchema(self::tableName(), true);
            if (!$schema) {
                return false;
            }
            foreach (self::PROTECTION_COLUMNS as $column) {
                if ($schema->getColumn($column) === null) {
                    return false;
                }
            }
            $unique = false;
            foreach ($db->getSchema()->findUniqueIndexes($schema) as $columns) {
                if ($columns === ['submit_key']) {
                    $unique = true;
                    break;
                }
            }

            return $unique && Form2FormSendNotification::isTableReady();
        } catch (\Throwable $e) {
            \Yii::warning('Form2: protection schema check failed: '.$e->getMessage(), 'form2');
            return false;
        }
    }

    /**
     * Rules of the protection columns, only with the complete protection schema: the code of a package may reach a
     * site before its migration.
     */
    private function protectionRules(): array
    {
        if (!self::isProtectionReady()) {
            return [];
        }

        return [
            [['check_status'], 'in', 'range' => [FormProtection::STATUS_ACCEPTED, FormProtection::STATUS_SUSPICIOUS]],
            [['check_reasons'], 'string', 'max' => 255],
            [['check_data'], 'string'],
            [['submit_key', 'session_hash', 'client_id'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 20],
            [['user_agent'], 'string', 'max' => 500],
            [['submit_key', 'phone', 'session_hash', 'client_id', 'user_agent', 'check_reasons', 'check_data'], 'default', 'value' => null],
            [
                'session_hash',
                'default',
                'value' => function () {
                    if (!\Yii::$app->has('session')) {
                        return null;
                    }
                    \Yii::$app->session->open();
                    $id = (string)\Yii::$app->session->id;

                    return $id !== '' ? hash('sha256', $id) : null;
                },
            ],
            [
                'user_agent',
                'default',
                'value' => function () {
                    $agent = trim((string)\Yii::$app->request->userAgent);

                    return $agent !== '' ? mb_substr($agent, 0, 500) : null;
                },
            ],
            [
                'client_id',
                'default',
                'value' => function () {
                    // The Metrika ClientID already set by the counter; nothing new is collected.
                    $uid = $_COOKIE['_ym_uid'] ?? '';

                    return is_string($uid) && preg_match('/^\d{5,40}$/', $uid) ? $uid : null;
                },
            ],
        ];
    }

    /** A submission that failed the protection checks: saved, but without notifications and a CRM deal. */
    public function isSuspicious(): bool
    {
        return self::isProtectionReady()
            && $this->getAttribute('check_status') === FormProtection::STATUS_SUSPICIOUS;
    }

    /** @return string[] codes of the reasons of suspicion */
    public function getCheckReasonCodes(): array
    {
        if (!self::isProtectionReady()) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', (string)$this->getAttribute('check_reasons')))));
    }

    public function getCheckDetails(): array
    {
        if (!self::isProtectionReady() || !$this->getAttribute('check_data')) {
            return [];
        }
        $data = json_decode((string)$this->getAttribute('check_data'), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Accepts a suspicious submission by hand, atomically: only the request that really changes the status gets the
     * consequences (the CRM deal, the notifications, EVENT_ACCEPTED). A concurrent or repeated request returns false
     * and changes nothing.
     */
    public function accept(?int $userId = null): bool
    {
        if (!self::isProtectionReady()) {
            return false;
        }
        $db = self::getDb();
        $transaction = $db->beginTransaction();
        try {
            $changed = $db->createCommand()->update(self::tableName(),
                ['check_status' => FormProtection::STATUS_ACCEPTED, 'updated_at' => time()],
                ['id' => (int)$this->id, 'check_status' => FormProtection::STATUS_SUSPICIOUS]
            )->execute();
            if ($changed !== 1) {
                $transaction->rollBack();
                $this->refresh();
                return false;
            }
            $this->refresh();
            $details = $this->getCheckDetails();
            $details['accepted_by'] = $userId;
            $details['accepted_at'] = time();
            $checkData = json_encode($details, JSON_UNESCAPED_UNICODE);
            $db->createCommand()->update(self::tableName(), ['check_data' => $checkData], ['id' => (int)$this->id])->execute();
            $this->setAttribute('check_data', $checkData);
            $this->setOldAttribute('check_data', $checkData);

            $this->ensureCmsLead();
            $this->queueNotifications();
            $transaction->commit();
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $e;
        }

        $this->afterAccepted();

        return true;
    }

    /**
     * Creates the notification rows of an accepted submission (one per recipient). Call inside the transaction that
     * accepts it; repeated calls add only missing recipients.
     */
    public function queueNotifications(): void
    {
        if (!Form2FormSendNotification::isTableReady() || !$this->form) {
            return;
        }
        // Recipients are compared as the unique index of the table does (case-insensitive collation): one address
        // written twice in different case is one recipient, never a duplicate key that rolls the submission back.
        $existing = [];
        foreach (Form2FormSendNotification::find()
            ->select('recipient')
            ->andWhere(['form_send_id' => (int)$this->id, 'channel' => Form2FormSendNotification::CHANNEL_EMAIL])
            ->column() as $recipient) {
            $existing[Form2FormSendNotification::recipientKey((string)$recipient)] = true;
        }
        $now = time();
        foreach ($this->form->emailsAsArray as $email) {
            $email = trim((string)$email);
            $key = Form2FormSendNotification::recipientKey($email);
            if ($email === '' || isset($existing[$key]) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $existing[$key] = true;
            self::getDb()->createCommand()->insert(Form2FormSendNotification::tableName(), [
                'form_send_id' => (int)$this->id,
                'channel' => Form2FormSendNotification::CHANNEL_EMAIL,
                'recipient' => $email,
                'status' => Form2FormSendNotification::STATUS_PENDING,
                'attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ])->execute();
        }
    }

    /**
     * What follows the acceptance of a submission, automatic or manual, after its transaction: delivery of the
     * queued notifications and EVENT_ACCEPTED for project integrations.
     */
    public function afterAccepted(): void
    {
        $this->deliverNotifications();
        $this->trigger(self::EVENT_ACCEPTED, new Event());
    }

    /**
     * Delivers pending and failed notifications. Each row is claimed atomically, so a recipient already sent is
     * never sent again and two deliveries never send one row twice.
     *
     * A row left «sending» longer than STALE_SENDING_SECONDS means the process stopped between handing the letter to
     * the mailer and recording the result: the letter may have gone. It is never sent again automatically — it becomes
     * «Результат неизвестен» (STATUS_UNKNOWN) for a person to check with the recipient.
     *
     * @return array [sent, failed] counts of this call
     */
    public function deliverNotifications(): array
    {
        if (!Form2FormSendNotification::isTableReady()) {
            $this->notifyLegacy();
            return [0, 0];
        }
        $sent = 0;
        $failed = 0;
        $db = self::getDb();
        $table = Form2FormSendNotification::tableName();

        $db->createCommand()->update($table,
            [
                'status' => Form2FormSendNotification::STATUS_UNKNOWN,
                'updated_at' => time(),
                'last_error' => 'Отправка прервалась: неизвестно, ушло ли письмо. Уточните у получателя.',
            ],
            ['and',
                ['form_send_id' => (int)$this->id],
                ['status' => Form2FormSendNotification::STATUS_SENDING],
                ['<', 'updated_at', time() - Form2FormSendNotification::STALE_SENDING_SECONDS],
            ]
        )->execute();

        $rows = Form2FormSendNotification::find()
            ->andWhere(['form_send_id' => (int)$this->id])
            ->andWhere(['status' => [Form2FormSendNotification::STATUS_PENDING, Form2FormSendNotification::STATUS_FAILED]])
            ->all();
        foreach ($rows as $row) {
            $claimed = $db->createCommand()->update($table,
                [
                    'status' => Form2FormSendNotification::STATUS_SENDING,
                    'attempts' => new Expression('attempts + 1'),
                    'updated_at' => time(),
                ],
                ['id' => (int)$row->id, 'status' => [Form2FormSendNotification::STATUS_PENDING, Form2FormSendNotification::STATUS_FAILED]]
            )->execute();
            if ($claimed !== 1) {
                continue;
            }

            $error = null;
            try {
                if (!$this->sendNotificationEmail((string)$row->recipient)) {
                    $error = 'Почтовый сервис не принял письмо.';
                }
            } catch (\Throwable $e) {
                \Yii::error($e, 'form2');
                $error = mb_substr('Ошибка отправки: '.$e->getMessage(), 0, 500);
            }

            $db->createCommand()->update($table, $error === null
                ? ['status' => Form2FormSendNotification::STATUS_SENT, 'sent_at' => time(), 'updated_at' => time(), 'last_error' => null]
                : ['status' => Form2FormSendNotification::STATUS_FAILED, 'updated_at' => time(), 'last_error' => $error],
                ['id' => (int)$row->id]
            )->execute();
            $error === null ? $sent++ : $failed++;
        }

        return [$sent, $failed];
    }

    /**
     * A value of a field for the administration (the card and the list columns). Values come from visitors: the base
     * getAsHtml() of a property type returns raw text, which is escaped here; a type that overrides getAsHtml() builds
     * its own safe markup (file links) and keeps it.
     */
    public function propertyValueHtml(string $code): string
    {
        $model = $this->relatedPropertiesModel;
        $property = $model->getRelatedProperty($code);
        if (!$property) {
            return '';
        }
        $handler = $property->handler;
        if ($handler && (new \ReflectionMethod($handler, 'getAsHtml'))->getDeclaringClass()->getName() !== PropertyType::class) {
            return (string)$model->getAttributeAsHtml($code);
        }

        return nl2br(Html::encode((string)$model->getAttributeAsText($code)));
    }

    /** @return \yii\db\ActiveQuery */
    public function getNotifications()
    {
        return $this->hasMany(Form2FormSendNotification::class, ['form_send_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }

    /**
     * A field of the format «Телефон» (skeeks/cms PropertyTypeText::FIELD_PHONE). Fields of forms are arbitrary, so
     * a phone is recognized only by this explicit format, never by a name.
     */
    public function isPhoneFormatField(string $code): bool
    {
        if (!defined(PropertyTypeText::class.'::FIELD_PHONE')) {
            return false;
        }
        $property = $this->relatedPropertiesModel->getRelatedProperty($code);
        $handler = $property ? $property->handler : null;

        return $handler instanceof PropertyTypeText && $handler->fieldElement === PropertyTypeText::FIELD_PHONE;
    }

    /**
     * Phones of fields of the format «Телефон» with their limit keys: [key => ['value' => …, 'name' => label]].
     */
    public function phoneFormatValues(array $values, array $labels): array
    {
        $protection = Module::protection();
        $result = [];
        foreach ($values as $code => $value) {
            if (!is_string($value) || trim($value) === '' || !$this->isPhoneFormatField((string)$code)) {
                continue;
            }
            $key = $protection->phoneKey($value);
            if ($key !== null && !isset($result[$key])) {
                $result[$key] = ['value' => trim($value), 'name' => trim((string)($labels[$code] ?? $code))];
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'              => \Yii::t('skeeks/form2/app', 'ID'),
            'processed_by'    => \Yii::t('skeeks/form2/app', 'Who handled'),
            'data_values'     => \Yii::t('skeeks/form2/app', 'Data Values'),
            'data_labels'     => \Yii::t('skeeks/form2/app', 'Data Labels'),
            'emails'          => \Yii::t('skeeks/form2/app', 'Emails'),
            'phones'          => \Yii::t('skeeks/form2/app', 'Phones'),
            'user_ids'        => \Yii::t('skeeks/form2/app', 'User Ids'),
            'email_message'   => \Yii::t('skeeks/form2/app', 'Email Message'),
            'phone_message'   => \Yii::t('skeeks/form2/app', 'Phone Message'),
            'status'          => \Yii::t('skeeks/form2/app', 'Status'),
            'form_id'         => \Yii::t('skeeks/form2/app', 'Form'),
            'ip'              => \Yii::t('skeeks/form2/app', 'Ip'),
            'page_url'        => \Yii::t('skeeks/form2/app', 'Page Url'),
            'data_server'     => \Yii::t('skeeks/form2/app', 'Data Server'),
            'data_session'    => \Yii::t('skeeks/form2/app', 'Data Session'),
            'data_cookie'     => \Yii::t('skeeks/form2/app', 'Data Cookie'),
            'data_request'    => \Yii::t('skeeks/form2/app', 'Data Request'),
            'additional_data' => \Yii::t('skeeks/form2/app', 'Additional Data'),
            'cms_site_id'     => \Yii::t('skeeks/form2/app', 'Site'),
            'processed_at'    => \Yii::t('skeeks/form2/app', 'When handled'),
            'comment'         => \Yii::t('skeeks/form2/app', 'Комментарий менеджера'),
            'utms'            => \Yii::t('skeeks/form2/app', 'Utm метки'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProcessedBy()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'processed_by']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm()
    {
        return $this->hasOne(Form2Form::className(), ['id' => 'form_id']);
    }

    /**
     * @return CmsSite
     * @deprecated
     */
    public function getSite()
    {
        //return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
        return CmsSite::getById($this->cms_site_id);
    }

    /**
     * @return CmsSite
     */
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'cms_site_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && !$this->isSuspicious()) {
            $this->ensureCmsLead();
        }
    }

    public function getLeadSiteId(): ?int
    {
        if ($this->cms_site_id) {
            return (int)$this->cms_site_id;
        }
        if ($this->form && $this->form->cms_site_id) {
            return (int)$this->form->cms_site_id;
        }
        if (\Yii::$app->has('skeeks') && \Yii::$app->skeeks->site) {
            return (int)\Yii::$app->skeeks->site->id;
        }

        return null;
    }

    public function isCmsLeadIntegrationAvailable(): bool
    {
        if (!class_exists(CmsLead::class) || !class_exists(CmsLeadService::class)) {
            return false;
        }

        try {
            return CmsLead::getDb()->getTableSchema(CmsLead::tableName()) !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getCmsLead(): ?CmsLead
    {
        if (!$this->id || !$this->isCmsLeadIntegrationAvailable()) {
            return null;
        }

        // The channel of the form may have changed since: the deal is looked up by every channel of a form.
        foreach (self::leadSourceTypes() as $type) {
            $lead = CmsLead::find()->fromSource($type, (string)$this->id, $this->leadSiteId)->one();
            if ($lead) {
                return $lead;
            }
        }

        return null;
    }

    /**
     * Channels of deals made from submissions: «Форма» and, with a core that has it, «Визит в салон / офис».
     *
     * @return string[]
     */
    public static function leadSourceTypes(): array
    {
        return array_values(array_filter([
            CmsLead::SOURCE_FORM,
            defined(CmsLead::class.'::SOURCE_VISIT') ? CmsLead::SOURCE_VISIT : null,
        ]));
    }

    /** The channel of the deal of this submission: the setting of its form (Form2Form::$lead_source_type), else «Форма». */
    public function leadSourceType(): string
    {
        $form = $this->form;
        $type = $form && $form->hasAttribute('lead_source_type') ? (string)$form->lead_source_type : '';

        return $type !== '' && in_array($type, self::leadSourceTypes(), true) ? $type : CmsLead::SOURCE_FORM;
    }

    public function getDisplayName(): string
    {
        $formName = $this->form ? trim((string)$this->form->name) : '';

        return ($formName !== '' ? $formName : 'Отправка формы').' №'.(int)$this->id;
    }

    public function ensureCmsLead(): ?CmsLead
    {
        if (!$this->isCmsLeadIntegrationAvailable()) {
            return null;
        }
        // A suspicious submission gets a deal only through acceptance (accept()), never directly.
        if ($this->isSuspicious()) {
            return null;
        }

        $values = is_array($this->data_values) ? $this->data_values : [];
        $labels = is_array($this->data_labels) ? $this->data_labels : [];
        $description = ['ID отправки формы: '.$this->id];
        $fields = [];
        $name = null;
        $phones = [];
        $emails = [];

        foreach ($values as $code => $value) {
            $label = trim((string)($labels[$code] ?? $code));
            $text = $this->leadValueAsText($value);
            if ($text === '') {
                continue;
            }

            $fields[] = [
                'code' => (string)$code,
                'label' => $label,
                'value' => $value,
                'as_text' => $text,
            ];
            $description[] = $label.': '.$text;
            $haystack = mb_strtolower((string)$code.' '.$label);
            if ($name === null && preg_match('/(^|\s)(имя|фио|название|name|title)(\s|$)|обраща|контактн(?:ое|ый)?\s+лиц/u', $haystack)) {
                $name = $text;
            }
            if (preg_match('/e-?mail|почт/u', $haystack)) {
                foreach ($this->leadScalarValues($value) as $candidate) {
                    if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                        $emails[mb_strtolower($candidate, 'UTF-8')] = ['value' => $candidate, 'name' => $label];
                    }
                }
            }
        }

        if (!$emails) {
            foreach ($fields as $field) {
                foreach ($this->leadScalarValues($field['value']) as $candidate) {
                    if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                        $emails[mb_strtolower($candidate, 'UTF-8')] = ['value' => $candidate, 'name' => $field['label']];
                    }
                }
            }
        }
        // Phones of the deal come only from fields of the format «Телефон» (the number is already validated by the
        // field); a field name or a number-like value of an arbitrary field never makes a phone.
        $phones = $this->phoneFormatValues($values, $labels);

        $formName = $this->form ? (string)$this->form->name : '';
        $leadName = $formName !== '' ? 'Форма на сайте «'.$formName.'»' : 'Форма на сайте №'.$this->form_id;
        if ($this->id) {
            $leadName .= ' №'.$this->id;
        }
        $mainPhone = $phones ? reset($phones)['value'] : null;
        $nameParts = array_values(array_filter([
            $name ? trim((string)$name) : '',
            $mainPhone ? trim((string)$mainPhone) : '',
        ], static fn(string $part): bool => $part !== ''));
        if ($nameParts) {
            $leadName .= ': '.implode(', ', $nameParts);
        }
        if ($this->page_url) {
            $description[] = 'Страница отправки: '.$this->page_url;
        }

        $utms = is_array($this->utms) ? $this->utms : [];
        foreach ($this->leadUtmValues($utms) as $label => $value) {
            $description[] = $label.': '.$value;
        }

        $sourceData = [
            'form_id' => (int)$this->form_id,
            'form_code' => $this->form ? (string)$this->form->code : null,
            'form_name' => $formName ?: null,
            'form_send_id' => (int)$this->id,
            'cms_site_id' => $this->leadSiteId,
            'created_at' => $this->created_at ? (int)$this->created_at : null,
            'created_by' => $this->created_by ? (int)$this->created_by : null,
            'ip' => $this->ip ?: null,
            'page_url' => $this->page_url ?: null,
            'labels' => $labels,
            'values' => $values,
            'fields' => $fields,
            'utm' => $utms,
            'request' => $this->leadRequestMetadata(),
        ];
        if (is_array($this->additional_data) && $this->additional_data) {
            $sourceData['additional_data'] = $this->additional_data;
        }

        return (new CmsLeadService())->createFromSource([
            'cms_site_id' => $this->leadSiteId,
            'submitted_by_id' => $this->created_by ?: null,
            'source_type' => $this->leadSourceType(),
            'source_ref' => (string)$this->id,
            'source_name' => $formName ?: 'Форма №'.$this->form_id,
            'source_url' => $this->page_url ?: null,
            'source_data' => $sourceData,
            'utm_source' => $utms['utm_source'] ?? $utms['source'] ?? null,
            'utm_medium' => $utms['utm_medium'] ?? $utms['medium'] ?? null,
            'utm_campaign' => $utms['utm_campaign'] ?? $utms['campaign'] ?? null,
            'utm_content' => $utms['utm_content'] ?? $utms['content'] ?? null,
            'utm_term' => $utms['utm_term'] ?? $utms['term'] ?? null,
            'name' => $leadName,
            'phones' => array_values($phones),
            'emails' => array_values($emails),
            'description' => $description ? implode("\n", $description) : null,
            'status' => CmsLead::STATUS_NEW,
        ]);
    }

    public function transactions()
    {
        return [self::SCENARIO_DEFAULT => self::OP_INSERT];
    }

    private function leadValueAsText($value): string
    {
        if (is_scalar($value)) {
            return trim((string)$value);
        }
        if (is_array($value)) {
            $parts = [];
            array_walk_recursive($value, static function ($item) use (&$parts) {
                if (is_scalar($item) && trim((string)$item) !== '') {
                    $parts[] = trim((string)$item);
                }
            });
            return implode(', ', array_unique($parts));
        }

        return '';
    }

    private function leadScalarValues($value): array
    {
        if (is_scalar($value)) {
            $value = trim((string)$value);
            return $value === '' ? [] : [$value];
        }
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        array_walk_recursive($value, static function ($item) use (&$result) {
            if (is_scalar($item) && trim((string)$item) !== '') {
                $result[] = trim((string)$item);
            }
        });
        return array_values(array_unique($result));
    }

    private function leadUtmValues(array $utms): array
    {
        $result = [];
        foreach ([
            'UTM source' => ['utm_source', 'source'],
            'UTM medium' => ['utm_medium', 'medium'],
            'UTM campaign' => ['utm_campaign', 'campaign'],
            'UTM content' => ['utm_content', 'content'],
            'UTM term' => ['utm_term', 'term'],
        ] as $label => $keys) {
            foreach ($keys as $key) {
                $value = isset($utms[$key]) ? trim((string)$utms[$key]) : '';
                if ($value !== '') {
                    $result[$label] = $value;
                    break;
                }
            }
        }

        return $result;
    }

    private function leadRequestMetadata(): array
    {
        $server = is_array($this->data_server) ? $this->data_server : [];
        $result = [];
        foreach ([
            'REQUEST_METHOD' => 'method',
            'HTTP_USER_AGENT' => 'user_agent',
            'HTTP_ACCEPT_LANGUAGE' => 'accept_language',
            'HTTP_REFERER' => 'referer',
            'REMOTE_ADDR' => 'remote_address',
        ] as $source => $target) {
            if (isset($server[$source]) && is_scalar($server[$source])) {
                $value = trim((string)$server[$source]);
                if ($value !== '') {
                    $result[$target] = $value;
                }
            }
        }

        return $result;
    }

    /**
     *
     * Все возможные свойства связанные с моделью
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRelatedProperties()
    {
        //return $this->form->form2FormProperties;
        return $this->hasMany(Form2FormProperty::className(), ['form_id' => 'id'])
            ->via('form')->orderBy(['priority' => SORT_ASC]);
    }


    /**
     * @return array
     */
    public function getEmailsAsArray()
    {
        $emailsAll = [];
        if ($this->emails) {
            $emails = explode(",", $this->emails);

            foreach ($emails as $email) {
                $emailsAll[] = trim($email);
            }
        }

        return $emailsAll;
    }

    /**
     * Notifies the recipients of the form. With the protection schema it queues one notification per recipient and
     * delivers them (a recipient already notified is not notified again); without it — the old direct sending.
     * A suspicious submission is never notified.
     */
    public function notify()
    {
        if ($this->isSuspicious()) {
            return;
        }
        if (!Form2FormSendNotification::isTableReady()) {
            $this->notifyLegacy();
            return;
        }
        $this->queueNotifications();
        $this->deliverNotifications();
    }

    /** The sending before the notification table (migration m261118_110000), one letter per recipient. */
    protected function notifyLegacy(): void
    {
        if (!$this->form) {
            return;
        }
        $seen = [];
        foreach ($this->form->emailsAsArray as $email) {
            $email = trim((string)$email);
            $key = Form2FormSendNotification::recipientKey($email);
            if ($email === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            try {
                if (!$this->sendNotificationEmail($email)) {
                    \Yii::error('Form2: the mailer did not accept the notification of #'.$this->id.' to '.$email, 'form2');
                }
            } catch (\Throwable $e) {
                \Yii::error($e, 'form2');
            }
        }
    }

    /**
     * The letter about this submission to one recipient.
     *
     * @return bool what the mailer returned
     */
    public function sendNotificationEmail(string $email): bool
    {
        \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/modules/cms/form2/mail';

        $message = \Yii::$app->mailer->compose('send-message', [
            'form'     => $this->form,
            'formSend' => $this,
        ])
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
            ->setTo($email)
            ->setSubject(\Yii::t('skeeks/form2/app', 'Submitting form').": «{$this->form->name}» #".$this->id);

        $rp = $this->relatedPropertiesModel;
        $rp->initAllProperties();

        foreach ($rp->toArray() as $code => $value) {
            $property = $rp->getRelatedProperty($code);

            if ($property && $property->handler instanceof PropertyTypeStorageFile) {
                if ($property->handler->isMultiple) {
                    if ($files = StorageFile::find()->where(['id' => $value])->all()) {
                        /** @var StorageFile $file */
                        foreach ($files as $file) {
                            $message->attach($file->getRootSrc());
                        }
                    }
                } else {
                    if ($file = StorageFile::find()->where(['id' => $value])->one()) {
                        /** @var StorageFile $file */
                        $message->attach($file->getRootSrc());
                    }
                }
            }
        }

        return (bool)$message->send();
    }
}
