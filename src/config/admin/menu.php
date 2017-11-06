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
                "label"     => \Yii::t('skeeks/form2/app', 'Form designer'),
                "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/forms.png'],

                'items' =>
                [
                    [
                        "label" => \Yii::t('skeeks/form2/app', 'Forms'),
                        "url"   => ["form2/admin-form"],
                        "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/forms.png'],
                    ],

                    [
                        "label" => \Yii::t('skeeks/form2/app', 'Messages'),
                        "url"   => ["form2/admin-form-send"],
                        "img"       => ['\skeeks\modules\cms\form2\assets\FormAsset', 'icons/form-submits.png'],
                    ],

                ]
            ],
        ]
    ]
];