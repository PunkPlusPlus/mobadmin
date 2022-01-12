<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * PrelandsController implements the CRUD actions for Prelands model.
 */
class MediaController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            
        ];
    }

    public function actionGetflag($country_code){
		if($country_code == "all"){
			$url = "/assets/all.png";
		}else{
			//$url = "https://www.countryflags.io/".$country_code."/shiny/32.png";
			$url = "https://flagcdn.com/32x24/".$country_code.".webp";
		}
		return $url;
	}
}
