<?php
/**
 * Publications
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 08.12.2014
 * @since 1.0.0
 */
namespace skeeks\modules\cms\form\widgets\form;

use skeeks\cms\base\Widget;
use skeeks\cms\models\Publication;
use skeeks\cms\models\Search;
use skeeks\cms\models\Tree;
use skeeks\cms\widgets\base\hasModels\WidgetHasModels;
use skeeks\cms\widgets\base\hasModelsSmart\WidgetHasModelsSmart;
use skeeks\cms\widgets\WidgetHasTemplate;
use skeeks\modules\cms\form\models\Form;
use skeeks\modules\cms\slider\models\Slide;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * Class FormWidget
 * @package skeeks\modules\cms\form\widgets\form
 */
class FormWidget extends Widget
{
    /**
     * @var null|string
     */
    public $slider_id               = '';

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['form_id'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'form_id'                         => 'Форма',
        ]);
    }


    public function run()
    {
        parent::run();
        $foemModel = Form::find()->where(['id' => $this->form_id])->one();
        if ($foemModel)
        {

        }

        return '';
    }




}
