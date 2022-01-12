<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use webvimark\modules\UserManagement\models\User;
use app\basic\debugHelper;
use yii\helpers\ArrayHelper;
use app\controllers\GpscraperController;
use app\models\Apps;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AppsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$appInfo = Apps::find()
    ->where(['id' => $_GET['id']])
    ->one();

$this->title = $appInfo->name;
$this->params['breadcrumbs'][] = $appInfo->name;
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
                        <h5><?= $appInfo->name; ?></h5>
                        <span>Debug информация</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

                <div class="row clearfix">
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <center><h4>Основные показатели</h4></center>
                        <table class="table table-striped table-bordered detail-view" id="main_test_table">
                            <thead><td style='font-weight: bold;'>Параметр</td><td style='font-weight: bold;'>Значение</td></thead>
                        </table>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12" style="margin-left:120px; min-width: 600px;">
                        <center><h4>Дополнительные показатели</h4></center>
                        <table class="table table-striped table-bordered detail-view" id="analyze_test_table">
                            <thead><td style='font-weight: bold;'>Параметр</td><td style='font-weight: bold;'>Значение</td></thead>
                        </table>
                    </div>
                </div>


                <script>
                    startSync("main", "main_test_table");
                    startSync("analyze", "analyze_test_table");

                    function startSync(action, table_id) {
                        var appId = <?=$_GET['id'];?>;
                        $.get('/apps/test?id=' + appId + '&action='+action, function (data) {
                            console.log('/apps/test?id=' + appId + '&action='+action);
                            var dataJson = JSON.parse(data);
                            var dataKeys = Object.keys(dataJson);
                            for (let i = 0; i < dataKeys.length; i++) {
                                var codeParam = dataKeys[i];
                                var nameParam = dataJson[codeParam].name;
                                var valueParam = dataJson[codeParam].value;
                                console.log(valueParam);
                                var valueParamLine = valueParam;
                                switch (codeParam) {
                                    case "prod_server":
                                        if (valueParam == "true")
                                            valueParamLine = "<label style='color:green;'>Да</label>";
                                        else
                                            valueParamLine = "<label style='color:red;''>Нет</label>";
                                        break;
                                    case "geturl_none":
                                        if (valueParam < 5)
                                            valueParamLine = "<label style='color:green;'>"+valueParam+"%</label>";
                                        else
                                            valueParamLine = "<label style='color:red;''>"+valueParam+"%</label>";
                                        break;
                                }
                                var newTR = "<tr><td>" + nameParam + "</td><td style='font-weight: bold;'>" + valueParamLine + "</td></tr>";
                                document.getElementById(table_id).innerHTML = document.getElementById(table_id).innerHTML + newTR;
                            }
                        });
                    }
                </script>

            </div>
        </div>
    </div>

</div>

