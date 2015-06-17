<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */
namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormProperty;
use yii\helpers\ArrayHelper;

/**
 * Class AdminFormController
 * @package skeeks\cms\controllers
 */
class AdminFormPropertyController extends AdminModelEditorController
{
    public function init()
    {
        $this->name                   = "Управление свойствами";
        $this->modelShowAttribute      = "name";
        $this->modelClassName          = Form2FormProperty::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                "update" =>
                [
                    "modelScenario" => RelatedPropertyModel::SCENARIO_UPDATE_CONFIG,
                ],
            ]
        );
    }
}