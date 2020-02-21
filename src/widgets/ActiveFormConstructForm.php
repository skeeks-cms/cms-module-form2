<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (�����)
 * @date 18.03.2015
 */

namespace skeeks\modules\cms\form2\widgets;

use skeeks\cms\modules\admin\traits\ActiveFormTrait;
use skeeks\cms\modules\admin\traits\AdminActiveFormTrait;
use skeeks\cms\traits\ActiveFormAjaxSubmitTrait;
use skeeks\modules\cms\form\models\Form;
use skeeks\modules\cms\form2\models\Form2Form;

/**
 * Class ActiveFormRelatedProperties
 * @package skeeks\cms\widgets
 */
class ActiveFormConstructForm extends \skeeks\cms\base\widgets\ActiveForm
{
    use AdminActiveFormTrait;
    use ActiveFormAjaxSubmitTrait;

    public $afterValidateCallback = "";

    /**
     * @var Form2Form
     */
    public $modelForm;

    public function __construct($config = [])
    {
        $this->validationUrl = \skeeks\cms\helpers\UrlHelper::construct('form2/backend/validate')->toString();
        $this->action = \skeeks\cms\helpers\UrlHelper::construct('form2/backend/submit')->toString();

        $this->enableAjaxValidation = true;
        $this->validateOnChange = false;
        $this->validateOnBlur = false;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        echo \yii\helpers\Html::hiddenInput("sx-model-value", $this->modelForm->id);
        echo \yii\helpers\Html::hiddenInput("sx-model", $this->modelForm->className());
    }
}
