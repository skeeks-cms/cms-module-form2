<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */
namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\helpers\Request;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\forms\PasswordChangeForm;
use skeeks\cms\models\User;
use skeeks\cms\relatedProperties\models\RelatedElementModel;
use skeeks\cms\relatedProperties\models\RelatedPropertiesModel;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class BackendController
 * @package skeeks\modules\cms\form2\controllers
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
                    'validate'  => ['post'],
                    'submit'    => ['post'],
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
        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            if (\Yii::$app->request->post('sx-model') && \Yii::$app->request->post('sx-model-value'))
            {
                $modelClass = \Yii::$app->request->post('sx-model');
                $modelValue = \Yii::$app->request->post('sx-model-value');
                /**
                 * @var RelatedElementModel $modelForm
                 * @var Form2FormSend $modelFormSend
                 * @var RelatedPropertiesModel $validateModel
                 * @var Form2Form $modelForm
                 */
                $modelForm                  = $modelClass::find()->where(['id' => $modelValue])->one();
                $modelFormSend              = $modelForm->createModelFormSend();
                $modelFormSend->cms_site_id   = \Yii::$app->cms->site->id;
                $modelFormSend->page_url    = \Yii::$app->request->referrer;

                $validateModel = $modelFormSend->relatedPropertiesModel;

                $modelFormSend->data_values     = $validateModel->toArray($validateModel->attributes());
                $modelFormSend->data_labels     = $validateModel->attributeLabels();
                $modelFormSend->emails          = $modelForm->emails;
                $modelFormSend->phones          = $modelForm->phones;


                if ($validateModel->load(\Yii::$app->request->post()) && $validateModel->validate())
                {
                    if ($this->_beforeSave($modelForm, $validateModel, $modelFormSend))
                    {
                        if (!$modelFormSend->save())
                        {
                            $rr->success = false;
                            $rr->message = \Yii::t('skeeks/form2/app', 'Failed to send the form');
                            return (array) $rr;
                        }

                        $validateModel->save();
                        $modelFormSend->notify();

                        $this->_afterSave($modelForm, $validateModel, $modelFormSend);

                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/form2/app', 'Successfully sent');
                    } else
                    {
                        $rr->success = false;
                        $rr->message = \Yii::t('skeeks/form2/app', 'Check the correctness of filling the form fields');
                    }

                } else
                {
                    $rr->success = false;
                    $rr->message = \Yii::t('skeeks/form2/app', 'Check the correctness of filling the form fields');
                }

                return (array) $rr;
            }
        }
    }

    /**
     * @param RelatedPropertiesModel $validateModel
     * @param Form2FormSend $modelFormSend
     * @return bool
     */
    protected function _beforeSave(Form2Form $modelForm, RelatedPropertiesModel $validateModel, Form2FormSend $modelFormSend)
    {
        return true;
    }

    /**
     * @param RelatedPropertiesModel $validateModel
     * @param Form2FormSend $modelFormSend
     */
    protected function _afterSave(Form2Form $modelForm, RelatedPropertiesModel $validateModel, Form2FormSend $modelFormSend)
    {
        return;
    }

    /**
     * Валидация данных с формы
     * @return array
     */
    public function actionValidate()
    {
        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            if (\Yii::$app->request->post('sx-model') && \Yii::$app->request->post('sx-model-value'))
            {
                $modelClass = \Yii::$app->request->post('sx-model');
                $modelValue = \Yii::$app->request->post('sx-model-value');

                /**
                 * @var $modelForm Form2Form
                 */
                $modelForm = $modelClass::find()->where(['id' => $modelValue])->one();
                $modelHasRelatedProperties = $modelForm->createModelFormSend();

                if (method_exists($modelHasRelatedProperties, "createPropertiesValidateModel"))
                {
                    $model = $modelHasRelatedProperties->createPropertiesValidateModel();
                } else
                {
                    $model = $modelHasRelatedProperties->getRelatedPropertiesModel();
                }

                return $rr->ajaxValidateForm($model);
            }
        }
    }
}