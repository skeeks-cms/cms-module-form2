<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \yii\db\ActiveRecord */
/* @var $console \skeeks\cms\controllers\AdminUserController */
?>


<?php $form = ActiveForm::begin(); ?>
<?php  ?>

<?= $form->fieldSet('Общая ниформация')?>
    <?= $form->field($model, 'name')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>

<?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
    'label'             => "Email адреса",
    'hint'              => "Укажите email адреса, на которые будут приходить уведомления об отправке формы.",
    'parentModel'       => $model,
    'relation'          => [
        'form_id' => 'id'
    ],
    'controllerRoute'   => 'form/admin-form-email',
    'gridViewOptions'   => [
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'value',
        ],
    ],
]); ?>

<?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
    'label'             => "Телефоны",
    'hint'              => "Укажите телефоны, на которые будут приходить уведомления об отправке формы.",
    'parentModel'       => $model,
    'relation'          => [
        'form_id' => 'id'
    ],
    'controllerRoute'   => 'form/admin-form-phone',
    'gridViewOptions'   => [
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'value',
        ],
    ],
]); ?>

<?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
    'label'             => "Элементы формы",
    'hint'              => "",
    'parentModel'       => $model,
    'relation'          => [
        'form_id' => 'id'
    ],
    'controllerRoute'   => 'form/admin-form-field',
    'gridViewOptions'   => [
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'value',
        ],
    ],
]); ?>


<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
