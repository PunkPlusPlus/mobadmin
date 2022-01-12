<?php


namespace app\components;


class TraffArmorComponent
{

    public $result;

    public function sendRequest($data,$payload = null){

        if (isset($data['magic'])) {
            $magic = json_decode($data['magic'], 1);
        } else {
            $magic = $_SERVER;
        }


        $headers = array();
        foreach ($magic as $name => $value) {
            if (preg_match('/^HTTP_/', $name)) {
                // convert HTTP_HEADER_NAME to header-name
                $name = strtr(substr($name, 5), '_', '-');
                $name = strtolower($name);
                $headers[$name] = $value;
            }
        }

            $payload = [
                'visitor'=>[
                    'remote_addr' => $data['ip'],
                    'v' => 4,
                    'xi' => 0,
                    'lp_url' => $data['server_referer'],
                    'referrer' => $magic['HTTP_REFERER'] ?? '',
                ],
                'browser_headers'=>$headers,
            ];



            $ch = curl_init("http://srvjs.com/imp/bpa752");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_USERAGENT, $data['ua']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt($ch, CURLOPT_ENCODING, ""); //Enables compression
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type: application/json"]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            //curl_setopt($ch, CURLOPT_HEADERFUNCTION, "forward_response_cookies"); //Forward response's cookies to visitor

            /*if (debug_mode_enabled()) {
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_STDERR, fopen(curl_log_filepath(), 'w+'));
            }*/

            /*
            if ($_COOKIE) {//Forward visitor's cookie to our server
                curl_setopt($ch, CURLOPT_COOKIE, encode_visitor_cookies());
            }
            */


            $response = curl_exec($ch);

            if($this->isJSON($response))
                LoggingComponent::getInstance()->log('trafficarmor_responce', json_decode($response,1));
            else
                LoggingComponent::getInstance()->log('trafficarmor_responce', $response);
            if($this->isJSON($response)) {
                $this->result = ['response'=>json_decode($response,1)];
            } else {
                $this->result = ['response'=>['cloak_reason'=>'Response error']];
                LoggingComponent::getInstance()->log('trafficarmor_responce.error', 'Response error');
            }

            curl_close($ch);

    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    private function isJSON($string) {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

}