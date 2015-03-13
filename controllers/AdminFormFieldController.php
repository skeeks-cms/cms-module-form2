<?php
/**
 * AdminInfoblockController
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 09.11.2014
 * @since 1.0.0
 */
namespace skeeks\modules\cms\form\controllers;
use skeeks\cms\modules\admin\controllers\AdminModelEditorSmartController;
use skeeks\modules\cms\form\models\Form;
use skeeks\modules\cms\form\models\FormEmail;
use skeeks\modules\cms\form\models\FormField;
use skeeks\modules\cms\form\models\FormPhone;

/**
 * Class AdminFormEmailController
 * @package skeeks\modules\cms\form\controllers
 */
class AdminFormFieldController extends AdminModelEditorSmartController
{
    public function init()
    {
        $this->_label                   = "Управление email форм";
        $this->_modelClassName          = FormField::className();
        $this->modelValidate            = true;
        $this->enableScenarios          = true;
        parent::init();
    }
}