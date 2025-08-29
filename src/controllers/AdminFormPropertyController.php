<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\measure\models\CmsMeasure;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\relatedProperties\PropertyType;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormProperty;
use skeeks\modules\cms\form2\models\Form2FormSendProperty;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminFormController
 * @package skeeks\cms\controllers
 */
class AdminFormPropertyController extends BackendModelStandartController
{
    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name = \Yii::t('skeeks/form2/app', 'Control of property values');
        $this->modelShowAttribute = "name";
        $this->modelClassName = Form2FormProperty::className();

        $this->generateAccessActions = false;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            'index' => [
                "filters" => [
                    'visibleFilters' => [
                        'q',
                        'component',
                        'content_ids',
                        'tree_ids',
                    ],

                    'filtersModel' => [
                        'rules' => [
                            ['q', 'safe'],
                        ],

                        'attributeDefines' => [
                            'q',
                        ],


                        'fields' => [

                            'q' => [
                                'label'          => 'Поиск',
                                'elementOptions' => [
                                    'placeholder' => 'Поиск',
                                ],
                                'on apply'       => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value) {
                                        $query->andWhere([
                                            'or',
                                            ['like', Form2FormProperty::tableName().'.name', $e->field->value],
                                            ['like', Form2FormProperty::tableName().'.code', $e->field->value],
                                            ['like', Form2FormProperty::tableName().'.id', $e->field->value],
                                        ]);

                                        $query->groupBy([Form2FormProperty::tableName().'.id']);
                                    }
                                },
                            ],
                        ],
                    ],
                ],

                "grid" => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $dataProvider = $e->sender->dataProvider;

                        //$query->joinWith('elementProperties as elementProperties');
                        $subQuery = Form2FormSendProperty::find()->select([new Expression("count(1)")])->where([
                            'property_id' => new Expression(Form2FormProperty::tableName().".id"),
                        ]);

                        $query->groupBy(Form2FormProperty::tableName().".id");
                        $query->select([
                            Form2FormProperty::tableName().'.*',
                            //'countElementProperties' => new Expression("count(*)"),
                            'countElementProperties' => $subQuery,
                        ]);
                    },

                    'sortAttributes' => [
                        'countElementProperties' => [
                            'asc'     => ['countElementProperties' => SORT_ASC],
                            'desc'    => ['countElementProperties' => SORT_DESC],
                            'label'   => \Yii::t('skeeks/cms', 'Number of partitions where the property is filled'),
                            'default' => SORT_ASC,
                        ],
                    ],
                    'defaultOrder'   => [
                        //'def' => SORT_DESC,
                        'priority' => SORT_ASC,
                    ],
                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'custom',
                        'form_id',
                        //'id',
                        //'image_id',

                        //'name',
                        //'domains',

                        'is_active',
                        'priority',
                        //'countElementProperties',
                    ],
                    'columns'        => [

                        'custom'  => [
                            'attribute' => "name",
                            'format'    => "raw",
                            'value'     => function (Form2FormProperty $model) {
                                return Html::a($model->asText, "#", [
                                        'class' => "sx-trigger-action",
                                    ])."<br />".Html::tag('small', $model->handler->name);
                            },
                        ],

                        'countElementProperties' => [
                            'attribute' => 'countElementProperties',
                            'format'    => 'raw',
                            'label'     => \Yii::t('skeeks/cms', 'Number of partitions where the property is filled'),
                            'value'     => function (Form2FormProperty $model) {
                                return Html::a($model->raw_row['countElementProperties'], [
                                    '/cms/admin-tree/list',
                                    'DynamicModel' => [
                                        'fill' => $model->id,
                                    ],
                                ], [
                                    'data-pjax' => '0',
                                    'target'    => '_blank',
                                ]);
                            },
                        ],

                        'is_active' => [
                            'class' => BooleanColumn::class,
                        ],

                    ],
                ],
            ],

            'create' => [
                'fields' => [$this, 'updateFields'],

                'on beforeSave' => function (Event $e) {
                    $model = $e->sender->model;

                    $handler = $model->handler;
                    if ($handler) {
                        if ($post = \Yii::$app->request->post()) {
                            $handler->load($post);
                        }
                        $model->component_settings = $handler->toArray();
                    }
                },

            ],
            'update' => [
                'fields' => [$this, 'updateFields'],

                'on beforeSave' => function (Event $e) {
                    $model = $e->sender->model;


                    $handler = $model->handler;
                    if ($handler) {
                        if ($post = \Yii::$app->request->post()) {
                            $handler->load($post);
                        }
                        $model->component_settings = $handler->toArray();
                    }

                },
            ],

            'enums' => [
                'class'           => BackendGridModelRelatedAction::class,
                'accessCallback'  => true,
                'name'            => "Элементы списка",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/form2/admin-form-property-enum",
                'relation'        => ['property_id' => 'id'],
                'priority'        => 150,

                'on gridInit'        => function($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'property_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },

                'accessCallback' => function (BackendModelAction $action) {

                    /**
                     * @var $model Form2FormProperty
                     */
                    $model = $action->model;

                    if (!$model) {
                        return false;
                    }

                    if ($model->property_type != PropertyType::CODE_LIST) {
                        return false;
                    }

                    return true;
                },
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

        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/cms', 'Basic settings'),
                'fields' => [
                    'is_required' => [
                        'class'      => BoolField::class,
                        'allowNull'  => false,
                    ],
                    'is_active'      => [
                        'class'      => BoolField::class,
                        'allowNull'  => false,
                    ],
                    'name',
                    'code',
                    'component'   => [
                        'class'          => SelectField::class,
                        'elementOptions' => [
                            'data' => [
                                'form-reload' => 'true',
                            ],
                        ],
                        /*'options' => [
                            'class' => 'teat'
                        ],*/
                        'items'          => function () {
                            return array_merge(['' => ' — '], \Yii::$app->cms->relatedHandlersDataForSelect);
                        },
                    ],
                    'cms_measure_code'   => [
                        'class'          => SelectField::class,
                        /*'elementOptions' => [
                            'data' => [
                                'form-reload' => 'true',
                            ],
                        ],*/
                        'items'          => function () {
                            return ArrayHelper::map(
                                CmsMeasure::find()->all(),
                                'code',
                                'asShortText'
                            );
                        },
                    ],
                    [
                        'class'           => HtmlBlock::class,
                        'on beforeRender' => function (Event $e) use ($model) {
                            /**
                             * @var $formElement Element
                             */
                            $formElement = $e->sender;
                            $formElement->activeForm;

                            $handler = $model->handler;

                            if ($handler) {
                                if ($post = \Yii::$app->request->post()) {
                                    $handler->load($post);
                                }
                                if ($handler instanceof \skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList) {
                                    $handler->enumRoute = 'form2/admin-form-property-enum';
                                }

                                $content = $handler->renderConfigFormFields($formElement->activeForm);

                                if ($content) {
                                    $formElement->content = \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget(['content' => \Yii::t('skeeks/cms', 'Settings')]) . $content;
                                }
                            }
                        },
                    ],
                ],
            ],

            'captions' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/cms', 'Additionally'),
                'fields' => [
                    'hint',
                    'priority' => [
                        'class'    => NumberField::class,
                    ],
                    'form_id' => [
                        'class'    => SelectField::class,
                        'multiple' => false,
                        'items'    => function () {
                            return \yii\helpers\ArrayHelper::map(
                                Form2Form::find()->all(), 'id', 'name'
                            );
                        },
                    ],
                ],
            ],
        ];
    }


}