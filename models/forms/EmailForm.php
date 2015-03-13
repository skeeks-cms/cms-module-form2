<?php
/**
 * LoginForm
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 28.10.2014
 * @since 1.0.0
 */

namespace skeeks\modules\cms\form\models\forms;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class EmailForm extends Model
{
    public $value;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['value'], 'required'],
            // rememberMe must be a boolean value
            ['value', 'email'],
        ];
    }


}
