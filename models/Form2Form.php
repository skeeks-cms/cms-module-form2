<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
namespace skeeks\modules\cms\form2\models;

use skeeks\cms\models\Core;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%form2_form}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property string $code
 * @property string $emails
 * @property string $phones
 * @property string $user_ids
 *
 * @property Form2FormSend[] $form2FormSends
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
            [['emails', 'phones', 'user_ids'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique']
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
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'code' => Yii::t('app', 'Code'),
            'emails' => Yii::t('app', 'Emails'),
            'phones' => Yii::t('app', 'Phones'),
            'user_ids' => Yii::t('app', 'User Ids'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm2FormSends()
    {
        return $this->hasMany(Form2FormSend::className(), ['form_id' => 'id']);
    }

}