<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 18.03.2015
 */

namespace skeeks\modules\cms\form2\widgets;

use skeeks\cms\modules\admin\traits\AdminActiveFormTrait;
use skeeks\cms\traits\ActiveFormAjaxSubmitTrait;
use skeeks\modules\cms\form2\assets\protection\Form2ProtectionAsset;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\Module;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * The public form of the form constructor: sends by ajax and carries the protection fields (the trap field and the
 * hidden field of the stamp, filled by form2-protection.js). Every template of a form gets them from here.
 */
class ActiveFormConstructForm extends \skeeks\cms\base\widgets\ActiveForm
{
    use AdminActiveFormTrait;
    use ActiveFormAjaxSubmitTrait;

    public $afterValidateCallback = "";

    public $validateOnChange = true;
    public $validateOnBlur = true;

    /**
     * @var Form2Form
     */
    public $modelForm;

    public function __construct($config = [])
    {
        //$this->validationUrl = \skeeks\cms\helpers\UrlHelper::construct('form2/backend/validate')->toString();
        $this->action = \skeeks\cms\helpers\UrlHelper::construct('/form2/backend/submit')->toString();

        /*$this->enableAjaxValidation = true;
        $this->validateOnChange = false;
        $this->validateOnBlur = false;*/

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        echo \yii\helpers\Html::hiddenInput("sx-model-value", $this->modelForm->id);
        echo \yii\helpers\Html::hiddenInput("sx-model", $this->modelForm->className());

        $protection = Module::protection();
        // The trap: invisible and unreachable for people, autofill and screen readers.
        echo Html::tag('div',
            Html::textInput($protection->honeypotField, '', [
                'tabindex' => '-1',
                'autocomplete' => 'off',
                'aria-hidden' => 'true',
                'data-form2-trap' => '1',
            ]),
            [
                'aria-hidden' => 'true',
                'style' => 'position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;',
            ]
        );
        echo Html::hiddenInput($protection->tokenField, '', ['data-form2-token' => '1']);
    }

    public function run()
    {
        $this->registerJs();

        // The protection belongs to this instance: its DOM id makes the registration unique, so two instances of one
        // form on a page (a block and a popup) each get their own stamp, waiting and reset.
        Form2ProtectionAsset::register($this->view);
        $protection = Module::protection();
        $options = Json::htmlEncode([
            'url' => Url::to(['/form2/backend/challenge']),
            'formDomId' => (string)$this->id,
            'formId' => (int)$this->modelForm->id,
            'model' => get_class($this->modelForm),
            'field' => $protection->tokenField,
            'trapField' => $protection->honeypotField,
        ]);
        $this->view->registerJs("sx.Form2Protection.attach(sx.ActiveForm, {$options});");

        return parent::run();
    }
}
