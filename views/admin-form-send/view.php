<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
/* @var $this yii\web\View */
/* @var $action \skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction */
/* @var $model \skeeks\modules\cms\form2\models\Form2FormSend */
$model = $action->controller->model;
?>

<? $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Данные с формы'); ?>
    <?= \yii\widgets\DetailView::widget([
        'model'         => $model->relatedPropertiesModel,
        'attributes'    => array_keys($model->relatedPropertiesModel->attributeLabels())
    ])?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Дополнительная информация'); ?>
    <?= \yii\widgets\DetailView::widget([
        'model'         => $model,
        'attributes'    =>
        [
            [
                'attribute'     => 'id',
                'label'         => 'Номер сообщения',
            ],

            [
                'attribute' => 'created_at',
                'value' => \Yii::$app->formatter->asDatetime($model->created_at, 'medium') . "(" . \Yii::$app->formatter->asRelativeTime($model->created_at) . ")",
            ],

            [
                'format' => 'raw',
                'label' => 'Отправлено с сайта',
                'value' => "<a href=\"{$model->site->url}\" target=\"_blank\" data-pjax=\"0\">{$model->site->name}</a>",
            ],

            [
                'format' => 'raw',
                'label' => 'Отправил пользователь',
                'value' => "{$model->createdBy->getDisplayName()} ({$model->createdBy->username})",
            ],

            [
                'attribute' => 'ip',
                'label' => 'Ip адрес отправителя',
            ]
        ]
    ]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Управление'); ?>

<?= $form->fieldSetEnd(); ?>

<? ActiveForm::end(); ?>
