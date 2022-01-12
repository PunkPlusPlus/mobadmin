<?php

namespace app\controllers;

use app\models\Stats;
use Yii;
use app\basic\debugHelper;
use app\models\Prices;
use yii\filters\VerbFilter;
use webvimark\modules\UserManagement\models\User;

class StatsController extends \yii\web\Controller
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
            'ghost-access' => [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
        ];
    }

    public function actionPartners()
    {
        $from = $_GET['from'] ?? date("d/m/Y", strtotime('-1 weeks'));
        $to = $_GET['to'] ?? date("d/m/Y");
        $dateStartFormat = substr($from, 6, 4) . "-" . substr($from, 3, 2) . "-" . substr($from, 0, 2) . " 00:00:00";
        $dateEndFormat = substr($to, 6, 4) . "-" . substr($to, 3, 2) . "-" . substr($to, 0, 2) . " 23:59:59";

        $user_id = isset($_GET['id']) ? '"'.$_GET['id'].'"' : '"%"';

        $data = Stats::getData($user_id, $dateStartFormat, $dateEndFormat);
        $partnersData = $data['partnersData'];
        $statsData = $data['statsData'];

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
        return $this->render('index', [
            'partnersData' => $partnersData,
            'statsData' => $statsData,
            'from' => $from,
            'to' => $to
        ]);
    }

}
