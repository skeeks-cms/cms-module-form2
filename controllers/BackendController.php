<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.03.2015
 */
namespace skeeks\modules\cms\form\controllers;
use skeeks\cms\base\Controller;
use skeeks\modules\cms\form\models\Form;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class BackendController
 * @package skeeks\modules\cms\form\controllers
 */
class BackendController extends Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'validate' => ['post'],
                    'submit' => ['post'],
                ],
            ],
        ]);
    }


    /**
     * Процесс отправки формы
     * @return array
     */
    public function actionSubmit()
    {

    }

    /**
     * Валидация данных с формы
     * @return array
     */
    public function actionValidate()
    {
        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            if ($formId = \Yii::$app->request->post(Form::FROM_PARAM_ID_NAME))
            {
                /**
                 * @var $modelForm Form
                 */
                $modelForm = Form::find()->where(['id' => $formId])->one();

                $model = $modelForm->createValidateModel();

                $model->load(\Yii::$app->request->post());
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }
    }
}