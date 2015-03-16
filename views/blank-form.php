<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.03.2015
 *
 * @var $formField \skeeks\modules\cms\form\models\FormField
 */
use \skeeks\cms\base\widgets\ActiveForm;
?>
Форма
<? $form = ActiveForm::begin(); ?>
    <? if ($fields) : ?>
        <? foreach ($fields as $formField) : ?>
            <?/*= $form->field($model, $formField->attribute)->textarea(); */?>
            <?= $formField->attribute; ?>
        <? endforeach; ?>
    <? endif; ?>
<? ActiveForm::end(); ?>