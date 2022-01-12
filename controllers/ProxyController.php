<?php

namespace app\controllers;

use mysql_xdevapi\Exception;
use Yii;
use app\basic\debugHelper;

class ProxyController extends \yii\web\Controller
{
    public function GetIP($location)
    {
        $apiKey = Yii::$app->params['proxyorbit_apikey'];
        $url = "https://api.proxyorbit.com/v1/?token=" . $apiKey . "&ssl=true&count=15";

        if ($location != "all")
            $url .= "&location=" . $location;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30));

        $response = curl_exec($curl);
        //$err = curl_error($curl);

        curl_close($curl);

        $proxies = [];
        $responseArr = json_decode($response);
	
        foreach ($responseArr as $proxy) {
            array_push($proxies, $proxy->curl);
        }

        $dataProxies = Yii::$app->cache->get("data_proxies") ?? false;
        if($dataProxies !== false && ($dataProxies['pos']+1) >= count($dataProxies['list'])){
            $dataProxies = false;
        }

        if ($dataProxies === false || count($dataProxies['list']) <= 0) {
            $dataProxies = ProxyController::сheckProxy($proxies);
        }else{
            $dataProxies['pos'] += 1;
        }
        Yii::$app->cache->set("data_proxies", $dataProxies, 900);
        $verifProxyIP = $dataProxies['list'][$dataProxies['pos']];

        return $verifProxyIP;
    }

    public function actionIndex($location)
    {
        return $this->GetIP($location);
    }

    public function actionTest($location)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://play.google.com/store/apps/details?id=cdn.mainrainbowdev.sweetalchemy",
            //CURLOPT_PROXY => "http://217.160.28.101:3128",
            CURLOPT_PROXY => $this->GetIP($location),
            CURLOPT_TIMEOUT => 15));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        //debugHelper::print($response);
        return ($response);
    }


    public function сheckProxy($listProxies)
    {
        $dataProxies['pos'] = 0;
        $dataProxies['list'] = [];
        $mc = curl_multi_init();
        for ($thread_no = 0; $thread_no < count($listProxies); $thread_no++) {
            $c [$thread_no] = curl_init();
            curl_setopt($c [$thread_no], CURLOPT_URL, "http://google.com");
            curl_setopt($c [$thread_no], CURLOPT_HEADER, 0);
            curl_setopt($c [$thread_no], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c [$thread_no], CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($c [$thread_no], CURLOPT_TIMEOUT, 10);
            curl_setopt($c [$thread_no], CURLOPT_PROXY, $listProxies[$thread_no]);
            curl_setopt($c [$thread_no], CURLOPT_PROXYTYPE, 0);
            curl_multi_add_handle($mc, $c [$thread_no]);
        }

        do {
            while (($execrun = curl_multi_exec($mc, $running)) == CURLM_CALL_MULTI_PERFORM) ;
            if ($execrun != CURLM_OK) break;
            while ($done = curl_multi_info_read($mc)) {
                $info = curl_getinfo($done ['handle']);
                if ($info ['http_code'] == 301) {
                    array_push($dataProxies['list'], $listProxies[array_search($done['handle'], $c)]);
                }
                curl_multi_remove_handle($mc, $done ['handle']);
            }
        } while ($running);
        curl_multi_close($mc);
        if(count($dataProxies['list']) <= 0){
            throw new \Exception("not_found_proxy");
        }
        return $dataProxies;
    }
}
