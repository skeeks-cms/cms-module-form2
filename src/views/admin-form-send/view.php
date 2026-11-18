<?php

use skeeks\cms\backend\helpers\BackendIcon;
use skeeks\cms\backend\widgets\BackendEntityLink;
use skeeks\cms\backend\widgets\BackendSurfaceWidget;
use skeeks\cms\models\CmsLead;
use skeeks\cms\relatedProperties\PropertyType;
use skeeks\modules\cms\form2\assets\FormSendViewAsset;
use skeeks\modules\cms\form2\models\Form2FormSend;
use skeeks\modules\cms\form2\models\Form2FormSendNotification;
use skeeks\modules\cms\form2\protection\FormProtection;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Form2FormSend $model */

$controller = $this->context;
$action = $controller->action;
$model = $action->model;
$lead = $model->cmsLead;
$formName = $model->form ? trim((string)$model->form->name) : '';
$canCreateLead = !$lead
    && $model->isCmsLeadIntegrationAvailable()
    && $model->leadSiteId === (int)\Yii::$app->skeeks->site->id;

FormSendViewAsset::register($this);

$formatDateTime = static function ($timestamp): string {
    return $timestamp ? \Yii::$app->formatter->asDatetime($timestamp, 'medium') : '—';
};
$cell = static function (string $label, string $value, bool $raw = false): string {
    $content = $value !== ''
        ? ($raw ? $value : Html::encode($value))
        : '<span class="sx-form-send-muted">Не указано</span>';

    return Html::tag('div',
        Html::tag('span', Html::encode($label), ['class' => 'sx-form-send-fact__label'])
        .Html::tag('span', $content, ['class' => 'sx-form-send-fact__value']),
        ['class' => 'sx-form-send-fact']
    );
};
$json = static function ($value): string {
    if ($value === null || $value === '' || $value === []) {
        return 'Нет данных';
    }

    return Json::encode(
        $value,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
};

$leadContent = '';
if ($model->isSuspicious()) {
    $reasonItems = '';
    foreach ($model->getCheckReasonCodes() as $code) {
        $reasonItems .= Html::tag('li', Html::encode(FormProtection::reasonLabel($code)));
    }
    $acceptAction = '';
    if (method_exists($controller, 'canAccept') && $controller->canAccept()) {
        $acceptAction = Html::beginForm(
            Url::to(['accept', $controller->requestPkParamName => $model->id]),
            'post',
            ['class' => 'sx-form-send-create-lead']
        )
            .Html::submitButton(
                BackendIcon::render('check-circle', ['size' => 15]).' Принять заявку',
                [
                    'class' => 'sx-button sx-button--primary sx-button--sm',
                    'data-confirm' => 'Принять заявку? Будут отправлены уведомления и создана сделка, как для обычной заявки.',
                ]
            )
            .Html::endForm();
    }
    $leadContent = BackendSurfaceWidget::widget([
        'title' => 'Подозрительная заявка',
        'hint' => 'Заявка сохранена, но уведомления не отправлялись и сделка не создана. Если это настоящий клиент — примите заявку.',
        'titleTag' => 'h2',
        'raised' => true,
        'responsive' => true,
        'actions' => $acceptAction,
        'content' => $reasonItems !== ''
            ? Html::tag('ul', $reasonItems, ['class' => 'sx-form-send-reasons'])
            : Html::tag('span', 'Причина не указана.', ['class' => 'sx-form-send-muted']),
        'options' => ['class' => 'sx-form-send-lead-surface sx-form-send-lead-surface--empty'],
    ]);
} elseif ($lead) {
    $leadStatus = Html::tag('span', Html::encode($lead->statusName), [
        'class' => 'sx-status '.CmsLead::statusCssClass($lead->status),
    ]);
    $leadBody = Html::tag('div',
        Html::tag('span', BackendIcon::render('users', ['size' => 20]), [
            'class' => 'sx-form-send-lead-icon',
            'aria-hidden' => 'true',
        ])
        .Html::tag('span',
            Html::tag('span', Html::encode($lead->displayName), ['class' => 'sx-form-send-lead-title'])
            .Html::tag('span', 'Открыть сделку и продолжить обработку в CRM', ['class' => 'sx-form-send-lead-hint']),
            ['class' => 'sx-form-send-lead-copy']
        )
        .$leadStatus
        .Html::tag('span', BackendIcon::render('chevron-right', ['size' => 18]), [
            'class' => 'sx-form-send-lead-arrow',
            'aria-hidden' => 'true',
        ]),
        ['class' => 'sx-form-send-lead-body']
    );
    $surface = BackendSurfaceWidget::widget([
        'title' => 'Связанная сделка',
        'hint' => 'Отправка уже находится в общем процессе CRM.',
        'titleTag' => 'h2',
        'raised' => true,
        'responsive' => true,
        'content' => $leadBody,
        'options' => ['class' => 'sx-form-send-lead-surface'],
    ]);
    $leadContent = BackendEntityLink::widget([
        'controllerId' => '/cms/admin-cms-lead',
        'action' => 'view',
        'modelId' => (int)$lead->id,
        'content' => $surface,
        'options' => [
            'class' => 'sx-form-send-lead-link',
            'aria-label' => 'Открыть сделку №'.(int)$lead->id,
        ],
    ]);
} else {
    $leadAction = '';
    if ($canCreateLead) {
        $leadAction = Html::beginForm(
            Url::to(['create-lead', $controller->requestPkParamName => $model->id]),
            'post',
            ['class' => 'sx-form-send-create-lead']
        )
            .Html::submitButton(
                BackendIcon::render('plus', ['size' => 15]).' Создать сделку',
                [
                    'class' => 'sx-button sx-button--primary sx-button--sm',
                    'data-confirm' => 'Создать сделку из этой отправки формы?',
                ]
            )
            .Html::endForm();
    }
    $leadContent = BackendSurfaceWidget::widget([
        'title' => 'Сделка ещё не создана',
        'hint' => $canCreateLead
            ? 'Это старая отправка. Создайте сделку выборочно — данные формы и контакты перенесутся автоматически.'
            : 'Интеграция со сделками для этой отправки сейчас недоступна.',
        'titleTag' => 'h2',
        'raised' => true,
        'responsive' => true,
        'actions' => $leadAction,
        'content' => Html::tag('div',
            Html::tag('span', BackendIcon::render('info', ['size' => 18]), ['aria-hidden' => 'true'])
            .Html::tag('span', 'Повторное действие безопасно: отправка может быть связана только с одной сделкой.'),
            ['class' => 'sx-form-send-lead-empty']
        ),
        'options' => ['class' => 'sx-form-send-lead-surface sx-form-send-lead-surface--empty'],
    ]);
}

$propertyRows = [];
foreach ($model->relatedProperties as $property) {
    // Values come from visitors: escaped except safe markup of their type (Form2FormSend::propertyValueHtml()).
    $value = $model->propertyValueHtml((string)$property->code);
    $propertyRows[] = Html::tag('div',
        Html::tag('div', Html::encode((string)$property->name), ['class' => 'sx-form-send-field-label'])
        .Html::tag('div', $value !== '' ? $value : '—', ['class' => 'sx-form-send-field-value']),
        ['class' => 'sx-form-send-field']
    );
}

$meta = [];
$meta[] = $cell('Форма', $formName ?: 'Форма №'.(int)$model->form_id);
$meta[] = $cell('Отправлено', $formatDateTime($model->created_at));
if ($model->createdBy) {
    $meta[] = $cell('Отправитель', (string)$model->createdBy->displayName);
}
if ($model->site) {
    $siteLink = Html::a(
        Html::encode((string)$model->site->name),
        $model->site->url,
        ['target' => '_blank', 'rel' => 'noopener', 'data-pjax' => '0']
    );
    $meta[] = $cell('Сайт', $siteLink, true);
}
$meta[] = $cell('IP-адрес', trim((string)$model->ip));
if (Form2FormSend::isProtectionReady() && $model->check_status) {
    $details = $model->getCheckDetails();
    $checkFacts = [];
    if (isset($details['fill_seconds'])) {
        $checkFacts[] = 'заполнение '.(int)$details['fill_seconds'].' с'.(!empty($details['fast_fill']) ? ' (быстро)' : '');
    }
    if (!empty($details['bot_check']['status'])) {
        $botStatuses = ['passed' => 'пройдена', 'failed' => 'не пройдена', 'unavailable' => 'недоступна'];
        $botStatus = (string)$details['bot_check']['status'];
        $checkFacts[] = 'проверка на бота: '.($botStatuses[$botStatus] ?? $botStatus);
    }
    if (!empty($details['accepted_at'])) {
        $checkFacts[] = 'принята вручную '.$formatDateTime($details['accepted_at']);
    }
    $meta[] = $cell('Проверка', ($model->isSuspicious() ? 'Подозрительная' : 'Принята').($checkFacts ? ': '.implode(', ', $checkFacts) : ''));
}
if (Form2FormSend::isProtectionReady() && $model->phone) {
    $meta[] = $cell('Телефон для лимитов', '+'.$model->phone);
}
if (Form2FormSend::isProtectionReady() && $model->client_id) {
    $meta[] = $cell('ClientID Метрики', (string)$model->client_id);
}
if (Form2FormSend::isProtectionReady() && $model->user_agent) {
    $meta[] = $cell('Браузер', (string)$model->user_agent);
}
if ($model->page_url) {
    $pageLink = Html::a(
        Html::encode((string)$model->page_url),
        $model->page_url,
        ['target' => '_blank', 'rel' => 'noopener', 'data-pjax' => '0']
    );
    $meta[] = $cell('Страница отправки', $pageLink, true);
}
foreach ((array)$model->utms as $key => $value) {
    if (is_scalar($value) && trim((string)$value) !== '') {
        $meta[] = $cell((string)$key, trim((string)$value));
    }
}

$notifications = array_filter([
    'Email' => trim((string)$model->emails),
    'Телефоны' => trim((string)$model->phones),
    'Пользователи' => trim((string)$model->user_ids),
]);

// Full request dumps of old submissions (cookies, the session, the raw request, $_SERVER) stay in the database but are
// never sent to the browser: they hold session cookies and tokens. Only the allowlisted request fields are shown.
$serverFacts = array_intersect_key(
    is_array($model->data_server) ? $model->data_server : [],
    array_flip(['REQUEST_METHOD', 'HTTP_USER_AGENT', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_REFERER', 'REMOTE_ADDR'])
);
$technicalGroups = [
    'Запрос' => $serverFacts,
    'Подписи полей' => $model->data_labels,
    'Значения полей' => $model->data_values,
    'Дополнительные данные' => $model->additional_data,
];

?>
<div class="sx-form-send-view">
    <?= $leadContent; ?>

    <div class="sx-detail-layout sx-detail-layout--main-first">
        <main class="sx-detail-layout__main sx-surface-stack">
            <?php BackendSurfaceWidget::begin([
                'title' => 'Данные формы',
                'hint' => $formName !== '' ? 'Форма «'.$formName.'»' : null,
                'titleTag' => 'h2',
                'raised' => true,
                'responsive' => true,
            ]); ?>
                <div class="sx-form-send-field-list">
                    <?= $propertyRows
                        ? implode('', $propertyRows)
                        : Html::tag('span', 'В отправке нет заполненных полей.', ['class' => 'sx-form-send-muted']); ?>
                </div>
            <?php BackendSurfaceWidget::end(); ?>

            <?php BackendSurfaceWidget::begin([
                'title' => 'Технические данные',
                'hint' => 'Нужны только для диагностики. Полные дампы запроса старых заявок хранятся в базе, но здесь не показываются.',
                'titleTag' => 'h2',
                'raised' => true,
                'responsive' => true,
            ]); ?>
                <div class="sx-form-send-debug-list">
                    <?php foreach ($technicalGroups as $label => $value) : ?>
                        <details class="sx-form-send-debug">
                            <summary><?= Html::encode($label); ?></summary>
                            <pre><?= Html::encode($json($value)); ?></pre>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php BackendSurfaceWidget::end(); ?>
        </main>

        <aside class="sx-detail-layout__aside sx-surface-stack">
            <?php BackendSurfaceWidget::begin([
                'title' => 'Сведения об отправке',
                'titleTag' => 'h2',
                'raised' => true,
                'responsive' => true,
            ]); ?>
                <div class="sx-form-send-meta-list"><?= implode('', $meta); ?></div>

                <?php
                $deliveries = Form2FormSendNotification::isTableReady() ? $model->getNotifications()->all() : [];
                $canRetry = false;
                foreach ($deliveries as $delivery) {
                    if (in_array($delivery->status, [Form2FormSendNotification::STATUS_FAILED, Form2FormSendNotification::STATUS_PENDING], true)) {
                        $canRetry = true;
                    }
                }
                $canRetry = $canRetry && !$model->isSuspicious()
                    && !\Yii::$app->user->isGuest
                    && \Yii::$app->user->can(\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
                ?>
                <?php if ($deliveries) : ?>
                    <section class="sx-form-send-meta-group">
                        <div class="sx-form-send-meta-group__heading">
                            <div class="sx-form-send-meta-group__title">Уведомления</div>
                            <div class="sx-form-send-meta-group__hint">Фактическая доставка писем получателям формы.</div>
                        </div>
                        <div class="sx-form-send-meta-list">
                        <?php foreach ($deliveries as $delivery) : ?>
                            <?php
                            $state = $delivery->statusLabel;
                            if ($delivery->status === Form2FormSendNotification::STATUS_SENT && $delivery->sent_at) {
                                $state .= ', '.$formatDateTime($delivery->sent_at);
                            }
                            if ($delivery->status !== Form2FormSendNotification::STATUS_SENT && $delivery->last_error) {
                                $state .= ': '.$delivery->last_error;
                            }
                            if ($delivery->attempts > 1) {
                                $state .= ' (попыток: '.(int)$delivery->attempts.')';
                            }
                            ?>
                            <?= $cell((string)$delivery->recipient, $state); ?>
                        <?php endforeach; ?>
                        </div>
                        <?php if ($canRetry) : ?>
                            <?= Html::beginForm(Url::to(['retry-notifications', $controller->requestPkParamName => $model->id]), 'post')
                                .Html::submitButton(BackendIcon::render('refresh', ['size' => 15]).' Повторить отправку', [
                                    'class' => 'sx-button sx-button--secondary sx-button--sm',
                                ])
                                .Html::endForm(); ?>
                        <?php endif; ?>
                    </section>
                <?php elseif ($notifications) : ?>
                    <section class="sx-form-send-meta-group">
                        <div class="sx-form-send-meta-group__heading">
                            <div class="sx-form-send-meta-group__title">Уведомления</div>
                            <div class="sx-form-send-meta-group__hint"><?= $model->isSuspicious()
                                ? 'Подозрительной заявке уведомления не отправляются.'
                                : 'Получатели исходного сообщения формы.'; ?></div>
                        </div>
                        <div class="sx-form-send-meta-list">
                        <?php foreach ($notifications as $label => $value) : ?>
                            <?= $cell($label, $value); ?>
                        <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($model->processed_at || $model->processed_by || trim((string)$model->comment) !== '') : ?>
                    <section class="sx-form-send-meta-group">
                        <div class="sx-form-send-meta-group__heading">
                            <div class="sx-form-send-meta-group__title">Архив обработки</div>
                            <div class="sx-form-send-meta-group__hint">Сохранённые данные прежнего процесса Form2.</div>
                        </div>
                        <div class="sx-form-send-meta-list">
                        <?= $cell('Обработано', $formatDateTime($model->processed_at)); ?>
                        <?= $cell('Ответственный', $model->processedBy ? (string)$model->processedBy->displayName : ''); ?>
                            <?php if (trim((string)$model->comment) !== '') : ?>
                                <?= $cell('Комментарий', nl2br(Html::encode((string)$model->comment)), true); ?>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php BackendSurfaceWidget::end(); ?>
        </aside>
    </div>
</div>
