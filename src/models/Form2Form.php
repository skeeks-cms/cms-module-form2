<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */

namespace skeeks\modules\cms\form2\models;

use skeeks\cms\models\Core;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\validators\EmailValidator;

/**
 * This is the model class for table "{{%form2_form}}".
 *
 * @property integer             $id
 * @property integer             $created_by
 * @property integer             $updated_by
 * @property integer             $created_at
 * @property integer             $updated_at
 * @property integer             $cms_site_id
 * @property string              $name
 * @property string              $description
 * @property string              $code
 * @property string              $emails
 * @property string              $phones
 * @property string              $user_ids
 * @property bool                $is_add_legal_checkbox
 * @property string              $legal_checkbox_template
 *
 * @property array               $emailsAsArray
 *
 * @property CmsSite             $cmsSite
 * @property Form2FormSend[]     $form2FormSends
 * @property Form2FormProperty[] $form2FormProperties
 * @property string $legalCheckboxText
 */
class Form2Form extends Core
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form2_form}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'required'],

            [['description'], 'string'],

            [['legal_checkbox_template'], 'string'],

            [['is_add_legal_checkbox'], 'integer'],
            [['is_add_legal_checkbox'], 'default', 'value' => 1],

            [['emails', 'phones', 'user_ids'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],

            [['cms_site_id'], 'integer'],
            [['cms_site_id'], 'default', 'value' => \Yii::$app->skeeks->site->id],

            [['code'], 'unique', 'targetAttribute' => ['code', 'cms_site_id']],

            // The channel of the deal a submission creates: empty — «Форма», `visit` — «Визит в салон / офис».
            [['lead_source_type'], 'filter', 'filter' => static fn($value) => $value === '' ? null : $value,
                'when' => static fn(self $model) => $model->hasAttribute('lead_source_type')],
            [['lead_source_type'], 'in', 'range' => array_keys(self::leadSourceTypeItems()),
                'when' => static fn(self $model) => $model->hasAttribute('lead_source_type')],

            [
                ['emails'],
                function ($attribute) {
                    if ($this->emailsAsArray) {
                        foreach ($this->emailsAsArray as $email) {
                            $validator = new EmailValidator();

                            if (!$validator->validate($email, $error)) {
                                $this->addError($attribute, $email.' — '.\Yii::t('skeeks/form2/app', 'Incorrect email address'));
                                return false;
                            }
                        }
                    }

                },
            ],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        $class = \Yii::$app->skeeks->siteClass;
        return $this->hasOne($class, ['id' => 'cms_site_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'name'                    => \Yii::t('skeeks/form2/app', 'Название'),
            'description'             => \Yii::t('skeeks/form2/app', 'Description'),
            'code'                    => \Yii::t('skeeks/form2/app', 'Code'),
            'emails'                  => \Yii::t('skeeks/form2/app', 'Email addresses'),
            'phones'                  => \Yii::t('skeeks/form2/app', 'Telephones'),
            'user_ids'                => \Yii::t('skeeks/form2/app', 'User Ids'),
            'is_add_legal_checkbox'   => "Показывать checkbox о согласии с обработкой персональных данных",
            'legal_checkbox_template' => "Сообщение возле галочки о персональных данных",
            'lead_source_type'        => 'Канал сделки',
        ]);
    }

    /**
     * Channels a form may give its deals besides «Форма» (the core CRM must know them).
     *
     * @return string[] value => label
     */
    public static function leadSourceTypeItems(): array
    {
        $lead = '\skeeks\cms\models\CmsLead';
        if (!class_exists($lead) || !defined($lead.'::SOURCE_VISIT')) {
            return [];
        }

        return [$lead::SOURCE_VISIT => 'Визит в салон / офис — анкета посетителя, QR-код в зале'];
    }

    public function getLegalCheckboxText()
    {
        $template = $this->legal_checkbox_template ? $this->legal_checkbox_template : 'Я принимаю <a href="{url}" target="_blank" data-pjax="0">условия обработки персональных данных</a>';
        $url = Url::to(['/cms/legal/personal-data']);
        $template = str_replace("{url}", $url, $template);
        return $template;
    }
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'emails' => \Yii::t('skeeks/form2/app', 'You can specify multiple Email addresses (separated by commas), which will receive the notification and filling out this form.'),
            'legal_checkbox_template' => "{url} - ссылка на условия обработки персональных данных. Если не заполнено поле, то будет показано автоматически.",
            'lead_source_type' => 'По умолчанию сделка из формы получает канал «Форма». Для анкеты посетителей салона или офиса (например, по QR-коду у входа) выберите «Визит»: в CRM и отчётах такие сделки будут считаться визитами.',
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm2FormSends()
    {
        return $this->hasMany(Form2FormSend::className(), ['form_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm2FormProperties()
    {
        return $this->hasMany(Form2FormProperty::className(), ['form_id' => 'id'])->orderBy(['priority' => SORT_ASC]);
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
     * @return Form2FormSend
     */
    public function createModelFormSend()
    {
        if ($this->isNewRecord) {
            throw new InvalidParamException;
        }

        $form2Send = new \skeeks\modules\cms\form2\models\Form2FormSend([
            'form_id' => (int)$this->id,
        ]);

        $rpm = $form2Send->relatedPropertiesModel;
        $rpm->loadDefaultValues();

        return $form2Send;
    }

}