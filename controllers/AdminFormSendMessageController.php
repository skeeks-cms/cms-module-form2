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
use skeeks\modules\cms\form\models\FormPhone;
use skeeks\modules\cms\form\models\FormSendMessage;

/**
 * Class AdminFormEmailController
 * @package skeeks\modules\cms\form\controllers
 */
class AdminFormSendMessageController extends AdminModelEditorSmartController
{
    public function init()
    {
        $this->_label                   = "Сообщения с форм";
        $this->_modelShowAttribute      = "id";
        $this->_modelClassName          = FormSendMessage::className();

        $this->modelValidate            = true;
        $this->enableScenarios          = true;

        $this->gridColumns = [
            'form_id',
            ['class' => \skeeks\cms\grid\CreatedAtColumn::className()],
            ['class' => \skeeks\cms\grid\CreatedByColumn::className()]
        ];

        parent::init();
    }


    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors[self::BEHAVIOR_ACTION_MANAGER]['actions']['create']);

        return $behaviors;
    }
}