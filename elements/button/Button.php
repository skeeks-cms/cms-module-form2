<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.03.2015
 */
namespace skeeks\modules\cms\form\elements\button;

use skeeks\cms\base\InputWidget;
use skeeks\cms\widgets\formInputs\storageFiles\Widget;
use skeeks\modules\cms\form\elements\Base;
use yii\helpers\Html;

class Button extends InputWidget
{
    public function run()
    {
        $submit = Html::submitButton("Отправить", ['class' => 'btn btn-primary']);

        return Html::tag('div',
            $submit,
            ['class' => 'form-group']
        );
    }
}