<?php

//use yii\helpers\Html;
use yii\grid\GridView;

//use yii\widgets\Pjax;
use webvimark\modules\UserManagement\models\User;
use app\basic\genKeyHelper;
use app\basic\debugHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VisitsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Запуски';
$this->params['breadcrumbs'][] = $this->title;

$logger = new \app\controllers\LogsController();
$logger->data['message'] = json_encode($stats);
$logger->infoSend('TestVisits');
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
                        <h5><?= Yii::t('app', 'visits_stats'); ?></h5>
                        <span><?= Yii::t('app', 'visits_stats_desc'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php if (isset($_GET['app_id'])) { ?>
        <div class="row clearfix">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="widget bg-primary">
                    <div class="widget-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="state">
                                <h6><?= Yii::t('app', 'visits'); ?></h6>
                                <h2><?= $stats['visits']; ?></h2>
                            </div>
                            <div class="icon">
                                <i class="ik ik-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="widget bg-success">
                    <div class="widget-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="state">
                                <h6><?= Yii::t('app', 'installs'); ?></h6>
                                <h2><?= $stats['devices']; ?></h2>
                            </div>
                            <div class="icon">
                                <i class="ik ik-user-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--        <div class="col-lg-3 col-md-6 col-sm-12">-->
            <!--            <div class="widget bg-warning">-->
            <!--                <div class="widget-body">-->
            <!--                    <div class="d-flex justify-content-between align-items-center">-->
            <!--                        <div class="state">-->
            <!--                            <h6>--><? //=Yii::t('app', 'bots');?><!--</h6>-->
            <!--                            <h2>--><? //=$stats['bots_devices'];?><!--</h2>-->
            <!--                        </div>-->
            <!--                        <div class="icon">-->
            <!--                            <i class="ik ik-user-x"></i>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->

            <?php if(User::hasRole('partner') || User::hasRole('manager') || User::hasRole('Admin')) { ?>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="widget bg-danger">
                        <div class="widget-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="state">
                                    <h6><?= Yii::t('app', 'total_price'); ?></h6>
                                    <h2><?= $stats['price']; ?></h2>
                                </div>
                                <div class="icon">
                                    <i class="ik ik-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if(User::hasRole('partner') || User::hasRole('manager') || User::hasRole('Admin')) { ?>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="widget bg-warning">
                        <div class="widget-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="state">
                                    <h6><?= Yii::t('app', 'total_redirect'); ?></h6>
                                    <h2><?= $stats['visits_redirect']; ?></h2>
                                </div>
                                <div class="icon">
                                    <i class="ik ik-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>


    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

                <!--                --><?php //if(User::hasPermission('view_all_apps')) { ?>
                <!--                    <a class="btn btn-primary" href="/visits/index?app_id=-1&sort=-id" style="margin-bottom: 25px;">Логи по всем приложениям</a>-->
                <!--                --><?php //} ?>
                <div style="display:flex;">
                    <div class="col-sm-6 col-xl-2 mb-30">
                        <h4 class="sub-title"><?= Yii::t('app', 'date_start'); ?></h4>
                        <input type="text" class="form-control" id="datepicker_start">
                    </div>

                    <div class="col-sm-6 col-xl-2 mb-30">
                        <h4 class="sub-title"><?= Yii::t('app', 'date_end'); ?></h4>
                        <input type="text" class="form-control" id="datepicker_end">
                    </div>

                </div>

                <h4 class="sub-title"><?= Yii::t('app', 'filter_linkcountry'); ?> <button onClick="checkboxFilterLinks();" class="btn btn-link" id="btn_filterlinks"><?=Yii::t('app', 'deselect_all');?></button></h4>
                <div id="filter_link_lists">
                    <?php $cacheLinkAll = ""; $checkedLinkAll = false; foreach ($allLinkCountries as $link) { ?>
                        <?php
                        $checked = "";
                        if($selectCountry) {
                            foreach ($selectCountry as $linkId) {
                                if ($link->id == $linkId) {
                                    $checked = "checked";
                                    break;
                                }
                            }
                        }else{
                            $checked = "checked";
                        }
                        $countryName = $link->country_code;

                        if($link->country_code == "all"){
                            $checkedLinkAll = $checked;
                            $cacheLinkAll = $link;
                            continue;
                        }
                        ?>
                        <div class="checkbox-fade fade-in-primary" style="width:95px;">
                            <label>
                                <input type="checkbox" id="<?= $link->id; ?>" <?=$checked;?>>
                                <span class="cr">
                                <i class="cr-icon ik ik-check txt-primary"></i>
                            </span>
                                <span><img height="25px" style="margin-right:5px;"
                                           src="<?= Yii::$app->runAction('media/getflag', ['country_code' => $link->country_code]); ?>"><?= strtoupper($countryName); ?></span>
                            </label>
                        </div>
                    <?php } ?>

                    <?php if(strlen($checkedLinkAll) > 0){ ?>
                        <div class="checkbox-fade fade-in-primary" style="width:120px;">
                            <label>
                                <input type="checkbox" id="<?= $cacheLinkAll->id; ?>" <?=$checkedLinkAll;?>>
                                <span class="cr">
                                <i class="cr-icon ik ik-check txt-primary"></i>
                            </span>
                                <span><img height="25px" style="margin-right:5px;"
                                           src="<?= Yii::$app->runAction('media/getflag', ['country_code' => $cacheLinkAll->country_code]); ?>"><?=Yii::t('app', 'other');?></span>
                            </label>
                        </div>
                    <?php } ?>
                </div>


                <!--фильтр по меткам-->
                <br><br>
                <h4 class="sub-title"><?= Yii::t('app', 'filter_label'); ?> <button onClick="checkboxFilterlabels();" class="btn btn-link" id="btn_filterlabels"><?=Yii::t('app', 'deselect_all');?></button></h4>
                <div id="filter_label_lists">
                    <?php $cacheLabelAll = ""; $checkedLabelAll = false; foreach ($allLabels as $label) { ?>
                        <?php
                        $checked = "";
                        if($selectLabels) {
                            foreach ($selectLabels as $labelName) {
                                if ($label['name'] == $labelName) {
                                    $checked = "checked";
                                    break;
                                }
                            }
                        }else{
                            $checked = "checked";
                        }
                        ?>
                        <div class="checkbox-fade fade-in-primary">
                            <label>
                                <input type="checkbox" id="<?= $label['name']; ?>" <?=$checked;?>>
                                <span class="cr">
                                    <i class="cr-icon ik ik-check txt-primary"></i>
                                </span>
                                <span>
                                    <?=$label['name'];?>
                                </span>
                            </label>
                        </div>
                    <?php } ?>

                    <div class="checkbox-fade fade-in-primary" style="width:120px;">
                        <label>
                            <input type="checkbox" id="otherlbl" <?php
                            if((isset($_GET['labels']) && (strpos($_GET['labels'], 'otherlbl') !== false || strlen($_GET['labels']) <= 0)) || !isset($_GET['labels']))
                                print "checked";
                            ?>>
                            <span class="cr"><i class="cr-icon ik ik-check txt-primary"></i></span>
                            <span><?=Yii::t('app', 'other');?>
                            </span>
                        </label>
                    </div>
                </div>
                <!--                end фильтр по меткам-->


                <!--фильтр по пользователям-->
                <?php if (User::hasPermission('view_all_statistics')) { ?>
                    <br><br>
                    <h4 class="sub-title"><?= Yii::t('app', 'filter_user'); ?> <button onClick="checkboxFilterUsers();" class="btn btn-link" id="btn_filterusers"><?=Yii::t('app', 'deselect_all');?></button></h4>
                    <div id="filter_user_lists">
                        <?php $cacheUserAll = ""; $checkedUserAll = false; foreach ($listUsers as $userId=>$userName) { ?>
                            <?php
                            $checked = "";
                            if($selectUsers) {
                                foreach ($selectUsers as $selectUserId) {
                                    if ($userId == $selectUserId) {
                                        $checked = "checked";
                                        break;
                                    }
                                }
                            }else{
                                $checked = "checked";
                            }
                            ?>
                            <div class="checkbox-fade fade-in-primary">
                                <label>
                                    <input type="checkbox" id="<?= $userId ?>" <?=$checked;?>>
                                    <span class="cr">
                                        <i class="cr-icon ik ik-check txt-primary"></i>
                                    </span>
                                    <span>
                                        <?=$userName;?>
                                    </span>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <!-- end фильтр по пользователям-->



                <h4 class="sub-title">&nbsp;</h4>
                <button type="button" class="btn btn-info btn-block" onClick="applyFilter()">Применить фильтр</button>
                <h4 class="sub-title">&nbsp;</h4>
                <script>
                    var params = window
                        .location
                        .search
                        .replace('?', '')
                        .split('&')
                        .reduce(
                            function (p, e) {
                                var a = e.split('=');
                                p[decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                                return p;
                            },
                            {}
                        );

                    $(document).ready(function () {
                        moment.updateLocale('en', {
                            week: {dow: 1} // Monday is the first day of the week
                        });

                        $('#datepicker_start').datetimepicker({
                            format: 'DD/MM/YYYY',
                            locale: 'ru',
                            //calendarWeeks:true,
                        });

                        $('#datepicker_end').datetimepicker({
                            format: 'DD/MM/YYYY',
                            locale: 'ru',
                            //calendarWeeks:true,
                        });

                        var dateStart = params['from'];
                        var dateEnd = params['to'];

                        if (dateStart == undefined) dateStart = '<?=$date_from;?>';
                        if (dateEnd == undefined) dateEnd = '<?=$date_to;?>';

                        $('#datepicker_start').val(dateStart);
                        $('#datepicker_end').val(dateEnd);
                    });

                </script>

                <script>

                    var filterChecked = true;
                    function checkboxFilterLinks(){
                        filterChecked = !filterChecked;
                        if(filterChecked) {
                            document.getElementById("btn_filterlinks").innerText = '<?=Yii::t('app', 'deselect_all');?>';
                        }else{
                            document.getElementById("btn_filterlinks").innerText = '<?=Yii::t('app', 'select_all');?>';
                        }
                        let divBlock = document.getElementById("filter_link_lists").getElementsByTagName("input");

                        for (let i = 0; i < divBlock.length; i++) {
                            divBlock[i].checked = filterChecked;
                        }
                    }


                    var filterLabelChecked = true;
                    function checkboxFilterlabels(){
                        filterLabelChecked = !filterLabelChecked;
                        if(filterLabelChecked) {
                            document.getElementById("btn_filterlabels").innerText = '<?=Yii::t('app', 'deselect_all');?>';
                        }else{
                            document.getElementById("btn_filterlabels").innerText = '<?=Yii::t('app', 'select_all');?>';
                        }
                        let divBlock = document.getElementById("filter_label_lists").getElementsByTagName("input");

                        for (let i = 0; i < divBlock.length; i++) {
                            divBlock[i].checked = filterLabelChecked;
                        }
                    }

                    var filterUserChecked = true;
                    function checkboxFilterUsers(){
                        filterUserChecked = !filterUserChecked;
                        if(filterUserChecked) {
                            document.getElementById("btn_filterusers").innerText = '<?=Yii::t('app', 'deselect_all');?>';
                        }else{
                            document.getElementById("btn_filterusers").innerText = '<?=Yii::t('app', 'select_all');?>';
                        }
                        let divBlock = document.getElementById("filter_user_lists").getElementsByTagName("input");

                        for (let i = 0; i < divBlock.length; i++) {
                            divBlock[i].checked = filterUserChecked;
                        }
                    }

                    function applyFilter() {

                        let dateStart = $('#datepicker_start').val();
                        let dateEnd = $('#datepicker_end').val();
                        let appId = params['app_id'];
                        let sort = params['sort'];
                        let linkId = params['VisitsSearch[linkcountry_id]'];

                        if (appId == undefined) appId = -1;
                        if (sort == undefined) sort = "-id";
                        let divBlock = document.getElementById("filter_link_lists").getElementsByTagName("input");
                        let listLinks = "";
                        for (let i = 0; i < divBlock.length; i++) {
                            if (divBlock[i].checked) {
                                if (listLinks.length <= 0) {
                                    listLinks = divBlock[i].getAttribute("id");
                                } else {
                                    listLinks += "," + divBlock[i].getAttribute("id");
                                }
                            }
                        }

                        divBlock = document.getElementById("filter_label_lists").getElementsByTagName("input");
                        let listLabels = "";
                        for (let i = 0; i < divBlock.length; i++) {
                            if (divBlock[i].checked) {
                                if (listLabels.length <= 0) {
                                    listLabels = divBlock[i].getAttribute("id");
                                } else {
                                    listLabels += "," + divBlock[i].getAttribute("id");
                                }
                            }
                        }

                        let listUsers = "";

                        <?php if (User::hasPermission('view_all_statistics')) { ?>
                        divBlock = document.getElementById("filter_user_lists").getElementsByTagName("input");
                        for (let i = 0; i < divBlock.length; i++) {
                            if (divBlock[i].checked) {
                                if (listUsers.length <= 0) {
                                    listUsers = divBlock[i].getAttribute("id");
                                } else {
                                    listUsers += "," + divBlock[i].getAttribute("id");
                                }
                            }
                        }
                        <?php } ?>

                        let paramListLinks = "";
                        if (listLinks.length > 0) {
                            paramListLinks = "&linkcountry=" + listLinks;
                        }

                        if(listLabels.length > 0) {
                            if (paramListLinks.length <= 0)
                                paramListLinks = "&labels=" + listLabels;
                            else
                                paramListLinks += "&labels=" + listLabels;
                        }else{
                            paramListLinks = "";
                        }

                        let paramListUsers = "";
                        if (listUsers.length > 0) {
                            paramListUsers = "&users=" + listUsers;
                        }

                        if (linkId == undefined) {
                            window.location.href = "https://" + window.location.hostname + window.location.pathname + "?app_id=" + appId + "&sort=" + sort + "&from=" + dateStart + "&to=" + dateEnd + paramListLinks + paramListUsers;
                        } else {
                            window.location.href = "https://" + window.location.hostname + window.location.pathname + "?VisitsSearch[linkcountry_id]=" + linkId + "&app_id=" + appId + "&sort=" + sort + "&from=" + dateStart + "&to=" + dateEnd;
                        }
                    }

                    //?app_id=43747&sort=-id&from=01/03/2020&to=01/05/2020
                </script>


                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        //'id',
                        [
                            'attribute' => 'id',
                            'value' => function ($model) {
                                return $model->id;
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'app'),
                            'value' => function ($model) {
                                $id = $_GET['app_id'] ?? null;
                                $app = \app\models\Apps::findOne($id);
                                return '<a href="/apps/view?id=' . $id . '" style="color:blue;">' .$app->name . '</a>';
                                //return '<a href="/apps/view?id=' . $model->link->linkcountry->app->id . '" style="color:blue;">' . $model->link->linkcountry->app->name . '</a>';
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'link'),
                            'value' => function ($model) {

                                if ($model->link->linkcountry_id == -1) {
                                    return "Naming";
                                } elseif (isset($model->link)) {
                                    $displayName = "<a href='/linkcountries/update?id=".$model->link->linkcountry->id."' style='color:blue;'>(id: ".$model->link->id.") ".$model->link->user->display_name."</a>";
                                    if ($model->link->linkcountry->country_code == "all") {
                                        $displayName = Yii::t('app', 'all_country');
                                    }
                                    $linkInfo = '<img src="' . Yii::$app->runAction('media/getflag', ['country_code' => $model->link->linkcountry->country_code]) . '" width="26px"> ' . $displayName . '';
                                    if ($model->link->archived) {
                                        $linkInfo .= " - <b>" . Yii::t('app', 'archived') . "</b>";
                                    }
                                }
//				                if (isset($model->link)) {
//					                $displayName = "<a href='/linkcountries/update?id=".$model->link->linkcountry->id."' style='color:blue;'>(id: ".$model->link->id.") ".$model->link->user->display_name ."</a>";
//                                    if ($model->link->linkcountry->country_code == "all") {
//                                        $displayName = Yii::t('app', 'all_country');
//                                    }
//                                    $linkInfo = '<img src="' . Yii::$app->runAction('media/getflag', ['country_code' => $model->link->linkcountry->country_code]) . '" width="26px"> ' . $displayName . '';
//                                    if ($model->link->archived) {
//                                        $linkInfo .= " - <b>" . Yii::t('app', 'archived') . "</b>";
//                                    }
//                                }
                                return $linkInfo;
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'price'),
                            'value' => function ($model) use ($appPrices) {

                                if ($model->link->linkcountry_id == -1){
                                    if ($model->is_first) {
                                        foreach ($appPrices as $price) {
                                            if ($price->user_id == $model->link->user_id && $price->country_code == -1) {
                                                return "$" . $price->price ?? 0;
                                            }
                                        }
                                    } else return "-";

                                }
                                $none = true;
                                if($model->is_first) {
                                    foreach ($appPrices as $price) {
                                        if ($price->user_id == $model->link->user_id && $model->link->linkcountry->country_code == $price->country_code) {
                                            $none = false;
                                            return "$" . $price->price ?? 0;
                                        }
                                    }
                                    if ($none) {
                                        foreach ($appPrices as $price) {
                                            if ($price->user_id == $model->link->user_id && $price->country_code == "all") {
                                                $none = false;
                                                return "$" . $price->price ?? 0;
                                            }
                                        }
                                    }
                                }else{
                                    return "-";
                                }
//                                $none = true;
//                                if($model->is_first) {
//                                    foreach ($appPrices as $price) {
//                                        if ($price->user_id == $model->link->user_id && $model->link->linkcountry->country_code == $price->country_code) {
//                                            $none = false;
//                                            return "$" . $price->price ?? 0;
//                                        }
//                                    }
//                                    if ($none) {
//                                        foreach ($appPrices as $price) {
//                                            if ($price->user_id == $model->link->user_id && $price->country_code == "all") {
//                                                $none = false;
//                                                return "$" . $price->price ?? 0;
//                                            }
//                                        }
//                                    }
//                                }else{
//                                    return "-";
//                                }
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'user_country'),
                            'value' => function ($model) {
                                $linkInfo = "None";
                                if (isset($model->filterlog)) {
                                    $linkInfo = ' <img src="' . Yii::$app->runAction('media/getflag', ['country_code' => strtolower($model->filterlog->country)]) . '" width="26px"> ' . $model->filterlog->country;
                                }
                                return $linkInfo;
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'model'),
                            'value' => function ($model) {

                                if (User::hasPermission('devices_view')) {
                                    if(isset($model->devices->model)){
                                        return '<a href="/devices/view?id=' . $model->devices->id . '" style="color:blue;">' . $model->devices->model . '</a>';
                                    }else{
                                        try {
                                            $deviceName = explode(";", $model->filterlog->ua)[2];
                                            $deviceModel = explode("/", $deviceName)[0];
                                            $deviceModel = str_replace("Build", "", $deviceModel);
                                            return '<a href="/devices/view?id=' . $model->devices->id . '" style="color:blue;">' . $deviceModel . '</a>';
                                        }catch (\Exception $e){
                                            return 'Not found';
                                        }
                                    }
                                } else {
                                    if(isset($model->devices->model)){
                                        return $model->devices->model;
                                    }else{
                                        try {
                                            $deviceName = explode(";", $model->filterlog->ua)[2];
                                            $deviceModel = explode("/", $deviceName)[0];
                                            $deviceModel = str_replace("Build", "", $deviceModel);
                                            return $deviceModel;
                                        }catch (\Exception $e){
                                            return 'Not found';
                                        }
                                    }
                                }
                            },
                            'format' => 'raw'
                        ],
                        'date',
                        //'extra:ntext',
                        [
                            'attribute' => Yii::t('app', 'label'),
                            'value' => function ($model) {
                                return $model->link->label;
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'cloaking'),
                            'value' => function ($model) {
                                if ($model->filterlog && $model->filterlog->is_bot) return "Не прошел"; else return "Прошел";
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'list_status'),
                            'visible' => User::hasPermission('blacklist'),
                            'value' => function ($model) {
                                $statuses = [
                                    '1' => Yii::t('app', 'in_black'),
                                    '0' => Yii::t('app', 'in_white'),
                                    '-1' => Yii::t('app', 'no_list')

                                ];
                                $status = \app\components\BlackListComponent::getStatus($model->devices->idfa);
                                return $statuses[$status];
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'actions'),
                            'value' => function ($model) {

                                if (User::hasPermission('logs_view')) {
                                    return '<a class="btn btn-primary" href="/visits/view?id=' . $model->id . '">Детали</a>';
                                } else {
                                    return "-";
                                }
                            },
                            'format' => 'raw'
                        ],
                        [
                            'attribute' => Yii::t('app', 'Black list'),
                            'visible' => User::hasPermission('blacklist'),
                            'value' => function ($model) {
                                $status = \app\components\BlackListComponent::getStatus($model->devices->idfa);
                                if (User::hasRole('Admin') || User::hasRole('Developer')) {
                                    if ($status == '1') {
                                        return '<button class="btn btn-secondary" disabled>В черный список</button>';
                                    } else {
                                        $query = http_build_query($_GET);
                                        return '<a class="btn btn-primary" href="/blacklist/block?id=' . $model->devices->idfa . '&'. $query . '">В черный список</a>';
                                    }
                                } else {
                                    return "No access";
                                }
                            },
                            'format' => 'raw'
                        ],
                        //['class' => 'yii\grid\ActionColumn'],
                    ],
                    'pager' => [
                        'options' => ['class' => 'pagination mb-0'],
                        'firstPageLabel' => '<i class="ik ik-chevrons-left"></i>',
                        'lastPageLabel' => '<i class="ik ik-chevrons-right"></i>',
                        'maxButtonCount' => 10,
                    ],
                ]); ?>


            </div>
        </div>
    </div>

</div>


