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
use yii\validators\EmailValidator;

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
 * @property array $emailsAsArray
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
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['emails', 'phones', 'user_ids'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],

            [['emails'], function($attribute)
            {
                if ($this->emailsAsArray)
                {
                    foreach ($this->emailsAsArray as $email)
                    {
                        $validator = new EmailValidator();

                        if (!$validator->validate($email, $error))
                        {
                            $this->addError($attribute, $email . ' — ' . Yii::t('skeeks/form2/app', 'Incorrect email address'));
                            return false;
                        }
                    }
                }

            }],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('skeeks/form2/app', 'ID'),
            'created_by' => Yii::t('skeeks/form2/app', 'Created By'),
            'updated_by' => Yii::t('skeeks/form2/app', 'Updated By'),
            'created_at' => Yii::t('skeeks/form2/app', 'Created At'),
            'updated_at' => Yii::t('skeeks/form2/app', 'Updated At'),
            'name' => Yii::t('skeeks/form2/app', 'Name'),
            'description' => Yii::t('skeeks/form2/app', 'Description'),
            'code' => Yii::t('skeeks/form2/app', 'Code'),
            'emails' => Yii::t('skeeks/form2/app', 'Email addresses'),
            'phones' => Yii::t('skeeks/form2/app', 'Telephones'),
            'user_ids' => Yii::t('skeeks/form2/app', 'User Ids'),
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