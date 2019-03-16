<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class AdminFormController
 * @package skeeks\cms\controllers
 */
class AdminFormController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/form2/app', 'Forms management');
        $this->modelShowAttribute = "name";
        $this->modelClassName = Form2Form::class;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            'index' => [

                'filters' => [
                    'visibleFilters' => [
                        'name',
                        'code',
                    ],
                ],

                "grid" => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $sendsQuery = Form2FormSend::find()->select(['count(*)'])->where(['form_id' => new Expression(Form2Form::tableName().".id")]);


                        $query->select([
                            Form2Form::tableName().'.*',
                            'count_sends' => $sendsQuery,
                        ]);
                    },

                    'sortAttributes' => [
                        'count_sends' => [
                            'asc'  => ['count_sends' => SORT_ASC],
                            'desc' => ['count_sends' => SORT_DESC],
                            'name' => \Yii::t('skeeks/form2/app', 'Number of posts'),
                        ],
                    ],

                    'defaultOrder'   => [
                        'count_sends' => SORT_DESC,
                        'id'          => SORT_DESC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'id',
                        //'created_at',

                        'name',
                        'code',

                        'emails',
                        //'phones',
                        'countFields',
                        'count_sends',

                    ],
                    'columns'        => [
                        'created_at' => [
                            'class' => DateTimeColumnData::class,
                        ],

                        'countFields' => [
                            'label' => \Yii::t('skeeks/form2/app', 'Number of fields in the form'),
                            'value' => function (Form2Form $model) {
                                return count($model->createModelFormSend()->relatedPropertiesModel->toArray());
                            },
                        ],
                        'count_sends' => [
                            'label' => \Yii::t('skeeks/form2/app', 'Number of posts'),
                            'attribute' => 'count_sends',
                            'value' => function (Form2Form $model) {
                                return $model->raw_row['count_sends'];
                            },
                        ],
                    ],
                ],
            ],

            'view' => [
                'class'    => AdminOneModelEditAction::className(),
                'name'     => \Yii::t('skeeks/form2/app', 'Result'),
                "icon"     => "fa fa-eye",
                "priority" => 0,
            ],
        ]);
    }

}