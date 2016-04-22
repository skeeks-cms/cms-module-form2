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

<?= $form->fieldSet( \Yii::t('skeeks/form2/app', 'General information'))?>
    <?= $form->field($model, 'name')->textInput(); ?>
    <?= $form->field($model, 'code')->textInput(); ?>
    <?= $form->field($model, 'description')->textarea(); ?>
<?= $form->fieldSetEnd(); ?>

<? if (!$model->isNewRecord) : ?>
    <?= $form->fieldSet( \Yii::t('skeeks/form2/app', 'form elements'))?>
        <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
                'label'             => \Yii::t('skeeks/form2/app', 'Element properties'),
                'hint'              => \Yii::t('skeeks/form2/app', 'Each content on the site has its own set of properties, and then they are set'),
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


    <?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Email Message'))?>

        <?= $form->field($model, 'emails')->textarea()
        ->hint(\Yii::t('skeeks/form2/app', 'You can specify multiple Email addresses (separated by commas), which will receive the notification and filling out this form.'))?>

    <?= $form->fieldSetEnd(); ?>

    <?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Phone Message'))?>

    <?= \yii\bootstrap\Alert::widget([
        'options' => [
          'class' => 'alert-info',
        ],
        'body' => \Yii::t('skeeks/form2/app', 'It works when connected to the center of sms notifications'),
    ]); ?>
    <?= $form->field($model, 'phones')->textarea()
        ->hint(\Yii::t('skeeks/form2/app', 'Phone Message'))?>

    <?= $form->fieldSetEnd(); ?>



<? endif; ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>

