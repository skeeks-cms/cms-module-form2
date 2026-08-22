<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */

namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\models\CmsUser;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\modules\cms\form2\models\Form2FormProperty;
use skeeks\modules\cms\form2\models\Form2FormSend;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class AdminFormController
 * @package skeeks\cms\controllers
 */
class AdminFormSendController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/form2/app', 'Messages');
        $this->modelShowAttribute = "id";
        $this->modelClassName = Form2FormSend::className();
        $this->modelDefaultAction = 'view';
        $this->modelHeader = function () {
            return $this->renderPartial('@skeeks/modules/cms/form2/views/admin-form-send/_model_header', [
                'model' => $this->model,
            ]);
        };

        $this->generateAccessActions = false;
       
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = ArrayHelper::merge(parent::actions(), [

            'index' => [

                'filters' => [
                    'visibleFilters' => [
                        'form_id',
                    ],
                ],

                "grid" => [

                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $query->joinWith("form as form");
                        $query->andWhere(['form.cms_site_id' => \Yii::$app->cms->cmsSite->id]);

                        /*$query->select([
                            Form2FormSend::tableName().'.*',
                        ]);*/
                    },
                    
                    'on beforeInit'        => function (Event $event) {
                        /**
                         * @var $query ActiveQuery
                         * @var $grid GridView
                         */
                        $grid = $event->sender;
                        $this->initGridColumns($grid);
                    },

                    'defaultOrder' => [
                        'id' => SORT_DESC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'created_at',

                        'form_id',
                        'status',

                        'processed_at',
                        'processed_by',


                        'comment',
                        'emails',
                        'ip',

                    ],
                    'columns'        => [
                        'created_at'   => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'processed_at' => [
                            'class' => DateTimeColumnData::class,
                        ],

                        'status' => [
                            'value' => function (Form2FormSend $model) {

                                if ($model->status == \skeeks\modules\cms\form2\models\Form2FormSend::STATUS_NEW) {
                                    $class = "danger";
                                } else if ($model->status == \skeeks\modules\cms\form2\models\Form2FormSend::STATUS_PROCESSED) {
                                    $class = "warning";
                                } else if ($model->status == \skeeks\modules\cms\form2\models\Form2FormSend::STATUS_EXECUTED) {
                                    $class = "success";
                                }

                                return '<span class="label label-'.$class.'">'.\yii\helpers\ArrayHelper::getValue(\skeeks\modules\cms\form2\models\Form2FormSend::getStatuses(), $model->status).'</span>';

                            },
                        ],
                    ],
                ],
            ],

            'view' => [
                'class'    => BackendModelAction::class,
                'name'     => \Yii::t('skeeks/form2/app', 'View'),
                'icon'     => 'svg:file',
                'priority' => 10,
            ],
            'process' => [
                'class' => BackendModelUpdateAction::class,
                'name' => 'Обработка',
                'icon' => 'svg:edit',
                'priority' => 20,
                'fields' => [$this, 'processFields'],
                'on beforeValidate' => function (Event $event) {
                    /** @var Form2FormSend $model */
                    $model = $event->sender->model;
                    if ((int)$model->status !== Form2FormSend::STATUS_NEW) {
                        $model->processed_by = $model->processed_by ?: (int)\Yii::$app->user->id;
                        $model->processed_at = time();
                    }
                },
            ],
            'create-lead' => [
                'class' => BackendModelAction::class,
                'isVisible' => false,
                'callback' => [$this, 'createLead'],
                'accessCallback' => fn() => $this->canCreateLead(),
            ],
        ]);

        ArrayHelper::remove($actions, 'update');
        ArrayHelper::remove($actions, 'create');
        ArrayHelper::remove($actions, 'system');
        ArrayHelper::remove($actions, 'related-properties');

        return $actions;
    }

    public function processFields($action = null): array
    {
        /** @var Form2FormSend|null $model */
        $model = $action ? $action->model : null;
        if ($model
            && (int)$model->status === Form2FormSend::STATUS_NEW
            && !$model->processed_by
            && !\Yii::$app->user->isGuest
        ) {
            $model->status = Form2FormSend::STATUS_PROCESSED;
            $model->processed_by = (int)\Yii::$app->user->id;
        }

        return [
            'processing' => [
                'class' => FieldSet::class,
                'name' => 'Обработка отправки',
                'fields' => [
                    'status' => [
                        'class' => SelectField::class,
                        'items' => Form2FormSend::getStatuses(),
                        'allowNull' => false,
                    ],
                    'processed_by' => [
                        'class' => WidgetField::class,
                        'label' => 'Ответственный',
                        'widgetClass' => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => CmsUser::class,
                            'searchQuery' => static function ($word = '') {
                                $query = CmsUser::find()->forManager()->cmsSite();
                                return $word ? $query->search($word) : $query;
                            },
                        ],
                    ],
                    'comment' => [
                        'class' => TextareaField::class,
                        'label' => 'Комментарий менеджера',
                        'elementOptions' => ['rows' => 6],
                    ],
                ],
            ],
        ];
    }

    public function createLead()
    {
        if (!\Yii::$app->request->isPost) {
            throw new BadRequestHttpException('Лид можно создать только явным действием из карточки отправки.');
        }

        $existing = $this->model->cmsLead;
        try {
            $lead = $this->model->ensureCmsLead();
            if (!$lead) {
                throw new \RuntimeException('Механизм лидов сейчас недоступен.');
            }
            \Yii::$app->session->setFlash('success', $existing
                ? 'Эта отправка уже связана с лидом.'
                : 'Лид создан из отправки формы.');

            return $this->redirect(['/cms/admin-cms-lead/view', 'pk' => $lead->id]);
        } catch (\Throwable $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->redirect(['view', 'pk' => $this->model->id]);
        }
    }

    private function canCreateLead(): bool
    {
        return $this->model
            && $this->model->isCmsLeadIntegrationAvailable()
            && $this->model->leadSiteId === (int)\Yii::$app->skeeks->site->id;
    }
    
    
    
    public function initGridColumns($grid)
    {
        $model = null;
        $autoColumns = [];

        $model = new Form2FormSend();

        $relatedPropertiesModel = $model->relatedPropertiesModel;

        $properties = Form2FormProperty::find()->all();

        foreach ($properties as $property) {
            $name = $property->code;
            //$property = $relatedPropertiesModel->getRelatedProperty($name);
            $filter = '';

            $autoColumns["property{$property->id}"] = [
                //'attribute' => $name,
                'headerOptions' => [
                    'style' => 'width: 150px;'
                ],
                'contentOptions' => [
                    'style' => 'width: 150px;'
                ],

                'label'  => "" . $property->name . " [данные]",
                'format' => 'raw',
                'value'  => function ($model, $key, $index) use ($name, $relatedPropertiesModel) {
                    /**
                     * @var $model \skeeks\cms\models\CmsContentElement
                     */
                    return $model->relatedPropertiesModel->getAttributeAsHtml($name);
                },
            ];
        }

        if ($autoColumns) {
            $grid->columns = ArrayHelper::merge($grid->columns, $autoColumns);
        }


        return $this;
    }
}