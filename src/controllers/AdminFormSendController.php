<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */

namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\BackendEntityLinkColumn;
use skeeks\cms\backend\widgets\BackendEntityLink;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\models\CmsLead;
use skeeks\cms\rbac\CmsManager;
use skeeks\modules\cms\form2\models\Form2FormProperty;
use skeeks\modules\cms\form2\models\Form2FormSend;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class AdminFormController
 * @package skeeks\cms\controllers
 */
class AdminFormSendController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/form2/app', 'Messages');
        $this->modelShowAttribute = "displayName";
        $this->modelClassName = Form2FormSend::className();
        $this->modelDefaultAction = 'view';
        $this->permissionName = 'cms/admin-lead';
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
                'configKey' => 'form2/messages-v2',

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
                        if (!$this->isAdmin()) {
                            $availableLeadRefs = CmsLead::find()
                                ->forManager()
                                ->cmsSite()
                                ->andWhere([CmsLead::tableName().'.source_type' => CmsLead::SOURCE_FORM])
                                ->select(CmsLead::tableName().'.source_ref');
                            $query->andWhere([Form2FormSend::tableName().'.id' => $availableLeadRefs]);
                        }
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
                        'message',
                        'lead',
                        'created_at',
                        'emails',
                        'phones',
                        'ip',
                        'actions',
                    ],
                    'columns'        => [
                        'message' => [
                            'class' => BackendEntityLinkColumn::class,
                            'attribute' => 'id',
                            'label' => 'Сообщение',
                            'controllerId' => '/form2/admin-form-send',
                            'action' => 'view',
                            'permissionName' => 'cms/admin-lead',
                            'value' => static fn(Form2FormSend $model) => $model->displayName,
                        ],
                        'lead' => [
                            'label' => 'Лид',
                            'format' => 'raw',
                            'value' => static function (Form2FormSend $model) {
                                $lead = $model->cmsLead;
                                if (!$lead) {
                                    return Html::tag('span', '—', ['class' => 'sx-text--muted']);
                                }

                                return BackendEntityLink::widget([
                                    'controllerId' => '/cms/admin-cms-lead',
                                    'action' => 'view',
                                    'modelId' => (int)$lead->id,
                                    'label' => $lead->displayName,
                                    'options' => ['class' => 'sx-collection-cell__primary'],
                                ]);
                            },
                        ],
                        'created_at'   => [
                            'class' => DateTimeColumnData::class,
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
            'create-lead' => [
                'class' => BackendModelAction::class,
                'isVisible' => false,
                'callback' => [$this, 'createLead'],
                'accessCallback' => fn() => $this->canCreateLead(),
            ],
            'delete' => [
                'accessCallback' => fn() => $this->isAdmin(),
            ],
            'delete-multi' => [
                'accessCallback' => fn() => $this->isAdmin(),
            ],
        ]);

        ArrayHelper::remove($actions, 'update');
        ArrayHelper::remove($actions, 'create');
        ArrayHelper::remove($actions, 'system');
        ArrayHelper::remove($actions, 'related-properties');

        return $actions;
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

    public function getModel()
    {
        if ($this->_model === null) {
            $pk = (int)\Yii::$app->request->get($this->requestPkParamName);
            if ($pk) {
                $query = Form2FormSend::find()
                    ->joinWith('form as form')
                    ->andWhere(['form.cms_site_id' => \Yii::$app->cms->cmsSite->id])
                    ->andWhere([Form2FormSend::tableName().'.id' => $pk]);

                if (!$this->isAdmin()) {
                    $availableLeadRefs = CmsLead::find()
                        ->forManager()
                        ->cmsSite()
                        ->andWhere([CmsLead::tableName().'.source_type' => CmsLead::SOURCE_FORM])
                        ->select(CmsLead::tableName().'.source_ref');
                    $query->andWhere([Form2FormSend::tableName().'.id' => $availableLeadRefs]);
                }

                $this->_model = $query->limit(1)->one();
                if (!$this->_model) {
                    throw new NotFoundHttpException('Сообщение формы не найдено.');
                }
            }
        }

        return $this->_model;
    }

    private function isAdmin(): bool
    {
        return !\Yii::$app->user->isGuest
            && \Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
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
