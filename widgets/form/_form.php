<?php
/**
 * _form
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 09.11.2014
 * @since 1.0.0
 */

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\WidgetConfig */
?>
<?php $form = ActiveForm::begin(); ?>
<?= $form->templateElement($model); ?>

<?= $form->field($model, 'form_id')->widget(

    \skeeks\cms\widgets\formInputs\EditedSelect::className(), [
        'items' => \yii\helpers\ArrayHelper::map(
            \skeeks\modules\cms\slider\models\Slider::find()->all(),
            'id', 'name'
        ),
        'controllerRoute' => 'form/admin-form',
    ]);
?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php $form = ActiveForm::end(); ?>