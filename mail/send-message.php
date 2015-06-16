<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 19.03.2015
 */
use skeeks\cms\mail\helpers\Html;
/**
 * @var $formSend \skeeks\modules\cms\form2\models\Form2FormSend
 * @var $form \skeeks\modules\cms\form2\models\Form2Form
 */
?>
<?= Html::beginTag('h1'); ?>
    Отправка формы  «<?= \yii\helpers\Html::encode($form->name)?>» #<?= $formSend->id; ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    Форма была заполнена и успешно отправлена со страницы: <?= Html::a($formSend->page_url, $formSend->page_url); ?><br />
    Дата и время отправки: <?= \Yii::$app->formatter->asDatetime($formSend->created_at) ?><br />
    Уникальный номер сообщения: <?= $formSend->id; ?>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('h3'); ?>
    Данные формы:
<?= Html::endTag('h3'); ?>

<?= Html::beginTag('p'); ?>
<?/* foreach((array) $form->fields() as $formField) : */?><!--
    <?/* if ($value = \yii\helpers\ArrayHelper::getValue((array) $formSend->data_values, $formField->attribute)) : */?>
            <?/*= Html::beginTag('b'); */?>
                <?/*= $formField->normalName() */?>:
            <?/*= Html::endTag('b'); */?>
            <?/*= Html::encode($value) */?>
            <br />
    <?/* endif; */?>
--><?/* endforeach; */?>

    <?= \yii\widgets\DetailView::widget([
        'model'         => $formSend->relatedPropertiesModel,
        'attributes'    => array_keys($formSend->relatedPropertiesModel->attributeLabels())
    ])?>

<?= Html::endTag('p'); ?>


<?= Html::beginTag('h5'); ?>
    Дополнительная информация:
<?= Html::endTag('h5'); ?>

<?= Html::beginTag('p'); ?>
    Дополнительные данные по данному сообщению можно посмотреть <?= Html::a('тут', \skeeks\cms\helpers\UrlHelper::construct('form2/admin-form-send/update', ['pk' => $formSend->id])->enableAdmin()->enableAbsolute()->toString()); ?>.
<?= Html::endTag('p'); ?>