<?php

namespace app\controllers;

use Yii;
use app\models\Visits;
use app\models\VisitsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use app\models\Devices;
/**
 * VisitsController implements the CRUD actions for Visits model.
 */
class VisitsController extends Controller
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
     * Lists all Visits models.
     * @return mixed
     */
    public function actionIndex()
    {
		$limit = $_GET['limit'] ?? '50';
		$model = Visits::find()
			->orderBy(['id' => SORT_DESC])
			->limit($limit)
			->all();

		
		//$otherData['allDevices'] = $this->getAllDevices();
        return $this->render('index', [
            'model' => $model
        ]);
    }
	
	public function getAllDevices(){
		$allDevices;
		foreach(Devices::find()->where([])->all() as $key => $value){
			$allDevices[$value['id']] = $value['device_name'];
		}
		return $allDevices;
	}
	
    /**
     * Displays a single Visits model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
		$otherData['allDevices'] = $this->getAllDevices();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'otherData' => $otherData,
        ]);
    }

    /**
     * Creates a new Visits model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Visits();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Visits model.
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
     * Deletes an existing Visits model.
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
     * Finds the Visits model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Visits the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Visits::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
