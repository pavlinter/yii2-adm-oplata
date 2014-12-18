<?php

namespace pavlinter\admoplata\controllers;

use pavlinter\admoplata\models\OplataItem;
use pavlinter\admoplata\models\OplataTransaction;
use pavlinter\admoplata\Module;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
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
        $id = Yii::$app->oplata->createOrder([
            'user_id' => null,
            'email' => 'ttt@ttt.lv',
            'description' => 'Тестовый заказ',
            'currency' => OplataTransaction::CURRENCY_USD,
            'shipping' => '0.89',
            'data' => [], //or string or object
        ]);
        if ($id !== false) {
            exit('id - '.$id);
            //return $this->redirect(['send', 'id' => $id]);
        }
        exit('error: '.Yii::$app->oplata->getError());
    }


    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionSend($id)
    {
        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where(['id' => $id])->one();

        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $request = array(
            'order_id' => $model->id,
            'order_desc' => $model->description,
            'currency' => $model->currency,
            'amount' => $model->price + $model->shipping,
            'merchant_id' => Yii::$app->oplata->merchantId,
            'response_url' => Url::to(['response'], true),
            'server_callback_url' => Url::to(['server'], true),
        );

        $signature = Yii::$app->oplata->getSignature($request);
        $request['signature'] = $signature;

        return $this->render('send',[
            'model' => $model,
            'request' => $request,
        ]);
    }

    /**
     *
     */
    public function actionResponse()
    {
        //client side
    }

    /**
     *
     */
    public function actionServer()
    {
        //server response
        $res = Yii::$app->oplata->checkPayment(Yii::$app->request->post());
        if (!$res) {
            $errors = Yii::$app->oplata->getErrors();
            echo '<pre>';
            echo print_r($errors);
            echo '</pre>';
        }
    }

}
