<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
namespace skeeks\modules\cms\form2\models;

use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\Core;
use yii\base\InvalidParamException;
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
 * @property Form2FormProperty[] $form2FormProperties
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
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            Implode::className() =>
            [
                'class' => Implode::className(),
                'fields' => ['emails', 'phones', 'user_ids']
            ],
        ]);
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
            'emails' => Yii::t('app', 'Email адреса'),
            'phones' => Yii::t('app', 'Телефоны'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm2FormProperties()
    {
        return $this->hasMany(Form2FormProperty::className(), ['form_id' => 'id']);
    }


    /**
     * @return Form2FormSend
     */
    public function createModelFormSend()
    {
        if ($this->isNewRecord)
        {
            throw new InvalidParamException;
        }

        return new \skeeks\modules\cms\form2\models\Form2FormSend([
            'form_id' => (int) $this->id
        ]);
    }

}