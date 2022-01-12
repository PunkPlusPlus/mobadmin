<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = Yii::t('app', 'Logs');
$this->params['breadcrumbs'][] = $this->title;
function getTextXX($text){
    $length = strlen($text);
    if($length > 12) {
        $short = substr($text, 0, 10);
        $long = substr($text, 11);
        return sprintf('<span class="txt-sort">%s<span class="txt-long">%s</span></span>',
            $short,$long);
    } else {
        return$text;
    }
}

?>

    <div class="container-fluid">

        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-inbox bg-blue"></i>
                        <div class="d-inline">
                            <h5><?=$this->title;?></h5>
                            <span><?=$this->title;?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <?=\yii\widgets\Breadcrumbs::widget([
                            'tag'=>'ol',
                            'activeItemTemplate' => '<li class="breadcrumb-item active">{link}</li>',
                            'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
                            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],

                        ]) ?>
                    </nav>
                </div>
            </div>
        </div>




<div class="log-index card p20">

    <?php

    /*<h1><?= Html::encode($this->title) ?></h1> */

    /*<p>
        <?=Html::a(Yii::t('app', 'Create Log'), ['create'], ['class' => 'btn btn-success'])?>
    </p>*/

    /*<?php echo $this->render('_search', ['model' => $searchModel]); ?>*/

    ?>

    <div>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            /*'layout'=>"{sorter}\n{pager}\n{summary}\n{items}",*/
            'tableOptions'=>['class'=>'table table-striped table-bordered table-hover scrolling'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                /*'id',*/
                /*'at_datetime:date',*/
                [
                    'label'=>Yii::t('app','Date'),
                    'attribute' => 'at_datetime',
                    'format' =>  'raw',
                    'content' => function($data){
                            return (new DateTime($data->at_datetime))->format("d.m.Y_H:i:s");
                    },
                ],
                [
                    'attribute' => 'ip',
                    'format' =>'html',
                    'content' => function($data){

                       if(strlen($data->ip)>15) {
                           $txt = substr($data->ip,0,15);
                           $class = 'btn btn-warning';
                       } else {
                           $txt = substr($data->ip,0,15);
                           $class = 'btn btn-info';
                       }
                        return Html::a($txt, sprintf('https://ipstack.com/?%s',  $data->ip),['class'=>$class, 'target'=>'_blank']);
                            
                    }
                ],
                /*'ip',*/
                [
                    'attribute'=>'language',
                    'filter'=>$languageArray
                ],
                /*'language',*/
                [
                    'attribute'=>'country',
                    'filter'=>$countryArray

                ],
                /*'country',*/
                [
                    'attribute'=>'os',
                    'filter'=>$osArray

                ],
                /*'os',*/
                [
                    'attribute'=>'browser',
                    'filter'=>$browserArray

                ],
                /*'browser',*/
                'external_uclick',
                'log_type',
                [
                    'attribute'=>'detailed',
                    'format'=>'html',
                    'content' => function($data){
                        $_arr = @json_decode($data->detailed,1);
                        $_res = [];
                        if($_arr) {
                            foreach( $_arr as $key=>$item) {
                                $_res[] = sprintf('<span class="badge badge-%s">%s</span>',
                                    (($item == 'true') ?'success':'danger')
                                    ,$key);

                            }
                        }
                    return  implode('<br>', $_res);
                }

                ],
                [
                    'attribute'=>'is_bot',
                    /*'label'=>'Создано',*/
                    'format'=>'html', // Доступные модификаторы - date:datetime:time
                    'content' => function($data){

                        if ($data->is_bot == 0) {
                            return Html::button(Html::tag('i','',['class'=>'ik ik-check green']),
                                ['class'=>'btn btn-success']);
                        } else {
                            return Html::button(Html::tag('i','',['class'=>'ik ik-x red']),
                                ['class'=>'btn btn-danger']);
                        }
                        //return
                    }

                ],
                [
                    'attribute'=>'meta_data',
                    /*'label'=>'Создано',*/
                    'format'=>'html', // Доступные модификаторы - date:datetime:time
                    'content' => function($data){

                        return Html::a(Html::tag('i','',['class'=>'ik ik-file-text']), ['view', 'id'=>$data->id], ['class'=>'btn btn-primary']);

                    }

                ],
/*'is_bot_passed',*/
                /*'meta_data:ntext',*/

                /*['class' => 'yii\grid\ActionColumn'],*/
            ],
        ]); ?>
    </div>

</div>


<?php
// overflow-x: scroll; width:100%; display:block;}
// .scrolling tbody{overflow-y:auto;width: 100%;height: 100%;display: block;}
$this->registerCss('.txt-sort{cursor: pointer; color: steelblue;}.txt-sort:hover{color: blue;}');
$this->registerCss('.scrolling{overflow-x:auto; width:100%; height:100%;display: block;position:relative;}');
$this->registerJs('$(function () {$(".txt-long").hide();$(".txt-sort").unbind("click").bind("click",function () {$(this).find(".txt-long").each(function (index,element) {if($(element).is(":hidden")) {$(element).show();} else {$(element).hide();}})});})');
?>
</div>

