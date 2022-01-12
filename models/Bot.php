<?php


namespace app\models;

use app\basic\debugHelper;
use app\controllers\GpscraperController;
use app\models\notification\Sender;
use app\models\notification\SlackBot;
use app\controllers\LogsController;

class Bot
{
    public function checkApps($apps) 
    {
        $checkedAppId = Settings::find()
            ->where(['key' => "curr_check_appid"])
            ->one();

        $oldAppId = intval($checkedAppId['value']);
        debugHelper::print($oldAppId, 0);
        $lastAppId = $apps[count($apps) - 1]->id;
        $iter = 0;

        foreach($apps as $app){
            if($app->id > $oldAppId && $iter < 6){
                $iter++;
                $this->checkApp($app);

                $checkedAppId->value = ($app->id == $lastAppId) ? '0' : "".$app->id;
                debugHelper::print($checkedAppId->value, 0);
            }
        }
        $checkedAppId->save();
    }

    public function checkApp($app)
    {
        
        $appId = $app->id;
        $status = 3;
	$statuses = array();
        for ($i = 0; $i < 6; $i++) {
            if($status == 3) {
                //делаем 6 проверок (если вдруг прокся оказалось недействительной)
                $dataResultGP = GpscraperController::checkStatus($app->package, $app);
                $status = $dataResultGP['status'];
//		array_push($statuses, $status);
            }
        }
//	$test = new LogsController()
//	$test->data['message']['statuses'] = json_encode($statuses);
//	$test->data['message']['final'] = $status;
//	$test->infoSend('TestStatus');
        $logger = new LogsController();
        if($status == 3) {
            Sender::setFlash('-1');
            $logger->data['message'] = "err";
            $logger->infoSend('Proxy_error');
            return 'proxy not work';
        }

        $appDB = Apps::find()
            ->where(['id' => $appId])
            ->one();
	
	if ($status == -1) {
            $queue = Queue::find()->where(['app_id' => $appDB->id])->one();
            if ($queue == null) {
                $queue = new Queue();
                $queue->app_id = $appDB->id;
                $queue->count = 1;
                $queue->save();
                return;
            } else {
                if ($queue->count < 2) {
                    $queue->count++;
                    $queue->save();
                    return;
                } else if ($queue->count == 2) {
                    $queue->count++;
                    $queue->save();
                    $queue->delete();
                }
            }
        } else {
            $queue = Queue::find()->where(['app_id' => $appDB->id])->one();
            if ($queue) $queue->delete();
        }
        $firstVisits = false;
	if($appDB->upload_time == null && $status !== 1 && $app->published != 1) {
            $firstVisits = self::checkVisits($appId);            
            if($firstVisits) {
                $status = 2;
            }
        } elseif($appDB->upload_time != null && $status !== 1 && $status != $app->published && $app->published != 1 &&  $app->published != 4 && $app->published != 3 && $app->published != 5) {
            self::checkUploadTime($appDB);
            $appDB->lastchecked_time = date("Y-m-d H:i:s");
            $appDB->save();           
            return 'checked upload time';
        }
        $logger = new LogsController();
        $isChange = false;
	if($status != $app->published && $app->published == 2 && $status == -1){
	        $isChange = false;
	}else if($status != $app->published){
	
            if ($status == 1 && $app->published == 4){
                $isChange = false;
            } else {
                $isChange = true;
            }
            
        }

        if ($app->published !== 3 && $app->published !== 5)
        {
            if($isChange) {
                $appDB = self::updateAppTime($appDB, $firstVisits, $status);
                $sender = new Sender($app, $status);
                //$sender->send();
                Sender::setFlash($sender->getSendText()['webpanel']);
            } else {
                Sender::setFlash('Статус приложения <b>' . $app->name . '</b> не изменился');
            }
        } else {
            Sender::setFlash('Статус приложения <b>' . $app->name . '</b> не изменился');
        }

        if($dataResultGP['status'] == 1 && $dataResultGP['new_version'] != -1 && $dataResultGP['old_version'] != $dataResultGP['new_version'] && !isset($sendText['slack']) && !$isChange){
            $sendNotifVersion = "Приложение *<https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$app->id."|".$app->name.">* обновлено до версии ".$dataResultGP['new_version'];
            SlackBot::send($sendNotifVersion);
        }

        $appDB->lastchecked_time = date("Y-m-d H:i:s");
        $appDB->save();
        return $appId;
    }
    
    public function checkTrafficRoute()
    {
        $time = strtotime('-1 hour');
        $apps = Apps::find()
            ->where(['traffic_route' => 1])
            ->andWhere(['<', 'updated_at', $time])
            ->all();
  
        foreach($apps as $app) {
            $app->traffic_route = 0;
            $app->save();
        }
    }

    private static function checkVisits($appId) 
    {
        $firstVisits = false;

        $countryAll = Linkcountries::find()
            ->where(['app_id' => $appId])
            ->andWhere(['country_code' => 'all'])
            ->andWhere(['archived' => '0'])
            ->one();

        $linksAll = Links::find()
            ->where(['linkcountry_id' => $countryAll->id])
            ->andWhere(['archived' => '0'])
            ->all();

        $whereLink = ['OR'];
        foreach ($linksAll as $value) {
            array_push($whereLink, ["link_id" => $value["id"]]);
        }

        $visitsAll = Visits::find()
            ->where($whereLink)
            ->limit(20)
            ->all();

        foreach ($visitsAll as $visit) {
            if(strtoupper($visit->filterlog->country) == "US"){
                if(!$firstVisits) {
                    $firstVisits = $visit->date;
                }
            }
        }

        return $firstVisits;
    }

    private static function updateAppTime($appDB, $firstVisits, $status)
    {
        $logger = new LogsController();
        $logger->data['message']['app']['package'] = $appDB->package;
        $logger->data['message']['app']['name'] = $appDB->name;
        $logger->data['message']['user'] = 'bot';
        if($appDB->published == 0 && $status == 2){
            if($firstVisits) {
                $appDB->upload_time = $firstVisits;
            }
        }
        if($appDB->published == 0 && $status == 1){
            if($firstVisits !== false) {
                $appDB->upload_time = $firstVisits;
            } else {
                $appDB->upload_time = date("Y-m-d H:i:s");
            }
            $appDB->published_time = date("Y-m-d H:i:s");
        }
        if($appDB->published == 2 && $status == 1){
            $appDB->published_time = date("Y-m-d H:i:s");
        }
        if($appDB->published == 1 && $status == -1){
            $appDB->banned_time = date("Y-m-d H:i:s");
        }
        $change = false;
        if ($appDB->published !== 4 && $appDB->published !== 3 && $appDB->published !== 5){
            $appDB->published = $status;
            $change = true;
        }
        if ($status == -1 && $appDB->published !== 3 && $appDB->published !== 5) {
            $appDB->banned_time = date("Y-m-d H:i:s");
            $appDB->published = $status;
            $change = true;
        }
        if ($status == 1 && $appDB->published != 4) {
            $appDB->published = $status;
        }
        

        switch ($appDB->published) {
            case -1:
                $logger->data['message']['event']['name'] = 'Banned';
                $logger->data['message']['event']['time'] = $appDB->banned_time;
                $logger->infoSend('Event');
                break;
            case 1:
                $logger->data['message']['event']['name'] = 'Published';
                $logger->data['message']['event']['time'] = $appDB->published_time;
                $logger->infoSend('Event');
                break;
            case 2:
                $logger->data['message']['event']['name'] = 'Upload';
                $logger->data['message']['event']['time'] = $appDB->upload_time;
                $logger->infoSend('Event');
                break;
        }


        return $appDB;
    }

    private static function checkUploadTime($appDB)
    {
        $logger = new LogsController();
        
        
        $logger->infoSend('uploadTime');
        $upload_time = new \DateTime($appDB->upload_time);
        $now = new \DateTime();
        $interval = $upload_time->diff($now)->format('%a');

        if($interval > 30) {
            $status = -1;
            $appDB->published = $status;
            $appDB->banned_time = date("Y-m-d H:i:s");
            $appDB->lastchecked_time = date("Y-m-d H:i:s");
            $appDB->save();

            $sender = new Sender($appDB, -2);
            $sender->send();
            Sender::setFlash($sender->getSendText()['webpanel']);

            return '';
        }
        Sender::setFlash('Приложение не опубликовано');
        return 'Приложение не опубликовано';
    }
}
