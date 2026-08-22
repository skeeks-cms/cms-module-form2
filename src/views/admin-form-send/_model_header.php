<?php

use skeeks\cms\backend\helpers\BackendIcon;
use skeeks\cms\backend\widgets\BackendModelHeader;
use skeeks\cms\models\CmsLead;
use skeeks\modules\cms\form2\models\Form2FormSend;
use yii\helpers\Html;

/** @var Form2FormSend $model */

$lead = $model->cmsLead;
if ($lead) {
    $status = Html::tag('div', Html::encode($lead->statusName), [
        'class' => 'sx-status '.CmsLead::statusCssClass($lead->status),
    ]);
} else {
    $status = Html::tag('div', 'Без лида', ['class' => 'sx-status sx-status--info']);
}

if ($lead && $lead->executor) {
    $status .= Html::tag('div',
        BackendIcon::render('user', ['size' => 13]).' '.Html::encode((string)$lead->executor->displayName),
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
    'title' => $model->displayName,
    'imageSrc' => false,
    'metaItems' => $metaItems,
    'status' => $status,
    'showBackLink' => false,
]);
