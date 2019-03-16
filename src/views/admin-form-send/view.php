<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
/* @var $this yii\web\View */
/* @var $action \skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction */
/* @var $model \skeeks\modules\cms\form2\models\Form2FormSend */
$controller = $this->context;
$action = $controller->action;
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
if ($attrs = $model->relatedProperties)
{
    foreach ($attrs as $code => $value)
    {
        $data['attribute']  = $value->name;
        $data['format']     = 'raw';

        $data['value']      = $model->relatedPropertiesModel->getAttributeAsHtml($value->code);

        $attribures[]       = $data;
    }
}
?>
<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Data from form')); ?>
<?= \yii\widgets\DetailView::widget([
    'model'         => $model->relatedPropertiesModel,
    'attributes'    => $attribures
])?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Who has been notified')); ?>

<?= \yii\widgets\DetailView::widget([
    'model'         => $model,
    'attributes'    =>
        [
            [
                'attribute' => 'emails',
                'format'    => 'raw',
                'label'     => \Yii::t('skeeks/form2/app', 'Email Message'),
                'value'     => $model->emails
            ],

            [
                'attribute' => 'phones',
                'format'    => 'raw',
                'label'     => \Yii::t('skeeks/form2/app', 'Phone Message'),
                'value'     => $model->phones
            ],

            [
                'attribute' => 'user_ids',
                'format'    => 'raw',
                'label'     => \Yii::t('skeeks/form2/app', 'Users messages'),
                'value'     => $model->user_ids
            ],
        ]
]); ?>

<?= $form->fieldSetEnd(); ?>
<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Additional Data')); ?>
<?= \yii\widgets\DetailView::widget([
    'model'         => $model,
    'attributes'    =>
        [
            [
                'attribute'     => 'id',
                'label'         => \Yii::t('skeeks/form2/app', 'Post Number'),
            ],

            [
                'attribute' => 'created_at',
                'value'     => \Yii::$app->formatter->asDatetime($model->created_at, 'medium') . "(" . \Yii::$app->formatter->asRelativeTime($model->created_at) . ")",
            ],

            [
                'format' => 'raw',
                'label'  => \Yii::t('skeeks/form2/app', 'Post Number'),
                'value'  => "<a href=\"{$model->site->url}\" target=\"_blank\" data-pjax=\"0\">{$model->site->name}</a>",
            ],

            [
                'format' => 'raw',
                'label'  => \Yii::t('skeeks/form2/app', 'Submitted by'),
                'value'  => $model->createdBy ? $model->createdBy->displayName : '-',
            ],

            [
                'attribute' => 'ip',
                'label' => \Yii::t('skeeks/form2/app', 'Ip address of the sender'),
            ],

            [
                'attribute' => 'page_url',
                'format' => 'raw',
                'label' => \Yii::t('skeeks/form2/app', 'Page Url'),
                'value' => Html::a($model->page_url, $model->page_url, [
                    'target' => '_blank',
                    'data-pjax' => 0
                ])
            ],
        ]
]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Control')); ?>
<?= $form->fieldSelect($model, 'status', \skeeks\modules\cms\form2\models\Form2FormSend::getStatuses())
    ->hint(\Yii::t('skeeks/form2/app', 'If you are treated with this message, change the status for convenience')); ?>

<?= $form->field($model, 'processed_by')->widget(
        \skeeks\cms\backend\widgets\SelectModelDialogUserWidget::class
)
    ->hint(\Yii::t('skeeks/form2/app', 'If you are treated with this message, change the status for convenience')); ?>

<?= $form->field($model, 'comment')->textarea(['rows' => 5])->hint(\Yii::t('skeeks/form2/app', 'Short note, personal notes on this ship. Not necessary.')); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'For developers')); ?>

    <div class="sx-block">
        <h3><?=\Yii::t('skeeks/form2/app', 'Additional information that may be useful in some cases, the developers.');?></h3>
        <small><?=\Yii::t('skeeks/form2/app', 'For the convenience of viewing the data, you can use the service:');?> <a href="http://jsonformatter.curiousconcept.com/#" target="_blank">http://jsonformatter.curiousconcept.com/#</a></small>
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