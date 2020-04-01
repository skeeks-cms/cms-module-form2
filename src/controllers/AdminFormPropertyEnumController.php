<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */
namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormProperty;
use skeeks\modules\cms\form2\models\Form2FormPropertyEnum;
use skeeks\yii2\form\fields\SelectField;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminFormPropertyEnumController
 * @package skeeks\modules\cms\form2\controllers
 */
class AdminFormPropertyEnumController extends BackendModelStandartController
{
    public function init()
    {
        $this->name                   = \Yii::t('skeeks/form2/app', 'Control of properties');
        $this->modelShowAttribute     = "value";
        $this->modelClassName         = Form2FormPropertyEnum::className();

        $this->generateAccessActions = false;

        parent::init();

    }


    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                'filters' => [
                    'visibleFilters' => [
                        'value',
                        'property_id',
                    ],
                ],
                'grid'    => [
                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'id',
                        'value',
                        'property_id',
                        'code',
                        'priority',
                    ],
                    'columns' => [
                        'value' => [
                            'attribute' => "value",
                            'format'    => "raw",
                            'value'     => function (Form2FormPropertyEnum $model) {
                                return Html::a($model->value, "#", [
                                    'class' => "sx-trigger-action",
                                ]);
                            },
                        ],
                    ]
                ],
            ],
            'create' => [
                'fields' => [$this, 'updateFields'],
            ],
            'update' => [
                'fields' => [$this, 'updateFields'],
            ],
        ]);
    }


    public function updateFields($action)
    {
        /**
         * @var $model Form2FormProperty
         */
        $model = $action->model;
        $model->load(\Yii::$app->request->get());

        if ($property_id = \Yii::$app->request->get("property_id")) {
            $model->property_id = $property_id;
        }
        return [
            'property_id' => [
                'class' => SelectField::class,
                'items' => function() {
                    return \yii\helpers\ArrayHelper::map(
                        Form2FormProperty::find()->all(),
                        "id",
                        "name"
                    );
                }
            ],
            'value',
            'code',
            'priority',
        ];
    }
}