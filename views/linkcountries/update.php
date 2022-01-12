<?php

use yii\helpers\Html;
use webvimark\modules\UserManagement\models\User;
use app\models\Linkcountries;

/* @var $this yii\web\View */
/* @var $model app\models\LinkCountries */


$this->title = Yii::t('app', 'edit_linkcountry') . ' №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Связи', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $listApps[$model['app_id']], 'url' => ['/apps/view', 'id' => $model['app_id']]];
$this->params['breadcrumbs'][] = Yii::t('app', 'edit');
?>

<div class="page-header">
    <div class="row align-items-end">
        <div class="col-lg-8">
            <div class="page-header-title">
                <i class="ik ik-dollar-sign bg-blue"></i>
                <div class="d-inline">
                    <h5><?= Yii::t('app', 'edit_linkcountry'); ?></h5>
                    <span><?= Yii::t('app', 'edit_linkcountry_desc'); ?></span>
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
                'errorText' => $errorText,
                'links' => $links,
                'errorDeeplinks' => $errorDeeplinks,
                'allLabels' => $allLabels
            ]) ?>

        </div>
    </div>
</div>
