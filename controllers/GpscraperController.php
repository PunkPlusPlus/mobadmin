<?php

namespace app\controllers;

use Yii;
use Raulr\GooglePlayScraper\Scraper;
//use app\basic\Scraper;
use yii\web\NotFoundHttpException;
use yii\base\ErrorException;
use app\basic\debugHelper;
use app\controllers\ProxyController;
use app\models\Apps;
use app\models\notification\SlackBot;

class GpscraperController extends \yii\web\Controller
{

    public static function getFilePath()
    {
        return $_SERVER['DOCUMENT_ROOT'] . "/images/apps/";
    }

    public static function checkStatus($package, $app, $country = "us", $lang = "us"){

        $dataResult = [];
        $appGP = GpscraperController::cacheGP($package);
        if($appGP['id'] == -1){
            $dataResult['status'] = -1;
        }elseif($appGP['id'] == 3) {
            $dataResult['status'] = 3;
            return $dataResult;
        }else{
            $dataResult['status'] = 1;
        }

        if(isset($app->market_detailed)){
            $dataResult['old_version'] = json_decode($app->market_detailed)->version;
        }else{
            $dataResult['old_version'] = 0;
        }
        $dataResult['new_version'] = $appGP['version'];


        if ($dataResult['status'] == -1 && $app->published == 0) {
            $dataResult['status'] = 0; //если приложение еще не было опубликовано, оно не считается забанненым
        }
        return $dataResult;
    }

    public static function getInfo($package, $country = "us", $lang = "us")
    {
        $appDB = Apps::find();
        $appDB->where(['package' => $package]);
        //$appDB->andWhere(['<>', 'market_detailed', null]);
        $appDB = $appDB->one();

        if($appDB && $appDB->market_detailed != null){
            $app = (array)json_decode($appDB->market_detailed);
            if ($app['id'] != -1 || $appDB->published == -1) {
                if($app['image'] != -1)
                    $app['image'] = GpscraperController::cacheLogo($app['image'], $package);
                if($app['screenshots'] != -1)
                    $app['screenshots'] = GpscraperController::cacheScreenshots($app['screenshots'], $package);
            }

        }else{
            $app['id'] = '-1';
            $app['url'] = '-1';
            $app['image'] = '-1';
            $app['title'] = '-1';
            $app['author'] = '-1';
            $app['author_link'] = '-1';
            $app['screenshots'] = '-1';
            $app['categories'][0] = '-1';
            $app['description'] = '-1';
            $app['rating'] = '-1';
            $app['votes'] = '-1';
            $app['last_updated'] = '-1';
            $app['size'] = '-1';
            $app['downloads'] = '-1';
            $app['version'] = '-1';
            $app['supported_os'] = '-1';
            $app['whatsnew'] = '-1';
        }

        return $app;
        //debugHelper::print($app);
    }
    
    public function actionTest(){
        $this->cacheGP("klr.proxymoxy.downtownvrgas");
    }

    public static function cacheGP($package){
	$logger = new LogsController();

        try {
            $scraper = new Scraper();
            $scraper->setDefaultLang("us");
            $scraper->setDefaultCountry("us");
            //$scraper->setProxy(ProxyController::GetIP("all"));
        }catch (\Exception $exception){
            if($exception->getMessage() == "not_found_proxy"){
                print "Not Found Proxies";
                exit();
            }
        }

        try {
            $app = $scraper->getApp($package);

            //debugHelper::print($app);
        } catch (\Exception $exception) {
	    
	    if($exception->getCode() == 404) {
                $app['id'] = '-1';
                $app['url'] = '-1';
                $app['image'] = '-1';
                $app['title'] = '-1';
                $app['author'] = '-1';
                $app['author_link'] = '-1';
                $app['screenshots'] = '-1';
                $app['categories'][0] = '-1';
                $app['description'] = '-1';
                $app['rating'] = '-1';
                $app['votes'] = '-1';
                $app['last_updated'] = '-1';
                $app['size'] = '-1';
                $app['downloads'] = '-1';
                $app['version'] = '-1';
                $app['supported_os'] = '-1';
                $app['whatsnew'] = '-1';
            } else{
                $app['id'] = '3';
                return $app;
//                debugHelper::print($exception->getMessage(), 0);
//                debugHelper::print($exception->getCode());
//                die("proxy not work");
            }

        }

        if ($app['id'] != -1) {
            $app['image'] = GpscraperController::cacheLogo($app['image'], $package);
            $app['screenshots'] = GpscraperController::cacheScreenshots($app['screenshots'], $package);

            //print ($app['image'];

            $appDB = Apps::find()
                ->where(['package' => $package])
                //->andWhere(['market_detailed' => null])
                ->one();
            if($appDB) {
                if($app['policy'] == '') {
                    if(!$appDB->market_detailed) {
                        SlackBot::send("В приложении *<https://".$_SERVER["SERVER_NAME"]."/apps/view?id=".$appDB->id."|".$appDB->name.">* нет политики конфиденциальности. Непорядок!");
                    }
                    $app['policy'] = '-';
                }
                
                $appDB->market_detailed = json_encode($app);
                $appDB->save();
            }
        }
        return $app;
    }

    public static function cacheLogo($logoUrl, $package)
    {
        $filePath = GpscraperController::getFilePath();
        $secretKey = Yii::$app->params['secret_key'];
        $filename = "l" . md5($package . $secretKey) . ".jpg";

        if (!file_exists($filePath . $filename)) {
            $ch = curl_init($logoUrl);
            $fp = fopen($filePath . $filename, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        return "/images/apps/" . $filename;
    }

    public static function cacheScreenshots($screenshotsUrl, $package)
    {
        $screenshots = [];
        $filePath = GpscraperController::getFilePath();
        $secretKey = Yii::$app->params['secret_key'];

        $i = -1;
        foreach ($screenshotsUrl as $screenshoot) {
            $i++;
            $filename = "s" . md5($package . $i . $secretKey) . ".jpg";

            if (!file_exists($filePath . $filename)) {
                $ch = curl_init($screenshoot);
                $fp = fopen($filePath . $filename, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }
            array_push($screenshots, "/images/apps/" . $filename);
        }
        return $screenshots;
    }

    public static function getLogo($package)
    {
        $filePath = GpscraperController::getFilePath();
        $secretKey = Yii::$app->params['secret_key'];
        $filename = "l" . md5($package . $secretKey) . ".jpg";

        if (file_exists($filePath . $filename)) {
            return "/images/apps/" . $filename;
        } else {
            return "not found";
        }
    }

    public static function getScreenshots($package)
    {
        $screenshots = [];
        $filePath = GpscraperController::getFilePath();
        $secretKey = Yii::$app->params['secret_key'];

        for($i=0; $i<10; $i++){
            $filename = "s" . md5($package . $i . $secretKey) . ".jpg";

            if (file_exists($filePath . $filename)) {
                array_push($screenshots, "/images/apps/" . $filename);
            }
        }
        return $screenshots;
    }
}
