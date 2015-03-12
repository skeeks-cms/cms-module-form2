
<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormStyled as ActiveForm;
use skeeks\cms\models\Tree;
use skeeks\cms\modules\admin\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model Tree */
?>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->buttonsCreateOrUpdate($model); ?>

<?php ActiveForm::end(); ?>




