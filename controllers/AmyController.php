<?php
namespace app\controllers;


use app\models\Apps;
use app\models\Linkcountries;
use app\models\LnkUserVisits;
use app\models\PostbackLog;
use app\models\Visits;
use Yii;
use yii\web\Controller;


/**
 * Class AmyController
 * @package app\controllers
 */
class AmyController extends Controller
{

    public function actionIndex()
    {
        $data = 'Invalid request';
        return $this->renderPartial('index', [
            'data' => $data
        ]);
    }


    /**
     * @param $click_id
     * @param $user_id
     * @param $payout
     * @return string
     */
    public function actionDevice($click_id, $user_id, $payout = 0)
    {
        $response = ['result'=>'error'];
        if(
            !empty($click_id) /*&& !empty($user_id)*/
        )
        {
            $myLog = new PostbackLog();
            $myLog->click_id = $click_id;
            $myLog->user_id = $user_id ?? 'unknown';
            $myLog->payout = $payout;

            $visits = Visits::find()->where(['click_id'=>$click_id])->one();
            if($visits) {
                $myLog->find_in_visits = 'yes';
                $visits->payout = $payout;
                if($visits->save(false)) {

                    $linkCountry = $visits->linkcountry;
                    if($linkCountry) {
                        $app_id = $linkCountry->app_id;
                    } else {
                        $linkCountry = Linkcountries::find()->where(['id'=>$visits->linkcountry_id])->one();
                        $app_id = $linkCountry->app_id;
                    }


                    if($app_id > 0) {

                        //var_dump($app_id);die;
                        $app =Apps::find()->where(['id'=>$app_id])->one();
                        if($app) {

                            $responseNotify = $this->sendNotify([
                                'post_api_key' => $app->yam_post_api_key,
                                'app_id' => $app->yam_app_id,
                                'profile_id'=>$click_id,
                                'payout'=>$payout,
                            ], $myLog);


                            //echo '<pre>';print_r($response);die;
                            if($responseNotify === true) {
                                $myLog->sent_event_to_yam = 'yes';
                                $response['result']='success';
                            } else {
                                $response['message'] = 'Cannot send data to appmetrika.yandex.ru';
                            }


                        } else {
                            // app data not retrieve
                            $response['message'] = 'Cannot retrieve app data from database';
                        }

                    } else {
                        // app id not retrieve
                        $response['message'] = 'Cannot retrieve app id from Linkcountries';
                    }


                    $linkUserVisits = LnkUserVisits::find()->where(['click_id'=>$click_id])->one();

                    if($linkUserVisits) {
                        $linkUserVisits->payout = $payout;
                        $linkUserVisits->save(false);

                        //$response['result']='success';

                    } else {
                        //$response['message'] = 'Cannot retrieve data of linkUserVisits';
                    }

                } else {
                    $response['message'] = 'Cannot update payout';
                }
            }  else {
                $response['message'] = 'Cannot retrieve data of  Visits';
            }


            $myLog->visitor_meta_data = json_encode([
                'response'=>$response,
                'app_id'=>($app_id ?? 'unknown'),
                'appmetrika_responce_if_error'=>var_export(($responseNotify ?? 'unknown'),1),
            ],JSON_UNESCAPED_UNICODE | JSON_OBJECT_AS_ARRAY);

            $myLog->save(false);
        } else {
            $response['message'] = 'Incorrect parameters';
        }


        return $this->renderPartial('index', [
            'data' => json_encode($response)
        ]);

    }


    /**
     * @param array $options
     * @param PostbackLog $logger
     * @return bool|string
     */
    private function sendNotify($options =[], &$logger) {


        $data = [
            'post_api_key'=>$options['post_api_key'] ?? '',
            'application_id'=>$options['app_id'] ?? '',
            'profile_id'=>$options['profile_id'] ?? '',
            'event_name'=>(floatval($options['payout'])>0) ? 'deposit' :'registration',
            'event_timestamp'=>(time() + 3600),
            'session_type'=>'foreground',
        ];


        $data = http_build_query($data);
        $url = 'https://api.appmetrica.yandex.ru/logs/v1/import/events?' . $data;
        $logger->yam_post_request = $url;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        /*curl_setopt($curl, CURLOPT_POSTFIELDS, $data);*/
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Host: api.appmetrica.yandex.com',
                'Content-Length: 0',
                'Connection: close'
        ));



        $response = curl_exec($curl);
        $logger->yam_post_answer = $response;
        if (strpos($response, 'Your data has been uploaded')!==false) {
            return true;
        } else {
            return $response || curl_error($curl);
        }

    }

}