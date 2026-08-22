<?php

use skeeks\cms\backend\helpers\BackendIcon;
use skeeks\cms\backend\widgets\BackendModelHeader;
use skeeks\modules\cms\form2\models\Form2FormSend;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var Form2FormSend $model */

$statusClass = ArrayHelper::getValue([
    Form2FormSend::STATUS_NEW => 'sx-status--danger',
    Form2FormSend::STATUS_PROCESSED => 'sx-status--warning',
    Form2FormSend::STATUS_EXECUTED => 'sx-status--success',
], (int)$model->status, 'sx-status--info');
$statusText = ArrayHelper::getValue(Form2FormSend::getStatuses(), $model->status, 'Неизвестный статус');
$status = Html::tag('div', Html::encode($statusText), [
    'class' => 'sx-status '.$statusClass,
]);

if ($model->processedBy) {
    $status .= Html::tag('div',
        BackendIcon::render('user', ['size' => 13]).' '.Html::encode((string)$model->processedBy->displayName),
        ['class' => 'sx-model-header__status-note']
    );
}

$metaItems = [];
if ($model->form) {
    $metaItems[] = BackendIcon::render('mail', ['size' => 13])
        .' '.Html::encode((string)$model->form->name);
}

echo BackendModelHeader::widget([
    'model' => $model,
    'title' => 'Отправка №'.(int)$model->id,
    'imageSrc' => false,
    'metaItems' => $metaItems,
    'status' => $status,
    'showBackLink' => false,
]);
