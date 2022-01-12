<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\LinkCountries */
	
$this->title = Yii::t('app', 'add_country_desc');
//$this->params['breadcrumbs'][] = ['label' => 'Связи', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $listApps[$_GET['appid']], 'url' => ['/apps/view', 'id' => $_GET['appid']]];
//$this->params['breadcrumbs'][] = $this->title;
?>



<div class="page-header">
    <div class="row align-items-end">
        <div class="col-lg-8">
            <div class="page-header-title">
                <i class="ik ik-dollar-sign bg-blue"></i>
                <div class="d-inline">
                    <h5><?=Yii::t('app', 'add_country');?></h5>
                    <span><?=Yii::t('app', 'add_country_desc');?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">

    <div class="card-body">
        <div class="dt-responsive"
             style="padding-left:20px; padding-right:20px; padding-bottom:20px;">

            <?= $this->render('_form', [
                'model' => $model,
                'appInfo' => $appInfo,
                'errorText' => $errorText
            ]) ?>

        </div>
    </div>
</div>



