<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.03.2015
 *
 * @var $widget \skeeks\modules\cms\form2\cmsWidgets\form2\FormWidget
 */
use skeeks\modules\cms\form2\widgets\ActiveFormConstructForm as ActiveForm;

$modelHasRelatedProperties = $widget->modelForm->createModelFormSend();

?>
    <?php $form = ActiveForm::begin([
        'id'                                        => $widget->id . "-active-form",
        'modelForm'                                 => $widget->modelForm,
        'options'                                 => [
            'data' => [
                'form_code' => $widget->form_code,
                'form_namespace' => $widget->namespace,
            ]
        ],
        'clientCallback'                     => new \yii\web\JsExpression(<<<JS
            function(ActiveFormAjaxSubmit)
            {
                ActiveFormAjaxSubmit.on('success', function(e, response)
                {
                    $('input, textarea', ActiveFormAjaxSubmit.jForm).each(function(value, key)
                    {
                        var name = $(this).attr('name');
                        if (name != '_csrf' && name != 'sx-model-value' && name != 'sx-model')
                        {
                            $(this).val('');
                        }
                    });
                });
            }
<script>     
sx.EventManager.on("ajaxFormSuccessSubmited", function(e, data) {
    //Успешно отправлена любая форма
    //ym(67855000,'reachGoal','any-form');
    
    if (data.form.jForm.data('form_code') == 'register') {
        //Отправлена конкретная форма (регистрация)
        ym(67855000,'reachGoal','register');
    }
    
    
    if (data.form.jForm.data('form_code') == 'test') {
        //Отправлена конкретная форма (с кодом test)
    }
});
</script>
JS
),
    ]);
?>

<? if ($properties = $modelHasRelatedProperties->relatedProperties) : ?>
    <? foreach ($properties as $property) : ?>
        <?= $property->renderActiveForm($form, $modelHasRelatedProperties); ?>
    <? endforeach; ?>
<? endif; ?>

<?= \yii\helpers\Html::submitButton("" . \Yii::t('skeeks/form2/app', $widget->btnSubmit), [
    'class' => $widget->btnSubmitClass,
]); ?>

<?php ActiveForm::end(); ?>