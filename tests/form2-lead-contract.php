<?php

$root = dirname(__DIR__).'/src';
$controller = file_get_contents($root.'/controllers/BackendController.php');
$send = file_get_contents($root.'/models/Form2FormSend.php');

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

if (strpos($send, "'name' => \$name") === false) {
    throw new RuntimeException('Form2 lead must map the standard name field.');
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

echo "Form2 lead contract: ok\n";
