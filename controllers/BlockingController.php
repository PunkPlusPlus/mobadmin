<?php

namespace app\controllers;

use app\models\BlockMethod;
use app\models\BlockType;
use Yii;
use app\models\Blocking;
use app\models\BlockingSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BlockingController implements the CRUD actions for Blocking model.
 */
class BlockingController extends Controller
{
	/**
     * {@inheritdoc}
     */
	public function behaviors()
	{
		return [
			'ghost-access'=> [
				'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
			],
		];
	}
    /**
     * Lists all Blocking models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BlockingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        /*$arrayBlockType[0] = Yii::t('app', 'Choose');
        $arrayBlockMethod_1 = Yii::t('app', 'Choose');*/

        $arrayBlockType = ArrayHelper::map($this->getBlockType(),'id','name');
        $arrayBlockMethod = ArrayHelper::map($this->getBlockMethod(),'id','name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'arrayBlockType'=>$arrayBlockType,
            'arrayBlockMethod'=>$arrayBlockMethod,


        ]);
    }

    /**
     * Displays a single Blocking model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    /*public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }*/

    /**
     * Creates a new Blocking model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id = null)
    {
        /* @var $arrayBlockType */
        /* @var $arrayBlockMethod */

        $model = new Blocking();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Blocking data saved successful! ')
            );
            return $this->redirect(['index']);
        }

        if($id !== null) {
            $model->block_type = $id;
        }

        $arrayBlockType = ArrayHelper::map($this->getBlockType(),'id','name');
        $arrayBlockMethod = ArrayHelper::map($this->getBlockMethod(),'id','name');

        $model->active = 'yes';
        $model->deleted = 'no';

        return $this->render('create', [
            'model' => $model,
            'arrayBlockType'=>$arrayBlockType,
            'arrayBlockMethod'=>$arrayBlockMethod,
            'id'=>$id

        ]);
    }

    /**
     * Updates an existing Blocking model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        /* @var $arrayBlockType */
        /* @var $arrayBlockMethod */

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            Yii::$app->session->addFlash('success', Yii::t('app', 'Data updated successful'));
            return $this->redirect(['index']); //'id' => $model->id
        }
        $arrayBlockType = ArrayHelper::map($this->getBlockType(),'id','name');
        $arrayBlockMethod = ArrayHelper::map($this->getBlockMethod(),'id','name');

        return $this->render('update', [
            'model' => $model,
            'arrayBlockType'=>$arrayBlockType,
            'arrayBlockMethod'=>$arrayBlockMethod,
        ]);
    }

    /**
     * Deletes an existing Blocking model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Blocking model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Blocking the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Blocking::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }


    private function getBlockType (){

        return BlockType::find()
            ->where(['active'=>'yes'])
            ->andWhere(['deleted'=>'no'])
            ->all();

    }


    private function getBlockMethod (){

        return BlockMethod::find()
            ->where(['active'=>'yes'])
            ->andWhere(['deleted'=>'no'])
            ->all();

    }
}
