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
        'formRegisteredElements' =>
        [
            'class' => 'skeeks\modules\cms\form\components\FormRegisteredElements',
            'components' =>
            [
                /*'skeeks\modules\cms\form\elements\textarea\Textarea' =>
                [
                    'name'          => 'Текстовое поле (textarea)',
                    'description'   => 'Виджет выводит нужные одразделы',
                ],*/
            ]
        ],

        "registeredWidgets" =>
        [
            'components' =>
            [
                'skeeks\modules\cms\form\widgets\form\FormWidget' =>
                [
                    'name'          => 'Конструктор форм',
                    'description'   => 'Виджет показа сконструированной формы',
                ],
            ]
        ],

    ],

    'modules' =>
    [
        'form' => [
            'class'         => '\skeeks\modules\cms\form\Module',
        ]
    ]
];