<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.06.2015
 */
namespace skeeks\modules\cms\form2;

use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\protection\FormProtection;

/**
 * Class Module
 * @package skeeks\modules\cms\form2
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'skeeks\modules\cms\form2\controllers';

    /**
     * @var array configuration of the protection of public forms, see FormProtection
     */
    public $protection = [];

    /**
     * @var string[] models of forms that may be submitted publicly (the form sends its class in `sx-model`)
     */
    public $formModelClasses = [Form2Form::class];

    private $_protection;

    public function getProtection(): FormProtection
    {
        if ($this->_protection === null) {
            $this->_protection = \Yii::createObject(array_merge(['class' => FormProtection::class], (array)$this->protection));
        }

        return $this->_protection;
    }

    /**
     * The form module of the application, also when it is not configured explicitly.
     */
    public static function instance(): self
    {
        $module = \Yii::$app->getModule('form2');
        if ($module instanceof self) {
            return $module;
        }
        static $fallback;
        if ($fallback === null) {
            $fallback = new self('form2');
        }

        return $fallback;
    }

    public static function protection(): FormProtection
    {
        return self::instance()->getProtection();
    }

    /**
     * The public form model class, or null for a class that is not a form of the constructor.
     */
    public function resolveFormModelClass($class): ?string
    {
        if (!is_string($class) || $class === '') {
            return null;
        }
        $class = ltrim($class, '\\');
        foreach ($this->formModelClasses as $allowed) {
            if ($class === ltrim($allowed, '\\')) {
                return $allowed;
            }
        }

        return null;
    }

    /**
     * The one check of a public form for challenge, validate and submit: an allowlisted model, an existing id and a
     * form of the current site. A form of another site of the installation is never available here.
     */
    public function findPublicForm($class, $id, ?int $siteId = null): ?Form2Form
    {
        $class = $this->resolveFormModelClass($class);
        if (!$class || !is_scalar($id) || !ctype_digit((string)$id)) {
            return null;
        }
        if ($siteId === null) {
            $siteId = \Yii::$app->has('skeeks') && \Yii::$app->skeeks->site ? (int)\Yii::$app->skeeks->site->id : 0;
        }
        if (!$siteId) {
            return null;
        }
        $form = $class::find()
            ->andWhere(['id' => (int)$id])
            ->andWhere(['cms_site_id' => $siteId])
            ->one();

        return $form instanceof Form2Form ? $form : null;
    }
}
