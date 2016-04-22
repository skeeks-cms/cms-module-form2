<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\models\Tree;
use skeeks\cms\modules\admin\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model Tree */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Basic settings')) ?>

    <?= $form->fieldRadioListBoolean($model, 'active') ?>
    <?= $form->fieldRadioListBoolean($model, 'is_required') ?>


<? if ($content_id = \Yii::$app->request->get('form_id')) : ?>

    <?= $form->field($model, 'form_id')->hiddenInput(['value' => $content_id])->label(false); ?>

<? else: ?>

    <?= $form->field($model, 'form_id')->label(\Yii::t('skeeks/form2/app', 'Contents'))->widget(
        \skeeks\cms\widgets\formInputs\EditedSelect::className(), [
            'items' => \yii\helpers\ArrayHelper::map(
                 \skeeks\modules\cms\form2\models\Form2Form::find()->all(),
                 "id",
                 "name"
             ),
            'controllerRoute' => 'form2/admin-form',
        ]);
    ?>

<? endif; ?>

    <?= $form->fieldSelect($model, 'component', [
        \Yii::t('skeeks/form2/app', 'Base types')          => \Yii::$app->cms->basePropertyTypes(),
        \Yii::t('skeeks/form2/app', 'Custom types') => \Yii::$app->cms->userPropertyTypes(),
    ])
        ->label(\Yii::t('skeeks/form2/app', 'Property type'))
        ;
    ?>
    <?= $form->field($model, 'component_settings')->label(false)->widget(
        \skeeks\cms\widgets\formInputs\componentSettings\ComponentSettingsWidget::className(),
        [
            'componentSelectId' => Html::getInputId($model, "component")
        ]
    ); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'code')->textInput() ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Additionally')) ?>
    <?= $form->field($model, 'hint')->textInput() ?>
    <?= $form->fieldInputInt($model, 'priority') ?>

    <?= $form->fieldRadioListBoolean($model, 'searchable') ?>
    <?= $form->fieldRadioListBoolean($model, 'filtrable') ?>
    <?= $form->fieldRadioListBoolean($model, 'smart_filtrable') ?>
    <?= $form->fieldRadioListBoolean($model, 'with_description') ?>
<?= $form->fieldSetEnd(); ?>


<? if (!$model->isNewRecord) : ?>
<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Values for the list')) ?>

    <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
        'label'             => \Yii::t('skeeks/form2/app', 'Values for the list'),
        'hint'              => \Yii::t('skeeks/form2/app', 'You can link to the item number of properties, and set them to the value'),
        'parentModel'       => $model,
        'relation'          => [
            'property_id' => 'id'
        ],

        'sort'              => [
            'defaultOrder' =>
            [
                'priority' => SORT_ASC
            ]
        ],

        'controllerRoute'   => 'form2/admin-form-property-enum',
        'gridViewOptions'   => [
            'sortable' => true,
            'columns' => [
                'id',
                'code',
                'value',
                'priority',
                'def',
            ],
        ],
    ]); ?>

<?= $form->fieldSetEnd(); ?>
<? endif; ?>

<?= $form->buttonsCreateOrUpdate($model); ?>

<?php ActiveForm::end(); ?>




