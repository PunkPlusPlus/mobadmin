<?php

namespace app\controllers;
use app\models\Links;
use app\models\Notifications;
use app\models\Stats;
use Yii;
use yii\filters\AccessControl;
use app\models\EmployeeProfiles;
use app\models\Prices;
use app\models\Users;
use app\basic\debugHelper;
use yii\filters\VerbFilter;
use webvimark\modules\UserManagement\models\User;

class ProfileController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'ghost-access'=> [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
        ];
    }


    public function actionIndex()
    {
        if(User::hasRole('employee') && !isset($_GET['partner']) && User::hasRole('employee')) {
            return $this->employeeProfile();
        }else{
            return $this->partnerProfile();
        }
    }

    public function partnerProfile(){
        $from = $_GET['from'] ?? date("d/m/Y", strtotime('-1 weeks'));
        $to = $_GET['to'] ?? date("d/m/Y");
        $dateStartFormat = substr($from, 6, 4) . "-" . substr($from, 3, 2) . "-" . substr($from, 0, 2) . " 00:00:00";
        $dateEndFormat = substr($to, 6, 4) . "-" . substr($to, 3, 2) . "-" . substr($to, 0, 2) . " 23:59:59";

        $user_id = User::getCurrentUser()->id;
        if(isset($_GET['id']) && User::hasPermission('change_notifications')){
            $user_id = $_GET['id'];
        }

        $statisticData = Stats::getData($user_id, $dateStartFormat, $dateEndFormat);
        $partnerData = $statisticData['partnersData'][$user_id];
        $statsData = $statisticData['statsData'];

        $userInfo = Users::findOne($user_id);
        $notifications = Notifications::find()->where([
            'user_id' => $user_id
        ])->with('app')->all();

        $linkList = Links::find()
            ->where(['=','user_id', $user_id])
            ->andWhere(['=','archived', 0])
            ->all();

        $apps = [-1 => 'Все'];
        foreach ($linkList as $link) {
            if($link->linkcountry->app && $link->linkcountry->app->published != -1 && $link->linkcountry->app_id) {
                $apps[$link->linkcountry->app_id] = $link->linkcountry->app->name ?? '';
            }
        }

        return $this->render('partner', compact('userInfo', 'notifications', 'apps', 'partnerData', 'statsData', 'from', 'to'));
    }

    public function employeeProfile(){

		$user_id = User::getCurrentUser()->id;
		
		if(isset($_GET['id']) && User::hasPermission('view_users')){
			$user_id = $_GET['id'];
		}
        $userProfile = employeeProfiles::find()
            ->where(['=', 'user_id', $user_id])
            ->one();
			
        $userInfo = Users::find()
            ->where(['=', 'id', $user_id])
            ->one();

        $statsInstalls = $this->checkAllInstall();
        $statsInstalls['total_bonus'] = $statsInstalls['total_installs']*$userProfile['bonus_factor'];

        return $this->render('employee',[
			'userInfo' => $userInfo,
            'userProfile' => $userProfile,
            'statsInstalls' => $statsInstalls
        ]);
    }
	
	public function actionCalcRevenue(){
		if(!User::hasPermission('BLOCK')){
			print "403 Forbidden";
			exit();
		}
	}


    public function checkAllInstall()
    {
        //$from = $_GET['from'] ?? "01/".date("m/Y");
        //$to = $_GET['to'] ?? cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y"))."/".date("m/Y");
        if(date("d") > 20){
            $from = $_GET['from'] ?? "20/".date("m/Y");
            $to = $_GET['to'] ?? "20/".date("m/Y", strtotime("+1 month"));
        }else{
            $from = $_GET['from'] ?? "20/".date("m/Y", strtotime("-1 month"));
            $to = $_GET['to'] ?? "20/".date("m/Y");
        }

        $dateStartFormat = substr($from, 6, 4) . "-" . substr($from, 3, 2) . "-" . substr($from, 0, 2) . " 00:00:00";
        $dateEndFormat = substr($to, 6, 4) . "-" . substr($to, 3, 2) . "-" . substr($to, 0, 2) . " 23:59:59";

        $connection = Yii::$app->getDb();
        $apps = $connection->createCommand("
            SELECT
                Count( * ) AS installs,
                tbl_linkcountries.country_code,
                tbl_linkcountries.app_id,
                `user`.display_name,
                `user`.email,
                `user`.id as user_id
            FROM
                tbl_devices
                INNER JOIN tbl_links ON tbl_devices.link_id = tbl_links.id
                INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id
                INNER JOIN `user` ON `user`.id = tbl_links.user_id 
            WHERE tbl_devices.date_reg >= CAST(:date_start AS DATETIME)
                AND tbl_devices.date_reg < CAST(:date_end AS DATETIME)
            GROUP BY
                tbl_linkcountries.country_code,
                tbl_links.user_id,
                tbl_links.id
            ", [':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);

        $apps = $apps->queryAll();
		
        $statsData['total_installs'] = 0;
        $statsData['total_profit'] = 0;

        foreach ($apps as $app) {

            $price = Prices::find()
                ->where(['app_id' => $app['app_id']])
                ->andWhere(['user_id' => $app['user_id']])
                ->andWhere(['country_code' => $app['country_code']])
                ->one();

            if(!$price){
                $price = Prices::find()
                    ->where(['app_id' => $app['app_id']])
                    ->andWhere(['user_id' => $app['user_id']])
                    ->andWhere(['country_code' => "all"])
                    ->one();
            }

            if(!isset($partnersData[$app['user_id']])) {
                $partnersData[$app['user_id']] = [
                    "id" => $app['user_id'],
                    "display_name" => $app['display_name'],
                    "email" => $app['email'],
                    "installs" => $app['installs'],
                    "profit" => $app['installs']*$price['price'],
                ];
            }else{
                $partnersData[$app['user_id']]['installs'] += $app['installs'];
                $partnersData[$app['user_id']]['profit'] += $app['installs']*$price['price'];
            }
            $statsData['total_installs'] += $app['installs'];
            $statsData['total_profit'] += $app['installs']*$price['price'];
        }

        $connection = Yii::$app->getDb();
        $partnersList = $connection->createCommand("
            SELECT user.display_name, user.email, user.id
            FROM user, auth_assignment
            WHERE user.id = auth_assignment.user_id
            AND auth_assignment.item_name = 'partner'
            ");

        $partnersList = $partnersList->queryAll();

        foreach($partnersList as $partner){
            if(!isset($partnersData[$partner['id']])){
                $partnersData[$partner['id']] = [
                    "id" => $partner['id'],
                    "display_name" => $partner['display_name'],
                    "email" => $partner['email'],
                    "installs" => 0,
                    "profit" => 0,
                ];
            }
        }

        $statsData['from'] = $from;
        $statsData['to'] = $to;

        return $statsData;
    }

}
