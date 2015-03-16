<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.03.2015
 */
namespace skeeks\modules\cms\form\elements;

use skeeks\cms\base\Widget;

abstract class Base extends Widget
{
    /**
     * @var bool Элемент или виджет
     */
    public $element = true;

    public $elementCode;

    public $value;
    public $attributeClass;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['attributeClass'], 'string'],
            [['value'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'attributeClass'                        => 'Класс',
            'value'                                 => 'Значение',
        ]);
    }
}