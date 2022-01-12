<?php

//use yii\helpers\Html;
use app\basic\debugHelper;
use yii\grid\GridView;
//use yii\widgets\Pjax;
use webvimark\modules\UserManagement\models\User;
use app\basic\genKeyHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VisitsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'partners');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="alert alert-primary alert-copy"
     role="alert"
     style="display:none;position:fixed;top:80px;right:40px;width:fit-content;z-index:100">
    Статистика скопирована
</div>
<div class="alert alert-warning alert-not-copy"
     role="alert"
     style="display:none;position:fixed;top:80px;right:40px;width:fit-content;z-index:100">
    Нет данных для копирования
</div>

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
                        <h5><?=$this->title;?></h5>
                        <span><?=Yii::t('app', 'list_partners');?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

                <div style="display:flex;">
                    <div class="col-sm-6 col-xl-2 mb-30">
                        <h4 class="sub-title"><?=Yii::t('app', 'date_start');?></h4>
                        <input type="text" class="form-control" id="datepicker_start">
                    </div>

                    <div class="col-sm-6 col-xl-2 mb-30">
                        <h4 class="sub-title"><?=Yii::t('app', 'date_end');?></h4>
                        <input type="text" class="form-control" id="datepicker_end">
                    </div>

                    <div class="col-sm-6 col-xl-2 mb-30">
                        <h4 class="sub-title">&nbsp;</h4>
                        <button type="button" class="btn btn-info btn-block" onClick="applyFilter()">Применить фильтр</button>
                    </div>
                </div>

                <script>

                    var params = window
                        .location
                        .search
                        .replace('?','')
                        .split('&')
                        .reduce(
                            function(p,e){
                                var a = e.split('=');
                                p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
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

                        if(dateStart == undefined) dateStart = '<?=$from;?>';
                        if(dateEnd == undefined) dateEnd = '<?=$to;?>';

                        $('#datepicker_start').val(dateStart);
                        $('#datepicker_end').val(dateEnd);
                    });

                </script>

                <script>
                    function applyFilter(){
                        params.from = $('#datepicker_start').val();
                        params.to = $('#datepicker_end').val();
                        let urlParams = new URLSearchParams(params).toString();
                        urlParams = decodeURIComponent(urlParams);

                        window.location.href = "https://"+window.location.hostname+window.location.pathname+"?"+urlParams;
                    }

                    //?app_id=43747&sort=-id&from=01/03/2020&to=01/05/2020
                </script>

                <script>
                    function copyStats(sel) {
                        $('.alert').hide();

                        try {
                            let stat = JSON.parse( $(sel).text() );
                            let statText = '';

                            stat.forEach((item) => {
                                statText += `${item.name} Установок: ${item.installs}. $${item.profit}\n`;
                            });

                            navigator.clipboard.writeText(statText);

                            $('.alert-copy').show();
                            setTimeout(() => {
                                $('.alert-copy').hide();
                            }, 3000);

                        } catch($e) {
                            $('.alert-not-copy').show();
                            setTimeout(() => {
                                $('.alert-not-copy').hide();
                            }, 3000);
                        }
                    }
                </script>


            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">
				<table id="stats-table" class="table table-striped table-bordered nowrap">
					<tr>
						<td>
                            <b>ID</b>
                        </td>
						<td>
                            <b>Name</b>
                        </td>
                        <td class="col-4" style="text-overflow: ellipsis;overflow: hidden;min-width: 0">
                            <b>Apps</b>
                        </td>
						<td onclick="sort_rows('td-installs', 'installs_order', this);" style="color:blue; cursor:pointer;">
                            <b>Installs</b><i class="ik ik-arrow-down"></i>
                        </td>
						<td onclick="sort_rows('td-profit', 'profit_order', this);" style="color:blue; cursor:pointer;">
                            <b>Profit</b><i class="ik ik-arrow-down"></i>
                        </td>
					</tr>

                    <tbody id="table1">
					<?php if(!empty($partnersData)) : ?>
                        <?php foreach($partnersData as $partner) : ?>
    						<tr>
    							<td><?=$partner['id'];?></td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center flex-md-row flex-column">
                                        <?php
                                        $is_partner = \app\models\PartnerBalance::find()->where(['partner_id' => $partner['id']])->count();
                                        $is_partner = $is_partner ? '&partner=' . $is_partner : '';
                                        ?>
                                        <?=Html::a($partner['display_name'], ['/profile?id=' . $partner['id'] . $is_partner], ['style' => 'color:blue;margin-right:3px']);?>
                                        <a data-toggle="collapse" href="#app-<?=$partner['id']?>" class="btn btn-outline-primary ml-auto d-flex">+</a>
                                        <button onclick="copyStats('#stat-<?=$partner['id'];?>')" class="btn btn-outline-primary ml-2">Copy stats</button>
                                        <div class="d-none" id="stat-<?=$partner['id'];?>">
                                            <?php if(isset($partner['apps'])) :
                                                $appStat = [];
    
                                                foreach($partner['apps'] as $app) :
                                                    $appStat[] = [
                                                        'name' => $app['name'],
                                                        'installs' => $app['installs'],
                                                        'profit' => round($app['profit'], 2)
                                                    ];
                                                endforeach;
    
                                                echo json_encode($appStat);
                                            endif; ?>
                                        </div>
                                    </div>
    
                                </td>
                                <td>
                                    <?php
                                        if(isset($partner['apps'])) : ?>
                                            <div class="collapse" id="app-<?=$partner['id']?>"  style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden;width: auto;min-width: 0">
                                                <?php foreach($partner['apps'] as $app) :
                                                    echo '<a href="/apps/view?id=' . $app['id'] . '" style="color:blue">' . $app['name'] . '</a>';
                                                    echo '<hr/>';
                                                endforeach; ?>
                                            </div>
                                    <?php
                                        endif;
                                    ?>
                                    <a data-toggle="collapse" href="#app-<?=$partner['id']?>" class="d-flex"><b>All</b></a>
                                </td>
                                <td class="td-installs">
                                    <?php
                                        if(isset($partner['apps'])) : ?>
                                            <div class="collapse" id="app-<?=$partner['id']?>">
                                                <?php foreach($partner['apps'] as $app) :
                                                    echo '<span>';
                                                    echo $app['installs'] ?? '0';
                                                    echo '</span><hr/>';
                                                endforeach; ?>
                                            </div>
                                    <?php
                                        endif;
                                    ?>
                                    <b><?=$partner['installs'];?></b>
                                </td>
    							<td class="td-profit">
                                    <?php
                                        if(isset($partner['apps'])) : ?>
                                            <div class="collapse" id="app-<?=$partner['id']?>">
                                                <?php foreach($partner['apps'] as $app) :
                                                    echo '<span>$';
                                                    echo $app['profit'] ?? '0';
                                                    echo '</span><hr/>';
                                                endforeach; ?>
                                            </div>
                                    <?php
                                        endif;
                                    ?>
                                    $<b><?=$partner['profit'];?></b>
                                </td>
    						</tr>
					    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>

                    <tr>
                        <td colspan="3"><b>Total</b></td>
                        <td><b><?=$statsData['total_installs'];?></b></td>
                        <td><b>$<?=$statsData['total_profit'];?></b></td>
                    </tr>
				</table>

                <input type="hidden" id="installs_order" value="desc">
                <input type="hidden" id="profit_order" value="desc">
            </div>
        </div>
    </div>

    <script>
        function sort_rows(tdClass, sortInputId, btn)
        {
            var tbody = $('#table1');

            tbody.find('tr').sort(function(a, b)
            {
                if($('#'+sortInputId).val()=='asc')
                {
                    return $('.'+tdClass+'>b', a).text().localeCompare($('.'+tdClass+'>b', b).text(),'en',{numeric:true});
                }
                else
                {
                    return $('.'+tdClass+'>b', b).text().localeCompare($('.'+tdClass+'>b', a).text(),'en',{numeric:true});
                }

            }).appendTo(tbody);

            var sort_order=$('#'+sortInputId).val();
            if(sort_order=="asc")
            {
                document.getElementById(sortInputId).value="desc";
                btn.getElementsByTagName("I")[0].className = 'ik ik-arrow-up';
            }
            if(sort_order=="desc")
            {
                document.getElementById(sortInputId).value="asc";
                btn.getElementsByTagName("I")[0].className = 'ik ik-arrow-down';
            }
        }
    </script>
	
	
</div>


