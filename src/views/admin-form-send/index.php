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
<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

    <?php echo $this->render('_search', [
        'searchModel'   => $searchModel,
        'dataProvider'  => $dataProvider
    ]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'  => $dataProvider,
    'filterModel'   => $searchModel,
    'adminController'   => $controller,
    'pjax'              => $pjax,
    'columns' => [
        [
            'attribute' => 'status',
            'class' => \yii\grid\DataColumn::className(),
            'filter' => \skeeks\modules\cms\form2\models\Form2FormSend::getStatuses(),
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

                return '<span class="label label-' . $class . '">' . \yii\helpers\ArrayHelper::getValue(\skeeks\modules\cms\form2\models\Form2FormSend::getStatuses(), $model->status) . '</span>';
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

<? $pjax::end(); ?>
