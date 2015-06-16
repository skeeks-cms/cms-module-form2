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

        'id',

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
        ]

    ],
]); ?>
