<?php

/**
 * @package yii2-adm-oplata
 * @author Pavels Radajevs <pavlinter@gmail.com>
 * @copyright Copyright &copy; Pavels Radajevs <pavlinter@gmail.com>, 2015
 * @version 1.0.0
 */

namespace pavlinter\admoplata\controllers;

use pavlinter\adm\Adm;
use pavlinter\adm\filters\AccessControl;
use pavlinter\admoplata\models\OplataTransaction;
use pavlinter\admoplata\Module;
use pavlinter\multifields\ModelHelper;
use Yii;
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
                        'actions' => ['cron'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'user-list', 'mail', 'remind-template', 'overdue-template'],
                        'roles' => ['Adm-OplataRead'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'send-email', 'send-overdue'],
                        'roles' => ['Adm-OplataCreate'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'send-email', 'send-overdue'],
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
                    Yii::$app->getSession()->setFlash('success', Adm::t('','Data successfully inserted!'));

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
                        return ['r' =>1, 'newId' => $newId, 'id' => $model->id];
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
                        Yii::$app->getSession()->setFlash('success', Adm::t('','Data successfully changed!'));

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
                        return ['r' =>1, 'newId' => $newId, 'id' => $model->id];
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
        Yii::$app->getSession()->setFlash('success', Adm::t('','Data successfully removed!'));
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

        $viewCallback = Module::getInstance()->userSelect['viewCallback'];
        $querySearch = Module::getInstance()->userSelect['querySearch'];
        $queryLoad = Module::getInstance()->userSelect['queryLoad'];

        if($viewCallback === null){
            $viewCallback = function ($row) {
                return Adm::t('oplata','{email}:select2 template', $row);
            };
        }
        if($querySearch === null){
            $querySearch = function ($query, $userTable, $search) {
                /* @var \yii\db\Query $query */
                return $query->from($userTable)
                    ->where(['like', 'email', $search])
                    ->limit(20)->all();
            };
        }

        if($viewCallback === null){
            $queryLoad = function ($query, $userTable, $id) {
                /* @var \yii\db\Query $query */
                return $query->from($userTable)
                    ->where(['id' => $id])->one();
            };
        }

        $userTable      = forward_static_call(array(Adm::getInstance()->manager->userClass, 'tableName'));
        $out = ['more' => false];

        if (!is_null($search)) {
            $query = new \yii\db\Query();
            $rows = call_user_func($querySearch, $query, $userTable, $search);
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
                if (isset($row['dot'])) {
                    $row['dot'] = false;
                }
                $results[] = [
                    'id' => $row['id'],
                    'text' => call_user_func($viewCallback, $row),
                    'template' => Adm::t('oplata', "Email - {email} Username - {username}", $params),
                ];
            }
            $out['results'] = $results;
        } else if ($id > 0) {
            $query = new \yii\db\Query();
            $row = call_user_func($queryLoad, $query, $userTable, $id);
            $out['results'] = ['id' => $id, 'text' => call_user_func($viewCallback, $row)];
        } else {
            $out['results'] = ['id' => 0, 'text' => 'No matching records found'];
        }
        echo Json::encode($out);
    }

    /**
     * @return string
     */
    public function actionMail() {

        $model = Module::getInstance()->manager->createOplataTransaction();
        $this->layout = false;
        $model->id = "xxxxxx";
        $model->currency = "USD";
        $model->email = "test@test.com";
        $model->created_at = time();
        $model->title = "test";

        return $this->render(Module::getInstance()->mailTemplate,[
            'model' => $model,
            'enableDot' => true,
            'username' => 'Bob',
        ]);
    }

    /**
 * @return string
 */
    public function actionRemindTemplate() {

        $model = Module::getInstance()->manager->createOplataTransaction();
        $this->layout = false;
        $model->id = "xxxxxx";
        $model->currency = "USD";
        $model->email = "test@test.com";
        $model->created_at = time();
        $model->title = "test";

        return $this->render(Module::getInstance()->remindTemplate,[
            'model' => $model,
            'enableDot' => true,
            'username' => 'Jon',
        ]);
    }

    /**
     * @return string
     */
    public function actionOverdueTemplate() {

        $model = Module::getInstance()->manager->createOplataTransaction();
        $this->layout = false;
        $model->id = "xxxxxx";
        $model->currency = "USD";
        $model->email = "test@test.com";
        $model->created_at = time();
        $model->title = "test";

        return $this->render(Module::getInstance()->overdueTemplate,[
            'model' => $model,
            'enableDot' => true,
            'username' => 'William',
        ]);
    }


    /**
     * @param null $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionSendEmail($id = null) {
        $json['r'] = 1;
        if ($id === null) {
            $order_id = Yii::$app->request->post('id');
        } else {
            $order_id = $id;
        }

        if (!$order_id) {
            if ($id !== null) {
                return $this->redirect(['index']);
            }
            $json['r'] = 0;
            return Json::encode($json);
        }

        $model = $this->findModel($order_id);
        $currentLang = Yii::$app->getI18n()->getId();
        Yii::$app->getI18n()->changeLanguage($model->language_id);

        $module = Module::getInstance();

        if ($module->sendFunc === null) {
            $sendFunc = function ($model, $module, $user, $username) {
                Yii::$app->mailer->htmlLayout = false;
                return Yii::$app->mailer->compose([
                        'html' => $module->mailTemplate,
                    ], [
                        'model' => $model,
                        'username' => $username,
                    ])->setTo($model->email)
                    ->setFrom($module->sendFrom)
                    ->setSubject(Adm::t("oplata", "Invoice Subject", ['dot' => false]))
                    ->send();
            };
        } else {
            $sendFunc = $module->sendFunc;
        }
        $username = '';
        $user = null;
        if ($model->user_id) {
            $user = $model->user;
            if ($user) {
                $username = $user->username;
            }
        }

        if (call_user_func($sendFunc, $model, $module, $user, $username)) {
            $model->sent_email = 1;
            if (!$model->save(false)) {
                $json['r'] = 0;
            }
        } else {
            $json['r'] = 0;
        }
        if ($id !== null) {
            Yii::$app->getI18n()->changeLanguage($currentLang);
            return $this->redirect(['index']);
        }
        return Json::encode($json);
    }


    /**
     * @param null $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionSendOverdue($id = null) {
        $json['r'] = 1;
        if ($id === null) {
            $order_id = Yii::$app->request->post('id');
        } else {
            $order_id = $id;
        }
        exit('dd');
        if (!$order_id) {
            if ($id !== null) {
                return $this->redirect(['index']);
            }
            $json['r'] = 0;
            return Json::encode($json);
        }

        $model = $this->findModel($order_id);
        $currentLang = Yii::$app->getI18n()->getId();
        Yii::$app->getI18n()->changeLanguage($model->language_id);

        $module = Module::getInstance();

        if ($module->overdueFunc === null) {
            $module->overdueFunc = function ($model, $module, $user, $username) {
                Yii::$app->mailer->htmlLayout = false;
                return Yii::$app->mailer->compose([
                    'html' => $module->overdueTemplate,
                ], [
                    'model' => $model,
                    'username' => $username,
                ])->setTo($model->email)
                    ->setFrom($module->sendFrom)
                    ->setSubject(Adm::t("oplata/overdue", "Overdue Payment", ['dot' => false]))
                    ->send();
            };
        }
        $username = '';
        $user = null;
        if ($model->user_id) {
            $user = $model->user;
            if ($user) {
                $username = $user->username;
            }
        }

        if (!call_user_func($module->overdueFunc, $model, $module, $user, $username)) {
            $json['r'] = 0;
        }
        if ($id !== null) {
            Yii::$app->getI18n()->changeLanguage($currentLang);
            return $this->redirect(['index']);
        } else {
            $json['text'] = Adm::t('oplata/overdue', "domain.com");
        }
        return Json::encode($json);
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

    /**
     * For cron
     */
    public function actionCron()
    {
        $module = Module::getInstance();
        if (!$module->remindDays) {
            return 'Remind Disabled';
        }

        $query = $module->manager->createOplataTransactionQuery();
        /* @var \yii\db\Query $query */
        $query->where([
            'response_status' => OplataTransaction::STATUS_NOT_PAID,
            'remind_note' => 0,
        ])->andWhere("NOW() > DATE_SUB(date_end, INTERVAL :day DAY) AND NOW() < date_end", ['day' => $module->remindDays]);

        foreach ($query->batch(10) as $models) {
            foreach ($models as $model) {
                Yii::$app->getI18n()->changeLanguage($model->language_id);
                $username = '';
                $user = null;
                if ($model->user_id) {
                    $user = $model->user;
                    if ($user) {
                        $username = $user->username;
                    }
                }

                Yii::$app->mailer->htmlLayout = false;
                $res = Yii::$app->mailer->compose([
                    'html' => $module->remindTemplate,
                ], [
                    'model' => $model,
                    'username' => $username,
                ])->setTo($model->email)
                    ->setFrom($module->sendFrom)
                    ->setSubject(Adm::t("oplata/remind", "Remind Payment Day", ['dot' => false]))
                    ->send();
                if ($res) {
                    $model->remind_note = 1;
                    $model->save(false);
                }
                sleep(2);
            }
        }
        return 'Success!';
    }
}
