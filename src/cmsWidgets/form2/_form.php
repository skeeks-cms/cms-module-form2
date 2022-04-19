<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.05.2015
 */
/* @var $this yii\web\View */
?>
<?= $form->fieldSet('Настройки'); ?>

    <?/*= $form->fieldSelect($model, 'form_id', \yii\helpers\ArrayHelper::map(
        \skeeks\modules\cms\form2\models\Form2Form::find()->all(),
        'id',
        'name'
    )); */?>

    <?= $form->field($model, 'form_id')->widget(
        \skeeks\cms\widgets\formInputs\EditedSelect::className(),
        [
            'controllerRoute' => '/form2/admin-form',
            'items' => \yii\helpers\ArrayHelper::map(
            \skeeks\modules\cms\form2\models\Form2Form::find()->cmsSite()->all(),
                'id',
                'name'
            ),
        ]
    ); ?>

    <?= $form->field($model, 'btnSubmit')->textInput(); ?>
    <?= $form->field($model, 'btnSubmitClass')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>
