<?php
/**
 * «Конструктор форм → Защита форм»: limits of accepted submissions for all forms of the site.
 *
 * @var $this yii\web\View
 * @var $component skeeks\modules\cms\form2\components\Form2ProtectionSettings
 * @var $configLimits string[] keys fixed in the project config
 */

use skeeks\cms\backend\widgets\BackendSurfaceWidget;
use yii\helpers\Html;
use yii\helpers\Url;

$names = ['phone' => 'телефона', 'session' => 'посетителя', 'ip' => 'IP-адреса'];
?>
<?php BackendSurfaceWidget::begin([
    'title' => 'Лимиты заявок',
    'hint' => 'Действуют для всех форм этого сайта.',
    'titleTag' => 'h2',
    'raised' => true,
    'responsive' => true,
]); ?>
    <p>
        Заявка сверх лимита не теряется: она сохраняется с пометкой «Подозрительная», без писем и без сделки, и её
        можно принять вручную в <?= Html::a('списке сообщений', Url::to(['/form2/admin-form-send']), ['data-pjax' => 0]) ?>.
        Считаются только принятые заявки. Лимиты примерные: заявки, отправленные в одну и ту же секунду, могут пройти
        вместе.
    </p>
    <?php if ($configLimits) : ?>
        <p class="sx-text--warning">
            В конфигурации сайта заданы свои лимиты для
            <?= Html::encode(implode(', ', array_map(static function ($key) use ($names) {
                return $names[$key] ?? $key;
            }, $configLimits))) ?>: для них значения на этой странице не действуют.
        </p>
    <?php endif; ?>

    <?php $form = $component->beginConfigForm(); ?>
    <?= $component->renderConfigFormFields($form) ?>
    <?= $form->buttonsStandart($component) ?>
    <?php $form::end(); ?>
<?php BackendSurfaceWidget::end(); ?>
