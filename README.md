Yii2: Oplata Модуль для Adm CMS
================

https://www.oplata.com

Установка
------------
Удобнее всего установить это расширение через [composer](http://getcomposer.org/download/).

```
   "pavlinter/yii2-adm-oplata": "*",
```

Настройка
-------------
```php
'modules' => [
    ...
    'adm' => [
        ...
        'modules' => [
            'admoplata'
        ],
        ...
    ],
    'admoplata' => [
       'class' => 'pavlinter\admoplata\Module',
       'modules' => [],
    ],
    ...
],
'components' => [
    ...
    'oplata' => [
        'class' => 'pavlinter\admoplata\components\Oplata',
        'merchantId' => 'xxxx', //own merchant id
        'password' => 'xxxx', //own password
    ],
    ...
],
```

Запустить миграцию
-------------
```php
   yii migrate --migrationPath=@vendor/pavlinter/yii2-adm-oplata/admoplata/migrations
```

Как использовать
-------------
```php
echo Html::a('My-Page',['/admoplata/default/invoice', 'alias' => 'My-hash']);
```