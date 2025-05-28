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
        ]);
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