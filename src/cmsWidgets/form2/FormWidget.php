<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.06.2015
 */

namespace skeeks\modules\cms\form2\cmsWidgets\form2;

use skeeks\cms\base\Widget;
use skeeks\cms\base\WidgetRenderable;
use skeeks\cms\helpers\UrlHelper;
use skeeks\modules\cms\form2\models\Form2Form;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/**
 * Class FormWidget
 * @package skeeks\cms\cmsWidgets\text
 */
class FormWidget extends WidgetRenderable
{
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/form2/app', 'Form designer')
        ]);
    }

    /**
     * @var array
     */
    public $activeFormConfig = [];

    public $form_id;
    public $form_code;

    public $btn_tag     = 'button';
    public $btn_options = [];

    public $btnSubmit = null;
    public $btnSubmitClass = 'btn btn-primary';

    public $afterValidateJs = '';
    public $successJs = '';
    public $errorJs = '';

    public function init()
    {
        if (!$this->btnSubmit)
        {
            $this->btnSubmit = \Yii::t('skeeks/form2/app', 'Submit');
        }

        $this->btn_options = ArrayHelper::merge([
            'type'  => 'submit',
            'class' => $this->btnSubmitClass,
        ], $this->btn_options);

        parent::init();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'form_id'        => \Yii::t('skeeks/form2/app', 'Form'),
            'form_code'      => \Yii::t('skeeks/form2/app', 'Form code'),
            'btnSubmit'      => \Yii::t('skeeks/form2/app', 'The inscription on the button send'),
            'btnSubmitClass' => \Yii::t('skeeks/form2/app', 'CSS Class send button')
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
        [
            ['form_id', 'integer'],
            ['form_code', 'string'],
            ['btnSubmit', 'string'],
            ['btnSubmitClass', 'string'],
        ]);
    }

    public function renderConfigForm(ActiveForm $form)
    {
        echo \Yii::$app->view->renderFile(__DIR__ . '/_form.php', [
            'form'  => $form,
            'model' => $this
        ], $this);
    }

    /**
     * @var Form2Form
     */
    public $modelForm;

    public function run()
    {
        try
        {
            $form = null;

            if ($this->form_id)
            {
                $this->modelForm = Form2Form::find()->cmsSite()->andWhere(['id' => $this->form_id])->one();

                if (!$this->modelForm)
                {
                    throw new ErrorException("Форма не найдена: id=" . $this->form_id);
                }

            } else
            {
                if ($this->form_code)
                {
                    $this->modelForm = Form2Form::find()->cmsSite()->andWhere(['code' => $this->form_code])->one();
                    if ($form)
                    {
                        $this->form_id = $form->id;
                    }
                }

                if (!$this->modelForm)
                {
                    throw new ErrorException("Форма не найдена: code=" . $this->form_code) ;
                }
            }
        } catch (\Exception $e)
        {
            \Yii::info($e->getMessage(), static::className());
        }

        if (!$this->modelForm)
        {
            return "";
        }

        return parent::run();
    }

}
