<?php


namespace app\components;
use app\models\Visits;
use app\models\Blacklist;
use app\models\Devices;
use yii\base\BaseObject;

class BlackListComponent
{

    public static function removeFromList($idfa)
    {
        if ($idfa == null) {
            return false;
        }
        $model = Blacklist::find()->where(['idfa' => $idfa])->one();
        if ($model != null) {
            $model->delete();
            return true;
        } else {
            return false;
        }

    }



    public static function updateIps()
    {
        $banned = Blacklist::getBannedVisits();
        $ips = array();
        foreach ($banned as $item) {
            try {
                $device = Devices::find()->where(['idfa' => $item->idfa])->one();
                $visit = Visits::find()->where(['device_id' => $device->id])->one();
                $item->ip = $visit->filterlog->ip;
                $item->save();
            } catch (\Exception $e) {
            }
        }
    }

    public static function getStatus($idfa)
    {
        $list = Blacklist::find()->where(['idfa' => $idfa])->one() ?? null;
        if ($list != null) {
            $status = $list->block;
        } else {
            $status = -1;
        }
        return $status;
    }

    public static function getCount($list)
    {
        $res = Blacklist::find()->where(['block' => $list])->count();
        return $res;
    }

    public static function addToList($idfa, $block)
    {
        if ($idfa == null) {
            return false;
        }
        // есть ли это устройство уже в нужном списке
        $model = Blacklist::find()->where(['idfa' => $idfa])->andWhere(['block' => $block])->one();
        if ($model == null) {
            // нет ли устройства в противоположном списке
            $model = Blacklist::find()->where(['idfa' => $idfa])->andWhere(['block' => !$block])->one();
            if ($model == null) {
                $model = new Blacklist();
                $model->idfa = $idfa;
                $model->block = $block;
                $model->save();
                return $model;
            } else {
                $model->block = $block;
                $model->save();
                return $model;
            }
        } else {
            return false;
        }
    }

    public static function checkDuplicates()
    {
         $res_arr = array();
         $ips = self::getIps();
         for ($i = 0; $i < count($ips); $i++) {
             for ($j = 0; $j < count($ips[$i]); $j++) {
                 for ($k = $i; $k < count($ips); $k++) {
                     $index = array_search($ips[$i][$j], $ips[$k], true);
                     if ($ips[$i] !== $ips[$k]) {
                         $res = array(
                           'array_index' => implode(".", $ips[$k]),
                           'checked_array' => implode(".", $ips[$i]),
                           'needle_index' => $j,
                           'coincidence_index' => $index
                         );
                         array_push($res_arr, $res);
                     }
                 }
             }
         }
         return $res_arr;
     }

     public static function checkDuplicatesV2()
    {
        $resArr = array(
            'first' => array(),
            'second' => array(),
            'third' => array()
        );
        $ips = self::getIps();
        $ips = BlackListComponent::deletev6($ips);
        for ($i = 0; $i < count($ips) - 1; $i++) {
            $count = 0;
            $count2 = 0;
            for ($k = 0; $k < count($ips)-1; $k++) {
                if ($ips[$i][1] === $ips[$k+1][1]) {
                    if ($count >= 1) {
                        array_push($resArr['first'], $ips[$i]);
                        array_push($resArr['first'], $ips[$k+1]);
                    }
                    $count++;
                }
                if ($ips[$i][2] === $ips[$k+1][2]) {
                    if ($count2 >= 1) {
                        array_push($resArr['second'], $ips[$i]);
                        array_push($resArr['second'], $ips[$k+1]);
                    }
                    $count2++;
                }
            }
        }
        return $resArr;
    }
 
     public static function getIps()
     {
         $banned_visits = Blacklist::getBannedVisits();
         $ips = array();
         foreach ($banned_visits as $visit) {
             if (isset($visit->ip)) {
                 array_push($ips, $visit->ip);
             }
         }
 
         $new_ips = array();
         foreach ($ips as $ip) {
             array_push($new_ips, explode('.', $ip));
         }
         return $new_ips;
     }

    public static function formatOutput($array)
    {
	$newArr = array(
	    'first' => array(),
	    'second' => array(),	    
	);
	foreach ($array['first'] as $item ) {
	    array_push($newArr['first'], implode(".", $item));
	}
	foreach ($array['second'] as $item) {
	    array_push($newArr['second'], implode(".", $item));
	}
	$newArr['first'] = array_unique($newArr['first']);
	$newArr['second'] = array_unique($newArr['second']);
	$second = array_diff($newArr['second'], $newArr['first']);
	$newArr['second'] = $second;
	return $newArr;
    }
     
    public static function deletev6(&$ips)
    {
        $newIps = array();
        for ($i = 0; $i < count($ips); $i++) {
            if (count($ips[$i]) == 4) {
                array_push($newIps, $ips[$i]);
            }
        }
        return $newIps;
    }
}
