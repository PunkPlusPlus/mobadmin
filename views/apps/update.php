<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Apps */
/* @var $prices app\models\Prices */

$this->title = Yii::t('app', 'edit_app').': ' . $model->name;
//$this->params['breadcrumbs'][] = ['label' => 'Приложения', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = 'Редактировать';
?>


<div class="page-header">
    <div class="row align-items-end">
        <div class="col-lg-8">
            <div class="page-header-title">
                <i class="ik ik-dollar-sign bg-blue"></i>
                <div class="d-inline">
                    <h5><?=$model->name;?></h5>
                    <span><?=Yii::t('app', 'edit_app');?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">

    <div class="card-body">
        <div class="dt-responsive"
             style="padding-left:20px; padding-right:20px; padding-bottom:20px;">
            <a class="btn btn-primary" href="/apps/view?id=<?=$model['id'];?>" style="color:#ffffff;"><- <?=Yii::t('app', 'back_app');?></a>
            <hr>
            <?= $this->render('_form', [
                'model' => $model,
                'prices' => $prices,
                'params' => $params
            ]); ?>

        </div>
    </div>
</div>