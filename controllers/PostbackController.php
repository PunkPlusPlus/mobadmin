<?php

namespace app\controllers;
use app\basic\ApiHelper;
use Yii;
use app\basic\debugHelper;
use app\controllers\LogsController;
use app\models\Devices;
use app\models\Apps;
use app\models\Params;
use app\models\Visits;

class PostbackController extends \yii\web\Controller
{

    public function actionIndex(){
        // if($this->blockByIp()) {
        //     die();
        // }

        $logger = new LogsController();

        $message['errors'] = [];
        if(!isset($_REQUEST['service'])) array_push($message['errors'], 'service not found');
        if(!isset($_REQUEST['key'])) array_push($message['errors'], 'key not found');
        if(!isset($_REQUEST['name'])) array_push($message['errors'], 'name not found');
        if(count($message['errors']) > 0) {
            die(json_encode($message));
        }


        $logMessage['service'] = $_REQUEST['service'];
        $logMessage['key'] = $_REQUEST['key'];
        $logMessage['name'] = $_REQUEST['name'];
        $logMessage['amount'] = $_REQUEST['amount'] ?? 0;
        $logMessage['currency'] = $_REQUEST['currency'] ?? "USD";
        $logMessage['binom'] = $_REQUEST['binom'] ?? 'not binom';


        $service = $_REQUEST['service'] ?? "appsflyer";
        $key = $_REQUEST['key'] ?? "";
        $name = $_REQUEST['name'] ?? "af_reg";
        $amount = $_REQUEST['amount'] ?? 0;
        $currency = $_REQUEST['currency'] ?? "USD";
        
        if($name === 'dep' && $amount == 0) {
            $name = 'reg';
            $logMessage['name'] = 'reg';
        }
	// sending reg or deb to onesignal
         $keys = $this->getKeys($key);
         $tags = $this->constructTags($name);
         ApiHelper::sendOnesignal($tags, $keys['external_id'], $keys['app_id']);
         // $onesignal_response = $this->sendOneSignal($name, $key);
         // end sending


        switch ($service){
            case "appsflyer":
                $data = $this->appsflyerConv($key, $name, $amount, $currency);
                $logMessage['package'] = $data['package'];
                break;
            case "branch":
                $data = $this->branchConv($key, $name, $amount, $currency);
                $logMessage['package'] = $data['package'];
                break;
            default:
                array_push($message['errors'], 'service not found');
                $logger->data['message'] = $logMessage;
                $logger->infoSend('Postback');
                die(json_encode($message));
                break;
        }
        // debugHelper::print($logMessage, 0);
        $logger->data['message'] = $logMessage;
        $logger->infoSend('Postback');
        die($data['result']);
    }

    private function getKeys($appsflyer_id)
    {
         $device = Devices::find()->where(['appsflyer_id' => $appsflyer_id])->one();
         try {
             $visit = Visits::find()->where(['device_id' => $device->id])->one();
         } catch (\Exception $e) {
             return false;
         }
         $external_user_id = $visit->onesignal_id;
         $extra = $visit->extra;
         try {
             $app_id = json_decode($extra, true)['one_signal_key'];
         } catch(\Exception $e) {
             return false;
         }
         $result = [
             'external_id' => $external_user_id,
             'app_id' => $app_id,
         ];
         return $result;
     }
 
     private function constructTags($name)
     {
         if ($name == 'reg') {
             $reg = true;
             $dep = false;
         } else if($name == 'dep') {
             $reg = true;
             $dep = true;
         } else {
             return false;
         }
         $tags = array(
             'reg' => $reg,
             'dep' => $dep
         );
         return $tags;
     }


    public function appsflyerConv($appsflyer_id, $name, $amount, $currency)
    {
        $message = [];

        $connection = Yii::$app->getDb();
        $findDevice = $connection->createCommand("
                SELECT
                    tbl_apps.id AS app_id,
                    tbl_apps.package,
                    tbl_visits.id,
                    tbl_devices.appsflyer_id,
                    tbl_links.user_id,
                    tbl_links.linkcountry_id
                FROM
                    tbl_visits
                    INNER JOIN tbl_links ON tbl_visits.link_id = tbl_links.id
                    INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id
                    INNER JOIN tbl_apps ON tbl_linkcountries.app_id = tbl_apps.id 
                    INNER JOIN tbl_devices ON tbl_visits.device_id = tbl_devices.id 
                WHERE
                    tbl_visits.device_id = tbl_devices.id
                    AND tbl_devices.appsflyer_id = :appsflyer_id 
                ORDER BY
                    tbl_apps.id ASC
                ", [':appsflyer_id' => $appsflyer_id]);

        $findDevice = $findDevice->queryOne();

        $packageApp = $findDevice['package'];

        $appsflyerDevKey = self::getAppsflyerKey($findDevice);

        $purchase_event = array(
            'appsflyer_id' => $appsflyer_id,
            'eventCurrency' => $currency, //USD, EUR, RUB etc.
            'eventTime' => date("Y-m-d H:i:s.000", time()),
            'af_events_api' => "true"
        );

        $purchase_event['eventName'] = $name; //af_purchase
        $purchase_event['eventValue'] = json_encode(array('af_revenue' => $amount, 'af_currency' => $currency));


        $data_string = json_encode($purchase_event);
        
        $ch = curl_init('https://api2.appsflyer.com/inappevent/'.$packageApp);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'authentication: '.$appsflyerDevKey,
                'Content-Length: ' . strlen($data_string))
        );
        
        
        $result = curl_exec($ch);
        // debugHelper::print(curl_getinfo($ch), 0);
        // echo 'apps_result: ' . debugHelper::print($result, 0) . '<br>';
        
        if(isset($_GET['debug'])){
            print "package: ".$packageApp;
            print "<br>appsflyerDevKey: ".$appsflyerDevKey;
            print "<pre>";
            print_r($result);
            exit();
        }
        $message['result'] = "ok";
        $message['package'] = $packageApp;
        return $message;
        //debugHelper::print($result);
    }

    public function branchConv($branch_id, $name, $amount, $currency)
    {
        $message = [];

        $device = Devices::find()
            ->where(['idfa' => $branch_id]);

        if(!$device = $device->one()){
            print "Device not found";
            exit();
        }

        $appDevice = Apps::find()
            ->where(['id' => $device->app_id])
            ->one();


        $params = Params::find()
            ->where(['app_id' => $appDevice->id])
            ->andWhere(['key' => "branch_key"]);

        if(!$params = $params->one()){
            print "branch_key not found";
            exit();
        }


        $purchase_event = array (
            'name' => 'PURCHASE',
            'customer_event_alias' => $name,
            'user_data' =>
                array (
                    'os' => 'Android',
                    'aaid' => $device->idfa,
                ),
            'event_data' =>
                array (
                    'currency' => $currency,
                    'revenue' => $amount,
                    'affiliation' => 'ProfitNetwork.App - rent mobile app',
                    'description' => 'R&B Afla Group - 2020',
                ),
            'metadata' =>
                array (
                ),
            'branch_key' => $params->value,
        );


        $data_string = json_encode($purchase_event);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api2.branch.io/v2/event/standard');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);


        $message['result'] = $result;
        $message['package'] = $appDevice->package;
        return $message;
    }

    public static function getAppsflyerKey($findDevice)
    {
        $appsflyerDevKey = '';
        $appsParams = Params::find()->where([
            'app_id' => $findDevice['app_id'],
            'key' => 'apps_flyer_key'
        ])->all();

        if(!empty($appsParams) && count($appsParams) === 1)
        {
            $appsflyerDevKey = $appsParams[0]->value;
        }
        elseif(count($appsParams) > 1)
        {
            $keys = [];

            foreach($appsParams as $param) {
                if($param->linkcountry_id == $findDevice['linkcountry_id'] && $param->user_id == $findDevice['user_id']) {
                    $keys['0'] = $param->value;
                } elseif($param->linkcountry_id == $findDevice['linkcountry_id']) {
                    $keys['1'] = $param->value;
                } elseif($param->user_id == $findDevice['user_id']) {
                    $keys['2'] = $param->value;
                } elseif($param->linkcountry_id == -1 && $param->user_id == -1) {
                    $keys['3'] = $param->value;
                }
            }
            ksort($appsflyerDevKeys);
            $appsflyerDevKey = array_shift($appsflyerDevKeys);
        }

        return $appsflyerDevKey;
    }

    private function blockByIp()
    {
        $ip = IpHelper::getIP();
        if(strpos($ip, '213.227') === 0) {
            return true;
        }
        return false;
    }
}
