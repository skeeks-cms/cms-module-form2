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
class FormEmail extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form_email}}';
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
            [['value', 'form_id'], 'required'],
            [['value'], 'string', 'max' => 255],
            [['form_id', 'value'], 'unique', 'targetAttribute' => ['form_id', 'value'], 'message' => 'The combination of Value and Form ID has already been taken.'],
            [['value'], 'email'],
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