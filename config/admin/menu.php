<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */
return [

    'other' =>
    [
        'items' =>
        [
            [
                "label"     => "Конструктор форм",
                "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/forms.png'],

                'items' =>
                [
                    [
                        "label" => "Формы",
                        "url"   => ["form2/admin-form"],
                        "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/forms.png'],
                    ],

                    [
                        "label" => "Сообщения с форм",
                        "url"   => ["form2/admin-form-send"],
                        "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/form-submits.png'],
                    ],

                ]
            ],
        ]
    ]
];