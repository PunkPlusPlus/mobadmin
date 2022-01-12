<?php


namespace app\controllers;


use app\basic\debugHelper;
use app\models\notification\Sender;
use app\models\PartnerBalance;
use app\models\Prices;
use webvimark\modules\UserManagement\models\User;
use yii\web\NotFoundHttpException;

class BalanceController extends \yii\web\Controller
{
    public function actionView()
    {
        PartnerBalance::checkNewPartners();
	$partners = PartnerBalance::find()->with('user')->orderBy('is_active DESC, partner_id ASC')->all();

        return $this->render('view', compact('partners'));
    }

    public function actionChangeBalance()
    {
        $id = \Yii::$app->request->get('id');
        $count = \Yii::$app->request->get('count');
        $count = round($count, 2);
        $operator = \Yii::$app->request->get('operator');
        $data = [];

        $partner = PartnerBalance::findOne($id);
        if(empty($partner)) {
            return false;
        }
        $data['old_balance'] = $partner->balance;

        if($operator === '+') {
            $newBalance = round($partner->balance + $count, 2);
            $partner->balance = $newBalance;
            $data['alert_class'] = 'alert-plus';
            $data['is_banned'] = PartnerBalance::checkIsBanned($partner);
        } elseif($operator === '-') {
            $newBalance = round($partner->balance - $count, 2);
            $partner->balance = $newBalance;
            $data['is_banned'] = PartnerBalance::checkIsBanned($partner);
            $data['alert_class'] = 'alert-minus';
        } else {
            return false;
        }
        $partner->last_update = date("Y-m-d H:i:s");

        $data['balance'] = $partner->balance;
        $data['last_update'] = date('d.m.Y', strtotime($partner->last_update));
        $data = json_encode($data);
        $partner->save();

        return $data;
    }

    public function actionChangeLimit()
    {
        $id = \Yii::$app->request->get('id');
        $count = \Yii::$app->request->get('count');
        $count = -abs($count);

        $partner = PartnerBalance::findOne($id);
        if(empty($partner)) {
            return false;
        }

        $data = [];

        $partner->money_limit = $count;
        $data['is_banned'] = PartnerBalance::checkIsBanned($partner);
        $partner->last_update = date("Y-m-d H:i:s");

        $data['limit'] = $partner->money_limit;
        $data['last_update'] = date('d.m.Y', strtotime($partner->last_update));
        $data = json_encode($data);
        $partner->save();

        return $data;
    }

    public static function get($id)
    {
        $partner = PartnerBalance::findOne($id);
        return $partner->balance;
    }

    public static function changeBalance($pid, $appId, $country_code)
    {
        $partnerBalance = PartnerBalance::findOne($pid);
        if(empty($partnerBalance) || !$partnerBalance->is_active) {
            return 0;
        }

        // перенаправляем трафик, если баланс партнера в минусе, иначе отнимаем цену за инсталл
        if($partnerBalance->is_banned) {
            return 1;
        }

        // получаем цены приложения
        $prices = Prices::find()->where(['app_id' => $appId])->all();
        if(empty($prices)) return 0;

        $priceArray = [];
        foreach($prices as $price) {
            if($price->user_id == $pid && $price->country_code == $country_code && $country_code != 'all') {
                $priceArray[0] = $price;
            } elseif($price->user_id == $pid && $price->country_code == 'all') {
                $priceArray[1] = $price;
            } elseif($price->user_id == -1 && $price->country_code == $country_code && $country_code != 'all') {
                $priceArray[2] = $price;
            } elseif($price->user_id == -1 && $price->country_code == 'all') {
                $priceArray[3] = $price;
            }
        }

        if(!empty($priceArray)) {
            ksort($priceArray);
            $price = array_shift($priceArray);

            //изменяем баланс партнера
            $newPartnerBalance = $partnerBalance->balance - $price->price;

//            if($newPartnerBalance < 100 && $partnerBalance->balance >= 100) {
//                Sender::sendOnTelegram($pid, 'На вашем аккаунте баланс достиг $100 (Для пополнения обратитесь к @malins3)');
//            } elseif($newPartnerBalance < 0 && $partnerBalance->balance >= 0) {
//                Sender::sendOnTelegram($pid, 'На вашем аккаунте баланс достиг $0 (Для пополнения обратитесь к @malins3)');
//            } elseif($newPartnerBalance <= $partnerBalance->money_limit) {
//                Sender::sendOnTelegram($pid, 'На вашем аккаунте недостаточно средств, трафик заблокирован. (Для пополнения обратитесь к @malins3)');
//            }

            $partnerBalance->balance = $newPartnerBalance;
//            if($partnerBalance->balance <= $partnerBalance->money_limit) {
//                $partnerBalance->is_banned = 1;
//            }

            $partnerBalance->save();
        }

        return 0;
    }

    public function actionDeactivate($id)
    {
        $balance = PartnerBalance::findOne($id);
        if($balance->is_active) {
            $balance->is_active = 0;
            $balance->save();
        }
        return $this->redirect('/balance/view');
    }

    public function actionActivate($id)
    {
        $balance = PartnerBalance::findOne($id);
        if(!$balance->is_active) {
            $balance->is_active = 1;
            $balance->save();
        }
        return $this->redirect('/balance/view');
    }
}
