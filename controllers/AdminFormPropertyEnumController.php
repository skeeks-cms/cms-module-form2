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
use skeeks\modules\cms\form2\models\Form2FormPropertyEnum;
use yii\helpers\ArrayHelper;

/**
 * Class AdminFormPropertyEnumController
 * @package skeeks\modules\cms\form2\controllers
 */
class AdminFormPropertyEnumController extends AdminModelEditorController
{
    public function init()
    {
        $this->name                   = \Yii::t('skeeks/form2/app', 'Control of properties');
        $this->modelShowAttribute     = "value";
        $this->modelClassName         = Form2FormPropertyEnum::className();

        parent::init();

    }
}