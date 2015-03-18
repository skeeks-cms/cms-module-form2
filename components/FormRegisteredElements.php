<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.03.2015
 */
namespace skeeks\modules\cms\form\components;

use skeeks\cms\base\db\ActiveRecord;
use skeeks\cms\components\CollectionComponents;
use skeeks\cms\components\RegisteredWidgets;
use skeeks\cms\models\StorageFile;
use skeeks\cms\models\WidgetDescriptor;
use skeeks\modules\cms\form\elements\Base;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * Class FormRegisteredElements
 * @package skeeks\modules\cms\form\components
 */
class FormRegisteredElements extends RegisteredWidgets
{
    public function baseElements()
    {
        return [
            'skeeks\modules\cms\form\elements\textarea\Textarea' =>
            [
                'name'          => 'Текстовое поле (textarea)',
            ],

            'skeeks\modules\cms\form\elements\textInput\TextInput' =>
            [
                'name'          => 'Текстовый input (TextInput)',
            ],

            'skeeks\modules\cms\form\elements\button\Button' =>
            [
                'name'          => 'Кнопка отправки формы',
                'description'   => '',
            ],
        ];
    }

    public function init()
    {
        parent::init();

        $this->components = ArrayHelper::merge($this->baseElements(), (array) $this->components);
    }
}