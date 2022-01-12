<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Visits;
use app\basic\debugHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Devices */


$this->title = $model->model;
$userAgent = [
    "name" => "Not found",
    "model" => "Not found",
];
$oneVisits = Visits::find()
    ->where(['=', 'device_id', $model->id])
    ->one();
try {
    $userAgent['name'] = explode(";", $oneVisits->filterlog->ua)[2];
    $userAgent['model'] = explode("/", $userAgent['name'])[0];
    $userAgent['model'] = str_replace("Build", "", $userAgent['model']);
    $this->title = $userAgent['name'];
} catch (\Exception $e) {

}


$this->params['breadcrumbs'][] = ['label' => 'Устройства', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model['model'];
\yii\web\YiiAsset::register($this);
?>

<div id="preloader_page" style="display:none;">
    <center>
        <div class="parent" style="width:50px; height:25px; display:inline-block;"> <div id="xLoader"><div class="audio-wave"><span></span><span></span><span></span><span></span><span></span></div></div></div>

    </center>
</div>

<div id="content_page">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title" style="display:flex;">
                    <i class="ik ik-clipboard bg-blue"></i>
                    <div class="d-inline">
                        <h5><?= Html::encode($this->title) ?></h5>
                        <span>Информация об устройстве</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    //'id',
                    //'uid',
                    //'device_model',
                    //'device_name',
                    //'device_vendor',
                    //'resolution',
                    //'language',
                    //'package',
                    //'extra:ntext',
                    //'date_reg',
                    [
                        'label' => 'ID',
                        'value' => function($model) {return $model['id'];},
                    ],

                    [
                        'label' => 'UID',
                        'value' => function($model) {return $model['uid'];},
                    ],

                    [
                        'label' => 'Модель',
                        'value' => function($model) use ($userAgent) {
                            if(isset($model['model'])) {
                                return $model['model'];
                            }else{
                                return $userAgent['model'];
                            }
                        },
                    ],
                    [
                        'label' => 'Имя',
                        'value' => function($model) use ($userAgent) {
                            if(isset($model['name'])) {
                                return $model['name'];
                            }else{
                                return $userAgent['name'];
                            }
                        },
                    ],
                    [
                        'label' => 'Бренд',
                        'value' => function($model) {return $model['brand'];},
                    ],
                    [
                        'label' => 'Разрешение',
                        'value' => function($model) {return $model['resolution'];},
                    ],
                    [
                        'label' => 'Язык устройства',
                        'value' => function($model) {return $model['language'];},
                    ],
                    [
                        'label' => 'Дата',
                        'value' => function($model) {return $model['date_reg'];},
                    ],
                ],
            ]) ?>
            </div>
        </div>
    </div>



    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">
                <div style="text-align: center; font-size: 20pt; padding-bottom: 20px;">Запуски устройства</div>
				<table class="table table-striped table-bordered detail-view">
					<thead>
						<tr>
							<td>
								<b>IP</b>
							</td>
							<td>
								<b>Страна</b>
							</td>
							<td>
								<b>Клоака</b>
							</td>
							<td>
								<b>Дата</b>
							</td>
							<td>
								<b>Действия</b>
							</td>
						</tr>
					</thead>
					<?php foreach($otherData['allVisits'] as $value){ ?>
					<tr>
						<td>
							<?php
                            if(isset($value->filterlog->ip)){
                                print $value->filterlog->ip;
                            }else if(isset($value->filterlog->ipv6)){
                                print $value->filterlog->ipv6;
                            }
                            ?>
						</td>
						<td>
                            <?php if(isset($value->filterlog->country)){ ?>
							    <img src="<?=Yii::$app->runAction('media/getflag', ['country_code' => $value->filterlog->country]);?>">  <?=Yii::$app->params['country'][strtolower($value->filterlog->country)] ?? $value->filterlog->country;?>
						    <?php } ?>
                        </td>
						<td>
							<?=$value['cloaking'] ? 'Не прошел':'Прошел';?>
						</td>
						<td>
							<?=$value['date'];?>
						</td>
						<td>
                            <a class="btn btn-primary" href="/visits/view?id=<?=$value['id'];?>">Детали</a>
						</td>
					</tr>
					<?php } ?>
				</table>
            </div>
        </div>
    </div>
