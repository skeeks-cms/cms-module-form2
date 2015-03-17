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
use \skeeks\cms\base\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin([
    'id' => 'sx_form_' . $model->form->id,
]); ?>

<? if ($fields) : ?>
    <? foreach ($fields as $formField) : ?>
        <?= $form->field($model, $formField->attribute)->textInput(); ?>
    <? endforeach; ?>
<? endif; ?>

<?php ActiveForm::end(); ?>