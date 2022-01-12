<?php

namespace app\models\notification;

use app\basic\debugHelper;

class TelegramBot
{
    const TOKEN = '1128445989:AAGHsQ1qw7FzE1tdLQjJMgJ9w8UxtB5Bbwo'; // 1 - 1128445989:AAGHsQ1qw7FzE1tdLQjJMgJ9w8UxtB5Bbwo     2 - 1272686954:AAGgzmtcFm2G-30mSC7iIHMs10HoH97koPQ
    private $chat_id;

    public function __construct($chat_id)
    {
        $this->chat_id = $chat_id;
    }

    public function sendMessage($message)
    {
        $sendData = [
            'text' => $message,
            'chat_id' => $this->chat_id,
            'parse_mode' => 'Markdown'
        ];
        self::request('sendMessage', $sendData);
    }

    public static function request($method, $data = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . self::TOKEN .  '/' . $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $out = json_decode(curl_exec($curl), true);

        curl_close($curl);

        return $out;
    }
}

// url для изменения webhook
// https://api.telegram.org/bot1128445989:AAGHsQ1qw7FzE1tdLQjJMgJ9w8UxtB5Bbwo/setWebhook?url=https://webpanel.profitnetwork.app/telegram&drop_pending_updates=true

// getWebhookInfo
// https://api.telegram.org/bot1128445989:AAGHsQ1qw7FzE1tdLQjJMgJ9w8UxtB5Bbwo/getWebhookInfo