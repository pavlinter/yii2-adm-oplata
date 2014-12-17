<?php

namespace pavlinter\admpages\controllers;

use pavlinter\adm\filters\AccessControl;
use pavlinter\admpages\Module;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PageController implements the CRUD actions for Page model.
 */
class PageController extends Controller
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
                        'roles' => ['Adm-Pages'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'nestable' => [
                'class' => 'pavlinter\adm\actions\GridNestableAction',
                'model' => Module::getInstance()->manager->pageClass,
            ]
        ];
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public function actionFiles($id)
    {
        $model = $this->findModel($id);

        $files = Module::getInstance()->files;
        $startPath = '';
        if (!isset($files[$model->type])) {
            return $this->redirect(['index', 'id_parent' => 0]);
        }
        if (isset($files[$model->type]['startPath'])) {
            $startPath = strtr($files[$model->type]['startPath'], [
                '{id}' => $model->id,
            ]);
        }
        foreach ($files[$model->type]['dirs'] as $path) {
            $dir = Yii::getAlias(strtr($path, [
                '{id}' => $model->id,
            ]));
            \yii\helpers\FileHelper::createDirectory($dir);
        }


        return $this->render('files', [
            'model' => $model,
            'startPath' => $startPath,
        ]);
    }

    /**
     * Lists all Page models.
     * @return mixed
     */
    public function actionIndex($id_parent = false)
    {
        $searchModel  = Module::getInstance()->manager->createPageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id_parent);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id_parent' => $id_parent,
        ]);
    }

    /**
     * Creates a new Page model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id = null, $id_parent = null)
    {

        $model = Module::getInstance()->manager->createPage();
        $model->loadDefaultValues();

        $data = Yii::$app->request->post();
        if ($model->loadAll($data)) {
            if ($model->validateAll()) {

                if ($model->saveAll(false)) {
                    return $this->redirect(['files', 'id' => $model->id]);
                }
            }
        } else {
            if($id){
                $model = $this->findModel($id);
                $model->setIsNewRecord(true);
            } else if($id_parent){
                $model->id_parent = $id_parent;
            }
        }



        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Page model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->loadAll(Yii::$app->request->post()) && $model->validateAll()) {
            if ($model->saveAll(false)) {
                return $this->redirect(['files', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Page model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if (!in_array($id, Module::getInstance()->closeDeletePage)) {
            $this->findModel($id)->delete();
        }
        return $this->redirect(['index', 'id_parent' => 0]);
    }

    /**
     * Finds the Page model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Page the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {

        $model = Module::getInstance()->manager->createPageQuery('find')->with(['translations'])->where(['id' => $id])->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
