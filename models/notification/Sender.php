<?php


namespace app\models\notification;


use app\basic\debugHelper;
use app\models\Notifications;

class Sender
{
    private $app;
    private $status;

    public function __construct($app, $status)
    {
        $this->app = $app;
        $this->status = $status;
    }

    public function getSendText()
    {
        $text = [
            0 => "",
            2 => [
                'webpanel' => 'Приложение <b>' . $this->app->name . '</b> отправлено на проверку в Google Play (Примерное время публикации: ~3 дня)',
                'slack' => "Приложение *<https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id."|".$this->app->name.">* отправлено на проверку в Google Play (Примерное время публикации: ~7 дней) :eyes:",
            ],
            1 => [
                'webpanel' => 'Приложение <b>' . $this->app->name . '</b> опубликовано в Google Play',
                'slack' => "Приложение *<https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id."|".$this->app->name.">* опубликовано в Google Play :dancer:",
            ],
            -1 => [
                'webpanel' => 'Приложение <b>' . $this->app->name . '</b> удалено из Google Play (Бан)',
                'slack' => "Приложение *<https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id."|".$this->app->name.">* удалено из Google Play (Бан) :pig2:",
                'tg' => "Приложение [".$this->app->name."](https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id.") удалено из Google Play (Бан)"
            ],
            -2 => [
                'webpanel' => 'Приложение <b>' . $this->app->name . '</b> не опубликовалось за 30 дней. (Бан)',
                'slack' => "Приложение *<https://".$_SERVER["SERVER_NAME"]."/apps/view?id=".$this->app->id."|".$this->app->name.">* не опубликовалось за 30 дней. (Бан) :crycat:",
            ],
            3 => [
                'webpanel' => 'Приложение <b>' . $this->app->name . '</b> тестируется.',
                'slack' => "Приложение *<https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id."|".$this->app->name.">* тестируется.",
                'tg' => "Приложение [".$this->app->name."](https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id.") тестируется."
            ],
            4 => [
                'webpanel' => 'Приложение <b>' . $this->app->name . '</b> передано баерам.',
                'slack' => "Приложение *<https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id."|".$this->app->name.">* передано баерам.",
                'tg' => "Приложение [".$this->app->name."](https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id.") передано баерам."
            ],
            5 => [
                'webpanel' => 'Приложение <b>' . $this->app->name . '</b> на доработке.',
                'slack' => "Приложение *<https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id."|".$this->app->name.">* на доработке.",
                'tg' => "Приложение [".$this->app->name."](https://".$_SERVER['SERVER_NAME']."/apps/view?id=".$this->app->id.") на доработке."
            ],
        ];
        return $text[$this->status];
    }

    public function send()
    {
        $message = $this->getSendText();
        SlackBot::send($message['slack']);

        if($this->status == -1) {
            $notifications = $this->getNotificationsList();
            if(empty($notifications)) return false;

            $telegram_ids = $notifications['telegram'];
//            $emails = $notifications['email'];
//            $phones = $notifications['sms'];

            if(!empty($telegram_ids)) {
                foreach($telegram_ids as $tg_id) {
                    $tg_bot = new TelegramBot($tg_id);
                    $tg_bot->sendMessage($message['tg']);
                }
            }
        }
    }

    public function getNotificationsList()
    {
        $listUser = [];
        $maxAccess = count($this->app->linkcountries);
        $groupUserList = [];

        foreach ($this->app->linkcountries as $value) {
            foreach ($value->links as $link) {
                $userInfo = $link->user;
                if (!isset($groupUserList[$userInfo->id]) && $link->archived == 0 && strripos($userInfo->username, "ProfitNetwork") === false) {
                    $maxAccess--;
                    $listUser[] = $userInfo->id;
                    $groupUserList[$userInfo->id] = 1;
                }
            }
        }

        $notifications = Notifications::find()
            ->select(['source_id', 'source_key1'])
            ->where(['user_id' => $listUser])
            ->andWhere(['IN', 'app_id', [-1, $this->app->id]])
            ->orderBy('source_id')
            ->all();

        if(empty($notifications)) return [];

        $notifArray = [];
        foreach ($notifications as $notification) {
            if($notification->source_id == 2)
            {
                $notifArray['telegram'][$notification->source_key1] = $notification->source_key1;
            }
//            elseif($notification->source_id == 4)
//            {
//                $notifArray['email'][] = $notification->source_key1;
//            }
//            elseif($notification->source_id == 5)
//            {
//                $notifArray['sms'][] = $notification->source_key1;
//            }
        }
        return $notifArray;
    }

    public static function setFlash($mess)
    {
        if($mess === '-1') {
            \Yii::$app->session->setFlash('danger', 'Не работают прокси. Попробуйте еще раз');
        } else {
            \Yii::$app->session->setFlash('success', $mess);
        }
    }

    public static function sendOnTelegram($user_id, $text)
    {
        $notif = Notifications::find()->where([
            'user_id' => $user_id,
            'source_id' => 2,
            'is_main' => 1
        ])->one();
        if(empty($notif)) return false;

        $tg_bot = new TelegramBot($notif->source_key1);
        $tg_bot->sendMessage($text);
    }
}