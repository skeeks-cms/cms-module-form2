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
 * Class FormEmail
 * @package skeeks\modules\cms\form\models
 */
class FormField extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form_field}}';
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
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'form_id'], 'integer'],
            [['hint'], 'string'],
            [['priority'], 'integer'],
            [['widget', 'rules'], 'safe'],
            [[ 'form_id'], 'required'],
            ['attribute', 'default', 'value' => function(FormField $model, $attribute)
            {
                return "sx-field-" . md5(rand(1, 10) . time());
            }],
            [['label', 'attribute'], 'string', 'max' => 255],
            [['attribute', 'form_id'], 'unique', 'targetAttribute' => ['attribute', 'form_id'], 'message' => 'Этот элемент уже привязан к форме']

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
            'value'         => \Yii::t('app', 'Email'),
            'form_id'       => \Yii::t('app', 'Форма'),
            'attribute'       => \Yii::t('app', 'Уникальный код (необязательно)'),
            'hint'       => \Yii::t('app', 'Небольшая подсказка элемента'),
            'label'       => \Yii::t('app', 'Название'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function findForm()
    {
        return $this->hasOne(Form::className(), ['id' => 'form_id']);
    }

    /**
     * @return Form
     */
    public function fetchForm()
    {
        return $this->findForm()->one();
    }
}