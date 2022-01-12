<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\AppBalance */

$this->title = Yii::t('app', 'Create balance record');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'App Balances'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="page-header">
    <div class="row align-items-end">
        <div class="col-lg-8">
            <div class="page-header-title">
                <i class="ik ik-dollar-sign bg-blue"></i>
                <div class="d-inline">
                    <h5><?=Yii::t('app', 'Create balance record');?></h5>
                    <span><?=\app\models\Apps::findOne($model->app_id)->name;?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div style="float:right;">
                <?= Html::a('<- Вернуться к записям', ['/app-balance/view', 'id' => $model->app_id], ['class' => 'btn btn-primary']) ?>
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
                'partners_array' => $partners_array
            ]) ?>

        </div>
    </div>
</div>


