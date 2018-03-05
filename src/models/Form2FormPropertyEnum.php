<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
namespace skeeks\modules\cms\form2\models;

use skeeks\cms\models\Core;
use skeeks\cms\relatedProperties\models\RelatedPropertyEnumModel;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%cms_content_property_enum}}".
 *
 * @property Form2FormProperty $property
 */
class Form2FormPropertyEnum extends RelatedPropertyEnumModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form2_form_property_enum}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), []);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(Form2FormProperty::className(), ['id' => 'property_id']);
    }
}