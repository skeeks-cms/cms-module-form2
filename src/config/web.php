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
                    'form2' => [
                        'priority' => 500,
                        'label'    => ['skeeks/form2/app', 'Form designer'],
                        "img"      => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/form.svg'],

                        'items' => [
                            [
                                "name"  => ['skeeks/form2/app', 'Forms'],
                                "url"   => ["form2/admin-form"],
                                "image" => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/form.svg'],
                            ],

                            [
                                "name"  => ['skeeks/form2/app', 'Messages'],
                                "url"   => ["form2/admin-form-send"],
                                "image" => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/mail.svg'],
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
