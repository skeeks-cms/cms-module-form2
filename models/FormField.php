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
use skeeks\modules\cms\form\elements\Base;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;

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
        return ArrayHelper::merge(parent::behaviors(), [
            Implode::className() =>
            [
                'class' => Implode::className(),
                'fields' => ['rules']
            ]
        ]);
    }

    /**
     * @return array
     */
    static public function availableRules()
    {
        return [
            'required'  => 'Обязательно к заполнению',
            'email'     => 'Проверка на email',
            'integer'   => 'Целое число'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'form_id'], 'integer'],
            [['hint'], 'string'],
            [['element_config'], 'safe'],
            [['priority'], 'integer'],
            [['rules'], 'safe'],
            [[ 'form_id', 'element'], 'required'],
            ['attribute', 'default', 'value' => function(FormField $model, $attribute)
            {
                return "sx_field_" . md5(rand(1, 10) . time());
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

    /**
     * @return \skeeks\cms\models\WidgetDescriptor
     */
    public function elenentDescriptor()
    {
        return \Yii::$app->formRegisteredElements->getComponent($this->element);
    }


    /**
     * @return array
     */
    public function rulesForActiveForm()
    {
        $result = [];

        if ((array) $this->rules)
        {
            foreach ((array) $this->rules as $ruleCode)
            {
                $result[] = [[$this->attribute], $ruleCode];
            }
        } else
        {
            $result[] = [[$this->attribute], 'safe'];
        }

        return $result;
    }

    /**
     * @param ActiveForm $activeForm
     * @param Model $model
     * @return mixed
     */
    public function renderActiveForm(ActiveForm $activeForm, Model $model)
    {
        if ($element = $this->elenentDescriptor())
        {
            $elementConfig = (array) $this->element_config;
            /**
             * @var $field ActiveField
             */
            $field = $activeForm
                ->field($model, $this->attribute);

            if (!$field)
            {
                return '';
            }

            //Элемент или виджет
            if (is_subclass_of($this->element, Base::className()))
            {
                $element = new $this->element;
                $field->{$element->elementCode}($elementConfig);
            } else
            {
                $field->widget($this->element, $elementConfig);
            }

            if ($this->hint)
            {
                $field->hint((string) $this->hint);
            }

            if ($this->label)
            {
                $field->label($this->label);
            } else
            {
                $field->label(false);
            }


            return $field;
        }

        return '';
    }
}