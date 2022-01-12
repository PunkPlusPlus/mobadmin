<?php

namespace app\controllers;

use Yii;
use app\models\Devices;
use app\models\DevicesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use app\models\Visits;
/**
 * DevicesController implements the CRUD actions for Devices model.
 */
class DevicesController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
			'ghost-access'=> [
				'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
			],
        ];
    }

    /**
     * Lists all Devices models.
     * @return mixed
     */
    public function actionIndex()
    {
		$limit = $_GET['limit'] ?? '50';
		$model = Devices::find()
			->orderBy(['id' => SORT_DESC])
			->limit($limit)
			->all();

        return $this->render('index', [
            'model' => $model
        ]);
    }

    /**
     * Displays a single Devices model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
		$otherData['allVisits'] = $this->getAllVisits($id);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'otherData' => $otherData,
        ]);
    }

	public function getAllVisits($device_id){
		$AllVisits = Visits::find()
           ->where(['device_id'=>$device_id])
		   ->limit(15)
		   ->orderBy(['id' => SORT_DESC])
           ->all();
		
		return $AllVisits;
	}

    /**
     * Creates a new Devices model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Devices();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Devices model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Devices model.
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
     * Finds the Devices model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Devices the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Devices::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}