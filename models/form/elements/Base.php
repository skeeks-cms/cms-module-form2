<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.03.2015
 */
namespace skeeks\modules\cms\form\models\form\elements;

use skeeks\cms\base\Widget;

class Base extends Widget
{
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