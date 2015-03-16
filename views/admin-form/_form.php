<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \yii\db\ActiveRecord */
/* @var $console \skeeks\cms\controllers\AdminUserController */
?>


<?php $form = ActiveForm::begin(); ?>
<?php  ?>

<?= $form->fieldSet('Общая информация')?>
    <?= $form->field($model, 'name')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet('Настройки уведомлений')?>
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

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet('Элементы формы')?>
    <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
        'label'             => "Элементы формы",
        'hint'              => "",
        'parentModel'       => $model,
        'relation'          => [
            'form_id' => 'id'
        ],

        'sort'              => [
            'defaultOrder' =>
            [
                'priority' => SORT_DESC
            ]
        ],

        'controllerRoute'   => 'form/admin-form-field',
        'gridViewOptions'   => [

            'sortable' => true,
            'columns' => [
                //['class' => 'yii\grid\SerialColumn'],
                'attribute',
                'label',
                'hint',
            ],
        ],
    ]); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
