<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SettingsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="preloader_page" style="display:none;">
    <center>
        <div class="parent" style="width:50px; height:25px; display:inline-block;">
            <div id="xLoader">
                <div class="audio-wave"><span></span><span></span><span></span><span></span><span></span></div>
            </div>
        </div>

    </center>
</div>

<div id="content_page">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-dollar-sign bg-blue"></i>
                    <div class="d-inline">
                        <h5><?= Yii::t('app', 'settings'); ?></h5>
                        <span><?= Yii::t('app', 'settings_desc'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

                <style>
                    .logo_app {
                        width: 45px;
                        height: 45px;
                        display: inline-block;
                        background-size: contain;
                        background-repeat: no-repeat;
                    }
                </style>
    <p>
        <?= Html::a('Create Settings', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear Cache', ['clear-cache'], ['class' => 'btn btn-primary']) ?>

    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'key',
            'value',

            [
                'attribute' => Yii::t('app', 'actions'),
                'value' => function ($model) {
                    return Html::a(Yii::t('app', 'view'), ['/settings/view?id=' . $model->id], ['class' => 'btn btn-info']);
                },
                'format' => 'html'
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

        </div>
    </div>
</div>

</div>
