<?php

namespace pavlinter\admoplata\models;

use pavlinter\admoplata\Module;
use Yii;

/**
 * This is the model class for table "{{%oplata_item}}".
 *
 * @property string $id
 * @property string $oplata_transaction_id
 * @property string $title
 * @property string $description
 * @property string $amount
 * @property string $price
 *
 * @property Transaction $oplataTransaction
 */
class OplataItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oplata_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'price', 'amount'], 'required'],
            [['oplata_transaction_id'], 'integer'],
            [['amount'], 'integer', 'min' => 1],
            [['description'], 'string'],
            [['price'], 'double'],
            [['title'], 'string', 'max' => 200]
        ];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['multiFields'] = ['title', 'description', 'price', 'amount'];
        return $scenarios;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('modelAdm/oplata_item', 'ID'),
            'oplata_transaction_id' => Yii::t('modelAdm/oplata_item', 'Oplata Transaction ID'),
            'title' => Yii::t('modelAdm/oplata_item', 'Title'),
            'description' => Yii::t('modelAdm/oplata_item', 'Description'),
            'price' => Yii::t('modelAdm/oplata_item', 'Price'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransaction()
    {
        return $this->hasOne(Module::getInstance()->manager->createOplataTransaction('className'), ['id' => 'oplata_transaction_id']);
    }
}
