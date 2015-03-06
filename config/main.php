<?php
/**
 * main
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 20.01.2015
 * @since 1.0.0
 */
return [
    'components' =>
    [
        'adminMenu' =>
        [
            'groups' =>
            [
                'form' =>
                [
                    'label'     => 'Конструктор форм',
                    'priority'  => 0,

                    'items' =>
                    [
                        [
                            "label" => "Формы",
                            "url"   => ["form/admin-form"]
                        ],

                        [
                            "label" => "Сообщения с форм",
                            "url"   => ["form/admin-message"]
                        ],

                    ]
                ]
            ]
        ]
    ],

    'modules' =>
    [
        'form' => [
            'class'         => '\skeeks\modules\cms\form\Module',
        ]
    ]
];