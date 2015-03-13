<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
namespace skeeks\modules\cms\form\models;

use skeeks\cms\base\db\ActiveRecord;
use skeeks\cms\models\behaviors\HasDescriptionsBehavior;
use skeeks\cms\models\behaviors\HasStatus;
use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\Core;

/**
 * Class Form
 * @package skeeks\modules\cms\form\models
 */
class Form extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form_form}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), []);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'required'],
            [['description', 'template'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],

            [['code'], 'validateCode']
        ]);
    }

    public function validateCode($attribute)
    {
        if(!preg_match('/^[a-z]{1}[a-z0-1-]{3,32}$/', $this->$attribute))
        {
            $this->addError($attribute, 'Используйте только буквы латинского алфавита и цифры. Начинаться должен с буквы. Пример block1.');
        }
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
            'name'          => \Yii::t('app', 'Name'),
            'code'          => \Yii::t('app', 'Уникальный код'),
            'description'   => \Yii::t('app', 'Небольшое описание'),
            'template'      => \Yii::t('app', 'Шаблон формы'),
        ]);
    }

}