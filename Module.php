<?php
/**
 * Module
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 31.10.2014
 * @since 1.0.0
 */
namespace skeeks\modules\cms\game;

use skeeks\cms\base\Module as CmsModule;
use skeeks\cms\App;
use yii\helpers\Inflector;

/**
 * Class Module
 * @package skeeks\cms\modules\admin
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
            "name"          => "Модуль форм",
            "description"   => "Модуль прорабатывает модель данных игрового портала. Поставляет модели Игра, Игровой жанр, Игровая платформа, Игровая компания (разработчик, издатель)",

            "admin" =>
            [
                "items" =>
                [
                    [
                        "label" => "Конструктор форм",
                        "route" => ["form/admin-form"]
                    ],

                    [
                        "label" => "Сообщения с форм",
                        "route" => ["form/admin-form"]
                    ],
                ]
            ]
        ]);
    }

}