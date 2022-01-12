<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Visits */

$deviceName = '';
$deviceUrl = '';
/*
foreach($otherData['allDevices'] as $key => $value){
	if($key == $model['device_id']){
		$deviceName = $value;
		$deviceUrl = '<a href="/devices/view?id='.$key.'">'.$value.'</a>';
	}
}
*/
$deviceName = $model->devices->device_name;
$deviceUrl = '<a href="/devices/view?id='.$model['device_id'].'">'.$model->devices->device_name.'</a>';

$this->title = $deviceName;
$this->params['breadcrumbs'][] = ['label' => 'Запуски', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="visits-view">

	<div class="card">
			<div class="header">
				<h2>
					<?= Html::encode($model->devices->device_model) ?>
				</h2>
			</div>
			<div class="body">
				<?= DetailView::widget([
					'model' => $model,
					'attributes' => [
						//'id',
						//'device_id',
						[                                              
							'label' => 'Устройство',
							'value' => function($model) use ($otherData) {
								return '<a href="/devices/view?id='.$model['device_id'].'">'.$model->devices->device_model.'</a>';
							},
							'format' => 'html'
						],
						//'user_ip',
						[                                              
							'label' => 'IP',
							'value' => function($model) {return $model['user_ip'];}, 
						],
						//'country_code',
						[                                              
							'label' => 'Страна',
							'value' => function($model) {return '<img src="'.Yii::$app->runAction('media/getflag', ['country_code' => $model['country_code']]).'"> '.(Yii::$app->params['country'][strtolower($model['country_code'])] ?? $model['country_code']);}, 
							'format' => 'html'
						],
						//'isp',
						//'proxy',
						[                                              
							'label' => 'ISP',
							'value' => function($model) {return $model['isp'];}, 
						],
						[                                              
							'label' => 'Прокси',
							'value' => function($model) {return $model['proxy'] ? 'Да':'Нет';}, 
						],
						//'ip_data:ntext',
						[                                              
							'label' => 'IP инфо',
							'value' => function($model) {return $model['ip_data'];}, 
						],
						[                                              
							'label' => 'Параметры',
							'value' => function($model) {return $model['extra'];}, 
						],
						//'cloaking',
						[                                              
							'label' => 'Клоака',
							'value' => function($model) {
								return $model['cloaking'] ? 'Не прошел':'Прошел';
								
							}, 
							'format' => 'html'
						],
						//'binom_response:ntext',
						[                                              
							'label' => 'Ответ бинома',
							'value' => function($model) {return $model['binom_response'];}, 
						],
						//'server_response:ntext',
						[                                              
							'label' => 'Ответ отправленный устройству',
							'value' => function($model) {return $model['server_response'];}, 
						],
						'date',
					],
				]) ?>
			</div>
		</div>
</div>