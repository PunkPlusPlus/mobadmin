<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Zones;
use app\basic\debugHelper;
use webvimark\modules\UserManagement\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\Apps */
/* @var $googlePlay array */
/* @var $blockInfo bool */

$this->title = $model->name;

\yii\web\YiiAsset::register($this);
$pricesList = $model->prices;

$listCountry = [
    //'all' => 'Все страны',
];

$zones = Zones::find()
    ->all();
foreach($zones as $zone){
    $listCountry[$zone->zone] = "(".strtoupper($zone->zone).") ".$zone->country;
}
?>

    <style>
        .screen {
            font-family: 'Helvetica';
            font-weight: 300;
            line-height: 2;
            text-align: center;

            width: 100%;
            height: auto;
            display: block;
            position: relative;
            min-height: 50px;
        }

        .screen:before {
            content: " ";
            display: block;

            position: absolute;
            top: -10px;
            left: 0;
            height: calc(100% + 10px);
            width: 100%;
            background-color: rgb(230, 230, 230);
            border: 2px dotted rgb(200, 200, 200);
            border-radius: 5px;
        }

        .screen:after {
            content: "\2639" " Картинка недоступна " attr(alt);
            display: block;
            font-size: 16px;
            font-style: normal;
            font-family: FontAwesome;
            color: rgb(100, 100, 100);

            position: absolute;
            top: 5px;
            left: 0;
            width: 100%;
            text-align: center;
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
                    <div class="page-header-title" style="display:flex;">
                        <?php if(strlen($googlePlay['image']) > 5){ ?>

                            <i class="ik" style="
                                    background-image: url(<?=$googlePlay['image'];?>?v=1); !important;
                                    background-repeat: no-repeat;
                                    background-size: 100% 100%;"></i>

                        <?php }else{ ?>
                            <i class="ik" style="
                                    background-image: url('/images/none-app.png'); !important;
                                   background-repeat: no-repeat;
                                    background-size: 100% 100%;"></i>
                        <?php } ?>
                        <div class="d-inline">
                            <h5><?=$model->name;?></h5>
                            <span><?=$model->package;?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-body">
                <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

                    <div style="float:left; padding-bottom: 20px; display: flex; align-items: center;">

                        <?php
                            if(User::hasRole(['Developer', 'support'])){
                                $trafficRoute = $model->traffic_route;
                                $text = "Перенаправить траффик на <b>APP for Testing</b>";
                        ?>
                                <!-- Rounded switch -->
                                <label class="switch">
                                    <input type="checkbox" id="traffic_route"  onchange="changeTrafficRoute(this)" <?php if($trafficRoute == 1) print "checked"; ?>>
                                    <span class="slider round"></span>
                                </label>
                                <label for="traffic_route">&nbsp;&nbsp;&nbsp;<?=$text;?></label>

                                <script>
                                    function changeTrafficRoute(checkboxElem) {
                                        let trafficRoute = 0;
                                        if (checkboxElem.checked) {
                                            trafficRoute = 1;
                                        } else {
                                            trafficRoute = 0;
                                        }
                                        document.location.replace('/apps/change-traffic?app_id='+<?=$model->id;?>+'&route_id='+trafficRoute);
                                    }
                                </script>
                        <?php } ?>
                    </div>
                    <div style="float:right; padding-bottom: 20px;">
                        <?php if(User::hasRole(['employee', 'Developer', 'manager', 'support'])) : ?>

                            <?= Html::a(
                                    Yii::t('app', 'check_status'),
                                    ['/bot/check', 'id' => $model->id],
                                    ['class' => 'btn btn-primary']
                            ) ?>
                            
                            <?= Html::a(
                                Yii::t('app', 'push_ev'),
                                ['/fb', 'id' => $model->id],
                                ['class' => 'btn btn-primary']
                            ) ?>

                        <?php endif; ?>
			
			<?= Html::a(
                                     "Пошарить РК ФБ",
                                     ['/task/index', 'id' => $model->id],
                                     ['class' => 'btn btn-primary']
                             ) ?>

                        <?= Html::a(
                                Yii::t('app', 'visits_stats'),
                                ['/visits/index', 'app_id' => $model->id, 'sort' => '-id'],
                                ['class' => 'btn btn-primary']
                        ) ?>

                        <?php if(!$blockInfo && User::hasPermission('edit_all_apps')){ ?>
                            <?= Html::a(
                                    Yii::t('app', 'Debug'),
                                    ['/apps/debug', 'id' => $model->id],
                                    ['class' => 'btn btn-primary']
                            ) ?>
                            <?= Html::a(
                                    Yii::t('app', 'add_country'),
                                    ['/linkcountries/create', 'appid' => $model->id],
                                    ['class' => 'btn btn-success']
                            ) ?>
                            <?= Html::a(
                                    Yii::t('app', 'edit'),
                                    ['update', 'id' => $model->id],
                                    ['class' => 'btn btn-primary']
                            ) ?>
                        <?php } ?>

                        <?php if(User::hasPermission('change_app_balance')) { ?>
                            <?= Html::a(
                                    Yii::t('app', 'Balance'),
                                    ['/app-balance/view', 'id' => $model->id],
                                    ['class' => 'btn btn-primary']
                            ) ?>
                        <?php } ?>

                        <?php if(User::hasPermission('app_del')) { ?>
                            <?= Html::a(
                                    Yii::t('app', 'delete'),
                                    ['delete', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-danger',
                                        'data' => [
                                            'confirm' => Yii::t('app', 'ask_delete_app'),
                                            'method' => 'post',
                                        ],
                                    ]
                            ) ?>
                        <?php } ?>
                    </div>
					
					<style>
					 .date_list{
						 display:none;
					 }
					</style>

                    <?php if(!$blockInfo){ ?>
                        <table class="table table-striped table-bordered nowrap">
                            <tr>
                                <td>Статус</td>
                                <td>
                                    <?php
                                    switch ($model->published) {
                                        case -1:
                                            print "<span style='color:red;'>".Yii::t('app', 'banned')."</span>";
                                            break;
                                        case 0:
                                            print Yii::t('app', 'no_published');
                                            break;
                                        case 1:
                                            print "<span style='color:green;'>".Yii::t('app', 'published')."</span>";
                                            break;
                                        case 3:
                                            print "<span style='color:green;'>".Yii::t('app', 'testing')."</span>";
                                            break;
                                        case 4:
                                            print "<span style='color:green;'>".Yii::t('app', 'ready')."</span>";
                                            break;
                                        case 5:
                                            print "<span style='color:green;'>".Yii::t('app', 'revision')."</span>";
                                            break;
                                    }
                                    ?>
									<a onClick="showDateList();" style="color: blue; cursor:pointer;"><i class="ik ik-eye"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>Google Play</td>
                                <td>
                                    <a href="https://play.google.com/store/apps/details?id=<?=$model->package;?>" style="color:blue;">
                                        https://play.google.com/store/apps/details?id=<?=$model->package;?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td><?=Yii::t('app', 'android_version');?></td>
                                <td><?=str_replace("and up", Yii::t('app', 'and_up'), $googlePlay['supported_os']);?></td>
                            </tr>
                            <tr>
                                <td>Название пакета</td>
                                <td><?=$model->package;?></td>
                            </tr>
                            <tr>
                                <td>Название класса</td>
                                <td>MainActivity</td>
                            </tr>
                            <tr>
                                <td>Github</td>
                                <td><?= Html::a($model->github, $model->github, ['style' => 'color:blue;', 'target' => '_blank']) ?></td>
                            </tr>
                            <tr>
                                        <?php
                                        if (User::hasPermission('download_apk')){
                                        ?>
                                <td>APK файл</td>
                                <td>
                                    <?php if($model->apk) : ?>
                                        <?=$model->apk?>
                                        
                                        <?=Html::a(
                                                'Скачать',
                                                ['download-apk', 'id' => $model->id],
                                                ['class' => 'btn btn-success']
                                        )?>
                                         
                                        <?php if(User::hasPermission('delete_apk')) : ?>
                                            <?=Html::a(
                                                    'Удалить',
                                                    ['delete-apk', 'id' => $model->id],
                                                    [
                                                        'class' => 'btn btn-danger',
                                                        'data' => [
                                                            'confirm' => 'Вы уверены?',
                                                            'method' => 'post',
                                                        ]
                                                    ]
                                            )?>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        Файл не загружен
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Keystore файл</td>
                                <td>
                                    <?php if($model->keystore) : ?>
                                        <?=$model->keystore?>
                                       
                                        <?=Html::a(
                                            'Скачать',
                                            ['download-keystore', 'id' => $model->id],
                                            ['class' => 'btn btn-success']
                                        )?>
                                       
                                        <?php if(User::hasPermission('delete_apk')) : ?>
                                            <?=Html::a(
                                                'Удалить',
                                                ['delete-keystore', 'id' => $model->id],
                                                [
                                                    'class' => 'btn btn-danger',
                                                    'data' => [
                                                        'confirm' => 'Вы уверены?',
                                                        'method' => 'post',
                                                    ]
                                                ]
                                            )?>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        Файл не загружен
                                    <?php endif; ?>
                                </td>
                                <?php
                                        }
                                        ?>
                            </tr>
                            <?php
                            if(User::hasPermission('view_all_apps')) {
                                if($model->created_time)  print "<tr class='date_list'><td>Дата создания</td><td>".$model->created_time."</td></tr>";
                                if($model->upload_time) print "<tr class='date_list'><td>Дата публикации</td><td>".$model->upload_time."</td></tr>";
                                if($model->published_time) print "<tr class='date_list'><td>Дата выхода</td><td>".$model->published_time."</td></tr>";
                                if($model->published == -1 && $model->banned_time) print "<tr class='date_list'><td>Дата бана</td><td>".$model->banned_time."</td></tr>";
                                if($model->lastchecked_time) print "<tr class='date_list'><td>Дата проверки</td><td>".$model->lastchecked_time."</td></tr>";
								if($model->note) print "<tr><td>Заметки</td><td>".$model->note."</td></tr>"; 
                            }

                            ?>

                        </table>


                        <table>
                            <tr>
                                <td style="padding-right:10px;">

                                    <div class="checkbox-fade fade-in-primary">
                                        <label for="gpInfo_ch">
                                            <input type="checkbox" id="gpInfo_ch" name="gpInfo_ch" onChange="spoiler('gpInfo');">
                                            <span class="cr"> <i class="cr-icon ik ik-check txt-primary"></i></span>
                                            <span><?=Yii::t('app', 'information_googleplay');?></span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    <?php }else{ ?>
                        <center><h1><?=Yii::t('app', 'banned_app_desc');?></h1></center>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>

	<script>
		var showDate = false;
		function showDateList(){
			showDate = !showDate;
			var rows = document.getElementsByClassName("date_list");
			for(var i=0; i<rows.length; i++){
				if(showDate){
					rows[i].style.display = 'table-row';
				}else{
					rows[i].style.display = 'none';
				}
			}
			console.log(rows);
		}
	</script>


    <div id="gpInfo" style="display:none;">
        <div class="card">
            <div class="card-body">
                <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">
                    <center><h3><?=Yii::t('app', 'information_googleplay');?></h3></center>
                    <?php if($googlePlay['screenshots'] != -1){ ?>
                        <style>
                            .table_detailed td{
                                padding: 15px !important;
                                vertical-align: top;
                            }
                        </style>
                        <table class="table_detailed">
                            <tr>
                                <td style="width:230px;"><?='<img src="'.$googlePlay['image'].'?v=1" width="230px" class="screen">';?></td>
                                <td>
                                    <!--                                    --><?//=Yii::t('app', 'filter_country');?><!--:-->
                                    <select id="asd" onchange="goToFilter(this);" class="show-tick" style="display:none;">
                                        <?php foreach($listCountry as $key => $value){ ?>
                                            <option value="<?=$key;?>"
                                                <?php
                                                $country = $_GET['country'] ?? 'ru';
                                                if($country == 'all')
                                                    $country = 'ru';

                                                if($country == $key) print 'selected'; ?>><?=$value;
                                                ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <script>
                                        function goToFilter(sel){
                                            //location.href='/apps/view?id=<?=$model->id;?>&country='+sel.value;
                                        }
                                    </script>
                                    <b><?=Yii::t('app', 'name');?>: </b><?=$googlePlay['title'];?>
                                    <br><b><?=Yii::t('app', 'author');?>: </b><a href="<?=$googlePlay['author_link'];?>" style="color:blue" target="_blank"><?=$googlePlay['author'];?></a>
                                    <br><b><?=Yii::t('app', 'category');?>: </b><?=$googlePlay['categories'][0];?>
                                    <br><b><?=Yii::t('app', 'android_version');?>: </b><?=$googlePlay['supported_os'];?>
                                    <br><b><?=Yii::t('app', 'rating');?>: </b><?=$googlePlay['rating'];?>
                                    <br><b><?=Yii::t('app', 'voted');?>: </b><?=$googlePlay['votes'];?> чел.
                                    <br><b><?=Yii::t('app', 'size');?>: </b><?=$googlePlay['size'];?>
                                    <br><b><?=Yii::t('app', 'downloads');?>: </b><?=$googlePlay['downloads'];?>
                                    <br><b><?=Yii::t('app', 'version');?>: </b><?=$googlePlay['version'];?>
                                    <br><b><?=Yii::t('app', 'content_rating');?>: </b><?=$googlePlay['content_rating'];?>
                                    <br><b><?=Yii::t('app', 'last_update');?>: </b><?=$googlePlay['last_updated'];?>
                                    <br><b><?=Yii::t('app', 'policy');?>: </b><a href="<?=$googlePlay['policy'] ?? '' ?>" style="color:blue;" target="_blank"><?=$googlePlay['policy'] ?? '' ?></a>
                                </td>
                            </tr>
                        </table>
                        <style>
                            .img-screens{
                                min-width: 185px;
                                margin: 10px;
                            }
                        </style>
                        <div style="display:flex; padding-top: 15px;">
                            <?php
                            foreach($googlePlay['screenshots'] as $url){
//                                print '<img src="'.$url.'" style="margin-right:20px; max-width: 100%; height: auto;">';
                                print '<div class="img-screens"><img class="screen" src="'.$url.'?v=1"></div>';
                            }
                            ?>
                        </div>

                    <?php }else{
                        switch($model->published){
                            case -1:
                                $textStatus = Yii::t('app', 'app_deleted_googleplay');
                                break;
                            case 0:
                            default:
                                $textStatus = Yii::t('app', 'app_no_published_googleplay');
                                break;
                        }
                        ?>
                        <center><h2><?=$textStatus;?></h2></center>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php
if(User::hasPermission("openAll")) {
?>

    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">
                <center><h3>Добавить ссылку на все страны</h3></center>
                <form action="/apps/open-all" method="get">
                    <input type="text" class="form-control" name="url" style="width: 30%" placeholder="link">
                    <input type="text" name="app_id" value="<?= $_GET['id'] ?>" style="visibility: hidden">
                    <br>
                    <button class="btn btn-primary" type="submit">Send</button>
                </form>
                <br>
                <a href="/apps/delete-all?app_id=<?= $_GET['id'] ?>" class="btn btn-primary">Удалить все ссылки</a>
            </div>
        </div>
    </div>

    <?php

    $link = \app\models\Namings::find()->where(['app_id' => $_GET['id'] ?? 0])->one();
    if ($link) {
        $url = \app\models\Links::findOne($link->link_id);
        $link_user = User::findOne($url->user_id);

//    $price = \app\models\Prices::find()
//        ->where(['app_id' => $link->app_id])
//        ->andWhere(['country_code' => -1])
//        ->andWhere(['user_id' => $url->user_id])->one();
    }

    if (User::hasPermission('edit_all_apps')) {
        $allUsers = User::find()->all();
        foreach ($allUsers as $user) {
            $listUsers[$user->id] = $user->display_name;
        }
    }
    $listUsers[User::getCurrentUser()->id] = User::getCurrentUser()->display_name;
    ?>

    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">
                <center><h3>Добавить нейминг ссылку</h3></center>
                <span style="display: inline-block; width: 40%">
                    <form action="/apps/open-naming" method="get">
                        <p><h4>Ссылка:</h4></p>
                        <input type="text" class="form-control" name="url" style="width: 70%; margin-bottom: 1%" placeholder="link">
                        <p><h4>Пользователь:</h4></p>
                        <select name="user_price" class="form-control" id="form-control" style="width: 70%;">
                            <?php foreach ($listUsers as $key => $value) { ?>
                                <option value="<?= $key; ?>"><?= $value; ?></option>
                            <?php } ?>
                        </select>
                    <input type="text" name="app_id" value="<?= $_GET['id'] ?>" style="visibility: hidden">
                    <br>
                    <button class="btn btn-primary" type="submit">Send</button>
                </form>
                </span>
                <span style="display: inline-block; width: 30%; float: right">
                    <p><h4>Текущая ссылка:</h4></p>
                    <input type="text" class="form-control" name="url" style="margin-bottom: 1%" disabled value="<?php echo $url->url ?? "-" ?>">
                    <p><h4>Пользователь:</h4></p>
                    <select name="user_price" disabled class="form-control" id="form-control" style="width: 70%;">
                        <option value=""><?= $link_user->display_name ?? "" ?></option>
                    </select>
                </span>

            </div>
        </div>
    </div>

<?php }  ?>
    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">
                <center><h3><?=Yii::t('app', 'country_distribution');?></h3></center>
                <table class="table table-striped table-bordered detail-view">
                    <thead>
                    <tr>
                        <td><?=Yii::t('app', 'country');?></td>
                        <td><?=Yii::t('app', 'user');?></td>
                        <td><?=Yii::t('app', 'cost');?></td>
                        <?php if(User::hasPermission('view_all_apps')) { ?>
                            <td><?=Yii::t('app', 'params');?></td>
                        <?php } ?>
                        <td><?=Yii::t('app', 'actions');?></td>
                    </tr>
                    </thead>
                    <?php for($i=0; $i<count($listCountryLinks); $i++){?>
                        <?php
                        if($listCountryLinks[$i]['country_code'] != "all" || User::hasPermission('edit_all_apps')){
                            ?>
                            <tr>
                                <td>
                                    <img src="<?=Yii::$app->runAction('media/getflag', ['country_code' => $listCountryLinks[$i]['country_code']]);?>">
                                    <?=strtoupper($listCountryLinks[strtolower($listCountryLinks[$i]['country_code'])] ?? $listCountryLinks[$i]['country_code']);?>
                                </td>
                                <td>
                                    <?php
                                        $b = 0;
                                        foreach($listCountryLinks[$i]['users'] as $user){
                                            if(count($listCountryLinks[$i]['users']) > 1 && $b > 0) print "<hr>";

                                            $is_partner = \app\models\PartnerBalance::find()->where(['partner_id' => $user['id']])->count();
                                            $is_partner = $is_partner ? '&partner=' . $is_partner : '';
                                            print Html::a($user['name'], ['/profile?id=' . $user['id'] . $is_partner], ['class' => 'btn btn-link btn-rounded']);
                                            $b++;
                                        }
                                    ?>
                                </td>
                                <td>
<!--                                    $--><?//=$pricesList[strtolower($listCountryLinks[$i]['country_code'])] ?? $pricesList['all'] ?? 0;?>

                                    <?php
                                    $b = 0;
                                    foreach($listCountryLinks[$i]['users'] as $user){
                                        $none = true;
                                        foreach($pricesList as $price) {
                                            if($price->user_id == $user['id'] && $price->country_code == $listCountryLinks[$i]['country_code']) {
                                                if (count($listCountryLinks[$i]['users']) > 1 && $b > 0) print "<hr>";
                                                print "$" . $price->price;
                                                $b++;
                                                $none = false;
                                            }
                                        }
                                        if($none){
                                            foreach($pricesList as $price) {
                                                if($price->user_id == $user['id'] && $price->country_code == "all") {
                                                    if (count($listCountryLinks[$i]['users']) > 1 && $b > 0) print "<hr>";
                                                    print "$" . $price->price;
                                                    $b++;
                                                    $none = false;
                                                }
                                            }
                                            if($none) {
                                                if (count($listCountryLinks[$i]['users']) > 1 && $b > 0) print "<hr>";
                                                print "$0";
                                                $b++;
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                            <?php if(User::hasPermission('view_all_apps')) { ?>
                                <td>
                                    <div class="jsonfy">
                                        <?php
                                        if(strlen($listCountryLinks[$i]['extra']) > 1) {
                                            $jsonData = json_decode($listCountryLinks[$i]['extra']);
                                            foreach ($jsonData as $key => $value) {
												$val = $value;
												if(strlen($val) > 17){
													$val = substr($val, 0, 17)."...";
												}
                                                print Yii::t('app', $key) . ": <b>" . $val . "</b><br>";
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                            <?php } ?>
                                <td>
                                    <?= Html::a(
                                            Yii::t('app', 'stats'),
                                            [
                                                '/visits/index',
                                                'linkcountry' => $listCountryLinks[$i]['id'],
                                                'app_id' => $model->id,
                                                'sort' => '-id'
                                            ],
                                            ['class' => 'btn btn-primary']
                                    )?>

                                    <?php if(!$blockInfo){ ?>
                                        <?= Html::a(
                                                Yii::t('app', 'edit'),
                                                [
                                                    'linkcountries/update',
                                                    'id' => $listCountryLinks[$i]['id']
                                                ],
                                                ['class' => 'btn btn-primary']
                                        )?>

                                        <?php if(User::hasPermission('view_all_apps') && $listCountryLinks[$i]['country_code'] !== 'all') { ?>
                                            <?= Html::a(Yii::t('app', 'delete'),
                                                [
                                                    'linkcountries/delete',
                                                    'id' => $listCountryLinks[$i]['id'],
                                                    'app_id' => $model->id
                                                ],
                                                [
                                                    'class' => 'btn btn-danger',
                                                    'data' => [
                                                        'confirm' => Yii::t('app', 'ask_delete_linkcountry'),
                                                        'method' => 'post',
                                                    ],
                                                ]) ?>
                                        <?php } ?>
                                    <?php } ?>

                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


    <script>
        function spoiler(divId){
            if(document.getElementById(divId).style.display == "block"){
                document.getElementById(divId).style.display = "none";
                deleteCookie(divId);
            }else{
                document.getElementById(divId).style.display = "block";
                setCookie(divId, true);
            }
        }

        function spolierVisible(divId, visible){
            if(visible){
                document.getElementById(divId).style.display = "block";
            }else{
                document.getElementById(divId).style.display = "none";
            }
        }

        loadSpoiler('visits_info');
        loadSpoiler('linkcountry');
        loadSpoiler('gpInfo');
        function loadSpoiler(divId){
            if(getCookie(divId) != undefined){
                document.getElementById(divId+"_ch").checked = true;
                spolierVisible(divId, true);
            }else{
                document.getElementById(divId+"_ch").checked = false;
                spolierVisible(divId, false);
            }
        }



        function getCookie(name) {

            var matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ))
            return matches ? decodeURIComponent(matches[1]) : undefined
        }

        // уcтанавливает cookie
        function setCookie(name, value, props) {

            props = props || {}

            var exp = props.expires

            if (typeof exp == "number" && exp) {

                var d = new Date()

                d.setTime(d.getTime() + exp*1000000)

                exp = props.expires = d

            }

            if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }

            value = encodeURIComponent(value)

            var updatedCookie = name + "=" + value

            for(var propName in props){

                updatedCookie += "; " + propName

                var propValue = props[propName]

                if(propValue !== true){ updatedCookie += "=" + propValue }
            }

            document.cookie = updatedCookie

        }

        // удаляет cookie
        function deleteCookie(name) {

            setCookie(name, null, { expires: -1 })

        }

    </script>
<?php
// JSONfy view
$this->registerCss('pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }.string { color: green; }.number { color: darkorange; }.boolean { color: blue; }.null { color: magenta; }.key { color: red; }');
$this->registerJs('$(function () {
        var jj = $("div.jsonfy>pre").html();
        var jjfy = JSON.stringify(JSON.parse(jj), undefined, 4);
        $("div.jsonfy>pre").html(syntaxHighlight(jjfy));
    });');
?>
