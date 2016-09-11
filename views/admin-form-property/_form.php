<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\models\Tree;
use skeeks\cms\modules\admin\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model Tree */
?>



<?php $form = ActiveForm::begin([
    'id'                                            => 'sx-dynamic-form',
    'enableAjaxValidation'                          => false,
]); ?>

<? $this->registerJs(<<<JS

(function(sx, $, _)
{
    sx.classes.DynamicForm = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;

            $("[data-form-reload=true]").on('change', function()
            {
                self.update();
            });
        },

        update: function()
        {
            _.delay(function()
            {
                var jForm = $("#sx-dynamic-form");
                jForm.append($('<input>', {'type': 'hidden', 'name' : 'sx-not-submit', 'value': 'true'}));
                jForm.submit();
            }, 200);
        }
    });

    sx.DynamicForm = new sx.classes.DynamicForm();
})(sx, sx.$, sx._);


JS
); ?>

<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Basic settings')) ?>

    <?= $form->fieldRadioListBoolean($model, 'active') ?>
    <?= $form->fieldRadioListBoolean($model, 'is_required') ?>


    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'code')->textInput() ?>

    <?= $form->field($model, 'component')->listBox(array_merge(['' => ' — '], \Yii::$app->cms->relatedHandlersDataForSelect), [
            'size' => 1,
            'data-form-reload' => 'true'
        ])
        ->label(\Yii::t('skeeks/cms',"Property type"))
        ;
    ?>

    <? if ($handler) : ?>
        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget(['content' => \Yii::t('skeeks/cms', 'Settings')]); ?>
        <? if($handler instanceof \skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList) : ?>
            <? $handler->enumRoute = 'form2/admin-form-property-enum'; ?>
        <? endif; ?>
        <?= $handler->renderConfigForm($form); ?>
    <? endif; ?>



<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Additionally')) ?>
    <?= $form->field($model, 'hint')->textInput() ?>
    <?= $form->fieldInputInt($model, 'priority') ?>

    <?= $form->fieldRadioListBoolean($model, 'searchable') ?>
    <?= $form->fieldRadioListBoolean($model, 'filtrable') ?>
    <?= $form->fieldRadioListBoolean($model, 'smart_filtrable') ?>
    <?= $form->fieldRadioListBoolean($model, 'with_description') ?>


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

<?= $form->fieldSetEnd(); ?>


<?= $form->buttonsCreateOrUpdate($model); ?>

<?php ActiveForm::end(); ?>




