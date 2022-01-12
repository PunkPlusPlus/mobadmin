<?php

namespace app\components;

use app\models\Apps;
use app\models\Devices;
use app\models\PostbacksIncome;
use app\models\power\LgPowerClientUserData;
use yii\base\Exception;


class FacebookComponent
{
    public static function constructUrl($idfa_array, $postbacks, $app_id = 'default', $app_secret = 'default')
    {
        $urls = array();
        $app_id = trim($app_id);
        $app_secret = trim($app_secret);
        $token = $app_id . "|" . $app_secret;
        $base_url = "https://graph.facebook.com/".$app_id."/activities";
        $query_string = 'event=CUSTOM_APP_EVENTS&advertiser_id={advertiser-tracking-id}&advertiser_tracking_enabled=1&application_tracking_enabled=1&custom_events=[{"_eventName":"{event}","fb_content":"[]","fb_content_type":"product","_valueToSum":{sum},"fb_currency":{currency},}]&{ud}&{token}';
        //$query_string = 'event=CUSTOM_APP_EVENTS&advertiser_tracking_enabled=1&application_tracking_enabled=1&custom_events=[{"_eventName":"{event}","fb_content":"[]","fb_content_type":"product","_valueToSum":{sum},"fb_currency":{currency},}]&{ud}&{token}';
        $query_string = str_replace('{token}', $token, $query_string);

        for ($i = 0; $i < count($postbacks); $i++)
        {
            $user = self::getUd($postbacks[$i]->click_id);
            $amount = $postbacks[$i]->payout;
            if ($amount == 0) {
                $name = 'fb_mobile_complete_registration';
            } else {
                $name = 'fb_mobile_purchase';
            }

            $urls[$i] = $base_url . '?' . $query_string;
            $urls[$i] = str_replace('{advertiser-tracking-id}', $idfa_array[$i], $urls[$i]);
            $urls[$i] = str_replace('{event}', $name, $urls[$i]);
            $urls[$i] = str_replace('{sum}', $amount, $urls[$i]);
            $urls[$i] = str_replace('{currency}', '"USD"', $urls[$i]);
            if ($user != null) {
                $ud = [
                    'em' => hash('sha256', $user->email),
                    'ph' => hash('sha256', $user->phone),
                    'cn' => hash('sha256', strtolower($user->country)),
                    'st' => hash('sha256', strtolower($user->region_code)),
                    'ct' => hash('sha256', strtolower($user->city))
                ];
                $u_d = json_encode($ud);
                $urls[$i] = str_replace('{ud}', "ud=$u_d", $urls[$i]);
            } else {
                $urls[$i] = str_replace('{ud}', "", $urls[$i]);
            }
        }
        return $urls;
    }

    private static function sendData($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
        ));
        return $ch;
    }

    public static function sendMulti($urls)
    {
        $response = array();
        $curls = array();
        $mcurl = curl_multi_init();
        foreach ($urls as $url) {
            array_push($curls, self::sendData($url));
        }
        foreach ($curls as $ch) {
            curl_multi_add_handle($mcurl,$ch);
        }
        do {
            $status = curl_multi_exec($mcurl, $active);
            if ($active) {
                curl_multi_select($mcurl);
            }
        } while ($active && $status == CURLM_OK);

        foreach ($curls as $ch) {
            curl_multi_remove_handle($mcurl, $ch);
        }
        curl_multi_close($mcurl);
        foreach($curls as $ch) {
            array_push($response, curl_multi_getcontent($ch));
        }
        $response = self::formatResponse($response);
        return $response;
    }

    private static function formatResponse($response)
    {
        $output = array();
        foreach ($response as $item) {
            array_push($output, json_decode($item, true));
        }
        return $output;
    }

    public static function getIdfa($limit)
    {
        $limit = intval($limit);
        $idfa_array = array();
        $devices = Devices::find()->where("idfa != 0")->limit($limit)->all();
        foreach ($devices as $device) {
            array_push($idfa_array, $device->idfa);
        }
        return $idfa_array;
    }

    public static function getPostbacks($from, $to, $limit = 0)
    {
        $from = date_create_from_format('d/m/Y H:i:s', substr($from, 0, 10) . " 00:00:00");
        $to = date_create_from_format('d/m/Y H:i:s', substr($to, 0, 10) . " 23:59:59");
        if ($limit == 0) {
            $postbacks = PostbacksIncome::find()->where(['between', 'datetime', date_format($from,"Y-m-d H:i:s"), date_format($to,"Y-m-d H:i:s") ])->all();
        } else {
            $postbacks = PostbacksIncome::find()->where(['between', 'datetime', date_format($from,"Y-m-d H:i:s"), date_format($to,"Y-m-d H:i:s") ])->limit($limit)->all();
        }
        return $postbacks;
    }

    public static function getUd($click_id)
    {
        $ud = LgPowerClientUserData::find()->where(['click_id' => $click_id])->one();
        return $ud;
    }
}