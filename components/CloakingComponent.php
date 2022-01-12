<?php
namespace app\components;

use Yii;

class CloakingComponent
{


    public $params;
    public $ipstack;
    public $configs;
    public $result;
    public $resultArray;

    /**
     * @var CloakingComponent
     */
    private static $instance;


    public function setConfigs($configs){
        $this->configs = $configs;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     *
     *
     * @param array $deviceParams ['deviceModel'=>null,'deviceName'=>null,'deviceBrand'=>null,'resolution'=>null]
     * @param array $disableFilter ['country','traffarmor','blocking']
     * @param array $countries       ['ru','uk','nl']
     * @param string|null $countryPolice   'deny'|'allow'
     * @return array
     */
    public function cloak(
        $deviceParams = [],
        $disableFilter=[],
        $countries = [],
        $blockByList = false,
        $countryPolice = null
    ){

        global $_SERVER;
        $configs = [];


        foreach (['country','traffarmor','blocking'] as $key) {
            if(array_search($key,$disableFilter)!==false){
                $configs[$key] = 'no';
            } else {
                $configs[$key] = 'yes';
            }
        }


        if(
            !is_null($countryPolice) &&
            !empty($countryPolice))
        {
            $configs['country_metadata']['policy'] = $countryPolice;

        } else {
            $configs['country_metadata']['policy'] = 'deny';
        }

        // $countryPolice
        if(!empty($countries)){
            $configs['country_metadata']['countries'] = implode(' ', $countries);
        } else {
            $configs['country_metadata']['countries'] = '';
        }



        foreach (['deviceModel'=>null,'deviceName'=>null,'deviceBrand'=>null,'resolution'=>null] as $key=>$null) {
            if(
                isset($deviceParams[$key]) &&
                !empty($deviceParams[$key])
            ){
                $configs[$key] = $deviceParams[$key];
            }else{
                $configs[$key] = $null;
            }
        }


        LoggingComponent::getInstance()->log('passed_info', []);
        LoggingComponent::getInstance()->log('_config', $configs);

        LoggingComponent::getInstance()->log('entry', date("Y-m-d H:-i:s"));
        if(!empty($configs))
        $this->configs = $configs;

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $this->ipstack = (new IPStack($this->getIp()))->getArrayResponse();
        LoggingComponent::getInstance()->log('ipstack', $this->ipstack);
        LoggingComponent::getInstance()->log('ip', $this->getIp());
        LoggingComponent::getInstance()->log('ua', $userAgent );
        LoggingComponent::getInstance()->log('referer', $_SERVER['HTTP_REFERER'] ?? '');
        if(is_array($this->ipstack)) {

            // language
            if (isset($this->ipstack['location']['languages'])) {
                $language = $this->ipstack['location']['languages'][0]['code'];
            } else {
                $language = $this->ipstack['country_code'];
            }
            LoggingComponent::getInstance()->log('language', $language);

            //country
            LoggingComponent::getInstance()->log('country', $this->ipstack['country_code']);
            //city
            LoggingComponent::getInstance()->log('city', $this->ipstack['city'] . '/' . $this->ipstack['region_name']);

            //isp
            LoggingComponent::getInstance()->log('isp', $this->ipstack['connection']['isp']);
            // asn
            LoggingComponent::getInstance()->log('asn', $this->ipstack['connection']['asn']);
        }

        LoggingComponent::getInstance()->log('os',BrowsingComponent::getInstance()->getOS($userAgent));
        LoggingComponent::getInstance()->log('browser',BrowsingComponent::getInstance()->getBrowser($userAgent));



        if(
            isset($this->configs['country']) &&
            isset($this->configs['country_metadata']) &&
            !($this->skipped()) &&
            $this->configs['country'] === 'yes'
        ) {
            $countryVerified = false;
            $countryVerified = $this->cloak_by_country();
        } else {
            $countryVerified = true;
        }


        if($this->configs['traffarmor'] == 'yes' && !($this->skipped())) {
            $trafficarmorVerified = false;
            $trafficarmorVerified = $this->cloak_traffarmor();
        } else {
            $trafficarmorVerified = true;
            //$this->debugArray['trafficarmor_result'] = 'skipped by company config';
            //LoggingComponent::getInstance()->log('trafficarmor_result', 'skipped by company config');
        }

        if($this->configs['blocking'] == 'yes' &&  !($this->skipped())) {
            $blockingVerified = false;
            $blockingVerified = $this->	cloak_by_custom_blocking();
        } else {
            $blockingVerified = true;
        }

        if($this->skipped()) {
            $passed_info = [
                'skip_inject'=> var_export($this->skipped(),1)
            ];

            $this->result = true;
        } else {
            $passed_info = [
                'trafficarmor_verified'=>$trafficarmorVerified,
                'country_verified'=>$countryVerified,
                'blocking_verified'=>$blockingVerified,
            ];
            $this->result = $trafficarmorVerified && $countryVerified &&  $blockingVerified;
        }
        $testParam = 'noBlackList';
        if ($blockByList == 1) {
            $this->result = false;
            $testParam = 'blockByBlacklist';
        }

        LoggingComponent::getInstance()->log('log_type','app_log');
        LoggingComponent::getInstance()->log('detailed',json_encode($passed_info,JSON_NUMERIC_CHECK));
        LoggingComponent::getInstance()->log('passed_info',$passed_info);
        LoggingComponent::getInstance()->log('is_bot', ($this->result === false ? 1 : 0));



		LoggingComponent::getInstance()->log('end', date("Y-m-d H:-i:s"));
        if(!LoggingComponent::getInstance()->isCommit())
            LoggingComponent::getInstance()->commit();


        $this->resultArray = [
            'isBot'         => (!$this->result),
            'isBotString'   => var_export((!$this->result),1),
            'countryCode'   => $this->ipstack['country_code'],
            'logId'         => LoggingComponent::getInstance()->getId(),
            //'logdata'         => LoggingComponent::getInstance()->logDataArray
            ];

        return $this->resultArray;
    }


    protected function __construct() {}
    protected function __clone() {}
    protected function __wakeup() {}

    /**
     * @return CloakingComponent;
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self() ;
        }

        return self::$instance;
    }



    ////////////////////////////////////////
    /// PRIVATE PART
    ////////////////////////////////////////


    //binom
    private function cloak_traffarmor(){
        // TrafficArmor part
        $trafficarmorVerified = false;
        /**
         * Own curl request to traffarmor
         */
        $trrm = new TraffArmorComponent();
        $trrm->sendRequest([
            'ip'=>$this->getIp(),
            'server_referer'=>$_SERVER['HTTP_HOST'],
            'referrer'=>$_SERVER['HTTP_REFERER'] ?? '',
            'ua' => $_SERVER['HTTP_USER_AGENT']

        ]); //just find magic

        $trafficarmorResult = $trrm->getResult();

        if(is_array($trafficarmorResult)) {
            if(
            empty($trafficarmorResult['response']['cloak_reason'])
                /*&& (strpos($arrayResult['response']['url'],'blackpage')!==false)*/
            ) {
                // passed
                $trafficarmorVerified = true;
                LoggingComponent::getInstance()->log('trafficarmor_passed', '1');

            } else {
                // banned
                $trafficarmorVerified = false;
                LoggingComponent::getInstance()->log('trafficarmor_cloacking_reason', $trafficarmorResult['response']['cloak_reason']);
                LoggingComponent::getInstance()->log('trafficarmor_passed', '0');
            }
        } else {
            //error occurred, skipped
            $trafficarmorVerified = true;
            LoggingComponent::getInstance()->log('trafficarmor_passed', '-1');
            LoggingComponent::getInstance()->log('trafficarmor.error', 'Empty response or json incorrect');
        }
        LoggingComponent::getInstance()->log('trafficarmor_result', var_export($trafficarmorVerified,1));
        return $trafficarmorVerified;
    }

    //binom
    private function cloak_by_country(){
        // check country

        $countryVerified = false;

        $country = strtolower($this->ipstack['country_code'] ?? null);

        LoggingComponent::getInstance()->log('country_for_clocking', $this->ipstack['country_code']);
        if($country) {
            if(isset($this->configs['country_metadata']) &&
                isset($this->configs['country_metadata']['policy']) &&
                isset($this->configs['country_metadata']['countries'])

            ){
                $meta = $this->configs['country_metadata'];

                if(!(strpos(trim($meta['countries']), 'global')===false)){
                    $countryVerified = true;
                } else {
                    $countryArray = explode(' ', $meta['countries']);
                    $countryArray = $this->checkUnitedKingdom($countryArray);
                    //LoggingComponent::getInstance()->log('country_from_database',$data->countries);
                    if(array_search(strtolower($country), $countryArray)!==false) {
                        $countryVerified = true;
                    }
                }

                if ($meta['policy'] === 'allow') {
                    $countryVerified = !$countryVerified;
                }

            } else {
                Yii::warning('Cannot retrieve metadata from configs', 'AivaCloacking');
                LoggingComponent::getInstance()->log('Cannot retrieve metadata from configs');
            }

        } else {
            Yii::warning('Cannot retrieve country from ipstack', 'AivaCloacking');
            LoggingComponent::getInstance()->log('ipstack.error','Cannot retrieve country from ipstack');
        }


        return $countryVerified;

    }

    //binom
    private function cloak_by_custom_blocking(){


        if(isset($this->ipstack['connection']['isp']) and !empty($this->ipstack['connection']['isp'])) {
            $ISP = $this->ipstack['connection']['isp'];
        } else {
            $ISP = null;
        }

        if(isset($this->ipstack['connection']['asn']) and !empty($this->ipstack['connection']['asn'])) {
            $ASN = $this->ipstack['connection']['asn'];
        } else {
            $ASN = null;
        }


        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $OS = BrowsingComponent::getInstance()->getOS($userAgent);
        $Browser = BrowsingComponent::getInstance()->getBrowser($userAgent);

        $IP = $this->getIp();


        if(strpos($IP,':')!==false){
            $IPPPv4 = '';
            $IPPPv6 = $IP;
        } else {
            $IPPPv4 = $IP;
            $IPPPv6 = '';
        }

        $blockingClaass = new BlockingComponent([
            'isp' => $ISP,
            'asn' => $ASN,
            'ua' => $userAgent,
            'os' => $OS,
            'ipv6'  => $IPPPv6,
            'ip'  => $IPPPv4,
            'browser' => $Browser,
            'deviceModel' => $this->configs['deviceModel'],
            'deviceName' => $this->configs['deviceName'],
            'deviceBrand' => $this->configs['deviceBrand'],
            'resolution' => $this->configs['resolution']
        ]);

        $blocked = $blockingClaass->block(true);

        LoggingComponent::getInstance()->log('blocking.debug', $blockingClaass->debugArray);
        LoggingComponent::getInstance()->log('blocking.isblocked', var_export($blocked, 1));

        return (!$blocked);

    }
    

    private function skipped(){
        // skiped
        if(isset($this->tokensArray['skip_inject'])) {
            $skip_inject = intval($this->tokensArray['skip_inject']) > 0;
        } else {
            $skip_inject = false;
        }

        return $skip_inject;
    }


    private function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip);
            $ip = trim($ip[0]);
        }

        return $ip;
    }


    private function isJSON($string) {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    /**
     * @param $arr
     * @return mixed
     */
    private function checkUnitedKingdom($arr) {
        $searchUk = array_search('uk', $arr) !== false;
        $searchGB = array_search('gb', $arr) !== false;
        if ((!$searchUk && !$searchGB) || ($searchUk && $searchGB)) {
            return $arr;
        } else {
            if($searchUk)
                array_push($arr, 'gb');
            if($searchGB)
                array_push($arr, 'uk');
            return $arr;
        }
    }


}
