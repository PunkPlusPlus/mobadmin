<?php


namespace app\components;


class CompareComponent
{

    const NOT_MATCH_VALUE_RESULT = false;
    const MATCH_VALUE_RESULT = true;
    public $result;
    public $method;
    public $filter;
    public $data;

    /**
     * CompareComponent constructor.
     * @param $method
     * @param $filter
     * @param $data
     */
    public function __construct($method, $filter, $data)
    {
        $this->method = $method;
        $this->filter = $filter;
        $this->data = $data;
    }

    /**
     * @param bool $return
     */
    public function compare($return = false){


        if(!empty($this->data)){

            //var_dump([$this->method,$this->filter,$this->data ]);die;
            switch ($this->method)
            {

                case 'equal':{
                    $this->result = $this->filter == $this->data;
                }
                    break;
                case 'more':{
                    $this->result = $this->filter < $this->data;
                }
                    break;
                case 'less':{
                    $this->result = $this->filter > $this->data;
                }
                    break;
                case 'in_value':{
                    $this->result = (strpos($this->data,$this->filter) !== false) || (strpos(strtolower($this->data),strtolower($this->filter)) !== false);
                }
                    break;
                case 'value_in':{
                    $this->result = (strpos($this->filter,$this->data) !== false) || (strpos(strtolower($this->filter),strtolower($this->data)) !== false);
                }
                    break;
                case 'range_int':{
                    if(strpos($this->filter, '-')!==false){
                        $_arr = explode('-', $this->filter);
                        $this->result = (false === array_search($this->data, range($_arr[0], $_arr[1])));
                    } else {
                        $this->result = self::NOT_MATCH_VALUE_RESULT;
                    }
                }
                    break;
                case 'range_ipv4':{

                    if(strpos($this->filter, '-')!==false){
                        $_arr = explode('-', $this->filter);
                        $this->result = (ip2long($this->data) >= ip2long($_arr[0]) && ip2long($this->data) <= ip2long($_arr[1]));
                    } else {
                        $this->result = self::NOT_MATCH_VALUE_RESULT;
                    }
                }
                    break;
                case 'range_ipv6':{
                    if(strpos($this->filter, '-')!==false){
                        $_arr = explode('-', $this->filter);
                        $this->result = (inet_pton($this->data) >= inet_pton($_arr[0]) && inet_pton($this->data) <= inet_pton($_arr[1]));
                    } else {
                        $this->result = self::NOT_MATCH_VALUE_RESULT;
                    }
                }
                    break;
                case 'range_abc':{
                    if(strpos($this->filter, '-')!==false){
                        $result = self::NOT_MATCH_VALUE_RESULT;
                        $_arr = explode('-', $this->filter);

                        $_A = substr($_arr[0],0,1);
                        $_B = substr($_arr[1],0,1);
                        if($_A < $_B) {
                            for ($i = $_A; $i <=$_B && strlen($i) < 2; $i++){
                                if( strtolower($i) == strtolower($this->data)){
                                    $result = self::MATCH_VALUE_RESULT;
                                }
                            }
                        }
                        $this->result = $result;
                    } else {
                        $this->result = self::NOT_MATCH_VALUE_RESULT;
                    }
                }
                    break;
                case 'regular':{
                    $this->result = (preg_match($this->filter,$this->data)===1);
                }
                    break;
                case 'list_in_value':{
                    $_arr =explode(',', $this->filter);
                    if(!empty($_arr) && is_array($_arr)) {
                        $result = self::NOT_MATCH_VALUE_RESULT;
                        foreach ($_arr as $i) {
                            if(strpos(strtolower($this->data),strtolower($i))!==false) {
                                $result = self::MATCH_VALUE_RESULT;
                            }
                        }
                        $this->result = $result;
                    } else {
                        $this->result = self::NOT_MATCH_VALUE_RESULT;
                    }
                }
                    break;
                case 'value_in_list':{
                    $_arr =explode(',', $this->filter);
                    if(!empty($_arr) && is_array($_arr)) {
                        $result = self::NOT_MATCH_VALUE_RESULT;
                        foreach ($_arr as $i) {
                            if(strpos(strtolower($i), strtolower($this->data))!==false) {
                                $result = self::MATCH_VALUE_RESULT;
                            }
                        }
                        $this->result = $result;
                    } else {
                        $this->result = self::NOT_MATCH_VALUE_RESULT;
                    }
                }
                    break;
            }
        } else {
            $this->result = self::NOT_MATCH_VALUE_RESULT;
        }


        if($return) return $this->result;
    }
}