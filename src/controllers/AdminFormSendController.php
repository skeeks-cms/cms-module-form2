<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use yii\helpers\ArrayHelper;

/**
 * Class AdminFormController
 * @package skeeks\cms\controllers
 */
class AdminFormSendController extends AdminModelEditorController
{
    public function init()
    {
        $this->name                     = \Yii::t('skeeks/form2/app', 'Messages');
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = Form2FormSend::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = ArrayHelper::merge(parent::actions(),
        [
            'view' =>
            [
                'class' => BackendModelAction::class,
                'name' => \Yii::t('skeeks/form2/app', 'View'),
                "icon" => "glyphicon glyphicon-eye-open",
                "priority" => 0,
            ],
        ]);

        ArrayHelper::remove($actions, 'update');
        ArrayHelper::remove($actions, 'create');
        ArrayHelper::remove($actions, 'system');
        ArrayHelper::remove($actions, 'related-properties');

        return $actions;
    }
}