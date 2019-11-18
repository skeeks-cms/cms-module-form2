<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
return [
    'components' => [
        'backendAdmin' => [
            'menu' => [
                'data' => [
                    'other' => [
                        'items' => [
                            [
                                "name"  => ['skeeks/form2/app', 'Form designer'],
                                "image" => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/forms.png'],

                                'items' => [
                                    [
                                        "name"  => ['skeeks/form2/app', 'Forms'],
                                        "url"   => ["form2/admin-form"],
                                        "image" => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/forms.png'],
                                    ],

                                    [
                                        "name"  => ['skeeks/form2/app', 'Messages'],
                                        "url"   => ["form2/admin-form-send"],
                                        "image" => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/form-submits.png'],
                                    ],
                                ],
                            ],
                        ],
                    ],

                ],
            ],
        ],
    ],

    'modules' => [
        'form2' => [
            'class' => '\skeeks\modules\cms\form2\Module',
        ],
    ],
];