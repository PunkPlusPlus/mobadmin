<?php


namespace app\components;


use Yii;

class IPStack
{

    private $ip;
    private $jsonResponse;
    private $arrayResponse;
    public $errorMessage=null;

    /**
     * IPStack constructor.
     * @param $ip
     */
    public function __construct($ip=null)
    {
        if(is_null($ip))
            $this->ip = $this->getClientIP();
        else
            $this->ip = $ip;
        $this->callApi();

    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }


    /**
     * @return mixed
     */
    public function getJsonResponse()
    {
        return $this->jsonResponse;
    }

    /**
     * @return mixed
     */
    public function getArrayResponse()
    {
        return $this->arrayResponse;
    }

    /**
     * Call to IPStack Api.
     */
    private function callApi()
    {
        $ch = curl_init('https://api.ipregistry.co/' . $this->getIp() . '?key=' . Yii::$app->params['ipregistryKey']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Store the data:
        $json = curl_exec($ch);

        if ($this->isJSON($json)) {

            $scope =  json_decode($json, true);
            $wrapped = (new WrapperIP($scope))->parse(true);

            $this->jsonResponse = json_encode($wrapped);
            $this->arrayResponse =$wrapped;


        } else {
            if (empty($json)) {
                $this->errorMessage = curl_error($ch);
            } else {
                $this->errorMessage = json_last_error_msg();
            }
        }

        curl_close($ch);
    }


    private function getClientIP(){
        global $_SERVER;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if(strpos($ip,',')!==false) {
            $ip = explode(',', $ip);
            $ip= trim($ip[0]);
        }

        return $ip;
    }

    private function isJSON($string) {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }


}