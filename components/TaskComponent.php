<?php


namespace app\components;


use app\controllers\LogsController;
use webvimark\modules\UserManagement\models\User;

class TaskComponent
{
    private const BASE_URL = "http://136.244.83.120:4000/api/v1/";

    public static function checkApp($app)
    {
        if ($app) {
            $data['uuid'] = $app->uuid;
            $data['app_id'] = $app->fb_app_id;
        } else {
            $data = null;
        }
        return $data;
    }

    public static function formatData($request, $model = -1)
    {
        try {
            $request = json_decode($request, true);
        } catch (\Exception $e) {

        }
        if (!User::hasPermission('share_accounts_manually')) {
            try {
                $ad_accounts_ids = self::constructArray($request['ids']);
                $fields = array(
                    'uuid' => $model->uuid,
                    'app_id' => $model->fb_app_id,
                    'ad_accounts_ids' => $ad_accounts_ids
                );
                return json_encode($fields);
            } catch (\Exception $e) {
                //echo $e->getMessage();
                return false;
            }
        }
        if (isset($request['uuid']) && isset($request['app_id'])) {
            $fields = array(
                'uuid' => $request['uuid'],
                'app_id' => $request['app_id'],
                'ad_accounts_ids' => self::constructArray($request['ids'])
            );
            foreach ($fields as $field) {
                if ($field == null || $field == "" || $field == " ") {
                    return false;
                }
            }
            return json_encode($fields);
        } else if (isset($request['task_id'])) {
            $str = trim($request['task_id']);
            return $str;
        } else {
            return false;
        }
    }

    public static function mergeTimestamp($response)
    {

        $response = json_decode($response, true);
        $new_response = array();
        try {
            foreach ($response as $key) {
                $key['timestamp'] = implode(" ", $key['timestamp']);
                array_push($new_response, $key);

            }
        } catch (\Exception $e) {
            $logger = new LogsController();
            $logger->data['message'] = $response;
            $logger->infoSend("TimeStamp");
        } 
        return $new_response;
    }

    public static function mergeIds($array)
    {
        //$response = json_decode($array, true);

        $array['ad_accounts_ids'] = implode(", ", $array['ad_accounts_ids']);
        return $array;
    }


    private static function constructArray(string $str)
    {
        if (strpos($str, ' ') !== false) {
            $ids = explode(",", $str);
        } else {
            $ids = explode("\r\n", $str);
        }
        $format_ids = array();
        foreach ($ids as $id) {
            if ($id != "" && $id != " " && $id != "\n") {
                array_push($format_ids, $id);
            }
        }
        $format_ids = array_map('trim', $format_ids);
        return $format_ids;
    }

    public static function constructAll($info)
    {
        $count = 0;
        $new = array();
        foreach ($info as $item) {
            $new["$count"] = $item;
            $count++;
        }
        return $new;
    }

    private static function getMethod($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public static function getTaskResult($id)
    {
        if ($id) {
            $url = self::BASE_URL . "task-results/" . trim($id);

            $result = self::getMethod($url);
            if (strrpos($result, "500")) {
                $result = array(
                    'Error' => 'Ошибка сервера'
                );
            }
        } else {
            $result = array(
                'Error' => 'Ошибка отправки'
            );
        }

        return $result;
    }

    public static function getAll()
    {
        $url = self::BASE_URL . "task-results";
        $result = self::getMethod($url);
        return $result;
    }

    public static function createTask($fields)
    {
        try {
            $url = self::BASE_URL . "create-task";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function logTask($request, $response, $method)
    {
        $logger = new LogsController();
        $logger->data['message']['request'] = json_encode($request);
        $logger->data['message']['response'] = $response;
        $logger->infoSend($method);

    }
}

