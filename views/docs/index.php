<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\LogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
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
                <div class="page-header-title">
                    <i class="ik ik-book-open bg-blue"></i>
                    <div class="d-inline">
                        <h5><?=Yii::t('app', 'docs');?></h5>
                        <span><?=$title;?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">
                <?= $this->render($template); ?>
            </div>
        </div>
    </div>

