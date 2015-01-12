<?php

namespace pavlinter\admoplata\controllers;

use pavlinter\admoplata\components\Oplata;
use pavlinter\admoplata\models\OplataItem;
use pavlinter\admoplata\models\OplataTransaction;
use pavlinter\admoplata\Module;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'response' => ['post'],
                    'server' => ['post'],
                ],
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @param $alias
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionInvoice($alias)
    {
        if ($alias === '') {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where(['alias' => $alias])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $this->render('invoice',[
            'model' => $model,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionTest()
    {
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
            'email' => 'ttt@ttt.lv',
            'title' => 'Тестовый заказ',
            'currency' => OplataTransaction::CURRENCY_USD,
            'shipping' => '0.89',
            'data' => [], //or string or object
        ]);
        if ($order !== false) {
            return $this->redirect(['invoice', 'alias' => $order->alias]);
        }
        exit('error: '.Yii::$app->oplata->getError());
    }


    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionSend($alias)
    {
        if (!$alias) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where(['alias' => $alias])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        
        if ($model->order_status !== null) {
            return $this->redirect(['invoice', 'alias' => $model->alias]);
        }

        $request = array(
            'order_id' => $model->id . Oplata::ORDER_SEPARATOR . time(),
            'order_desc' => $model->title,
            'currency' => $model->currency,
            'amount' => ($model->price + $model->shipping) * 100,
            'merchant_id' => Yii::$app->oplata->merchantId,
            'response_url' => Url::to(['response'], true),
            'server_callback_url' => Url::to(['server'], true),
        );

        if ($model->order_status === null) {
            $signature = Yii::$app->oplata->getSignature($request);
            $request['signature'] = $signature;
            $isPaid = false;
        } else {
            $isPaid = true;
        }

        return $this->render('send',[
            'model' => $model,
            'request' => $request,
            'isPaid' => $isPaid,
        ]);
    }


    /**
     * @param $id
     */
    public function actionResponse()
    {
        //client side
        list($order_id,) = explode(Oplata::ORDER_SEPARATOR, Yii::$app->request->post('order_id'));
        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where(['id' => $order_id])->one();

        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->render('response',[
            'model' => $model,
        ]);

    }


    /**
     * @param $id
     */
    public function actionServer()
    {
        //server response
        $res = Yii::$app->oplata->checkPayment(Yii::$app->request->post());
        if (!$res) {
            $errors = Yii::$app->oplata->getErrors();
            foreach ($errors as $error) {
                Yii::warning($error, 'admoplata');
                echo $error;
            }
        }
    }

}
