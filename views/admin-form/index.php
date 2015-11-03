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

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'  => $dataProvider,
    'filterModel'   => $searchModel,
    'adminController'   => $controller,
    'columns' => [

        'name',
        'code',

        [
            'attribute' => 'emails',
            'class' => \yii\grid\DataColumn::className(),
            'format' => 'raw',
            'value' => function(\skeeks\modules\cms\form2\models\Form2Form $model)
            {
                return $model->emails;
            }
        ],

        [
            'attribute' => 'phones',
            'class' => \yii\grid\DataColumn::className(),
            'format' => 'raw',
            'value' => function(\skeeks\modules\cms\form2\models\Form2Form $model)
            {
                return $model->phones;
            }
        ],

        [
            'label' => "Количество полей в форме",
            'class' => \yii\grid\DataColumn::className(),
            'format' => 'raw',
            'value' => function(\skeeks\modules\cms\form2\models\Form2Form $model)
            {
                return count($model->createModelFormSend()->relatedPropertiesModel->attributeLabels());
            }
        ],

        [
            'label' => "Количество сообщений",
            'class' => \yii\grid\DataColumn::className(),
            'format' => 'raw',
            'value' => function(\skeeks\modules\cms\form2\models\Form2Form $model)
            {
                return \yii\helpers\Html::a(count($model->form2FormSends), \skeeks\cms\helpers\UrlHelper::construct('/form2/admin-form-send', [
                    'Form2FormSend' =>
                    [
                        'form_id' => $model->id
                    ]
                ])->enableAdmin()->toString());
            }
        ],
    ],
]); ?>
