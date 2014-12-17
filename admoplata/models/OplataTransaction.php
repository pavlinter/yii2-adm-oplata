<?php

namespace pavlinter\admoplata\models;

use Yii;

/**
 * This is the model class for table "{{%oplata_transaction}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $email
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
 * @property OplataItem[] $oplataItems
 */
class OplataTransaction extends \yii\db\ActiveRecord
{
    const CURRENCY_EUR = "EUR";
    const CURRENCY_USD = "USD";
    const CURRENCY_RUB = "RUB";
    const CURRENCY_UAH = "UAH";

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
            [['payment_id', 'price', 'currency', 'order_status', 'response_status', 'alias', 'created_at'], 'required'],
            [['price', 'shipping'], 'number'],
            [['data', 'response_data'], 'string'],
            [['created_at'], 'safe'],
            [['email'], 'string', 'max' => 255],
            [['currency'], 'in', 'range' => array_keys(self::currency_list())],
            [['order_status', 'response_status'], 'string', 'max' => 50],
            [['alias'], 'string', 'max' => 32]
        ];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return $scenarios;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adm/oplata_transaction', 'ID'),
            'user_id' => Yii::t('adm/oplata_transaction', 'User ID'),
            'email' => Yii::t('adm/oplata_transaction', 'Email'),
            'payment_id' => Yii::t('adm/oplata_transaction', 'Payment ID'),
            'price' => Yii::t('adm/oplata_transaction', 'Price'),
            'shipping' => Yii::t('adm/oplata_transaction', 'Shipping'),
            'currency' => Yii::t('adm/oplata_transaction', 'Currency'),
            'order_status' => Yii::t('adm/oplata_transaction', 'Order Status'),
            'response_status' => Yii::t('adm/oplata_transaction', 'Response Status'),
            'data' => Yii::t('adm/oplata_transaction', 'Data'),
            'response_data' => Yii::t('adm/oplata_transaction', 'Response Data'),
            'alias' => Yii::t('adm/oplata_transaction', 'Alias'),
            'created_at' => Yii::t('adm/oplata_transaction', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOplataItems()
    {
        return $this->hasMany(OplataItem::className(), ['oplata_transaction_id' => 'id']);
    }

    /**
     * @param null $currency
     * @return array|null
     */
    public static function currency_list($currency = null)
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


}
