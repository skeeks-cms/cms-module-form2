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
                        'priority' => SORT_ASC
                    ]
                ],

                'dataProviderCallback' => function($dataProvider)
                {
                    /**
                     * @var \yii\data\BaseDataProvider $dataProvider
                     */
                    $dataProvider->getPagination()->defaultPageSize   = 5000;
                },

                'controllerRoute'   => 'form2/admin-form-property',
                'gridViewOptions'   => [
                    'sortable' => true,
                    'columns' => [
                        [
                            'attribute'     => 'name',
                            'enableSorting' => false
                        ],

                        [
                            'class'         => \skeeks\cms\grid\BooleanColumn::className(),
                            'attribute'     => 'active',
                            'falseValue'    => \skeeks\cms\components\Cms::BOOL_N,
                            'trueValue'     => \skeeks\cms\components\Cms::BOOL_Y,
                            'enableSorting' => false
                        ],

                        [
                            'attribute'     => 'code',
                            'enableSorting' => false
                        ],

                        [
                            'attribute'     => 'priority',
                            'enableSorting' => false
                        ],
                    ],
                ],
            ]); ?>


    <?= $form->fieldSetEnd(); ?>


    <?= $form->fieldSet('Email уведомления')?>

        <?= $form->field($model, 'emails')->textarea()
        ->hint('Вы можете указать несколько Email адресов (через запятую), на которые будут приходить уведомления и заполнении этой формы.')?>

    <?= $form->fieldSetEnd(); ?>

    <?= $form->fieldSet('Sms уведомления')?>

    <?= \yii\bootstrap\Alert::widget([
        'options' => [
          'class' => 'alert-info',
        ],
        'body' => 'Работает если подключен центр sms уведомлений',
    ]); ?>
    <?= $form->field($model, 'phones')->textarea()
        ->hint('Вы можете указать несколько телефонных номеров (через запятую), на которые будут приходить sms уведомления и заполнении этой формы.')?>

    <?= $form->fieldSetEnd(); ?>



<? endif; ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>

