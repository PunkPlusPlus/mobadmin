<?php


namespace app\models\notification;


class SlackBot
{
    public static function send($mess)
    {
        $slack_bot_urls = \Yii::$app->params['slack_bot'];

        foreach($slack_bot_urls as $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"text\":\"\n" . $mess . "\"}");
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_exec($ch);
            curl_close($ch);
        }

    }
}