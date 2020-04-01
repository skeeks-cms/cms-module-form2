<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
return [
    'components' => [
        'i18n' => [
            'translations' => [
                'skeeks/form2/app' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@skeeks/modules/cms/form2/messages',
                    'fileMap'  => [
                        'skeeks/form2/app' => 'app.php',
                    ],
                ],
            ],
        ],

        'authManager' => [
            'config' => [
                'roles' => [
                    [
                        'name'  => \skeeks\cms\rbac\CmsManager::ROLE_ADMIN,
                        'child' => [
                            //Есть доступ к системе администрирования
                            'permissions' => [
                                "form2/admin-form",
                                "form2/admin-form-send",
                                "form2/admin-form-property",
                                "form2/admin-form-property-enum",
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];