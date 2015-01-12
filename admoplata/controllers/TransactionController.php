<?php

namespace pavlinter\admoplata\controllers;

use pavlinter\adm\Adm;
use pavlinter\adm\filters\AccessControl;
use pavlinter\admoplata\Module;
use pavlinter\multifields\ModelHelper;
use Yii;
use yii\db\Query;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TransactionController implements the CRUD actions for OplataTransaction model.
 */
class TransactionController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'user-list'],
                        'roles' => ['Adm-OplataRead'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['Adm-OplataCreate'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'roles' => ['Adm-OplataUpdate'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'roles' => ['Adm-OplataDelete'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete-item'],
                        'roles' => ['Adm-OplataDeleteItem'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'delete-item' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all OplataTransaction models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = Module::getInstance()->manager->createOplataTransactionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new OplataTransaction model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = Module::getInstance()->manager->createOplataTransaction();
        /* @var $model \pavlinter\admoplata\models\OplataTransaction */
        $model->setScenario('admCreate');
        $model->loadDefaultValues();
        $items = [Module::getInstance()->manager->createOplataItem(['scenario' => 'multiFields'])];

        if(Yii::$app->request->isPost) {

            $loaded = $model->load(Yii::$app->request->post());
            $loaded = ModelHelper::load($items) && $loaded;

            if ($loaded) {
                if (ModelHelper::validate([$model, $items])) {
                    $model->price = 0;
                    foreach ($items as $item) {
                        $model->price += $item->price * $item->amount;
                    }
                    $model->save(false);

                    $newId = [];
                    foreach ($items as $oldId => $item) {
                        $item->oplata_transaction_id = $model->id;
                        $item->save(false);
                        ModelHelper::ajaxChangeField($newId, $item, 'title', $oldId);
                        ModelHelper::ajaxChangeField($newId, $item, 'description', $oldId);
                        ModelHelper::ajaxChangeField($newId, $item, 'amount', $oldId);
                        ModelHelper::ajaxChangeField($newId, $item, 'price', $oldId);
                    }
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return ['r' =>1, 'newId' => $newId];
                    } else {
                        return $this->redirect(['index']);
                    }
                } else {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        $errors = ModelHelper::ajaxErrors([$model, $items]);
                        return ['r' => 0, 'errors' => $errors];
                    }
                }
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    /**
     * Updates an existing OplataTransaction model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario('admUpdate');
        /* @var $model \pavlinter\admoplata\models\OplataTransaction */

        $items = $model->getItems()->indexBy('id')->all();
        if (empty($items)) {
            $items[] = Module::getInstance()->manager->createOplataItem(['scenario' => 'multiFields']);
        } else {
            foreach ($items as $item) {
                $item->scenario = 'multiFields';
            }
        }

        if(Yii::$app->request->isPost) {

            $loaded = $model->load(Yii::$app->request->post());
            $loaded = ModelHelper::load($items) && $loaded;

            if ($loaded) {

                if (ModelHelper::validate([$model, $items])) {
                    $model->price = 0;
                    foreach ($items as $item) {
                        $model->price += $item->price * $item->amount;
                    }
                    $newId = [];
                    if ($model->order_status === null) {

                        if ($model->user_id) {
                            $model->person = null;
                            $model->email = null;
                        }

                        $model->save(false);
                        foreach ($items as $oldId => $item) {
                            $item->oplata_transaction_id = $model->id;
                            $item->save(false);
                            ModelHelper::ajaxChangeField($newId, $item, 'title', $oldId);
                            ModelHelper::ajaxChangeField($newId, $item, 'description', $oldId);
                            ModelHelper::ajaxChangeField($newId, $item, 'amount', $oldId);
                            ModelHelper::ajaxChangeField($newId, $item, 'price', $oldId);
                        }
                    }

                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return ['r' =>1, 'newId' => $newId];
                    } else {
                        return $this->redirect(['index']);
                    }
                } else {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        $errors = ModelHelper::ajaxErrors([$model, $items]);
                        return ['r' => 0, 'errors' => $errors];
                    }
                }
            }
        }


        return $this->render('update', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    /**
     * Deletes an existing OplataTransaction model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @return array
     */
    public function actionDeleteItem()
    {
        $id = Yii::$app->request->post('id');
        $model = Module::getInstance()->manager->createOplataItemQuery('findOne', $id);
        /* @var $model \pavlinter\admoplata\models\OplataItem */
        $json['r'] = 1;
        if ($model !== null) {
            if ($model->transaction) {
                if ($model->transaction->order_status === null) {
                    $price = $model->price * $model->amount;
                    $model->transaction->price -= $price;
                    $model->transaction->save(false);
                    $model->delete();
                } else {
                    $json['r'] = 0;
                }
                $json['price'] = Yii::$app->formatter->asDecimal($model->transaction->price, 2);
            } else {
                $model->delete();
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $json;
    }

    /**
     * @param null $search
     * @param null $id
     */
    public function actionUserList($search = null, $id = null) {

        $out = ['more' => false];
        if (!is_null($search)) {
            $userTable      = forward_static_call(array(Adm::getInstance()->manager->userClass, 'tableName'));
            $query = new Query;
            $query->from($userTable)
                ->where('email LIKE "%' . $search .'%"')
                ->limit(20);
            $command = $query->createCommand();
            $rows = $command->queryAll();
            $results = [];
            foreach ($rows as $row) {

                $params = [];
                foreach ($row as $attribute => $value) {
                    if (in_array($attribute, ['auth_key', 'password_hash', 'password_reset_token', 'role', 'status'])) {
                        continue;
                    }
                    $params[$attribute] = $value;
                }
                $params['dot'] = false;
                $params['br']  = false;
                $results[] = [
                    'id' => $row['id'],
                    'text' => $row['email'],
                    'template' => Adm::t('oplata', "Email - {email}\nUsername - {username}", $params),
                ];
            }

            $out['results'] = $results;
        }
        elseif ($id > 0) {
            $model = Adm::getInstance()->manager->createUserQuery()->where(['id' => $id])->one();
            $out['results'] = ['id' => $id, 'text' => $model->email];
        }
        else {
            $out['results'] = ['id' => 0, 'text' => 'No matching records found'];
        }
        echo Json::encode($out);
    }

    /**
     * Finds the OplataTransaction model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return OplataTransaction the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where(['id' => $id])->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
