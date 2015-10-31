<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
namespace skeeks\modules\cms\form2;
/**
 * Class Module
 * @package skeeks\modules\cms\form2
 */
class Module extends \skeeks\cms\base\Module
{
    public $controllerNamespace = 'skeeks\modules\cms\form2\controllers';

    /**
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            "name"          => "Конструктор форм",
            "description"   => "Модуль прорабатывает модель данных игрового портала. Поставляет модели Игра, Игровой жанр, Игровая платформа, Игровая компания (разработчик, издатель)",
        ]);
    }

}