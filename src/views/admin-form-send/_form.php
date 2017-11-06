<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \yii\db\ActiveRecord */
/* @var $console \skeeks\cms\controllers\AdminUserController */
?>


<?php $form = ActiveForm::begin(); ?>
<?php  ?>

<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'General information'))?>
    <?= $form->field($model, 'name')->textInput(); ?>
    <?= $form->field($model, 'code')->textInput(); ?>
    <?= $form->field($model, 'description')->textarea(); ?>
<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Notification settings'))?>


<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/form2/app', 'Form elements'))?>
    <?/*= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
        'label'             => "Элементы формы",
        'hint'              => "",
        'parentModel'       => $model,
        'relation'          => [
            'form_id' => 'id'
        ],

        'sort'              => [
            'defaultOrder' =>
            [
                'priority' => SORT_DESC
            ]
        ],

        'controllerRoute'   => 'form/admin-form-field',
        'gridViewOptions'   => [

            'sortable' => true,
            'columns' => [
                //['class' => 'yii\grid\SerialColumn'],
                'attribute',
                'name',
                'label',
                'hint',
                [
                    'class' => \skeeks\cms\grid\BooleanColumn::className(),
                    'attribute' => 'active'
                ],
            ],
        ],
    ]); */?>


<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>

<!--<div class="row">
    <div class="col-md-12">
        <div class="" style="border: 1px solid rgba(32, 168, 216, 0.23); padding: 10px; margin-top: 10px;">
            <h2>Вот так будет выглядеть форма:</h2>
            <hr />
            <?/*= $model->render(); */?>
        </div>

    </div>
</div>
-->