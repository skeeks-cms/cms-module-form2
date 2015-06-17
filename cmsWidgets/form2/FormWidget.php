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
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class FormWidget
 * @package skeeks\cms\cmsWidgets\text
 */
class FormWidget extends WidgetRenderable
{
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => 'Конструктор форм'
        ]);
    }

    public $form_id;
    public $form_code;

    public $btnSubmit = "Отправить";
    public $btnSubmitClass = 'btn btn-primary';

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'form_id'   => 'Форма',
            'form_code' => 'Код формы',
            'btnSubmit' => 'Надпись на кнопке отправить',
            'btnSubmitClass' => 'CSS Класс кнопки отправить'
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

    /**
     * @var Form2Form
     */
    public $modelForm;

    protected function _run()
    {
        if (!$this->form_id)
        {
            if ($this->form_code)
            {
                $this->modelForm = Form2Form::find()->where(['code' => $this->form_code])->one();
                if ($form)
                {
                    $this->form_id = $form->id;
                }
            }
        } else
        {
            $this->modelForm = Form2Form::find()->where(['id' => $this->form_id])->one();
        }

        if (!$this->modelForm)
        {
            return "";
        }

        return parent::_run();
    }

}