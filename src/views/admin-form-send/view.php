<?php

use skeeks\cms\backend\helpers\BackendIcon;
use skeeks\cms\backend\widgets\BackendEntityLink;
use skeeks\cms\backend\widgets\BackendSurfaceWidget;
use skeeks\cms\models\CmsLead;
use skeeks\modules\cms\form2\assets\FormSendViewAsset;
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
        'action' => 'view',
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
        </main>

        <aside class="sx-detail-layout__aside sx-surface-stack">
            <?php BackendSurfaceWidget::begin([
                'title' => 'Сведения об отправке',
                'titleTag' => 'h2',
                'raised' => true,
                'responsive' => true,
            ]); ?>
                <div class="sx-form-send-meta-list"><?= implode('', $meta); ?></div>

                <?php if ($notifications) : ?>
                    <section class="sx-form-send-meta-group">
                        <div class="sx-form-send-meta-group__heading">
                            <div class="sx-form-send-meta-group__title">Уведомления</div>
                            <div class="sx-form-send-meta-group__hint">Получатели исходного сообщения формы.</div>
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
