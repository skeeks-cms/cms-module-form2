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
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\modules\cms\form2\models\Form2FormSend;
use yii\helpers\ArrayHelper;

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
                            'label' => \Yii::t('skeeks/form2/app', 'Number of fields in the form'),
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
                "icon"     => "fa fa-eye",
                "priority" => 0,
            ],
        ]);

        ArrayHelper::remove($actions, 'update');
        ArrayHelper::remove($actions, 'create');
        ArrayHelper::remove($actions, 'system');
        ArrayHelper::remove($actions, 'related-properties');

        return $actions;
    }
}