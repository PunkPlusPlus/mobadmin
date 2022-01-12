<?php


namespace app\components;


use app\models\Log;

class LoggingComponent
{

    /**
     * @var bool
     */
    public $commit = false;
    public $id;

    /**
     * @var array
     */
    public $logDataFields = [
        'ip',
        'ipv6',
        'ua',
        'referer',
        'referer_prelanding',
        'manager_key',
        'language',
        'country',
        'city',
        'isp',
        'asn',
        'os',
        'browser',
        'external_uclick',
        'log_type',
        'detailed',
        'is_bot'
    ];

    /**
     * @var array
     */
    public $logDataArray;

    /**
     * @var LoggingComponent
     */
    private static $instance;

    protected function __construct() {}
    protected function __clone() {}
    protected function __wakeup() {}

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self() ;
        }

        return self::$instance;
    }

    /**
     * @return int
     */
    public function getId(){
        return $this->id;
    }
    /**
     * @return bool
     */
    public function isCommit()
    {
        return $this->commit;
    }


    public function log($key,$value) {
        if(isset($this->logDataArray[$key])) {
            $this->logDataArray[$key . '_copy_' . time()] = $this->logDataArray[$key];
        }
        $this->logDataArray[$key] = $value;
    }


    public function commit() {

        $this->logDataArray['at_datetime'] = date('Y-m-d H:i:s');
        // todo: JSON_ERROR_NONE
        $this->logDataArray['meta_data'] = json_encode($this->logDataArray, JSON_HEX_APOS | JSON_HEX_QUOT );

        $log = new Log();
        $log->at_datetime = $this->logDataArray['at_datetime'];
        $log->meta_data = $this->logDataArray['meta_data'];

        if( is_array($log->detailed) || !UtilisesComponent::getInstance()->isJSON($log->detailed)) {
            $log->detailed = json_encode($log->detailed,JSON_NUMERIC_CHECK);
        }

        foreach ($this->logDataFields as $fld)
        {
            $log->$fld = (isset($this->logDataArray[$fld]) ? $this->logDataArray[$fld]:'');
        }

        $this->commit = $log->save(false);
        $this->id = $log->id;

        /*if($log->validate()) {
            $this->commit = $log->save(false);
            echo '<pre>';
            print_r($log);
            print_r($log->getErrors());
        } else {
            $this->commit = false;

        }*/
    }

}
