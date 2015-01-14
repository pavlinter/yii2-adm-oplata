<?php

namespace pavlinter\admoplata\components;

use pavlinter\admoplata\models\OplataTransaction;
use pavlinter\admoplata\Module;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class Oplata
 *
 * $item1 = new OplataItem();
 * $item1->title = 'Item 1';
 * $item1->description = 'Item 1Item 1Item 1Item 1Item 1';
 * $item1->price = '20';
 * $item1->amount = 1;
 *
 * $item2 = new OplataItem();
 * $item2->title = 'Item 2';
 * $item2->description = 'Item 2Item 2Item 2Item 2Item 2';
 * $item2->price = '0.9';
 * $item2->amount = 2;
 *
 * Yii::$app->oplata->clearItems();
 * Yii::$app->oplata->addItem($item1);
 * Yii::$app->oplata->addItem($item2);
 * $order = Yii::$app->oplata->createOrder([
 *     'user_id' => null,
 *     'language_id' => Yii::$app->getI18n()->getId(),
 *     'email' => 'bob@bob.com',
 *     'title' => 'Тестовый заказ',
 *     'currency' => OplataTransaction::CURRENCY_USD,
 *     'shipping' => '0.89',
 *     'data' => [], //or string or object
 * ]);
 * if ($order !== false) {
 *    return $this->redirect(['invoice', 'alias' => $order->alias]);
 * }
 *
 * echo '<pre>';
 * echo print_r(Yii::$app->oplata->getErrors());
 * echo '</pre>';
 */

class Oplata extends Component
{
    const ORDER_APPROVED = 'approved';
    const ORDER_DECLINED = 'declined';
    const SIGNATURE_SEPARATOR = '|';
    const ORDER_SEPARATOR = ":";

    public $merchantId;

    public $password;

    public $url = 'https://api.oplata.com/api/checkout/redirect/'; //'https://api.oplata.com/api/checkout/url/';

    public $responseFields = [
        'lang',
        'rrn',
        'masked_card',
        'sender_cell_phone',
        'response_status',
        'currency',
        'fee',
        'reversal_amount',
        'settlement_amount',
        'actual_amount',
        'order_status',
        'response_description',
        'order_time',
        'actual_currency',
        'order_id',
        'tran_type',
        'eci',
        'settlement_date',
        'payment_system',
        'approval_code',
        'merchant_id',
        'settlement_currency',
        'payment_id',
        'sender_account',
        'card_bin',
        'response_code',
        'card_type',
        'amount',
        'sender_email',
    ];

    private $items = [];
    private $errors;
    private $response;

    public function init()
    {
        $this->responseFields[] = Yii::$app->request->csrfParam;

        if ($this->merchantId === null) {
            throw new InvalidConfigException('The "merchantId" property must be set.');
        }
        if ($this->password === null) {
            throw new InvalidConfigException('The "password" property must be set.');
        }
    }

    /**
     * @param $data
     * @param bool $encoded
     * @return string
     */
    public function getSignature($data, $encoded = true)
    {
        $data = array_filter($data, function($var) {
            return $var !== '' && $var !== null;
        });
        ksort($data);

        $str = $this->password;
        foreach ($data as $k => $v) {
            $str .= self::SIGNATURE_SEPARATOR . $v;
        }

        if ($encoded) {
            return sha1($str);
        } else {
            return $str;
        }
    }

    /**
     * @param $response
     * @return bool|string
     */
    public function checkPayment($response)
    {
        $this->clearErrors();
        $data = serialize($response);

        $responseSignature = $response['signature'];
        foreach ($response as $k => $v) {
            if (!in_array($k, $this->responseFields)) {
                unset($response[$k]);
            }
        }
        $this->response = $response;

        if (isset($response['order_id'])) {
            list($order_id,) = explode(self::ORDER_SEPARATOR, $response['order_id']);
        } else {
            $order_id = null;
        }
        if (!$order_id) {
            $this->setError('Error 1: #{order_id}, status: {order_status}', $response);
            return false;
        }

        $order = Module::getInstance()->manager->createOplataTransactionQuery()->where(['id' => $order_id])->one();

        if (!$order) {
            $this->setError('Error 2: #{order_id}, status: {order_status}', $response);
            return false;
        }

        if ($this->merchantId != $response['merchant_id']) {
            $this->setError('Error 3: #{order_id}, status: {order_status}', $response);
            return false;
        }


        if ($this->getSignature($response) != $responseSignature) {
            $this->setError('Error 4: #{order_id}, status: {order_status}', $response);
            return false;
        }

        if ($response['order_status'] == self::ORDER_DECLINED) {
            $this->setError('Error 5: #{order_id}, status: {order_status}', $response);
            return false;
        }

        if ($response['order_status'] == self::ORDER_APPROVED) {
            $order->payment_id = $response['payment_id'];
            $order->order_status = $response['order_status'];
            $order->response_status = $response['response_status'];
            $order->response_data = $data;
            if (!$order->save(false)) {
                $this->setError('Error 6: #{order_id}, status: {order_status}', $response);
                return false;
            }
        } else {
            $this->setError('Error 7: #{order_id}, status: {order_status}', $response);
        }
        return true;
    }

    /**
     * @param $data
     * @return bool|\pavlinter\admoplata\models\OplataItem|OplataTransaction
     */
    public function createOrder($data)
    {
        $this->clearErrors();
        $items = $this->getItems();

        if (empty($items)) {
            $this->setError('Basket is empty!');
            return false;
        }

        $price = 0;
        foreach ($items as $item) {
            /* @var $item \pavlinter\admoplata\models\OplataItem */
            if (!$item->validate()) {
                $errors = $item->getErrors();
                foreach ($errors as $field => $errs) {
                    foreach ($errs as $err) {
                        $this->setError($err, false);
                    }
                }
                return false;
            }
            $price += $item->price * $item->amount;
        }

        $order = Module::getInstance()->manager->createOplataTransaction();
        /* @var $order \pavlinter\admoplata\models\OplataTransaction */
        $order->setScenario('createOrder');
        if ($order->load($data, '') && $order->validate()) {
            $order->price = $price;
            $order->response_status = OplataTransaction::STATUS_NOT_PAID;
            $order->save(false);

            foreach ($items as $item) {
                $order->link('items', $item);
            }
            return $order;
        }
        $errors = $order->getErrors();
        foreach ($errors as $field => $errs) {
            foreach ($errs as $err) {
                $this->setError($err, false);
            }
        }
        return false;
    }

    /**
     * @param $int
     * @param $currency
     * @return string
     */
    public function price($int, $currency = null)
    {
        if ($currency) {
            $currency = ' ' . Module::getInstance()->manager->createOplataTransactionQuery('currency_list', $currency);
        } else {
            $currency = null;
        }
        return Yii::$app->formatter->asDecimal($int, 2) . $currency;
    }

    /**
     * @param $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }
    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     *
     */
    public function clearItems()
    {
        $this->items = [];
    }

    /**
     *
     */
    public function clearErrors()
    {
        $this->errors = [];
    }

    /**
     * @param $msg
     */
    public function setError($msg, $param = [])
    {
        if ($param === false) {
            $this->errors[] = $msg;
            return;
        }

        if (!isset($params['dot'])) {
            $params['dot'] = false;
        }
        $this->errors[] = Yii::t('admoplata/server_errors', $msg, $params);
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        $errors = $this->getErrors();
        if (!empty($errors)) {
            return reset($errors);
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        $errors = $this->getErrors();
        return !empty($errors);
    }

    /**
     * @return bool
     */
    public function getResponse()
    {
        $this->response;
    }
}