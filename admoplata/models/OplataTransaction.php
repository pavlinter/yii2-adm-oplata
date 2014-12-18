<?php

namespace pavlinter\admoplata\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\BaseActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%oplata_transaction}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $email
 * @property string $description
 * @property string $payment_id
 * @property string $price
 * @property string $shipping
 * @property string $currency
 * @property string $order_status
 * @property string $response_status
 * @property string $data
 * @property string $response_data
 * @property string $alias
 * @property string $created_at
 *
 * @property OplataItem[] $items
 */
class OplataTransaction extends \yii\db\ActiveRecord
{
    const CURRENCY_EUR = "EUR";
    const CURRENCY_USD = "USD";
    const CURRENCY_RUB = "RUB";
    const CURRENCY_UAH = "UAH";

    const STATUS_NOT_PAID = 'not paid';
    const STATUS_FAILURE  = 'failure';
    const STATUS_SUCCESS  = 'success';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oplata_transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'payment_id'], 'integer'],
            [['price', 'currency','response_status', 'alias', 'description'], 'required'],
            [['price', 'shipping'], 'double'],
            [['email'], 'email'],
            [['currency'], 'in', 'range' => array_keys(self::currency_list())],
            [['data', 'response_data'], 'safe'],
            [['order_status', 'response_status'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 1024],
            [['email'], 'string', 'max' => 255],
            [['alias'], 'string', 'max' => 32],
        ];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['createOrder'] = ['user_id', 'email', 'shipping', 'data', 'description', 'currency'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (is_array($this->data) || is_object($this->data)) {
            $this->data = serialize($this->data);
        }
        if (is_array($this->response_data) || is_object($this->response_data)) {
            $this->response_data = serialize($this->response_data);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $data = @unserialize($this->data);
        if ($data !== false) {
            $this->data = $data;
        }
        $response_data = @unserialize($this->response_data);
        if ($response_data !== false) {
            $this->response_data = $response_data;
        }

        return parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adm/oplata_transaction', 'ID'),
            'user_id' => Yii::t('adm/oplata_transaction', 'User'),
            'email' => Yii::t('adm/oplata_transaction', 'Email'),
            'payment_id' => Yii::t('adm/oplata_transaction', 'Payment'),
            'price' => Yii::t('adm/oplata_transaction', 'Price'),
            'shipping' => Yii::t('adm/oplata_transaction', 'Shipping'),
            'currency' => Yii::t('adm/oplata_transaction', 'Currency'),
            'order_status' => Yii::t('adm/oplata_transaction', 'Order Status'),
            'response_status' => Yii::t('adm/oplata_transaction', 'Status'),
            'data' => Yii::t('adm/oplata_transaction', 'Data'),
            'response_data' => Yii::t('adm/oplata_transaction', 'Response Data'),
            'alias' => Yii::t('adm/oplata_transaction', 'Alias'),
            'created_at' => Yii::t('adm/oplata_transaction', 'Created'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(OplataItem::className(), ['oplata_transaction_id' => 'id']);
    }

    /**
     * @param null $currency
     * @return array|null
     */
    public static function currency_list($currency = false)
    {
        $list = [
            self::CURRENCY_EUR => Yii::t('adm/oplata_transaction', 'EUR'),
            self::CURRENCY_USD => Yii::t('adm/oplata_transaction', 'USD'),
            self::CURRENCY_RUB => Yii::t('adm/oplata_transaction', 'RUB'),
            self::CURRENCY_UAH => Yii::t('adm/oplata_transaction', 'UAH'),
        ];
        if ($currency) {
            if (isset($list[$currency])) {
                return $list[$currency];
            }
            return null;
        }
        return $list;
    }

    public static function status_list($status = false)
    {
        $list = [
            self::STATUS_NOT_PAID => Yii::t('adm/oplata_transaction', 'Not paid'),
            self::STATUS_FAILURE => Yii::t('adm/oplata_transaction', 'Failure'),
            self::STATUS_SUCCESS => Yii::t('adm/oplata_transaction', 'Success'),
        ];
        if ($status !== false) {
            if (isset($list[$status])) {
                return $list[$status];
            }
            return null;
        }
        return $list;
    }

}
