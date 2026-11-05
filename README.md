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



```js  

sx.EventManager.on("ajaxFormSuccessSubmited", function(e, data) {
    //Успешно отправлена любая форма
    //ym(67855000,'reachGoal','any-form');
    
    if (data.form.jForm.data('form_code') == 'register') {
        //Отправлена конкретная форма (регистрация)
        ym(67855000,'reachGoal','register');
    }
    
    
    if (data.form.jForm.data('form_code') == 'test') {
        //Отправлена конкретная форма (с кодом test)
    }
});

```


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


