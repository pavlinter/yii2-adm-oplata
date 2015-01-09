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
```

Запустить миграцию
-------------
```php
   yii migrate --migrationPath=@vendor/pavlinter/yii2-adm-oplata/admoplata/migrations
```

Как использовать
-------------
```php
echo Html::a('My-Page',['adm/admoplata/default/invoice', 'alias' => 'My-hash']);
```