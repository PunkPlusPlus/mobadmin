<?php


namespace app\components;


class WrapperIP
{

    private $vendor;
    private $scope;
    private $result;
    /**
     * WrapperIP constructor.
     */
    public function __construct($scope, $vendor='default')
    {
        $this->vendor = $vendor;
        $this->scope = $scope;

    }

    /**
     * @param false $return
     */
    public function parse($return = false){

        switch ($this->vendor){
            default:
                $method = 'IPRegistry';
                break;
        }


        if(method_exists($this, $method)) {
            $this->result = $this->$method();
        }


        if($return) {
            return $this->result;
        }
    }

    public function getResult(){
        return $this->result;
    }


    private function IPRegistry(){

        return [
            'ip' => $this->scope['ip'] ?? null,
            'type' => $this->scope['type'] ?? null,
            'continent_code' => $this->scope['location']['continent']['code'] ?? null,
            'continent_name' => $this->scope['location']['continent']['name'] ?? null,
            'country_code' => $this->scope['location']['country']['code'] ?? null,
            'country_name' => $this->scope['location']['country']['name'] ?? null,
            'region_code' => $this->scope['location']['region']['code'] ?? null,
            'region_name' => $this->scope['location']['region']['name'] ?? null,
            'city' => $this->scope['location']['city'] ?? null,
            'zip' =>  $this->scope['location']['postal'] ?? null,
            'latitude' => $this->scope['location']['latitude'] ?? null,
            'longitude' => $this->scope['location']['latitude'] ?? null,
            'location' =>
                [
                    'geoname_id' => 0 ?? null,
                    'capital' => $this->scope['location']['country']['capital'] ?? null,
                    'languages' =>
                        [
                            0 =>
                                [
                                    'code' => $this->scope['location']['language']['code'] ?? null,
                                    'name' => $this->scope['location']['language']['name'] ?? null,
                                    'native' => $this->scope['location']['language']['native'] ?? null,
                                ]

                        ],
                    'country_flag' =>  $this->scope['location']['country']['flag']['emojitwo'] ?? null,
                    'country_flag_emoji' =>  $this->scope['location']['country']['flag']['emoji'] ?? null,
                    'country_flag_emoji_unicode' =>  $this->scope['location']['country']['flag']['emoji_unicode'] ?? null,
                    'calling_code' => $this->scope['location']['country']['calling_code'] ?? null,
                    'is_eu' =>$this->scope['location']['in_eu'] ?? null,
                ],
            'time_zone' =>
                [
                    'id' => $this->scope['time_zone']['id'] ?? null,
                    'current_time' => $this->scope['time_zone']['current_time'] ?? null,
                    'gmt_offset' => $this->scope['time_zone']['offset'] ?? null,
                    'code' =>  $this->scope['time_zone']['abbreviation'] ?? null,
                    'is_daylight_saving' => $this->scope['time_zone']['in_daylight_saving'] ?? null,
                ],

            'currency' =>
                [
                    'code' => $this->scope['currency']['code'] ?? null,
                    'name' => $this->scope['currency']['name'] ?? null,
                    'plural' => $this->scope['currency']['plural'] ?? null,
                    'symbol' => $this->scope['currency']['symbol'] ?? null,
                    'symbol_native' => $this->scope['currency']['symbol_native'] ?? null,
                ],

            'connection' =>
                [
                    'asn' => $this->scope['connection']['asn'] ?? null,
                    'isp' => $this->scope['connection']['organization'] ?? null,
                ]
        ];
    }
}