<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */
return [
    'form' =>
    [
        'label'     => 'Конструктор форм',
        'priority'  => 0,
        "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/forms.png'],

        'items' =>
        [
            [
                "label" => "Формы",
                "url"   => ["form/admin-form"],
                "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/forms.png'],
            ],

            [
                "label" => "Сообщения с форм",
                "url"   => ["form/admin-form-send-message"],
                "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/form-submits.png'],
            ],

        ]
    ]
];