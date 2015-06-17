<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<?= \skeeks\cms\modules\admin\widgets\GridViewHasSettings::widget([
    'dataProvider'  => $dataProvider,
    'filterModel'   => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        [
            'class'         => \skeeks\cms\modules\admin\grid\ActionColumn::className(),
            'controller'    => $controller
        ],

        [
            'attribute' => 'status',
            'class' => \yii\grid\DataColumn::className(),
            'filter' => \skeeks\modules\cms\form2\models\Form2FormSend::$statuses,
            'format' => 'raw',
            'value' => function(\skeeks\modules\cms\form2\models\Form2FormSend $model)
            {
                if ($model->status == \skeeks\modules\cms\form2\models\Form2FormSend::STATUS_NEW)
                {
                    $class = "danger";
                } else if ($model->status == \skeeks\modules\cms\form2\models\Form2FormSend::STATUS_PROCESSED)
                {
                    $class = "warning";
                } else if ($model->status == \skeeks\modules\cms\form2\models\Form2FormSend::STATUS_EXECUTED)
                {
                    $class = "success";
                }

                return '<span class="label label-' . $class . '">' . \yii\helpers\ArrayHelper::getValue(\skeeks\modules\cms\form2\models\Form2FormSend::$statuses, $model->status) . '</span>';
            }
        ],


        [
            'class' => \skeeks\cms\grid\DateTimeColumnData::className(),
            'attribute' => 'processed_at'
        ],

        [
            'class' => \skeeks\cms\grid\UserColumnData::className(),
            'attribute' => 'processed_by'
        ],



        [
            'attribute' => 'form_id',
            'class' => \yii\grid\DataColumn::className(),
            'filter' => \yii\helpers\ArrayHelper::map(
                \skeeks\modules\cms\form2\models\Form2Form::find()->all(),
                'id',
                'name'
            ),
            'value' => function(\skeeks\modules\cms\form2\models\Form2FormSend $model)
            {
                return $model->form->name;
            }
        ],

        [
            'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
            'label' => 'Отправлена'
        ],

        [
            'attribute' => 'site_code',
            'class' => \yii\grid\DataColumn::className(),
            'filter' => \yii\helpers\ArrayHelper::map(
                \skeeks\cms\models\CmsSite::find()->all(),
                'code',
                'name'
            ),
            'value' => function(\skeeks\modules\cms\form2\models\Form2FormSend $model)
            {
                return $model->site->name;
            }
        ],

        'comment'

    ],
]); ?>
