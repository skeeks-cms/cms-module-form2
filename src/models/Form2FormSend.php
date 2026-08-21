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
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeStorageFile;
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
            [['ip'], 'string', 'max' => 32],
            [['page_url'], 'string', 'max' => 500],
            [['comment'], 'string'],
            [['utms'], 'safe'],
            [['status'], 'in', 'range' => array_keys(self::getStatuses())],

            [
                'data_request',
                'default',
                'value' => function (self $model, $attribute) {
                    return $_REQUEST;
                },
            ],

            [
                'data_server',
                'default',
                'value' => function (self $model, $attribute) {
                    return $_SERVER;
                },
            ],

            [
                'data_cookie',
                'default',
                'value' => function (self $model, $attribute) {
                    return $_COOKIE;
                },
            ],

            [
                'data_session',
                'default',
                'value' => function (self $model, $attribute) {
                    \Yii::$app->session->open();
                    return $_SESSION;
                },
            ],

            [
                'ip',
                'default',
                'value' => function (self $model, $attribute) {
                    return \Yii::$app->request->userIP;
                },
            ],
        ]);
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

        if (!$insert
            || !class_exists(CmsLead::class)
            || !class_exists(CmsLeadService::class)
            || !CmsLead::getDb()->getTableSchema(CmsLead::tableName())
        ) {
            return;
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
            if (preg_match('/телефон|phone|mobile/u', $haystack)) {
                foreach ($this->leadScalarValues($value) as $candidate) {
                    if ($this->isLeadPhone($candidate)) {
                        $phones[$this->leadPhoneKey($candidate)] = ['value' => $candidate, 'name' => $label];
                    }
                }
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
        if (!$phones) {
            foreach ($fields as $field) {
                foreach ($this->leadScalarValues($field['value']) as $candidate) {
                    if ($this->isLeadPhone($candidate)) {
                        $phones[$this->leadPhoneKey($candidate)] = ['value' => $candidate, 'name' => $field['label']];
                    }
                }
            }
        }

        $formName = $this->form ? (string)$this->form->name : '';
        if (!$name) {
            $name = $formName !== '' ? 'Заявка из формы «'.$formName.'»' : 'Заявка из формы №'.$this->form_id;
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
            'cms_site_id' => $this->cms_site_id ? (int)$this->cms_site_id : null,
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

        (new CmsLeadService())->createFromSource([
            'cms_site_id' => $this->cms_site_id ?: null,
            'submitted_by_id' => $this->created_by ?: null,
            'source_type' => CmsLead::SOURCE_FORM,
            'source_ref' => (string)$this->id,
            'source_name' => $formName ?: 'Форма №'.$this->form_id,
            'source_url' => $this->page_url ?: null,
            'source_data' => $sourceData,
            'utm_source' => $utms['utm_source'] ?? $utms['source'] ?? null,
            'utm_medium' => $utms['utm_medium'] ?? $utms['medium'] ?? null,
            'utm_campaign' => $utms['utm_campaign'] ?? $utms['campaign'] ?? null,
            'utm_content' => $utms['utm_content'] ?? $utms['content'] ?? null,
            'utm_term' => $utms['utm_term'] ?? $utms['term'] ?? null,
            'name' => $name,
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

    private function isLeadPhone(string $value): bool
    {
        $digits = preg_replace('/\D+/', '', $value);
        return strlen($digits) >= 10 && strlen($digits) <= 15;
    }

    private function leadPhoneKey(string $value): string
    {
        return preg_replace('/\D+/', '', $value);
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
     * Уведомить всех кого надо и как надо
     */
    public function notify()
    {
        if ($this->form) {
            if ($this->form->emailsAsArray) {
                foreach ($this->form->emailsAsArray as $email) {
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
                                    /**
                                     * @var StorageFile $file
                                     */
                                    foreach ($files as $file) {
                                        $message->attach($file->getRootSrc());
                                    }
                                }
                            } else {
                                if ($file = StorageFile::find()->where(['id' => $value])->one()) {
                                    /**
                                     * @var StorageFile $file
                                     */
                                    $message->attach($file->getRootSrc());
                                }
                            }
                        }
                    }


                    $message->send();
                }
            }
        }
    }
}
