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
use skeeks\cms\models\behaviors\Serialize;
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
        return array_merge(parent::behaviors(), [
            [
                "class"  => Serialize::className(),
                'fields' => ['emails', 'phones', 'elements']
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name'], 'required'],
            [['name', 'description'], 'string'],
            [['emails', 'phones', 'elements'], 'safe'],
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
        return  array_merge(parent::attributeLabels(), [
            'id' => \Yii::t('app', 'ID'),
            'name' => \Yii::t('app', 'Name'),
            'description' => \Yii::t('app', 'Description'),
        ]);
    }


}