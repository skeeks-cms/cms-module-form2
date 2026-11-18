<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\relatedProperties\models\RelatedPropertiesModel;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use skeeks\modules\cms\form2\Module;
use skeeks\modules\cms\form2\protection\CheckResult;
use skeeks\modules\cms\form2\protection\FormProtection;
use yii\db\IntegrityException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Public submission of the forms of the constructor.
 *
 * The browser first takes a signed one-time stamp with a bot check task (actionChallenge), then sends the form
 * (actionSubmit). All three actions use one check of the form (Module::findPublicForm): an allowlisted model and a
 * form of the current site. A submission that fails the checks is saved as suspicious, without notifications, a CRM
 * deal and a conversion. A repeated request with the same stamp returns the result of the first one without a second
 * submission. See README «Protection of public forms».
 *
 * Hooks for projects: _beforeSave() may refuse saving; _afterSave() runs after every saved submission, accepted or
 * suspicious (check $modelFormSend->isSuspicious()). Side effects of an accepted request (SMS, integrations) belong
 * to Form2FormSend::EVENT_ACCEPTED, which also fires when an administrator accepts a suspicious submission.
 */
class BackendController extends Controller
{
    /**
     * @var Form2FormSend|null the submission of this request: saved now or found by the stamp of a repeated request
     */
    protected $submittedSend;

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'validate'  => ['post'],
                    'submit'    => ['post'],
                    'challenge' => ['get'],
                ],
            ],
        ]);
    }

    /**
     * The signed one-time stamp of a form and the bot check task for the browser.
     * @return array
     */
    public function actionChallenge()
    {
        $response = \Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex');

        $request = \Yii::$app->request;
        $form = Module::instance()->findPublicForm($request->get('model', Form2Form::class), $request->get('form'));
        if (!$form) {
            $response->statusCode = 404;
            return ['success' => false];
        }

        return ['success' => true] + Module::protection()->issueChallenge($form);
    }

    /**
     * Процесс отправки формы
     * @return array
     */
    public function actionSubmit()
    {
        $rr = new RequestResponse();

        if (!\Yii::$app->request->isAjax || \Yii::$app->request->isPjax) {
            return null;
        }

        $modelForm = $this->findPublicForm();
        if (!$modelForm) {
            $rr->success = false;
            $rr->message = \Yii::t('skeeks/form2/app', 'Failed to send the form');
            return (array)$rr;
        }

        $protection = Module::protection();
        $ready = Form2FormSend::isProtectionReady();
        $check = $protection->checkRequest($modelForm, \Yii::$app->request);

        // A repeated request (double click, a network retry) gets the result of the first one.
        if ($ready && $check->submitKey && ($previous = $this->findBySubmitKey($check->submitKey))) {
            $this->submittedSend = $previous;
            return $this->successResponse($rr, $previous, false);
        }

        /**
         * @var Form2FormSend          $modelFormSend
         * @var RelatedPropertiesModel $validateModel
         */
        $modelFormSend = $modelForm->createModelFormSend();
        $modelFormSend->cms_site_id = (int)$modelForm->cms_site_id;
        $modelFormSend->utms = (array)\Yii::$app->seo->utms;
        $modelFormSend->page_url = \Yii::$app->request->referrer;

        $validateModel = $modelFormSend->relatedPropertiesModel;

        $modelFormSend->emails = $modelForm->emails;
        $modelFormSend->phones = $modelForm->phones;

        if ($validateModel->load(\Yii::$app->request->post()) && $validateModel->validate()) {
            $modelFormSend->data_values = $validateModel->getAttributes();
            $modelFormSend->data_labels = array_intersect_key(
                $validateModel->attributeLabels(),
                $modelFormSend->data_values
            );

            if ($this->_beforeSave($modelForm, $validateModel, $modelFormSend)) {
                if ($ready) {
                    $this->detectPhone($modelFormSend);
                    // Defaults (ip, session) are needed by the limits before the save.
                    $modelFormSend->validate(['ip', 'session_hash', 'user_agent', 'client_id']);
                    $protection->checkLimits($check, $modelFormSend);
                    $this->applyCheck($modelFormSend, $check);
                } else {
                    // Before the protection migration a submission can not be marked as suspicious, and a visitor is
                    // told «sent» only when the submission is saved. A page without the stamp is an ordinary page
                    // cached before the update, so it is saved as before; any other sign of a bot gets a temporary
                    // error the visitor can retry, and nothing is saved.
                    $reasons = array_values(array_diff($check->reasons, [FormProtection::REASON_NO_TOKEN]));
                    if ($reasons) {
                        \Yii::warning('Форма: отправка формы #'.$modelForm->id.' отклонена до миграции защиты: '.implode(', ', $reasons), 'form2');
                        $rr->success = false;
                        $rr->message = 'Не удалось отправить форму. Обновите страницу и попробуйте ещё раз через минуту или свяжитесь с нами по телефону.';
                        return (array)$rr;
                    }
                }

                $transaction = Form2FormSend::getDb()->beginTransaction();
                try {
                    if (!$modelFormSend->save()) {
                        throw new \RuntimeException(implode('; ', $modelFormSend->getFirstErrors()));
                    }

                    if (!$validateModel->save()) {
                        throw new \RuntimeException('Не удалось сохранить значения полей формы.');
                    }

                    if (!$modelFormSend->isSuspicious()) {
                        $modelFormSend->queueNotifications();
                    }

                    $transaction->commit();
                } catch (IntegrityException $e) {
                    if ($transaction->isActive) {
                        $transaction->rollBack();
                    }
                    // The same stamp was saved by a parallel request.
                    if ($ready && $check->submitKey && ($previous = $this->findBySubmitKey($check->submitKey))) {
                        $this->submittedSend = $previous;
                        return $this->successResponse($rr, $previous, false);
                    }
                    \Yii::error($e, 'form2');
                    $rr->success = false;
                    $rr->message = \Yii::t('skeeks/form2/app', 'Failed to send the form');
                    return (array)$rr;
                } catch (\Throwable $e) {
                    if ($transaction->isActive) {
                        $transaction->rollBack();
                    }
                    \Yii::error($e, 'form2');
                    $rr->success = false;
                    $rr->message = \Yii::t('skeeks/form2/app', 'Failed to send the form');
                    return (array)$rr;
                }

                $this->submittedSend = $modelFormSend;
                $accepted = !$modelFormSend->isSuspicious();
                if ($accepted) {
                    $modelFormSend->afterAccepted();
                } else {
                    \Yii::warning('Форма: заявка #'.$modelFormSend->id.' подозрительная: '.implode(', ', $check->reasons), 'form2');
                }

                $this->_afterSave($modelForm, $validateModel, $modelFormSend);

                return $this->successResponse($rr, $modelFormSend, $accepted);
            }

            $rr->success = false;
            $rr->message = \Yii::t('skeeks/form2/app', 'Check the correctness of filling the form fields');

        } else {

            $rr->data = [
                'validation' => ArrayHelper::merge(
                    ActiveForm::validate($validateModel),
                    []
                )
            ];

            $rr->success = false;
            $rr->message = \Yii::t('skeeks/form2/app', 'Check the correctness of filling the form fields');
        }

        return (array)$rr;
    }

    /**
     * A suspicious submission gets the same answer as an accepted one; only `conversion` differs. The browser counts
     * a goal of analytics only for `conversion = true`: a new submission saved and accepted by this very request.
     */
    protected function successResponse(RequestResponse $rr, ?Form2FormSend $send, bool $conversion): array
    {
        $rr->success = true;
        $rr->message = \Yii::t('skeeks/form2/app', 'Successfully sent');
        $rr->data = ['form2' => [
            'conversion' => $conversion,
            'send_id' => $conversion && $send ? (int)$send->id : null,
        ]];

        return (array)$rr;
    }

    /**
     * The form of this request: an allowlisted model of the current site (Module::findPublicForm).
     */
    protected function findPublicForm(): ?Form2Form
    {
        $request = \Yii::$app->request;

        return Module::instance()->findPublicForm($request->post('sx-model'), $request->post('sx-model-value'));
    }

    protected function findBySubmitKey(string $key): ?Form2FormSend
    {
        if (!Form2FormSend::isProtectionReady()) {
            return null;
        }

        return Form2FormSend::find()->where(['submit_key' => $key])->limit(1)->one();
    }

    /**
     * The main phone of a submission for the limits: the first field of the format «Телефон» (skeeks/cms
     * PropertyTypeText::FIELD_PHONE), as an E.164 key. The number itself is validated by that field; other fields
     * are arbitrary and never taken as a phone.
     */
    protected function detectPhone(Form2FormSend $send): void
    {
        $values = is_array($send->data_values) ? $send->data_values : [];
        $labels = is_array($send->data_labels) ? $send->data_labels : [];
        $phones = $send->phoneFormatValues($values, $labels);
        reset($phones);
        $send->setAttribute('phone', $phones ? (string)key($phones) : null);
    }

    protected function applyCheck(Form2FormSend $send, CheckResult $check): void
    {
        $send->setAttribute('check_status', $check->isSuspicious() ? FormProtection::STATUS_SUSPICIOUS : FormProtection::STATUS_ACCEPTED);
        $send->setAttribute('check_reasons', $check->reasons ? implode(',', $check->reasons) : null);
        $send->setAttribute('check_data', $check->data ? json_encode($check->data, JSON_UNESCAPED_UNICODE) : null);
        $send->setAttribute('submit_key', $check->submitKey);
    }

    /**
     * @param RelatedPropertiesModel $validateModel
     * @param Form2FormSend          $modelFormSend
     * @return bool
     */
    protected function _beforeSave(Form2Form $modelForm, RelatedPropertiesModel $validateModel, Form2FormSend $modelFormSend)
    {
        return true;
    }

    /**
     * After every saved submission, accepted or suspicious. For side effects of an accepted submission use
     * Form2FormSend::EVENT_ACCEPTED.
     *
     * @param RelatedPropertiesModel $validateModel
     * @param Form2FormSend          $modelFormSend
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

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
            $modelForm = $this->findPublicForm();
            if (!$modelForm) {
                return (array)$rr;
            }
            $modelHasRelatedProperties = $modelForm->createModelFormSend();

            if (method_exists($modelHasRelatedProperties, "createPropertiesValidateModel")) {
                $model = $modelHasRelatedProperties->createPropertiesValidateModel();
            } else {
                $model = $modelHasRelatedProperties->getRelatedPropertiesModel();
            }

            return $rr->ajaxValidateForm($model);
        }
    }
}
