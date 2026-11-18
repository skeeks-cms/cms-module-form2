<?php

namespace skeeks\modules\cms\form2\controllers;

use skeeks\cms\backend\BackendAction;
use skeeks\cms\backend\BackendController;
use skeeks\cms\base\Component;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\rbac\CmsManager;
use skeeks\modules\cms\form2\components\Form2ProtectionSettings;
use skeeks\modules\cms\form2\Module;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * «Конструктор форм → Защита форм»: limits of accepted submissions for all forms of the site. Administrators only.
 * Saves at the level the administration of components uses (references/component-settings.md): the default record
 * on the default site, the site record elsewhere.
 */
class AdminFormProtectionController extends BackendController
{
    public function init()
    {
        $this->name = 'Защита форм';
        $this->generateAccessActions = false;
        $this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;
        $this->defaultAction = 'index';

        parent::init();
    }

    public function actions()
    {
        return [
            'index' => [
                'class' => BackendAction::class,
                'name' => 'Лимиты заявок',
                'icon' => 'fas fa-shield-alt',
                'priority' => 10,
                'callback' => [$this, 'settings'],
            ],
        ];
    }

    public function settings()
    {
        $site = \Yii::$app->skeeks->site;
        $component = new Form2ProtectionSettings();
        if ($site->is_default) {
            $component->overridePath = [Component::OVERRIDE_DEFAULT];
        } else {
            $component->overridePath = [Component::OVERRIDE_DEFAULT, Component::OVERRIDE_SITE];
            $component->cmsSite = $site;
        }
        $component->refresh();

        $request = \Yii::$app->request;
        if ($request->isAjax && $request->isPost && !$request->isPjax) {
            $component->load($request->post());
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return ActiveForm::validateMultiple(ArrayHelper::merge([$component], $component->getConfigFormModels()));
        }
        if ($request->isPost && !$request->post(RequestResponse::DYNAMIC_RELOAD_NOT_SUBMIT)) {
            if ($component->load($request->post()) && $component->validate()) {
                if ($site->is_default) {
                    $component->override = Component::OVERRIDE_DEFAULT;
                } else {
                    $component->override = Component::OVERRIDE_SITE;
                    $component->cmsSite = $site;
                }
                $saved = $component->save();
                Form2ProtectionSettings::reset();
                \Yii::$app->getSession()->setFlash($saved ? 'success' : 'error', $saved ? 'Сохранено' : 'Не удалось сохранить');
            } else {
                \Yii::$app->getSession()->setFlash('error', 'Проверьте значения лимитов');
            }
        }

        return $this->render('@skeeks/modules/cms/form2/views/admin-form-protection/index', [
            'component' => $component,
            'configLimits' => array_keys((array)Module::protection()->limits),
        ]);
    }
}
