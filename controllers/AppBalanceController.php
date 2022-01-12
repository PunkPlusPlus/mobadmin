<?php

namespace app\controllers;

use app\basic\debugHelper;
use app\models\Apps;
use app\models\Users;
use Yii;
use app\models\AppBalance;
use app\models\AppBalanceSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AppBalanceController implements the CRUD actions for AppBalance model.
 */
class AppBalanceController extends BaseAdminController
{
    /**
     * Lists all AppBalance models.
     * @return mixed
     */
    public function actionIndex()
    {
        $records = AppBalance::find()->with('app')->orderBy('app_id, status')->all();
        $apps = [];
        $total = [
            'account' => 0,
            'bug' => 0,
            'partner' => 0,
            'all' => 0
        ];

        if(!empty($records)) {
            foreach($records as $record) {
                if(!isset($apps[$record->app_id])) {
                    $apps[$record->app_id] = [
                        'id' => $record->app_id,
                        'name' => $record->app->name,
                        'account' => 0,
                        'bug' => 0,
                        'partner' => 0,
                        'profit' => 0
                    ];
                }
                $apps[$record->app_id][$record->status] += $record->count;
                $apps[$record->app_id]['profit'] += $record->count;

                $total[$record->status] += $record->count;
                $total['all'] += $record->count;
            }
        }

        return $this->render('index', [
            'apps' => $apps,
            'total' => $total
        ]);
    }

    /**
     * Displays a single AppBalance model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $app = Apps::findOne($id);
        if(empty($app)) {
            throw new NotFoundHttpException('Приложение не найдено');
        }
        $records = AppBalance::find()->where(['app_id' => $id])->with('partner')->orderBy('created_at DESC')->all();
        $total_sum = 0;
        foreach ($records as $record) {
            $total_sum += $record->count;
        }

        return $this->render('view', compact(['records', 'app', 'total_sum']));
    }

    /**
     * Creates a new AppBalance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($app_id = -1)
    {
        if($app_id !== -1) {
            $app = Apps::findOne($app_id);
            if(empty($app)) {
                throw new NotFoundHttpException('Приложение не найдено');
            }
        }

        $model = new AppBalance();
        $model->app_id = $app_id;

        if ($model->load(Yii::$app->request->post())) {
            $model->count = self::getCount();

            if($model->save()) {
                return $this->redirect(['view', 'id' => $model->app_id]);
            }
        }

        $partners_array = Users::getPartnersArray();

        return $this->render('create', [
            'model' => $model,
            'partners_array' => $partners_array
        ]);
    }

    /**
     * Updates an existing AppBalance model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->count = self::getCount();
            
            if($model->save()) {
                return $this->redirect(['view', 'id' => $model->app_id]);
            }
        }

        $partners_array = Users::getPartnersArray();

        return $this->render('update', [
            'model' => $model,
            'partners_array' => $partners_array
        ]);
    }

    /**
     * Deletes an existing AppBalance model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $record = $this->findModel($id);
        $app_id = $record->app_id;
        $record->delete();

        return $this->redirect(['view?id='.$app_id]);
    }

    /**
     * Finds the AppBalance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AppBalance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AppBalance::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    protected static function getCount()
    {
        switch ($_POST['AppBalance']['status']) {
            case 'account':
            case 'bug':
                $count = -abs($_POST['AppBalance']['count']);
                break;
            case 'partner':
                $count = abs($_POST['AppBalance']['count']);
        }
        return $count;
    }
}
