<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\basic\debugHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Visits */

$deviceName = '';
$deviceUrl = '';
/*
foreach($otherData['allDevices'] as $key => $value){
	if($key == $model['device_id']){
		$deviceName = $value;
		$deviceUrl = '<a href="/devices/view?id='.$key.'">'.$value.'</a>';
	}
}
*/
$deviceName = $model->devices->name;
$deviceUrl = '<a href="/devices/view?id='.$model['device_id'].'">'.$model->devices->name.'</a>';

$this->title = $deviceName;
$this->params['breadcrumbs'][] = ['label' => 'Запуски', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<link href="/library/jsoneditor/jsoneditor.css" rel="stylesheet" type="text/css">
<script src="/library/jsoneditor/jsoneditor.js"></script>
<style>
    table.none-bg>tbody>tr{
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

<div class="page-header">
    <div class="row align-items-end">
        <div class="col-lg-8">
            <div class="page-header-title">
                <i class="ik ik-dollar-sign bg-blue"></i>
                <div class="d-inline">
                    <h5>Информация о запуске</h5>
                    <span>
                        <?php

                            if(isset($model->devices->model)){
                                print '<a href="/devices/view?id=' . $model->devices->id . '" style="color:blue;">' . $model->devices->model . '</a>';
                            }else{
                                try {
                                    $deviceName = explode(";", $model->filterlog->ua)[2];
                                    $deviceModel = explode("/", $deviceName)[0];
                                    $deviceModel = str_replace("Build", "", $deviceModel);
                                    print '<a href="/devices/view?id=' . $model->devices->id . '" style="color:blue;">' . $deviceModel . '</a>';
                                }catch (\Exception $e){
                                    print 'Not found';
                                }
                            }

                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card">

    <div class="card-body">
        <div class="dt-responsive"
             style="padding-left:20px; padding-right:20px; padding-bottom:20px;">
                <table class="table table-striped table-bordered detail-view">
                    <tr>
                        <td>Устройство</td>
                        <td>
                            <?php

                                if(isset($model->devices->model)){
                                    return '<a href="/devices/view?id=' . $model->devices->id . '" style="color:blue;">' . $model->devices->model . '</a>';
                                }else{
                                    try {
                                        $deviceName = explode(";", $model->filterlog->ua)[2];
                                        $deviceModel = explode("/", $deviceName)[0];
                                        $deviceModel = str_replace("Build", "", $deviceModel);
                                        print '<a href="/devices/view?id=' . $model->devices->id . '" style="color:blue;">' . $deviceModel . '</a>';
                                    }catch (\Exception $e){
                                        print 'Not found';
                                    }
                                }

                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>IP</td>
                        <td><?=$model->filterlog->ip;?></td>
                    </tr>
                    <tr>
                        <td>Страна</td>
                        <td><?= '<img src="'.Yii::$app->runAction('media/getflag', ['country_code' => strtolower($model->filterlog->country)]).'"> '.(Yii::$app->params['country'][strtolower($model->filterlog->country)] ?? $model->filterlog->country);?></td>
                    </tr>
                    <tr>
                        <td>Город</td>
                        <td><?=$model->filterlog->city;?></td>
                    </tr>
                    <tr>
                        <td>ISP</td>
                        <td><?=$model->filterlog->isp;?></td>
                    </tr>
                    <tr>
                        <td>ASN</td>
                        <td><?=$model->filterlog->asn;?></td>
                    </tr>
                    <tr>
                        <td>UserAgent</td>
                        <td><?=$model->filterlog->ua;?></td>
                    </tr
                    <tr>
                        <td>ОС</td>
                        <td><?=$model->filterlog->os;?></td>
                    </tr>
                    <tr>
                        <td>Браузер</td>
                        <td><?=$model->filterlog->browser;?></td>
                    </tr>
                    <tr>
                        <td>Язык</td>
                        <td><?=$model->filterlog->language;?></td>
                    </tr>
                    <tr>
                        <td>Сервер</td>
                        <td><?=$model->server_name ?? "Неизвестен";?></td>
                    </tr>
                    <tr>
                        <td>URL-адрес</td>
                        <td><?=$model->url ?? "Отсутствует";?></td>
                    </tr>
                    <tr>
                        <td>Campaign AF</td>
                        <td><?=$model->campaign_af ?? "Отсутствует";?></td>
                    </tr>
                    <tr>
                        <td>Диплинк</td>
                        <td><?=$model->deeplink ?? "Отсутствует";?></td>
                    </tr>
                    <tr>
                        <td><?=Yii::t('app', 'access_token');?></td>
                        <td><?=$model['access_token'] ?? "Использован";?></td>
                    </tr>
                    <tr>
                        <td>Клоака</td>
                        <td><?php

                            if($model->filterlog->is_bot) {
                                print "Не прошел";
                            }else {
                                print "Прошел";
                            }

                            ?></td>
                    </tr>
                    <tr>
                        <td>Информация о блокировках</td>
                        <td><?php

                            $infoBlock = json_decode($model->filterlog->detailed);
                            $html = "<b>Блокировка по TrafficArmor:</b> ".($infoBlock->trafficarmor_verified ? "<span class='pblock_green'>Пройдена</span>":"<span class='pblock_red'>Не пройдена</span>");
                            $html .= "<br><b>Блокировка по фильтрам:</b> ".($infoBlock->blocking_verified ? "<span class='pblock_green'>Пройдена</span>":"<span class='pblock_red'>Не пройдена</span>");
                            $html .= "<br><b>Блокировка по Стране:</b> ".($infoBlock->country_verified ? "<span class='pblock_green'>Пройдена</span>":"<span class='pblock_red'>Не пройдена</span>");
                            print $html;

                            ?></td>
                    </tr>
                    <tr>
                        <td>IDFA/AAID</td>
                        <td><?=$model->devices->idfa ?? "Отсутствует";?></td>
                    </tr>
                    <tr>
                        <td>Appsflyer Device ID</td>
                        <td><?=$model->devices->appsflyer_id ?? "Отсутствует";?></td>
                    </tr>
                    <tr>
                        <td>Переход по ссылке</td>
                        <td>
                            <?php
                            if($model->is_redirect == '1') {
                                print "Был";
                            }elseif($model->is_redirect == '0') {
                                print "Не было";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Actions</td>
                        <td><?php

                            $infoFilterLog = '<a class="btn btn-primary" href="/log/view?id='.$model->filterlog_id.'">Отчет по фильтрам полностью</a>';
                            print $infoFilterLog;

                            ?></td>
                    </tr>
                </table>
			</div>
		</div>
</div>


<style>
    .pblock{

    }
    .pblock_green{
        color: green;
        font-weight: bold;
    }

    .pblock_red{
        color: red;
        font-weight: bold;
    }
</style>


<?php
$this->registerCss('.jsonfy {display: grid;} pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }.string { color: green; }.number { color: darkorange; }.boolean { color: blue; }.null { color: magenta; }.key { color: red; }');
?>
<script>
    $(function () {
        $("div.jsonfy").each(function (index, element) {
            var pre = $(element).find("pre").html();
            var jjfy = JSON.stringify(JSON.parse(pre), undefined, 4);
            $(element).find("pre").html(syntaxHighlight(jjfy));
        });
    });

    function syntaxHighlight(json) {
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }
</script>