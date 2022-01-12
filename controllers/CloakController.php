<?php
namespace app\controllers;


use app\components\CloakingComponent;
use Yii;
use yii\web\Controller;


/**
 * Class AmyController
 * @package app\controllers
 */
class CloakController extends Controller
{

    public function actionIndex()
    {
     

		$data = 'is bot';
		
		/*['country','traffarmor','blocking']*/
		
		$data = print_r(CloakingComponent::getInstance()
	->cloak(
	['deviceModel'=>'1','deviceName'=>'2','deviceBrand'=>'2','resolution'=>'4'],
	[],
	['uk nl de ru']
	),1);
		
		
        return $this->renderPartial('index', [
            'data' => $data
        ]);
    }

}