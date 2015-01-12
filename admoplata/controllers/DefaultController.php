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
        $id = Yii::$app->oplata->createOrder([
            'user_id' => null,
            'email' => 'ttt@ttt.lv',
            'title' => 'Тестовый заказ',
            'currency' => OplataTransaction::CURRENCY_USD,
            'shipping' => '0.89',
            'data' => [], //or string or object
        ]);
        if ($id !== false) {
            return $this->redirect(['send', 'id' => $id]);
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
            'amount' => ($model->price + $model->shipping) * 100,
            'merchant_id' => Yii::$app->oplata->merchantId,
            'response_url' => Url::to(['response', 'id' => $model->id], true),
            'server_callback_url' => Url::to(['server', 'id' => $model->id], true),
        );

        $signature = Yii::$app->oplata->getSignature($request);
        $request['signature'] = $signature;


        return $this->render('send',[
            'model' => $model,
            'request' => $request,
        ]);
    }


    /**
     * @param $id
     */
    public function actionResponse($id)
    {
        exit('hellow client');
        //client side
    }


    /**
     * @param $id
     */
    public function actionServer($id)
    {
        ob_start();
        //server response
        $res = Yii::$app->oplata->checkPayment(Yii::$app->request->post());
        if (!$res) {
            $errors = Yii::$app->oplata->getErrors();
            echo '<pre>';
            echo print_r($errors);
            echo '</pre>';
        } else {
            echo 'success';
        }
        $cont = ob_get_clean();
        file_put_contents(Yii::getAlias('@app/runtime/oplata.txt'),$cont);
    }

}
