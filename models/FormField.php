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
            [['hint', 'widget', 'rules'], 'string'],
            [['attribute', 'form_id'], 'required'],
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
        ]);
    }

}