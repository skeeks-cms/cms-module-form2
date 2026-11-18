<?php

namespace skeeks\modules\cms\form2\assets\protection;

use skeeks\cms\assets\ActiveFormAjaxSubmitAsset;
use yii\web\AssetBundle;

/**
 * The browser part of the protection of public forms: the stamp, the bot check and the `form2Conversion` event.
 */
class Form2ProtectionAsset extends AssetBundle
{
    public $sourcePath = '@skeeks/modules/cms/form2/assets/protection';

    public $js = [
        'form2-protection.js',
    ];

    public $publishOptions = [
        'only' => ['*.js'],
    ];

    public $depends = [
        ActiveFormAjaxSubmitAsset::class,
    ];
}
