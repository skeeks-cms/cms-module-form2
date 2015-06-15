<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \skeeks\modules\cms\form2\models\Form2Form */
/* @var $console \skeeks\cms\controllers\AdminUserController */
?>


<?php $form = ActiveForm::begin(); ?>
<?php  ?>

<?= $form->fieldSet('Общая информация')?>
    <?= $form->field($model, 'name')->textInput(); ?>
    <?= $form->field($model, 'code')->textInput(); ?>
    <?= $form->field($model, 'description')->textarea(); ?>
<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet('Настройки уведомлений')?>


<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet('Элементы формы')?>
    <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
            'label'             => "Свойства элементов",
            'hint'              => "У каждого контента на сайте есть свой набор свойств, тут они и задаются",
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

            'controllerRoute'   => 'form2/admin-form-property',
            'gridViewOptions'   => [
                'sortable' => true,
                'columns' => [
                    'name',


                    [
                        'class'         => \skeeks\cms\grid\BooleanColumn::className(),
                        'attribute'     => 'active',
                        'falseValue'    => \skeeks\cms\components\Cms::BOOL_N,
                        'trueValue'     => \skeeks\cms\components\Cms::BOOL_Y
                    ],


                    'code',
                    'priority',
                ],
            ],
        ]); ?>


<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>

