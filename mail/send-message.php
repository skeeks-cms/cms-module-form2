<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 19.03.2015
 */
use skeeks\cms\mail\helpers\Html;
/**
 * @var $formSendMessage \skeeks\modules\cms\form\models\FormSendMessage
 * @var $form \skeeks\modules\cms\form\models\Form
 * @var $formField \skeeks\modules\cms\form\models\FormField
 */
?>
<?= Html::beginTag('h1'); ?>
    Отправка формы  «<?= \yii\helpers\Html::encode($form->name)?>» #<?= $formSendMessage->id; ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    Форма была заполнена и успешно отправлена со страницы: <?= Html::a($formSendMessage->page_url, $formSendMessage->page_url); ?><br />
    Дата и время отправки: <?= \Yii::$app->formatter->asDatetime($formSendMessage->created_at) ?><br />
    Уникальный номер сообщения: <?= $formSendMessage->id; ?>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('h3'); ?>
    Данные формы:
<?= Html::endTag('h3'); ?>

<?= Html::beginTag('p'); ?>
<? foreach((array) $form->fields() as $formField) : ?>
    <? if ($value = \yii\helpers\ArrayHelper::getValue((array) $formSendMessage->data_values, $formField->attribute)) : ?>
            <?= Html::beginTag('b'); ?>
                <?= $formField->normalName() ?>:
            <?= Html::endTag('b'); ?>
            <?= Html::encode($value) ?>
            <br />
    <? endif; ?>
<? endforeach; ?>
<?= Html::endTag('p'); ?>


<?= Html::beginTag('h5'); ?>
    Дополнительная информация:
<?= Html::endTag('h5'); ?>

<?= Html::beginTag('p'); ?>
    Дополнительные данные по данному сообщению можно посмотреть <?= Html::a('тут', \skeeks\cms\helpers\UrlHelper::construct('form/admin-form-send-message/update', ['id' => $formSendMessage->id])->enableAdmin()->enableAbsolute()->toString()); ?>.
<?= Html::endTag('p'); ?>