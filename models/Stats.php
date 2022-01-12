<?php


namespace app\models;


use app\basic\debugHelper;
use Yii;

class Stats
{
    public static function getData($user_id, $dateStartFormat, $dateEndFormat)
    {
        $connection = Yii::$app->getDb();
        $apps = $connection->createCommand("
            SELECT
                Count(*) AS installs,
                tbl_linkcountries.country_code,
                tbl_linkcountries.app_id,
                `user`.display_name,
                `user`.email,
                `user`.id AS user_id,
                tbl_apps.`name` AS app_name
            FROM
                tbl_devices
                INNER JOIN tbl_links ON tbl_devices.link_id = tbl_links.id
                INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id
                INNER JOIN `user` ON `user`.id = tbl_links.user_id
                INNER JOIN tbl_apps ON tbl_linkcountries.app_id = tbl_apps.id
            WHERE 
                tbl_devices.date_reg >= CAST(:date_start AS DATETIME)
                AND tbl_devices.date_reg < CAST(:date_end AS DATETIME)
                AND `user`.id LIKE ".$user_id."
                
            GROUP BY
                tbl_linkcountries.country_code,
                tbl_links.user_id,
                tbl_links.id
                
            ORDER BY 
                TRIM(`user`.display_name)
            ", [':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);

        $apps = $apps->queryAll();
        if(empty($apps)) return false;

        $statsData = [
            'total_installs' => 0,
            'total_profit' => 0
        ];
        $partnersData = [];

        foreach ($apps as $app) {

            $price = Prices::find()
                ->where(['app_id' => $app['app_id']])
                ->andWhere(['user_id' => $app['user_id']])
                ->andWhere(['country_code' => $app['country_code']])
                ->one();
            if(!$price){
                $price = Prices::find()
                    ->where(['app_id' => $app['app_id']])
                    ->andWhere(['user_id' => $app['user_id']])
                    ->andWhere(['country_code' => "all"])
                    ->one();
            }

            if(!isset($partnersData[$app['user_id']])) {
                $partnersData[$app['user_id']] = [
                    "id" => $app['user_id'],
                    "display_name" => $app['display_name'],
                    "email" => $app['email'],
                    "installs" => $app['installs'],
                    "profit" => $app['installs']*$price['price'],
                    "apps" => []
                ];

                $partnersData[$app['user_id']]['apps'][$app['app_id']] = [
                    'id' => $app['app_id'],
                    'name' => $app['app_name'],
                    'installs' => $app['installs'],
                    'profit' => $app['installs']*$price['price']
                ];
            }else{
                if(!isset($partnersData[$app['user_id']]['apps'][$app['app_id']])) {
                    $partnersData[$app['user_id']]['apps'][$app['app_id']] = [
                        'id' => $app['app_id'],
                        'name' => $app['app_name'],
                        'installs' => $app['installs'],
                        'profit' => $app['installs']*$price['price']
                    ];
                } else {
                    $partnersData[$app['user_id']]['apps'][$app['app_id']]['installs'] += $app['installs'];
                    $partnersData[$app['user_id']]['apps'][$app['app_id']]['profit'] += $app['installs']*$price['price'];
                }

                $partnersData[$app['user_id']]['installs'] += $app['installs'];
                $partnersData[$app['user_id']]['profit'] += $app['installs']*$price['price'];
            }

            $statsData['total_installs'] += $app['installs'];
            $statsData['total_profit'] += $app['installs']*$price['price'];

        }

        return [
            'partnersData' => $partnersData,
            'statsData' => $statsData
        ];
    }
}

//попробовать подцеплять цены в первом запросе
//"
//SELECT
//    Count(*) AS installs,
//    tbl_linkcountries.country_code,
//    tbl_linkcountries.app_id,
//    `user`.display_name,
//    `user`.email,
//    `user`.id AS user_id,
//    tbl_apps.`name` AS app_name,
//    tbl_prices.`price` AS app_price
//FROM
//    tbl_devices
//    INNER JOIN tbl_links ON tbl_devices.link_id = tbl_links.id
//    INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id
//    INNER JOIN `user` ON `user`.id = tbl_links.user_id
//    INNER JOIN tbl_apps ON tbl_linkcountries.app_id = tbl_apps.id
//    INNER JOIN tbl_prices ON tbl_linkcountries.app_id = tbl_prices.app_id
//        AND `user`.id = tbl_prices.user_id
//        AND `tbl_linkcountries`.country_code = tbl_prices.country_code
//WHERE
//    tbl_devices.date_reg >= CAST(:date_start AS DATETIME)
//    AND tbl_devices.date_reg < CAST(:date_end AS DATETIME)
//    AND `user`.id LIKE ".$user_id."
//GROUP BY
//    tbl_linkcountries.country_code,
//    tbl_links.user_id,
//    tbl_links.id,
//    tbl_prices.`price`
//"