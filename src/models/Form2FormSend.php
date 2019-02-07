<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
namespace skeeks\modules\cms\form2\models;

use skeeks\cms\helpers\Request;
use skeeks\cms\models\behaviors\HasRelatedProperties;
use skeeks\cms\models\behaviors\HasStorageFile;
use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\behaviors\Serialize;
use skeeks\cms\models\behaviors\traits\HasRelatedPropertiesTrait;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\Core;
use skeeks\cms\models\StorageFile;
use skeeks\cms\models\User;
use skeeks\cms\relatedProperties\models\RelatedElementModel;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeStorageFile;
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
 * @property integer processed_at
 * @property integer $processed_by
 * @property string $data_values
 * @property string $data_labels
 * @property string $emails
 * @property string $site_code
 * @property string $comment
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
 * @property CmsSite                    $site
 */
class Form2FormSend extends RelatedElementModel
{
    const STATUS_NEW        = 0;
    const STATUS_PROCESSED  = 5;
    const STATUS_EXECUTED   = 10;

    static public function getStatuses()
    {
        return [
            self::STATUS_NEW          => \Yii::t('skeeks/form2/app', 'New message'),
            self::STATUS_PROCESSED    => \Yii::t('skeeks/form2/app', 'In progress'),
            self::STATUS_EXECUTED     => \Yii::t('skeeks/form2/app', 'Completed'),
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

            HasRelatedProperties::className() =>
            [
                'class'                             => HasRelatedProperties::className(),
                'relatedElementPropertyClassName'   => Form2FormSendProperty::className(),
                'relatedPropertyClassName'          => Form2FormProperty::className(),
            ],

            Serialize::className() =>
            [
                'class' => Serialize::className(),
                'fields' => ['data_labels', 'data_values', 'data_server', 'data_session', 'data_cookie', 'additional_data', 'data_request']
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'processed_by', 'processed_at', 'status', 'form_id'], 'integer'],
            [['email_message', 'phone_message', 'site_code'], 'string'],
            [['data_labels', 'data_values', 'data_server', 'data_session', 'data_cookie', 'additional_data', 'data_request'], 'safe'],
            [['emails', 'phones', 'user_ids'], 'string'],
            [['ip'], 'string', 'max' => 32],
            [['page_url'], 'string', 'max' => 500],
            [['comment'], 'string'],
            [['status'], 'in', 'range' => array_keys(self::getStatuses())],

            ['data_request', 'default', 'value' => function(self $model, $attribute)
            {
                return $_REQUEST;
            }],

            ['data_server', 'default', 'value' => function(self $model, $attribute)
            {
                return $_SERVER;
            }],

            ['data_cookie', 'default', 'value' => function(self $model, $attribute)
            {
                return $_COOKIE;
            }],

            ['data_session', 'default', 'value' => function(self $model, $attribute)
            {
                \Yii::$app->session->open();
                return $_SESSION;
            }],

            ['ip', 'default', 'value' => function(self $model, $attribute)
            {
                return \Yii::$app->request->userIP;
            }],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => \Yii::t('skeeks/form2/app', 'ID'),
            'created_by' => \Yii::t('skeeks/form2/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/form2/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/form2/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/form2/app', 'Updated At'),
            'processed_by' => \Yii::t('skeeks/form2/app', 'Who handled'),
            'data_values' => \Yii::t('skeeks/form2/app', 'Data Values'),
            'data_labels' => \Yii::t('skeeks/form2/app', 'Data Labels'),
            'emails' => \Yii::t('skeeks/form2/app', 'Emails'),
            'phones' => \Yii::t('skeeks/form2/app', 'Phones'),
            'user_ids' => \Yii::t('skeeks/form2/app', 'User Ids'),
            'email_message' => \Yii::t('skeeks/form2/app', 'Email Message'),
            'phone_message' => \Yii::t('skeeks/form2/app', 'Phone Message'),
            'status' => \Yii::t('skeeks/form2/app', 'Status'),
            'form_id' => \Yii::t('skeeks/form2/app', 'Form'),
            'ip' => \Yii::t('skeeks/form2/app', 'Ip'),
            'page_url' => \Yii::t('skeeks/form2/app', 'Page Url'),
            'data_server' => \Yii::t('skeeks/form2/app', 'Data Server'),
            'data_session' => \Yii::t('skeeks/form2/app', 'Data Session'),
            'data_cookie' => \Yii::t('skeeks/form2/app', 'Data Cookie'),
            'data_request' => \Yii::t('skeeks/form2/app', 'Data Request'),
            'additional_data' => \Yii::t('skeeks/form2/app', 'Additional Data'),
            'site_code' => \Yii::t('skeeks/form2/app', 'Site'),
            'processed_at' => \Yii::t('skeeks/form2/app', 'When handled'),
            'comment' => \Yii::t('skeeks/form2/app', 'Comment'),
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
     */
    public function getSite()
    {
        //return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
        return CmsSite::getByCode($this->site_code);
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
        if ($this->emails)
        {
            $emails = explode(",", $this->emails);

            foreach ($emails as $email)
            {
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
        if ($this->form)
        {
            if ($this->form->emailsAsArray)
            {
                foreach ($this->form->emailsAsArray as $email)
                {
                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/modules/cms/form2/mail';

                    $message = \Yii::$app->mailer->compose('send-message', [
                        'form'              => $this->form,
                        'formSend'          => $this
                    ])
                    ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
                    ->setTo($email)
                    ->setSubject(\Yii::t('skeeks/form2/app', 'Submitting form') . ": «{$this->form->name}» #" . $this->id)
                    ;

                    $rp = $this->relatedPropertiesModel;
                    $rp->initAllProperties();

                    foreach ($rp->toArray() as $code => $value)
                    {
                        $property = $rp->getRelatedProperty($code);

                        if ($property && $property->handler instanceof PropertyTypeStorageFile) {
                            if ($property->handler->isMultiple) {
                                if ($files = StorageFile::find()->where(['id' => $value])->all()) {
                                    /**
                                     * @var StorageFile $file
                                     */
                                    foreach ($files as $file)
                                    {
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
