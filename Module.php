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
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            "version"               => file_get_contents(__DIR__ . "/VERSION"),

            "name"          => "Конструктор форм",
            "description"   => "Модуль прорабатывает модель данных игрового портала. Поставляет модели Игра, Игровой жанр, Игровая платформа, Игровая компания (разработчик, издатель)",
        ]);
    }

}