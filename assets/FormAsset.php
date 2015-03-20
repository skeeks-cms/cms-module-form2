<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */

namespace skeeks\modules\cms\form\assets;
use skeeks\cms\base\AssetBundle;

/**
 * Class SliderAsset
 * @package skeeks\modules\cms\slider\assets
 */
class FormAsset extends AssetBundle
{
    public $sourcePath = '@vendor/skeeks/cms-module-form/assets';

    public $css = [];
    public $js = [];
    public $depends = [];
}
