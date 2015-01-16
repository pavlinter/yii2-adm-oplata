Yii2: Oplata Модуль для Adm CMS
================

https://www.oplata.com

Установка
------------------
Удобнее всего установить это расширение через [composer](http://getcomposer.org/download/).

```
   "pavlinter/yii2-adm-oplata": "*",
```

Настройка
------------------
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
        'invoiceLayout' => '/main';
        'layout' => '@vendor/pavlinter/yii2-adm/adm/views/layouts/main';
        'userSelect' => [
            'viewCallback' => null, //function ($row) {return Adm::t('oplata','{email}:select2 template', $row);}
            'querySearch' => null, // function ($query, $userTable, $search) {/* @var \yii\db\Query $query */return $query->from($userTable)->where(['like', 'email', $search])->limit(20)->all();}
            'queryLoad' => null, //function ($query, $userTable, $id) {/* @var \yii\db\Query $query */return $query->from($userTable)->where(['id' => $id])->one();}
        ];
        'sendFunc' => null; //function ($model, $module, $user, $username) {}
        'sendFrom' => null; // default Yii::$app->params['adminEmail']
        'mailTemplate' => '@vendor/pavlinter/yii2-adm-oplata/admoplata/views/transaction/email-template';
        'pdf' => [
            'image' => [ //logo htmlOptions
                //'src' => '',
            ],
            'imageLink' => [ //logo link htmlOptions
                //'href' => '',
            ],
        ];
    ],
    'response' => [
        'formatters' => [
            'adm-pdf' => [
                'class' => 'pavlinter\admoplata\PdfResponseFormatter',
            ],
        ]
    ],
    ...
],
```

Запустить миграцию
------------------
```php
yii migrate --migrationPath=@vendor/pavlinter/yii2-adm-oplata/admoplata/migrations
```

Как использовать
------------------
```php
echo Html::a('My-Page',['/admoplata/default/invoice', 'alias' => 'My-hash']);
```

Контроллер
------------------
```php
public function actionOrder() {
    $item1 = new OplataItem();
    $item1->title = 'Item 1';
    $item1->description = 'Item 1Item 1Item 1Item 1Item 1';
    $item1->price = '20';
    $item1->amount = 1;

    $item2 = new OplataItem();
    $item2->title = 'Item 2';
    $item2->description = 'Item 2Item 2Item 2Item 2Item 2';
    $item2->price = '0.9';
    $item2->amount = 2;

    Yii::$app->oplata->clearItems();
    Yii::$app->oplata->addItem($item1);
    Yii::$app->oplata->addItem($item2);
    $order = Yii::$app->oplata->createOrder([
        'user_id' => null,
        'language_id' => Yii::$app->getI18n()->getId(),
        'email' => 'bob@bob.com',
        'title' => 'Тестовый заказ',
        'currency' => OplataTransaction::CURRENCY_USD,
        'shipping' => '0.89',
        'data' => [], //or string or object
    ]);
    if ($order !== false) {
        return $this->redirect(['invoice', 'alias' => $order->alias]);
    }
    echo '<pre>';
    echo print_r(Yii::$app->oplata->getErrors());
    echo '</pre>';
}
```