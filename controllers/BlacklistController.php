<?php


namespace app\controllers;

use yii\data\ArrayDataProvider;
use app\components\BlackListComponent;
use yii\data\Pagination;
use yii\web\Controller;
use app\models\Blacklist;
use app\models\Devices;
use Yii;
use webvimark\modules\UserManagement\models\User;

class BlacklistController extends Controller
{
    public function actionIndex($idfa = -1)
    {
         if (!User::hasRole('Admin') && !User::hasRole('Developer'))
         {
             header('HTTP/1.0 403 Forbidden');
             echo '403 Forbidden';
             exit();
         }
        
        $model = Blacklist::find()->where(['idfa' => $idfa])->one();
        if ($model == null) {
            $model = new Blacklist();
        }
        $params = Yii::$app->request->post() ?? null;
        $option = null;
        $idfa = null;
        if ($params != null) {
            $idfa = trim($params['idfa']) ?? null;
            $option = $params['list'] ?? null;
        }

        if ($idfa == null) {
            return $this->render('index');
        }

        switch ($option) {
            case 1:
                $model = BlackListComponent::addToList($idfa, 1);
                break;
            case 0:
                $model = BlackListComponent::addToList($idfa, 0);
                break;
            case -1:
                BlackListComponent::removeFromList($idfa);
                break;
        }

        return $this->render('index', ['model' => $model]);
    }

    public function actionWhite()
    {
        return $this->render('whitelist');
    }

    public function actionBlack()
    {
        return $this->render('blacklist');
    }

    public function actionUpdate()
    {
	BlackListComponent::updateIps();
    }

    public function actionIps()
     {
             $data = BlackListComponent::checkDuplicatesV2();
             $output = BlackListComponent::formatOutput($data);
             //$pages = new Pagination(['totalCount' => count($output)]);
	     $providerFirst = new ArrayDataProvider([
		 'allModels' => $output['first'],
		 'pagination' => [
		     'pageSize' => 10 
		 ]
	     ]);
	     $providerSecond = new ArrayDataProvider([
	         'allModels' => $output['second'],
		 'pagination' => [
		     'pageSize' => 10
		 ]
	     ]);
             return $this->render('ips', ['data' => $output, 'providerFirst' => $providerFirst, 'providerSecond' => $providerSecond]);
     }


    public function actionBlock()
    {
         if (!User::hasRole('Admin') && !User::hasRole('Developer'))
         {
             header('HTTP/1.0 403 Forbidden');
             echo '403 Forbidden';
             exit();
         }
        $params = Yii::$app->request->get() ?? null;
        if ($params != null) {
            $idfa = $params['id'] ?? null;
            $app_id = $params['app_id'] ?? null;
            BlackListComponent::removeFromList($idfa);
            if($idfa != null) {
                $model = BlackListComponent::addToList($idfa, 1);
            }            
            unset($_GET['id']);
            $query = http_build_query($_GET);
        }
        return $this->redirect('/visits/index?'.$query);
    }



}
