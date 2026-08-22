<?php

$root = dirname(__DIR__).'/src';
$controller = file_get_contents($root.'/controllers/BackendController.php');
$adminController = file_get_contents($root.'/controllers/AdminFormSendController.php');
$send = file_get_contents($root.'/models/Form2FormSend.php');
$view = file_get_contents($root.'/views/admin-form-send/view.php');
$header = file_get_contents($root.'/views/admin-form-send/_model_header.php');

$load = strpos($controller, '$validateModel->load(');
$snapshot = strpos($controller, '$modelFormSend->data_values = $validateModel->getAttributes();');
$save = strpos($controller, 'if (!$modelFormSend->save())');
if ($load === false || $snapshot === false || $save === false || !($load < $snapshot && $snapshot < $save)) {
    throw new RuntimeException('Form2 values must be captured after load/validation and before saving the submission.');
}
if (strpos($controller, 'Form2FormSend::getDb()->beginTransaction()') === false
    || strpos($controller, '$transaction->commit()') === false
    || strpos($controller, '$transaction->rollBack()') === false
) {
    throw new RuntimeException('Form2 send, related values, and lead must share one transaction.');
}
if (strpos($send, 'public function transactions()') === false
    || strpos($send, 'self::OP_INSERT') === false
) {
    throw new RuntimeException('Form2 lead projection must be atomic for non-controller inserts too.');
}

foreach ([
    "'form_send_id' => (int)\$this->id",
    "'labels' => \$labels",
    "'values' => \$values",
    "'fields' => \$fields",
    "'utm' => \$utms",
    "'request' => \$this->leadRequestMetadata()",
    "'source_ref' => (string)\$this->id",
    "'source_type' => CmsLead::SOURCE_FORM",
] as $fragment) {
    if (strpos($send, $fragment) === false) {
        throw new RuntimeException('Missing Form2 lead source contract: '.$fragment);
    }
}

foreach ([
    "\$leadName = \$formName !== '' ? 'Форма на сайте «'.\$formName.'»' : 'Форма на сайте №'.\$this->form_id",
    "\$leadName .= ' №'.\$this->id",
    "\$mainPhone = \$phones ? reset(\$phones)['value'] : null",
    "\$leadName .= ': '.implode(', ', \$nameParts)",
    "'name' => \$leadName",
] as $nameContract) {
    if (strpos($send, $nameContract) === false) {
        throw new RuntimeException('Form2 lead must persist its complete display name: '.$nameContract);
    }
}
foreach (['phones', 'emails'] as $attribute) {
    if (strpos($send, "'{$attribute}' => array_values(\${$attribute})") === false) {
        throw new RuntimeException('Form2 lead must map every recognized contact: '.$attribute);
    }
}

if (strpos($send, "'data_cookie'") === false || strpos($send, "'data_session'") === false) {
    throw new RuntimeException('The Form2 model serialization contract unexpectedly changed.');
}
if (strpos($send, "'data_cookie' =>") !== false || strpos($send, "'data_session' =>") !== false) {
    throw new RuntimeException('Lead source_data must not duplicate raw Form2 cookies or sessions.');
}

foreach ([
    'public function getCmsLead(): ?CmsLead',
    'public function ensureCmsLead(): ?CmsLead',
    '->fromSource(CmsLead::SOURCE_FORM, (string)$this->id, $this->leadSiteId)',
    '$this->ensureCmsLead();',
    'return (new CmsLeadService())->createFromSource([',
] as $reverseLinkContract) {
    if (strpos($send, $reverseLinkContract) === false) {
        throw new RuntimeException('Form2 reverse lead link is incomplete: '.$reverseLinkContract);
    }
}
foreach ([
    "'process' => [",
    'BackendModelUpdateAction::class',
    "'fields' => [\$this, 'processFields']",
    "'create-lead' => [",
    '!\\Yii::$app->request->isPost',
    '$this->model->ensureCmsLead()',
    "'/cms/admin-cms-lead/view'",
] as $adminContract) {
    if (strpos($adminController, $adminContract) === false) {
        throw new RuntimeException('Form2 admin lead/process action is incomplete: '.$adminContract);
    }
}
foreach ([
    'BackendSurfaceWidget',
    'BackendEntityLink',
    'sx-detail-layout--main-first',
    '$model->cmsLead',
    "Url::to(['create-lead'",
    '<details class="sx-form-send-debug">',
] as $viewContract) {
    if (strpos($view, $viewContract) === false) {
        throw new RuntimeException('Form2 read-only card is incomplete: '.$viewContract);
    }
}
foreach (['ActiveFormUseTab', '$model->save()', 'buttonsStandart(', 'fieldSelect('] as $legacyMutation) {
    if (strpos($view, $legacyMutation) !== false) {
        throw new RuntimeException('Form2 view must stay read-only and free of the legacy processing form: '.$legacyMutation);
    }
}
foreach (['BackendModelHeader', "'Отправка №'.(int)\$model->id", 'sx-status'] as $headerContract) {
    if (strpos($header, $headerContract) === false) {
        throw new RuntimeException('Form2 model header is incomplete: '.$headerContract);
    }
}

echo "Form2 lead contract: ok\n";
