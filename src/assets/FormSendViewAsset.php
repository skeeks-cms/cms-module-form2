<?php

namespace skeeks\modules\cms\form2\assets;

use skeeks\cms\backend\assets\BackendUiAsset;
use yii\web\AssetBundle;

final class FormSendViewAsset extends AssetBundle
{
    public $sourcePath = '@skeeks/modules/cms/form2/assets';

    public $css = [
        'form-send-view.css',
    ];

    public $depends = [
        BackendUiAsset::class,
    ];
}
