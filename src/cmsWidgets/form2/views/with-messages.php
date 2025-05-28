<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.03.2015
 *
 * @var $widget \skeeks\modules\cms\form2\cmsWidgets\form2\FormWidget
 */
$modelHasRelatedProperties = $widget->modelForm->createModelFormSend();

?>
    <?php
    $successJs = '';
    $errorJs = '';
    if ($widget->successJs)
    {
        $successJs = <<<JS
        var successJs = {$widget->successJs};
        if (successJs)
        {
            successJs(jForm, data);
        }
JS;
;;
    }
    if ($widget->errorJs)
    {
        $errorJs = <<<JS
        var errorJs = {$widget->errorJs};
        if (errorJs)
        {
            errorJs(jForm, data);
        }
JS;
;
    }
    $form = \skeeks\modules\cms\form2\widgets\ActiveFormConstructForm::begin([
        'id'                                        => $widget->id . "-active-form",
        'modelForm'                                 => $widget->modelForm,
        'options'                                 => [
            'data' => [
                'form_code' => $widget->form_code,
                'form_namespace' => $widget->namespace,
            ]
        ],
        'clientCallback' => new \yii\web\JsExpression(<<<JS
    function (ActiveFormAjaxSubmit) {
    
        ActiveFormAjaxSubmit.on('error', function(e, response) {
            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseSuccessMessage", false);
            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseErrorMessage", false);
            
            var jForm = ActiveFormAjaxSubmit.jForm;
            var data = response.data;
            $('.sx-success-message', jForm).hide();
            $('.sx-error-message', jForm).show();
            $('.sx-error-message .sx-body', jForm).empty().append(response.message);

            {$errorJs}    
        });
        
        ActiveFormAjaxSubmit.on('success', function(e, response) {
            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseSuccessMessage", false);
            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseErrorMessage", false);
            
            var jForm = ActiveFormAjaxSubmit.jForm;
            var data = response.data;
            $('.sx-error-message', jForm).hide();
            $('.sx-success-message', jForm).show();
            $('.sx-success-message .sx-body', jForm).empty().append(response.message);

            $('input, textarea', jForm).each(function(value, key)
            {
                var name = $(this).attr('name');
                if (name != '_csrf' && name != 'sx-model-value' && name != 'sx-model')
                {
                    $(this).val('');
                }
            });

            {$successJs}
            
        });
        
    }
JS
),
        
        
    ]);
?>


    <?/*= $form->errorSummary($modelHasRelatedProperties); */?>

<?= \yii\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-success sx-success-message',
        'style' => 'display: none;',
    ],
    'closeButton' => false,
    'body' => '<div class="sx-body">Ok</div>',
])?>

<?= \yii\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-danger sx-error-message',
        'style' => 'display: none;',
    ],
    'closeButton' => false,
    'body' => '<div class="sx-body">Ok</div>',
])?>

<? if ($properties = $modelHasRelatedProperties->relatedProperties) : ?>
    <? foreach ($properties as $property) : ?>
        <?= $property->renderActiveForm($form, $modelHasRelatedProperties); ?>
    <? endforeach; ?>
<? endif; ?>

<?php if ($widget->modelForm->is_add_legal_checkbox) : ?>
<div class="form-group field-relatedpropertiesmodel-phone required has-error">
    <input type="checkbox" required id="sx-rules">
    <label for="sx-rules"><?php echo $widget->modelForm->legalCheckboxText; ?></label>
</div>
<? endif; ?>

<?= \yii\helpers\Html::tag($widget->btn_tag, \Yii::t('skeeks/form2/app', $widget->btnSubmit), $widget->btn_options); ?>

<?php \skeeks\modules\cms\form2\widgets\ActiveFormConstructForm::end(); ?>