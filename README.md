Form designer for SkeekS CMS
===================================

The module provides an opportunity to collect a variety of forms through the admin panel. Manage elemntov order forms, and views. Configure whom to notify.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist skeeks/cms-module-form2 "*"
```

or add

```
"skeeks/cms-module-form2": "*"
```

Configuration app
----------

```php

'components' =>
[
    'i18n' => [
        'translations' =>
        [
            'skeeks/form2/app' => [
                'class'             => 'yii\i18n\PhpMessageSource',
                'basePath'          => '@skeeks/modules/cms/form2/messages',
                'fileMap' => [
                    'skeeks/form2/app' => 'app.php',
                ],
            ]
        ]
    ],
],
'modules' =>
[
    'form2' => [
        'class'         => '\skeeks\modules\cms\form2\Module',
    ]
]

```



Analytics goals: listen to `form2Conversion`. It fires once for a new submission
that this very request saved and accepted; a suspicious submission, a validation
error or a repeated request with the same stamp does not fire it.

```js
sx.EventManager.on("form2Conversion", function(e, data) {
    // data.form_code — the code of the form, data.send_id — the submission
    if (data.form_code == 'register') {
        ym(67855000, 'reachGoal', 'register');
    }
});
```

`ajaxFormSuccessSubmited` still fires on every successful answer, suspicious
submissions included (they get the same answer as accepted ones so that a bot
learns nothing). Do not use it for goals. If the first successful HTTP answer is
lost (the connection drops after the server saved the submission), the browser
repeats nothing by itself; a repeat by the visitor with the same stamp returns
`conversion = false`, so such a submission stays without a conversion — a goal
may be missed, never doubled.


Protection of public forms
-------
A submission is never silently dropped. One that fails a check is saved with
`check_status = suspicious` and the codes of its reasons (`check_reasons`): no
notification, no CRM deal, no conversion, no `EVENT_ACCEPTED`. The list
«Сообщения» has the filter «Проверка»; the card of a suspicious submission
shows the reasons and the button «Принять заявку» (administrators only). A
manager sees only submissions with their deals, so suspicious ones are visible
to administrators.

### The form and its site

`Module::findPublicForm()` is the one check of `challenge`, `validate` and
`submit`: an allowlisted model (`Module::$formModelClasses`) and a form of the
current site. A form of another site of the installation is not available
there. The signed stamp names the site, the form model and the form; sites of
one installation share `cookieValidationKey`, so a stamp of one site never
confirms a submission of another. The submission, its deal and its
notifications take the site of the form.

### Stamp and bot check

- `ActiveFormConstructForm` renders the trap field `email_confirm` (off screen,
  `tabindex=-1`, `autocomplete=off`, `aria-hidden`) and the hidden field
  `form2_protect`, and attaches `Form2ProtectionAsset` to this widget instance
  by its DOM id: two instances of one form on a page (a block and a popup, a
  Pjax replacement) each have their own stamp, waiting and reset. Trap and
  `form_time` fields of old custom templates are removed by the script; the
  server ignores `form_time`.
- On the first interaction the browser takes `/form2/backend/challenge?form=ID`:
  a signed one-time stamp (site, model, form, time, key; HMAC from
  `cookieValidationKey`) and a bot check task. The answer goes with the form; if
  the visitor sends earlier, the sending waits for it (at most 15 s, then goes
  without it and is saved as suspicious). A stamp older than `tokenTtl` is
  replaced before sending.
- The server checks the trap, the stamp (signature, site, model, form, age),
  the bot check, then the limits. Fill time below `minFillSeconds` is only noted
  in the details.
- The key of the stamp is unique (`submit_key`): a double click or a network
  retry returns the result of the first request without a second submission,
  letter or deal; parallel requests are resolved by the unique index. After a
  success the browser takes a new stamp, so the visitor can send the form again.
- Bot checks are pluggable (`protection\BotCheckInterface`): `issue()` /
  `verify()` on the server and a browser solver
  `sx.Form2Protection.solvers[type]`. Built in: `PowBotCheck`, a small SHA-256
  task without third-party services (about 0.1–0.5 s in the background). It
  makes mass direct requests expensive; it does not stop a person filling the
  form by hand or a fully automated real browser. Captcha services are the next
  stage, as further implementations.

### Limits

Set by the site in the administration: «Конструктор форм → Защита форм»
(`/form2/admin-form-protection`, administrators only), one set for all forms of
the site — `components\Form2ProtectionSettings`, stored as component settings
(the default record on the default site, the site record elsewhere). Defaults:
one phone — 3 per day, one visitor session — 5 per hour, one IP — 3 per hour
and 10 per day; 0 switches a limit off. A key of
`modules.form2.protection.limits` in the project config wins over the page for
that key (the page says so).

Approximate, a soft filter and not a quota: they count saved accepted
submissions of the site before the new one is saved, so submissions sent at the
same moment may pass together (two at the boundary give one over the limit; the
next one is refused). Only accepted submissions count, so junk of a bot with
someone's phone does not block that person. Keys: the phone of a field of the
format «Телефон», the session, the IP.

### Phones

Fields of forms are arbitrary («Модель телефона», «Email или телефон»), so a
phone is recognized only by the explicit format «Телефон» of a text field
(skeeks/cms `PropertyTypeText::FIELD_PHONE`): an input `type=tel` with a soft
mask, validated and stored as «+7 900 000-00-00» by `PhoneValidator`. Only such
fields give the limit key (E.164 digits by libphonenumber, one key for different
spellings) and the phone contacts of the CRM deal, on every site and with no
switch back to names. A site whose deals got phones from fields named «Телефон»
switches those fields to the format when it updates.

### Acceptance and notifications

A submission is accepted automatically (it passed the checks) or by
«Принять заявку». Manual acceptance is atomic (`UPDATE … WHERE
check_status = 'suspicious'`): only the request that changed the status creates
the deal, queues the notifications and fires `Form2FormSend::EVENT_ACCEPTED`.
That event fires exactly once per accepted submission, after the commit, never
for a suspicious one — project side effects (SMS, integrations) belong there:

```php
Event::on(Form2FormSend::class, Form2FormSend::EVENT_ACCEPTED, function ($e) {
    $send = $e->sender; // an accepted submission
});
```

`BackendController::_beforeSave()` / `_afterSave()` stay for compatibility:
`_afterSave()` runs after every saved submission, accepted or suspicious, and
`$this->submittedSend` is the submission of the request (also for a repeated
request, which does not call `_afterSave()`). Move side effects of a project
`_afterSave()` to `EVENT_ACCEPTED`.

Notifications are rows of `form2_form_send_notification` (one per recipient),
created in the transaction that accepts the submission and delivered after the
commit. Recipients are compared case-insensitively, as the unique index of the
table does (`test@x.ru` and `TEST@x.ru` are one recipient, one letter). Each row
is claimed atomically; a delivered recipient is never notified again.
`send() === false` and exceptions mark the row «Не доставлено» with the reason;
«Повторить отправку» (administrators) retries only such rows. A row left
«sending» for 10 minutes means the process stopped after the letter was handed
to the mailer: it may have gone, so it becomes «Результат неизвестен» and is
never resent automatically — a person checks with the recipient.

### Diagnostics

Columns instead of full dumps: `phone`, `session_hash`, `client_id` (the Metrika
`_ym_uid` cookie), `user_agent`, `check_data`; from `$_SERVER` only method, user
agent, language, referer and address are kept. Cookies, the session and the raw
request are not stored any more. Old submissions keep their dumps in the
database, but the card never sends them to the browser. Values of visitors are
escaped in the card; handlers that build their own safe markup (file links)
keep it.

### Settings per site (project config), all optional

```php
'modules' => [
    'form2' => [
        'protection' => [
            'enabled' => true,                      // false — accept everything (duplicates are still caught)
            'limits' => [                           // normally set on the page «Защита форм»; a key here
                'ip' => [3600 => 20],               // wins over the page, [] switches it off
            ],
            'botCheck' => ['maxNumber' => 50000],   // false — no bot check
            'botCheckUnavailable' => 'suspicious',  // or 'accept'
            'requireToken' => true,                 // false only during the update (see below)
            'phoneRegion' => 'RU',                  // a phone without a country code
        ],
    ],
],
```

### Updating a site

The code works with any state of the schema: until both migrations
(m261118_100000, m261118_110000) are complete — every column, the unique index
of `submit_key`, the notification table with its unique index, status index and foreign key —
`Form2FormSend::isProtectionReady()` is false and submissions work as before the
protection. A visitor sees «sent» only when the submission is saved: a page
without the stamp (cached before the update) is saved as an ordinary submission;
any other sign of a bot gets a temporary error to retry, and nothing is saved.
Notifications are sent directly, one per recipient. Both migrations are
resumable: a repeated run adds what an interrupted run left out and keeps the
rows. Order:

1. Update the packages (`skeeks/cms` with the format «Телефон», then
   `skeeks/cms-module-form2`) by the lock and run `cms/migrate`.
2. If pages are cached long (HTML cache, CDN), set `requireToken => false` for
   the site until pages cached before the update expire: old pages have no
   stamp. Otherwise their submissions are saved as suspicious (not lost) — check
   the filter «Проверка».
3. Clear the HTML cache / CDN of pages with forms; published assets are keyed
   and need no manual clearing.
4. Custom form templates: remove their own `email_confirm` and `form_time`
   (the script removes them anyway); keep the form inside
   `ActiveFormConstructForm`.
5. Switch phone fields of forms to the format «Телефон» (field settings →
   «Элемент формы»), otherwise deals get no phone contacts.
6. Move analytics goals to `form2Conversion`.
7. Move side effects of a project `_afterSave()` to `EVENT_ACCEPTED`.
8. Turn `requireToken` back on (default) after step 2.

Tests: `FORM2_PROTECTION_TEST=local php tests/form2-protection-contract.php`,
`php tests/form2-lead-contract.php`.


CRM: the channel of a form's deals
-------
A submission makes a deal in the CRM of skeeks/cms (`Form2FormSend::ensureCmsLead()`).
The form setting «Канал сделки» (`Form2Form.lead_source_type`, migration
m261105_100000) gives these deals the channel «Визит в салон / офис»
(`CmsLead::SOURCE_VISIT`) instead of «Форма»: a visitor questionnaire by a QR
code in a showroom. A submission finds its deal by every channel of a form
(`Form2FormSend::leadSourceTypes()`). The marks and the first page of the visit
(`seo->utms`) go to the deal, where the core fills «Откуда узнали». Tests:
`tests/form2-lead-contract.php`, `FORM2_VISIT_TEST=local php tests/form2-visit-channel-stand.php`.


Links
-------
* [Web site](http://en.cms.skeeks.com)
* [Web site (rus)](http://cms.skeeks.com)
* [Author](http://skeeks.com)
* [ChangeLog](https://github.com/skeeks-cms/cms-module-form2/blob/master/CHANGELOG.md)
* [Page on SkeekS CMS Marketplace](http://marketplace.cms.skeeks.com/solutions/podderjka-klientov/obratnaya-svyaz/12-konstruktor-web-form-2)




> [![skeeks!](https://skeeks.com/img/logo/logo-no-title-80px.png)](https://skeeks.com)  
<i>SkeekS CMS (Yii2) — quickly, easily and effectively!</i>  
[skeeks.com](https://skeeks.com) | [cms.skeeks.com](https://cms.skeeks.com)


