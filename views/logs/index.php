<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\LogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Логи';
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
                    <i class="ik ik-dollar-sign bg-blue"></i>
                    <div class="d-inline">
                        <h5>Логи</h5>
                        <span>Общие логи</span>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

        <?php
            $array = [
                ['id' => '0', 'name' => 'Default log'],
            ];
        ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [

                'id',
                'user_ip',
                'date',
                'title',
                //'text:ntext',

                [
                    'attribute' => 'text',
                    'value' => function ($model) {
                        $text = "";
                        $maxLength = 999*10*10*10;
                        if(strlen($model->text) > $maxLength){
                            $text = "<div id='text_short_".$model->id."'>".substr($model->text, 0, $maxLength)."...</div>";
                            $text .= "<div onClick=\"$('#text_short_".$model->id."').text('$model->text');\" style='cursor:pointer; color:blue;'>Show full</div>";
                        }else{
                            $text = "<div id='text_short_".$model->id."'>".$model->text."</div>";
                        }

                        return $text;
                    },
                    'format' => 'html'
                ],
                [
                    'attribute' => 'type',
                    'value' => function ($model) {
                        switch ($model->type) {
                            case 0:
                                return "Default log";
                                break;
                        }
                    },
                    'format' => 'raw',
                    'filter' => ArrayHelper::map($array, 'id', 'name')
                ],

//                [
//                    'attribute' => 'Actions',
//                    'value' => function ($model) {
//                        return "<a class=\"btn btn-primary\" href=\"/logs/view?id=".$model->id."\">Детали</a>";
//                    },
//                    'format' => 'html'
//                ],


            ],
        ]); ?>


        </div>
    </div>
</div>

