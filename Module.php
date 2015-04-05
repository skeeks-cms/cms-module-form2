<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
namespace skeeks\modules\cms\form;

use skeeks\cms\base\Module as CmsModule;

/**
 * Class Module
 * @package skeeks\modules\cms\form
 */
class Module extends CmsModule
{
    public $controllerNamespace = 'skeeks\modules\cms\form\controllers';

    /**
     * @return array
     */
    protected function _descriptor()
    {
        return array_merge(parent::_descriptor(), [
            "version"       => "1.0.3",

            "name"          => "Модуль конструктор форм",
            "description"   => "Модуль прорабатывает модель данных игрового портала. Поставляет модели Игра, Игровой жанр, Игровая платформа, Игровая компания (разработчик, издатель)",
        ]);
    }

}