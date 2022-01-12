<?php

namespace app\controllers;

use Yii;
use app\models\Log;
use app\models\LogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\basic\debugHelper;
use app\controllers\LogsController;

/**
 * LogController implements the CRUD actions for Log model.
 */
class LogController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return parent::behaviors();
    }

    public function actionShow($count = 20, $package = false){

        function read_last_lines($fp, $num)
        {
            $idx   = 0;

            $lines = array();
            while(($line = fgets($fp)))
            {
                $lines[$idx] = $line;
                $idx = ($idx + 1) % $num;
            }

            $p1 = array_slice($lines,    $idx);
            $p2 = array_slice($lines, 0, $idx);
            $ordered_lines = array_merge($p1, $p2);

            return $ordered_lines;
        }

        $fp    = fopen('/var/www/main/data/www/logs/debug_' . date('Y-m-d') . '.log', 'r');
        $lines = array_reverse(read_last_lines($fp, $count));
        fclose($fp);

        $packageList = [];

        $parseLine = [];
        foreach($lines as $line){
            $regx = null;
            preg_match('/\[(.{6}.*?)\]\s+(\w*)\.(\w*)\W+(\w*).+?(\{.*\})/', $line, $regx, PREG_OFFSET_CAPTURE);

            $newLine = [
                "date" => $regx[1][0],
                "path" => $regx[2][0],
                "status" => $regx[3][0],
                "method" => $regx[4][0],
                "content" => $regx[5][0]
            ];

            try{
                $contentJson = json_decode($regx[5][0]);
                $packageList[$contentJson->app->package] = 1;
            }catch (\Exception $e){

            }
            array_push($parseLine, $newLine);
        }

        if($package == false || $package == "false") {
            $validList = $parseLine;
        }else{
            $validList = [];
            for ($i = 0; $i < count($parseLine); $i++) {
                try {
                    $contentJson = json_decode($parseLine[$i]['content']);

                    if ($contentJson->app->package == $package) {
                        $validList[count($validList)] = $parseLine[$i];
                    }

                } catch (\Exception $e) {

                }
            }
        }

        return $this->render('logview', [
            "logLines" => $validList,
            "packageList" => $packageList
        ]);
    }

    /**
     * Lists all Log models.
     * @return mixed
     */
    public function actionIndex()
    {

        // language
        $data = Log::find()->select(["language"])->groupBy(['language'])->all();
        $languageArray = [];
        foreach ($data as $item) {
            $languageArray[$item->language] = $item->language;
        }

        // country
        $data = Log::find()->select(["country"])->groupBy(['country'])->all();
        $countryArray = [];
        foreach ($data as $item) {
            $countryArray[$item->country] = $item->country;
        }

        // os
        $data = Log::find()->select(["os"])->groupBy(['os'])->all();
        $osArray = [];
        foreach ($data as $item) {
            $osArray[$item->os] = $item->os;
        }

        // browser
        $data = Log::find()->select(['browser'])->groupBy(['browser'])->all();
        $browserArray = [];
        foreach ($data as $item) {
            $browserArray[$item->os] = $item->os;
        }



        $searchModel = new LogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'languageArray' => $languageArray,
            'countryArray' => $countryArray,
            'osArray' => $osArray,
            'browserArray' => $browserArray,
        ]);
    }

    /**
     * Displays a single Log model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Log model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Log();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Log model.
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
     * Deletes an existing Log model.
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
     * Finds the Log model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Log the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Log::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    public function actionSend($type = 'info', $data = 'dummy')
    {
        $action = $type . 'Send';
        $data = json_decode($data, true, 512, JSON_BIGINT_AS_STRING);

        $logger = new LogsController();
        $logger->data['data'] = $data;
        $logger->$action("AppInfo");
        //debugHelper::print($data);
    }
    
}
