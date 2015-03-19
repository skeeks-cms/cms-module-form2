<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
namespace skeeks\modules\cms\form\models;

use skeeks\cms\base\db\ActiveRecord;
use skeeks\cms\helpers\Request;
use skeeks\cms\models\behaviors\HasDescriptionsBehavior;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\behaviors\HasStatus;
use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\behaviors\Serialize;
use skeeks\cms\models\Core;

/**
 * This is the model class for table "{{%form_send_message}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $processed_by
 * @property string $data
 * @property string $emails
 * @property string $phones
 * @property string $email_message
 * @property string $phone_message
 * @property integer $status
 * @property integer $form_id
 * @property string $ip
 * @property string $page_url
 * @property string $data_server
 * @property string $data_session
 * @property string $data_cookie
 * @property string $additional_data
 *
 * @property CmsUser $processedBy
 * @property CmsUser $createdBy
 * @property FormForm $form
 * @property CmsUser $updatedBy
 */
class FormSendMessage extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form_send_message}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

            Serialize::className() =>
            [
                'class' => Serialize::className(),
                'fields' => ['data', 'data_server', 'data_session', 'data_cookie', 'additional_data', 'data_request']
            ],

            Implode::className() =>
            [
                'class' => Implode::className(),
                'fields' => ['emails', 'phones']
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'processed_by', 'status', 'form_id'], 'integer'],
            [['data', 'emails', 'phones', 'email_message', 'phone_message', 'data_server', 'data_session', 'data_cookie', 'data_request', 'additional_data'], 'safe'],
            [['ip'], 'string', 'max' => 32],
            [['page_url'], 'string', 'max' => 500],
            [['form_id'], 'required'],

            ['data_request', 'default', 'value' => function(FormSendMessage $model, $attribute)
            {
                return $_REQUEST;
            }],

            ['data_server', 'default', 'value' => function(FormSendMessage $model, $attribute)
            {
                return $_SERVER;
            }],

            ['data_cookie', 'default', 'value' => function(FormSendMessage $model, $attribute)
            {
                return $_COOKIE;
            }],

            ['data_session', 'default', 'value' => function(FormSendMessage $model, $attribute)
            {
                \Yii::$app->session->open();
                return $_SESSION;
            }],

            ['ip', 'default', 'value' => function(FormSendMessage $model, $attribute)
            {
                return Request::getRealUserIp();
            }],
        ]);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['create'] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios['update'] = $scenarios[self::SCENARIO_DEFAULT];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id'            => \Yii::t('app', 'ID'),
            'value'         => \Yii::t('app', 'Телефон'),
            'form_id'       => \Yii::t('app', 'Форма'),
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
        return $this->hasOne(FormForm::className(), ['id' => 'form_id']);
    }
}