<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */
namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\ModelEditorGridAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\modules\cms\form2\models\Form2Form;
use yii\helpers\ArrayHelper;

/**
 * Class AdminFormController
 * @package skeeks\cms\controllers
 */
class AdminFormController extends AdminModelEditorController
{
    public function init()
    {
        $this->name                     = \Yii::t('skeeks/form2/app', 'Forms management');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = Form2Form::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'view' =>
                [
                    'class' => AdminOneModelEditAction::className(),
                    'name' => \Yii::t('skeeks/form2/app', 'Result'),
                    "icon" => "glyphicon glyphicon-eye-open",
                    "priority" => 0,
                ],
            ]
        );
    }

}