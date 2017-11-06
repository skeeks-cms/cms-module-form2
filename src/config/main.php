<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
return [
    'components' =>
    [
        'i18n' => [
            'translations' =>
            [
                'skeeks/form2/app' => [
                    'class'             => 'yii\i18n\PhpMessageSource',
                    'basePath'          => '@skeeks/modules/cms/form2/messages',
                    'fileMap' => [
                        'skeeks/form2/app' => 'app.php',
                    ],
                ]
            ]
        ],
    ],

    'modules' =>
    [
        'form2' => [
            'class'         => '\skeeks\modules\cms\form2\Module',
        ]
    ]
];