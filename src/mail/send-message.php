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
    <?= \Yii::t('skeeks/form2/app', 'Submitting forms');?>  «<?= \yii\helpers\Html::encode($form->name)?>» #<?= $formSend->id; ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    <?= \Yii::t('skeeks/form2/app', 'The form has been completed and successfully sent from the page');?>: <?= Html::a($formSend->page_url, $formSend->page_url); ?><br />
    <?= \Yii::t('skeeks/form2/app', 'Date and time of sending');?>: <?= \Yii::$app->formatter->asDatetime($formSend->created_at) ?><br />
    <?= \Yii::t('skeeks/form2/app', 'Unique message number');?>: <?= $formSend->id; ?>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('h3'); ?>
    <?= \Yii::t('skeeks/form2/app', 'Data from form');?>:
<?= Html::endTag('h3'); ?>

<?= Html::beginTag('p'); ?>



<?
$attribures = [];
$rp = $formSend->relatedPropertiesModel;
$rp->initAllProperties();
if ($attrs = $rp->attributeLabels())
{
    foreach ($attrs as $code => $value)
    {
        $data['attribute']  = $code;
        $data['format']     = 'raw';

        $value              = $rp->getAttributeAsHtml($code);
        $data['value']      = $value;
        if (is_array($value))
        {
            $data['value']      = implode(', ', $value);
        }

        $attribures[]       = $data;
    }
};
?>


    <?= \yii\widgets\DetailView::widget([
        'model'         => $rp,
        'attributes'    => $attribures
    ])?>

<?= Html::endTag('p'); ?>


<?= Html::beginTag('h5'); ?>
    <?= \Yii::t('skeeks/form2/app', 'Additional Data');?>:
<?= Html::endTag('h5'); ?>

<?= Html::beginTag('p'); ?>
    <?= \Yii::t('skeeks/form2/app', 'Additional information on the report can be viewed');?> <?= Html::a(\Yii::t('skeeks/form2/app', 'here'), \yii\helpers\Url::to(['/form2/admin-form-send/view', 'pk' => $formSend->id], true) ); ?>.
<?= Html::endTag('p'); ?>