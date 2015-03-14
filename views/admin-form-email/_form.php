<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \yii\db\ActiveRecord */
/* @var $console \skeeks\cms\controllers\AdminUserController */
?>


<?php $form = ActiveForm::begin(); ?>
<?php  ?>

<? if ($form_id = \Yii::$app->request->get('form_id')) : ?>
    <?= $form->field($model, 'form_id')->hiddenInput(['value' => $form_id])->label(false); ?>
<? else: ?>
    <?= $form->field($model, 'form_id')->label('Форма')->widget(
        \skeeks\widget\chosen\Chosen::className(), [
                'items' => \yii\helpers\ArrayHelper::map(
                    \skeeks\modules\cms\form\models\Form::find()->all(),
                     "id",
                     "name"
                 ),
        ]);
    ?>
<? endif; ?>

<?= $form->field($model, 'value')->textInput(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
