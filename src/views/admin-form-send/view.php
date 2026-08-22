<?php

use skeeks\cms\backend\helpers\BackendIcon;
use skeeks\cms\backend\widgets\BackendEntityLink;
use skeeks\cms\backend\widgets\BackendSurfaceWidget;
use skeeks\cms\models\CmsLead;
use skeeks\modules\cms\form2\models\Form2FormSend;
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

$formatDateTime = static function ($timestamp): string {
    return $timestamp ? \Yii::$app->formatter->asDatetime($timestamp, 'medium') : '—';
};
$cell = static function (string $label, string $value, bool $raw = false): string {
    $content = $value !== ''
        ? ($raw ? $value : Html::encode($value))
        : '<span class="sx-form-send-muted">Не указано</span>';

    return Html::tag('div',
        Html::tag('span', Html::encode($label), ['class' => 'sx-collection-cell__secondary'])
        .Html::tag('span', $content, ['class' => 'sx-collection-cell__primary sx-form-send-value']),
        ['class' => 'sx-collection-cell sx-collection-cell--stack']
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
if ($lead) {
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
            .Html::tag('span', 'Открыть лид и продолжить обработку в CRM', ['class' => 'sx-form-send-lead-hint']),
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
        'title' => 'Связанный лид',
        'hint' => 'Отправка уже находится в общем процессе CRM.',
        'titleTag' => 'h2',
        'raised' => true,
        'responsive' => true,
        'content' => $leadBody,
        'options' => ['class' => 'sx-form-send-lead-surface'],
    ]);
    $leadContent = BackendEntityLink::widget([
        'controllerId' => '/cms/admin-cms-lead',
        'modelId' => (int)$lead->id,
        'content' => $surface,
        'options' => [
            'class' => 'sx-form-send-lead-link',
            'aria-label' => 'Открыть лид №'.(int)$lead->id,
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
                BackendIcon::render('plus', ['size' => 15]).' Создать лид',
                [
                    'class' => 'sx-button sx-button--primary sx-button--sm',
                    'data-confirm' => 'Создать лид из этой отправки формы?',
                ]
            )
            .Html::endForm();
    }
    $leadContent = BackendSurfaceWidget::widget([
        'title' => 'Лид ещё не создан',
        'hint' => $canCreateLead
            ? 'Это старая отправка. Создайте лид выборочно — данные формы и контакты перенесутся автоматически.'
            : 'Интеграция с лидами для этой отправки сейчас недоступна.',
        'titleTag' => 'h2',
        'raised' => true,
        'responsive' => true,
        'actions' => $leadAction,
        'content' => Html::tag('div',
            Html::tag('span', BackendIcon::render('info', ['size' => 18]), ['aria-hidden' => 'true'])
            .Html::tag('span', 'Повторное действие безопасно: отправка может быть связана только с одним лидом.'),
            ['class' => 'sx-form-send-lead-empty']
        ),
        'options' => ['class' => 'sx-form-send-lead-surface sx-form-send-lead-surface--empty'],
    ]);
}

$propertyRows = [];
foreach ($model->relatedProperties as $property) {
    $value = (string)$model->relatedPropertiesModel->getAttributeAsHtml($property->code);
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

$technicalGroups = [
    'SERVER' => $model->data_server,
    'REQUEST' => $model->data_request,
    'SESSION' => $model->data_session,
    'COOKIE' => $model->data_cookie,
    'Подписи полей' => $model->data_labels,
    'Значения полей' => $model->data_values,
    'Дополнительные данные' => $model->additional_data,
];

$this->registerCss(<<<CSS
.sx-form-send-view {
    display: grid;
    gap: 10px;
}
.sx-form-send-lead-link {
    display: block;
    color: inherit;
    text-decoration: none;
}
.sx-form-send-lead-link:hover,
.sx-form-send-lead-link:focus {
    color: inherit;
    text-decoration: none;
    outline: none;
}
.sx-form-send-lead-surface {
    transition: border-color 150ms ease-out, box-shadow 150ms ease-out;
}
.sx-form-send-lead-link:hover .sx-form-send-lead-surface,
.sx-form-send-lead-link:focus-visible .sx-form-send-lead-surface {
    border-color: var(--sx-color-accent-border);
    box-shadow: var(--sx-button-focus-shadow);
}
.sx-form-send-lead-body {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.sx-form-send-lead-icon {
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    color: var(--sx-color-accent);
    background: var(--sx-color-accent-soft);
    border-radius: 50%;
}
.sx-form-send-lead-copy {
    display: grid;
    flex: 1 1 auto;
    gap: 3px;
    min-width: 0;
}
.sx-form-send-lead-title {
    color: var(--sx-color-text);
    font-weight: 600;
    overflow-wrap: anywhere;
}
.sx-form-send-lead-hint,
.sx-form-send-muted {
    color: var(--sx-color-text-muted);
}
.sx-form-send-lead-arrow {
    display: inline-flex;
    flex: 0 0 auto;
    color: var(--sx-color-text-subtle);
}
.sx-form-send-lead-empty {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--sx-color-text-muted);
}
.sx-form-send-create-lead {
    margin: 0;
}
.sx-form-send-field-list {
    display: grid;
}
.sx-form-send-field {
    display: grid;
    grid-template-columns: minmax(150px, 0.65fr) minmax(0, 1.35fr);
    gap: 16px;
    padding: 13px 0;
    border-bottom: 1px solid var(--sx-color-border);
}
.sx-form-send-field:first-child {
    padding-top: 0;
}
.sx-form-send-field:last-child {
    padding-bottom: 0;
    border-bottom: 0;
}
.sx-form-send-field-label {
    color: var(--sx-color-text-muted);
}
.sx-form-send-field-value {
    min-width: 0;
    color: var(--sx-color-text);
    overflow-wrap: anywhere;
}
.sx-form-send-meta-list,
.sx-form-send-notification-list {
    display: grid;
    gap: 14px;
}
.sx-form-send-value {
    min-width: 0;
    overflow-wrap: anywhere;
}
.sx-form-send-comment {
    margin: 0;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}
.sx-form-send-debug-list {
    display: grid;
}
.sx-form-send-debug {
    border-top: 1px solid var(--sx-color-border);
}
.sx-form-send-debug:first-child {
    border-top: 0;
}
.sx-form-send-debug > summary {
    display: flex;
    min-height: 44px;
    align-items: center;
    color: var(--sx-color-text);
    font-weight: 600;
    cursor: pointer;
}
.sx-form-send-debug > pre {
    max-height: 360px;
    margin: 0 0 16px;
    overflow: auto;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}
@media (max-width: 767.98px) {
    .sx-form-send-lead-body {
        align-items: flex-start;
        flex-wrap: wrap;
    }
    .sx-form-send-lead-body .sx-status {
        margin-left: 50px;
    }
    .sx-form-send-field {
        grid-template-columns: minmax(0, 1fr);
        gap: 5px;
    }
}
CSS
);

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

            <?php if (trim((string)$model->comment) !== '') : ?>
                <?php BackendSurfaceWidget::begin([
                    'title' => 'Комментарий менеджера',
                    'titleTag' => 'h2',
                    'raised' => true,
                    'responsive' => true,
                ]); ?>
                    <p class="sx-form-send-comment"><?= nl2br(Html::encode((string)$model->comment)); ?></p>
                <?php BackendSurfaceWidget::end(); ?>
            <?php endif; ?>
        </main>

        <aside class="sx-detail-layout__aside sx-surface-stack">
            <?php BackendSurfaceWidget::begin([
                'title' => 'Сведения об отправке',
                'titleTag' => 'h2',
                'raised' => true,
                'responsive' => true,
            ]); ?>
                <div class="sx-form-send-meta-list"><?= implode('', $meta); ?></div>
            <?php BackendSurfaceWidget::end(); ?>

            <?php if ($notifications) : ?>
                <?php BackendSurfaceWidget::begin([
                    'title' => 'Кого уведомили',
                    'titleTag' => 'h2',
                    'raised' => true,
                    'responsive' => true,
                ]); ?>
                    <div class="sx-form-send-notification-list">
                        <?php foreach ($notifications as $label => $value) : ?>
                            <?= $cell($label, $value); ?>
                        <?php endforeach; ?>
                    </div>
                <?php BackendSurfaceWidget::end(); ?>
            <?php endif; ?>

            <?php if ($model->processed_at) : ?>
                <?php BackendSurfaceWidget::begin([
                    'title' => 'Предыдущая обработка',
                    'hint' => 'Переходный статус старого механизма Form2.',
                    'titleTag' => 'h2',
                    'raised' => true,
                    'responsive' => true,
                ]); ?>
                    <div class="sx-form-send-meta-list">
                        <?= $cell('Обработано', $formatDateTime($model->processed_at)); ?>
                        <?= $cell('Ответственный', $model->processedBy ? (string)$model->processedBy->displayName : ''); ?>
                    </div>
                <?php BackendSurfaceWidget::end(); ?>
            <?php endif; ?>
        </aside>
    </div>

    <?php BackendSurfaceWidget::begin([
        'title' => 'Технические данные',
        'hint' => 'Служебные payload’ы скрыты по умолчанию и нужны только для диагностики.',
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
</div>
