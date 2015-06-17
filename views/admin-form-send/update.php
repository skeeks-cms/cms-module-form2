<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
/* @var $this yii\web\View */
/* @var $action \skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction */
/* @var $model \skeeks\modules\cms\form2\models\Form2FormSend */
$model = $action->controller->model;

if ($model->status == \skeeks\modules\cms\form2\models\Form2FormSend::STATUS_NEW && !$model->processed_by)
{
    $model->processed_by = \Yii::$app->user->identity->id;
    $model->processed_at = \Yii::$app->formatter->asTimestamp(time());
    $model->status = \skeeks\modules\cms\form2\models\Form2FormSend::STATUS_PROCESSED;

    $model->save();
}

?>

<? $form = ActiveForm::begin(); ?>

<?
$attribures = [];
if ($attrs = $model->relatedPropertiesModel->attributeLabels())
{
    foreach ($attrs as $code => $value)
    {
        $data['attribute']  = $code;
        $data['format']     = 'raw';

        $value              = $model->relatedPropertiesModel->getSmartAttribute($code);
        $data['value']      = $value;
        if (is_array($value))
        {
            $data['value']      = implode(', ', $value);
        }

        $attribures[]       = $data;
    }
};
?>
<?= $form->fieldSet('Данные с формы'); ?>
    <?= \yii\widgets\DetailView::widget([
        'model'         => $model->relatedPropertiesModel,
        'attributes'    => $attribures
    ])?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Кто был уведомлен'); ?>

    <?= \yii\widgets\DetailView::widget([
        'model'         => $model,
        'attributes'    =>
        [
            [
                'attribute' => 'emails',
                'format' => 'raw',
                'label' => 'Email уведомления',
                'value' => implode(", ", $model->emails)
            ],

            [
                'attribute' => 'phones',
                'format' => 'raw',
                'label' => 'Sms уведомления',
                'value' => implode(", ", $model->phones)
            ],

            [
                'attribute' => 'user_ids',
                'format' => 'raw',
                'label' => 'Уведомления пользователей',
                'value' => implode(", ", $model->user_ids)
            ],
        ]
    ]); ?>

<?= $form->fieldSetEnd(); ?>
<?= $form->fieldSet('Дополнительная информация'); ?>
    <?= \yii\widgets\DetailView::widget([
        'model'         => $model,
        'attributes'    =>
        [
            [
                'attribute'     => 'id',
                'label'         => 'Номер сообщения',
            ],

            [
                'attribute' => 'created_at',
                'value' => \Yii::$app->formatter->asDatetime($model->created_at, 'medium') . "(" . \Yii::$app->formatter->asRelativeTime($model->created_at) . ")",
            ],

            [
                'format' => 'raw',
                'label' => 'Отправлено с сайта',
                'value' => "<a href=\"{$model->site->url}\" target=\"_blank\" data-pjax=\"0\">{$model->site->name}</a>",
            ],

            [
                'format' => 'raw',
                'label' => 'Отправил пользователь',
                'value' => "{$model->createdBy->displayName}",
            ],

            [
                'attribute' => 'ip',
                'label' => 'Ip адрес отправителя',
            ],

            [
                'attribute' => 'page_url',
                'format' => 'raw',
                'label' => 'Отправлена со страницы',
                'value' => Html::a($model->page_url, $model->page_url, [
                    'target' => '_blank',
                    'data-pjax' => 0
                ])
            ],
        ]
    ]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Управление'); ?>
    <?= $form->fieldSelect($model, 'status', \skeeks\modules\cms\form2\models\Form2FormSend::$statuses)
        ->hint('Если вы обработали это сообщение, измените его статус для удобства'); ?>

    <?= $form->fieldSelect($model, 'processed_by', \yii\helpers\ArrayHelper::map(
            \skeeks\cms\models\User::find()->active()->all(),
            'id',
            'displayName'
        ))
        ->hint('Если вы обработали это сообщение, измените его статус для удобства'); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Для разработчиков'); ?>

<div class="sx-block">
  <h3>Дополнительные данные, которые могут пригодиться в некоторых случаях, разработчикам.</h3>
  <small>Для удобства просмотра данных, можно воспользоваться сервисом: <a href="http://jsonformatter.curiousconcept.com/#" target="_blank">http://jsonformatter.curiousconcept.com/#</a></small>
</div>
<hr />


    <?= \yii\widgets\DetailView::widget([
        'model'         => $model,
        'attributes'    =>
        [
            [
                'attribute' => 'data_server',
                'format' => 'raw',
                'label' => 'SERVER',
                'value' => "<textarea class='form-control' rows=\"10\">" . \yii\helpers\Json::encode($model->data_server) . "</textarea>"
            ],

            [
                'attribute' => 'data_cookie',
                'format' => 'raw',
                'label' => 'COOKIE',
                'value' => "<textarea class='form-control' rows=\"5\">" . \yii\helpers\Json::encode($model->data_cookie) . "</textarea>"
            ],

            [
                'attribute' => 'data_session',
                'format' => 'raw',
                'label' => 'SESSION',
                'value' => "<textarea class='form-control' rows=\"5\">" . \yii\helpers\Json::encode($model->data_session) . "</textarea>"
            ],

            [
                'attribute' => 'data_request',
                'format' => 'raw',
                'label' => 'REQUEST',
                'value' => "<textarea class='form-control' rows=\"10\">" . \yii\helpers\Json::encode($model->data_request) . "</textarea>"
            ],

            [
                'attribute' => 'data_labels',
                'format' => 'raw',
                'value' => "<textarea class='form-control' rows=\"10\">" . \yii\helpers\Json::encode($model->data_labels) . "</textarea>"
            ],
            [
                'attribute' => 'data_values',
                'format' => 'raw',
                'value' => "<textarea class='form-control' rows=\"10\">" . \yii\helpers\Json::encode($model->data_values) . "</textarea>"
            ],
        ]
    ]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model); ?>

<? ActiveForm::end(); ?>
