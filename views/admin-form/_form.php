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

<? if (!$model->isNewRecord) : ?>
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


    <?= $form->fieldSet('Email уведомления')?>

        <?= $form->field($model, 'emails')->widget(
            \skeeks\cms\widgets\formInputs\EditedSelect::className(),
            [
                'controllerRoute' => 'cms/admin-user-email',
                'items' => \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\models\user\UserEmail::find()->all(),
                    'value',
                    'value'
                ),
                'multiple' => true
            ]
        )
        ->hint('Вы можете указать несколько Email адресов, на которые будут приходить уведомления и заполнении этой формы.')?>

    <?= $form->fieldSetEnd(); ?>

    <?= $form->fieldSet('Sms уведомления')?>

    <?= $form->field($model, 'phones')->widget(
            \skeeks\cms\widgets\formInputs\EditedSelect::className(),
            [
                'controllerRoute' => 'cms/admin-user-phone',
                'items' => \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\models\user\UserPhone::find()->all(),
                    'value',
                    'value'
                ),
                'multiple' => true
            ]
        )
        ->hint('Вы можете указать несколько телефонных номеров, на которые будут приходить sms уведомления и заполнении этой формы.')?>

    <?= $form->fieldSetEnd(); ?>



<? endif; ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>

