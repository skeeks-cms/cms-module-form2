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
use skeeks\modules\cms\form2\models\Form2FormSendNotification;
use skeeks\modules\cms\form2\protection\FormProtection;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
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
        $hasProtection = Form2FormSend::isProtectionReady();

        $actions = ArrayHelper::merge(parent::actions(), [

            'index' => [
                'configKey' => 'form2/messages-v2',

                'filters' => [
                    'visibleFilters' => array_values(array_filter([
                        'form_id',
                        $hasProtection ? 'protection' : null,
                    ])),
                    'filtersModel' => $hasProtection ? [
                        'rules' => [
                            ['protection', 'safe'],
                        ],
                        'attributeDefines' => [
                            'protection',
                        ],
                        'fields' => [
                            'protection' => [
                                'class' => SelectField::class,
                                'label' => 'Проверка',
                                'items' => [
                                    FormProtection::STATUS_ACCEPTED => 'Принятые',
                                    FormProtection::STATUS_SUSPICIOUS => 'Подозрительные',
                                ],
                                'on apply' => function (QueryFiltersEvent $e) {
                                    $query = $e->dataProvider->query;
                                    $column = Form2FormSend::tableName().'.check_status';
                                    if ($e->field->value === FormProtection::STATUS_SUSPICIOUS) {
                                        $query->andWhere([$column => FormProtection::STATUS_SUSPICIOUS]);
                                    } elseif ($e->field->value === FormProtection::STATUS_ACCEPTED) {
                                        $query->andWhere(['or', [$column => null], [$column => FormProtection::STATUS_ACCEPTED]]);
                                    }
                                },
                            ],
                        ],
                    ] : [],
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
                                ->andWhere([CmsLead::tableName().'.source_type' => Form2FormSend::leadSourceTypes()])
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

                    // A suspicious submission is marked by the tone of its row, as other CRM lists do.
                    'rowOptions' => static function (Form2FormSend $model) {
                        return $model->isSuspicious() ? ['class' => 'sx-collection-item--warning'] : [];
                    },

                    'visibleColumns' => [
                        'checkbox',
                        'message',
                        'check',
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
                        'check' => [
                            'label' => 'Проверка',
                            'format' => 'raw',
                            'value' => static function (Form2FormSend $model) {
                                if (!Form2FormSend::isProtectionReady()) {
                                    return Html::tag('span', '—', ['class' => 'sx-text--muted']);
                                }
                                if (!$model->isSuspicious()) {
                                    return Html::tag('span', 'Принята', ['class' => 'sx-status sx-status--success']);
                                }
                                $reasons = array_map([FormProtection::class, 'reasonLabel'], $model->getCheckReasonCodes());

                                return Html::tag('span', 'Подозрительная', [
                                    'class' => 'sx-status sx-status--warning',
                                    'title' => implode("\n", $reasons),
                                ]);
                            },
                        ],
                        'lead' => [
                            'label' => 'Сделка',
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
            'accept' => [
                'class' => BackendModelAction::class,
                'isVisible' => false,
                'callback' => [$this, 'acceptSend'],
                'accessCallback' => fn() => $this->canAccept(),
            ],
            'retry-notifications' => [
                'class' => BackendModelAction::class,
                'isVisible' => false,
                'callback' => [$this, 'retryNotifications'],
                'accessCallback' => fn() => $this->isAdmin() && $this->model && !$this->model->isSuspicious(),
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
            throw new BadRequestHttpException('Сделку можно создать только явным действием из карточки отправки.');
        }
        // A suspicious submission gets a deal only by «Принять заявку».
        if ($this->model->isSuspicious()) {
            throw new ForbiddenHttpException('Это подозрительная заявка. Чтобы создать сделку, примите заявку.');
        }

        $existing = $this->model->cmsLead;
        try {
            $lead = $this->model->ensureCmsLead();
            if (!$lead) {
                throw new \RuntimeException('Механизм сделок сейчас недоступен.');
            }
            \Yii::$app->session->setFlash('success', $existing
                ? 'Эта отправка уже связана со сделкой.'
                : 'Сделка создана из отправки формы.');

            return $this->redirect(['/cms/admin-cms-lead/view', 'pk' => $lead->id]);
        } catch (\Throwable $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->redirect(['view', 'pk' => $this->model->id]);
        }
    }

    /**
     * A suspicious submission accepted by hand: the CRM deal, the notifications and EVENT_ACCEPTED, exactly once
     * even for concurrent requests (Form2FormSend::accept()).
     */
    public function acceptSend()
    {
        if (!\Yii::$app->request->isPost) {
            throw new BadRequestHttpException('Заявку можно принять только явным действием из карточки отправки.');
        }

        try {
            if (!$this->model->accept((int)\Yii::$app->user->id)) {
                \Yii::$app->session->setFlash('info', 'Заявка уже принята.');
            } else {
                \Yii::$app->session->setFlash('success', 'Заявка принята. '.$this->deliverySummary());
            }
        } catch (\Throwable $e) {
            \Yii::error($e, 'form2');
            \Yii::$app->session->setFlash('error', 'Не удалось принять заявку: '.$e->getMessage());
        }

        return $this->redirect(['view', 'pk' => $this->model->id]);
    }

    /**
     * Retries the notifications that were not delivered; delivered recipients are not notified again.
     */
    public function retryNotifications()
    {
        if (!\Yii::$app->request->isPost) {
            throw new BadRequestHttpException('Повторить отправку можно только явным действием из карточки отправки.');
        }
        $this->model->deliverNotifications();
        \Yii::$app->session->setFlash('info', $this->deliverySummary());

        return $this->redirect(['view', 'pk' => $this->model->id]);
    }

    /** The real state of the notifications of the current submission, for a flash message. */
    private function deliverySummary(): string
    {
        if (!Form2FormSendNotification::isTableReady()) {
            return 'Уведомления отправлены.';
        }
        $counts = ['sent' => 0, 'failed' => 0, 'unknown' => 0, 'other' => 0];
        foreach ($this->model->getNotifications()->all() as $notification) {
            if ($notification->status === Form2FormSendNotification::STATUS_SENT) {
                $counts['sent']++;
            } elseif ($notification->status === Form2FormSendNotification::STATUS_FAILED) {
                $counts['failed']++;
            } elseif ($notification->status === Form2FormSendNotification::STATUS_UNKNOWN) {
                $counts['unknown']++;
            } else {
                $counts['other']++;
            }
        }
        if (!array_sum($counts)) {
            return 'Получателей уведомлений у формы нет.';
        }
        $parts = ['Уведомлений доставлено: '.$counts['sent']];
        if ($counts['failed']) {
            $parts[] = 'не доставлено: '.$counts['failed'].' — см. блок «Уведомления»';
        }
        if ($counts['unknown']) {
            $parts[] = 'результат неизвестен: '.$counts['unknown'].' — уточните у получателя';
        }
        if ($counts['other']) {
            $parts[] = 'в очереди: '.$counts['other'];
        }

        return implode(', ', $parts).'.';
    }

    public function canAccept(): bool
    {
        return $this->isAdmin() && $this->model && $this->model->isSuspicious();
    }

    private function canCreateLead(): bool
    {
        return $this->model
            && !$this->model->isSuspicious()
            && $this->model->isCmsLeadIntegrationAvailable()
            && $this->model->leadSiteId === (int)\Yii::$app->skeeks->site->id;
    }

    public function getModel()
    {
        // Embedded as the "Сообщения" tab of a form (BackendGridModelRelatedAction) the request pk
        // belongs to the form, not to a submission: there is no current submission then.
        if ($this->_model === null && \Yii::$app->controller === $this) {
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
                        ->andWhere([CmsLead::tableName().'.source_type' => Form2FormSend::leadSourceTypes()])
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
                'value'  => function (Form2FormSend $model, $key, $index) use ($name) {
                    // Values come from visitors: escaped except safe markup of their type (file links).
                    return $model->propertyValueHtml((string)$name);
                },
            ];
        }

        if ($autoColumns) {
            $grid->columns = ArrayHelper::merge($grid->columns, $autoColumns);
        }


        return $this;
    }
}
