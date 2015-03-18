<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 18.03.2015
 */
namespace skeeks\modules\cms\form\widgets;
use skeeks\modules\cms\form\models\Form;

/**
 * Class ActiveForm
 * @package skeeks\modules\cms\form\widgets
 */
class ActiveForm extends \skeeks\cms\base\widgets\ActiveForm
{
    /**
     * @var Form
     */
    public $modelForm;

    public function __construct($config = [])
    {
        $this->validationUrl                = \skeeks\cms\helpers\UrlHelper::construct('form/backend/validate')->toString();
        $this->action                       = \skeeks\cms\helpers\UrlHelper::construct('form/backend/submit')->toString();

        $this->enableAjaxValidation         = true;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        echo \yii\helpers\Html::hiddenInput(Form::FROM_PARAM_ID_NAME, $this->modelForm->id);
    }
}
