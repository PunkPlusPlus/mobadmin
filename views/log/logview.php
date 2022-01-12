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
<link href="/library/jsoneditor/jsoneditor.css" rel="stylesheet" type="text/css">
<script src="/library/jsoneditor/jsoneditor.js"></script>
<style>
    table.none-bg>tbody{
        background-color: rgba(0,0,0,0) !important;
    }
    .none-bg tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,0) !important;
    }
    .none-bg tr:nth-of-type(even) {
        background-color: rgba(0,0,0,.05) ;
    }

    .none-bg td{
        border: none;
    }
</style>

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
                <script>
                    const options = {
                        mode: 'view'
                    }
                </script>
                <select id="show_lines">
                    <option value="5" <?php if(isset($_GET['count']) && $_GET['count'] == 5) print "selected"; ?>>Отображать 5 последних логов</option>
                    <option value="20" <?php if((isset($_GET['count']) && $_GET['count'] == 20) || !isset($_GET['count'])) print "selected"; ?>>Отображать 20 последних логов</option>
                    <option value="40" <?php if(isset($_GET['count']) && $_GET['count'] == 40) print "selected"; ?>>Отображать 40 последних логов</option>
                    <option value="80" <?php if(isset($_GET['count']) && $_GET['count'] == 80) print "selected"; ?>>Отображать 80 последних логов</option>
                    <option value="160" <?php if(isset($_GET['count']) && $_GET['count'] == 160) print "selected"; ?>>Отображать 160 последних логов</option>
                    <option value="320" <?php if(isset($_GET['count']) && $_GET['count'] == 320) print "selected"; ?>>Отображать 320 последних логов</option>
                </select>

                <select id="package_list">
                    <option value="false" <?php if(isset($_GET['package']) && $_GET['package'] == "false") print "selected"; ?>>Все пакеты</option>
                    <?php foreach($packageList as $key=>$value){ ?>
                        <option value="<?=$key;?>" <?php if(isset($_GET['package']) && $_GET['package'] == $key) print "selected"; ?>><?=$key;?></option>
                    <?php } ?>
                </select>

                <script>
                    // $( "#show_lines22" ).change(function () {
                    //         var str = "";
                    //         $( "select option:selected" ).each(function() {
                    //             alert($( this ).val());
                    //             //window.location
                    //         });
                    //     }).change();
                    jQuery('#show_lines').on('change',function(){
                        var package = parseHref(window.location.href)['package'];
                        if(package == undefined) package = false;
                        window.location.replace("/logview?count="+$( this ).val()+"&package="+package);
                    });
                    jQuery('#package_list').on('change',function(){
                        var count = parseHref(window.location.href)['count'];
                        if(count == undefined) count = 80;
                        window.location.replace("/logview?count="+count+"&package="+$( this ).val());
                    });

                    function parseHref(h) {
                        var iOf = h.indexOf('?');
                        var a = h.substring(iOf, h.length).substr(1).split('&');
                        if (a == "") return {};
                        var b = {};
                        for (var i = 0; i < a.length; ++i){
                            var p=a[ i ].split('=');
                            if (p.length != 2) continue;
                            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
                        }
                        return b;
                    }

                </script>
                <table class="table table-striped table-bordered detail-view none-bg" >
                    <thead>
                    <tr>
                        <td><b>Данные</b></td>
                        <td><b>Дата</b></td>
                        <td><b>Тип</b></td>
                        <td><b>Метод</b></td>
                    </tr>
                    </thead>
                    <?php $i=0; foreach($logLines as $log){ $i++; ?>
                        <tr>
                            <td><div id="request<?=$i;?>_editor"></div>

                                <script>
                                    const container<?=$i;?> = document.getElementById('request<?=$i;?>_editor');



                                    const json<?=$i;?> = <?=$log['content'];?>;

                                    const editor<?=$i;?> = new JSONEditor(container<?=$i;?>, options, json<?=$i;?>);
                                </script>

                            </td>
                            <td style="width:15%;"><?=$log['date'];?></td>
                            <td style="width:15%;"><?=$log['status'];?></td>
                            <td style="width:15%;"><?=$log['method'];?></td>
                        </tr>
                    <?php } ?>
                </table>

            </div>
        </div>
    </div>

