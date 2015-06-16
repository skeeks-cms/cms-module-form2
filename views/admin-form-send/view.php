<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
/* @var $this yii\web\View */
/* @var $action \skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction */
/* @var $model \skeeks\modules\cms\form2\models\Form2FormSend */
$model = $action->controller->model;
?>

<? $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Данные с формы'); ?>
    <? foreach($model->relatedPropertiesModel->attributeLabels() as $code => $label) : ?>
        <p><b><?= $label; ?>:</b> <?= $model->relatedPropertiesModel->getAttribute($code); ?></p>
    <? endforeach; ?>
<?= $form->fieldSetEnd(); ?>
<?= $form->fieldSet('Дополнительная информация'); ?>
    <p><b>Дата и время отправки:</b> <?= \Yii::$app->formatter->asDatetime($model->created_at, 'medium'); ?> (<?= \Yii::$app->formatter->asRelativeTime($model->created_at); ?>)</p>
<?= $form->fieldSetEnd(); ?>

<? ActiveForm::end(); ?>
