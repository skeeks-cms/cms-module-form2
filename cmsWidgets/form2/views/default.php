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
        'modelForm'                                 => $widget->modelForm,
    ]);
?>

<? if ($properties = $modelHasRelatedProperties->relatedProperties) : ?>
    <? foreach ($properties as $property) : ?>
        <?= $property->renderActiveForm($form, $modelHasRelatedProperties)?>
    <? endforeach; ?>
<? endif; ?>

<?= \yii\helpers\Html::submitButton("" . \Yii::t('app', 'Отправить'), [
    'class' => 'btn btn-primary',
]); ?>

<?php ActiveForm::end(); ?>