<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
namespace skeeks\modules\cms\form2\models;

use skeeks\cms\models\behaviors\HasRelatedProperties;
use skeeks\cms\models\behaviors\traits\HasRelatedPropertiesTrait;
use skeeks\cms\models\Core;
use skeeks\cms\models\User;
use skeeks\cms\relatedProperties\models\RelatedElementModel;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "{{%form2_form_send}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $processed_by
 * @property string $data_values
 * @property string $data_labels
 * @property string $emails
 * @property string $phones
 * @property string $user_ids
 * @property string $email_message
 * @property string $phone_message
 * @property integer $status
 * @property integer $form_id
 * @property string $ip
 * @property string $page_url
 * @property string $data_server
 * @property string $data_session
 * @property string $data_cookie
 * @property string $data_request
 * @property string $additional_data
 *
 * @property User $processedBy
 * @property Form2Form $form
 *
 * @property Form2FormSendProperty[]    relatedElementProperties
 * @property Form2FormProperty[]        relatedProperties
 */
class Form2FormSend extends RelatedElementModel
{
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

            HasRelatedProperties::className() =>
            [
                'class'                             => HasRelatedProperties::className(),
                'relatedElementPropertyClassName'   => Form2FormSendProperty::className(),
                'relatedPropertyClassName'          => Form2FormProperty::className(),
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'processed_by', 'status', 'form_id'], 'integer'],
            [['data_values', 'data_labels', 'emails', 'phones', 'user_ids', 'email_message', 'phone_message', 'data_server', 'data_session', 'data_cookie', 'data_request', 'additional_data'], 'string'],
            [['ip'], 'string', 'max' => 32],
            [['page_url'], 'string', 'max' => 500]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'processed_by' => Yii::t('app', 'Processed By'),
            'data_values' => Yii::t('app', 'Data Values'),
            'data_labels' => Yii::t('app', 'Data Labels'),
            'emails' => Yii::t('app', 'Emails'),
            'phones' => Yii::t('app', 'Phones'),
            'user_ids' => Yii::t('app', 'User Ids'),
            'email_message' => Yii::t('app', 'Email Message'),
            'phone_message' => Yii::t('app', 'Phone Message'),
            'status' => Yii::t('app', 'Status'),
            'form_id' => Yii::t('app', 'Form ID'),
            'ip' => Yii::t('app', 'Ip'),
            'page_url' => Yii::t('app', 'Page Url'),
            'data_server' => Yii::t('app', 'Data Server'),
            'data_session' => Yii::t('app', 'Data Session'),
            'data_cookie' => Yii::t('app', 'Data Cookie'),
            'data_request' => Yii::t('app', 'Data Request'),
            'additional_data' => Yii::t('app', 'Additional Data'),
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
     *
     * Все возможные свойства связанные с моделью
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRelatedProperties()
    {
        return $this->form->form2FormProperties;
    }
}
