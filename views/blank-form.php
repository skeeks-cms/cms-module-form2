<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.03.2015
 *
 * @var $formField \skeeks\modules\cms\form\models\FormField
 * @var $model \skeeks\modules\cms\form\models\FormValidateModel
 */
use skeeks\modules\cms\form\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin([
    'modelForm'                 => $model->modelForm,
]);
?>

<? if ($fields) : ?>
    <? foreach ($fields as $formField) : ?>
        <?= $formField->renderActiveForm($form, $model)?>
    <? endforeach; ?>
<? endif; ?>

<?php ActiveForm::end(); ?>