<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\AppsAccess;
use app\models\Zones;
use app\models\Linkcountries;
use app\basic\debugHelper;
use webvimark\modules\UserManagement\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\LinkCountries */
/* @var $form yii\widgets\ActiveForm */

$listUsers = [];


//$userInfo = User::find()
//    ->where(['id' => $model->user_id]);
//
//if ($userInfo = $userInfo->one()) {
//    $listUsers[$userInfo->id] = $userInfo->display_name;
//}

$isAccessEdit = false;
if (User::hasPermission('edit_all_apps')) {
    $isAccessEdit = true;
    $appAccessUserList = Linkcountries::find()
        ->where(['app_id' => $model['app_id']])
        ->all();

    $allUsers = User::find()->all();
    //foreach($appAccessUserList as $accessUser) {
    foreach ($allUsers as $user) {
        //if($accessUser->user_id == $user->id) {
        $listUsers[$user->id] = $user->display_name;
        //}
    }
    //}
}
$listUsers[User::getCurrentUser()->id] = User::getCurrentUser()->display_name;

$appId = intval($_GET['appid'] ?? $model['app_id']);
?>
<style>
    .form-control {
        height: 30px;
    }
</style>


<div class="form-group">
    <a class="btn btn-primary" href="/apps/view?id=<?=$appId?>" style="color:#ffffff;"><- <?=Yii::t('app', 'back_app');?></a>
    <hr>

    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"form-line\">\n{input}</div>\n{hint}\n{error}",
        ],
    ]); ?>

    <?php


    if ($appId)
        $model['app_id'] = $appId;


    $zones = Zones::find()
        ->all();

    $listCountry = [
        //'all' => 'Все страны',
    ];

    if(User::hasPermission('edit_all_apps')) {
        $listCountry['all'] = Yii::t('app', 'all_country');
    }

    foreach($zones as $zone){
        $listCountry[$zone->zone] = "".strtoupper($zone->zone)." - ".$zone->country;
    }

    $listApps = [];
    $listApps[$appInfo['id']] = $appInfo['name'] . ' (' . $appInfo['package'] . ')';

    $listUsers = [];



    //    $userInfo = User::find()
    //        ->where(['id' => $model->user_id]);
    //
    //    if($userInfo = $userInfo->one()) {
    //        $listUsers[$userInfo->id] = $userInfo->display_name;
    //    }

    if(User::hasPermission('edit_all_apps')) {
        $appAccessUserList = Linkcountries::find()
            ->where(['app_id' => $model['app_id']])
            ->all();

        $allUsers = User::find()->all();
        //foreach($appAccessUserList as $accessUser) {
        foreach($allUsers as $user){
            //if($accessUser->user_id == $user->id) {
            $listUsers[$user->id] = $user->display_name;
            //}
        }
        //}
    }
    $listUsers[User::getCurrentUser()->id] = User::getCurrentUser()->display_name;
    ?>

    <?php if (isset($errorText) && strlen($errorText) > 0) { ?>
        <center><p style="color:red; font-size:30px;"><?= $errorText; ?></p></center>
    <?php } ?>

    <?= $form->field($model, 'country_code')->dropDownList($listCountry, ['prompt' => Yii::t('app', 'select_country').'...', 'class' => 'form-control select2 fabienpof', 'disabled' => true]); ?>


    <?php if(!isset($_GET['id'])){ ?>
        <script>
            document.getElementById("linkcountries-country_code").removeAttribute("disabled");
        </script>
    <?php } ?>
    <?= $form->field($model, 'app_id')->dropDownList($listApps, ['disabled' => true]); ?>


    <?= $form->field($model, 'extra')
             ->textarea(['rows' => 0, 'style' => 'visibility:hidden; height:0px; padding:0px; margin:0px;display:none;'])
             ->label(false); 
    ?>
    
    <input type="text" style="display:none;" name="new_urls">



    <script>
        function save() {
            //var extraList = new Map();
            // var extraList = {};
            // var count = 90;//document.getElementsByClassName("item").length;
            // for (var i = 1; i <= count; i++) {
            //     if(document.getElementsByName("offer_line_key" + i)) {
            //         if (document.getElementsByName("offer_line_key" + i).length >= 1) {
            //             var keyLine = document.getElementsByName("offer_line_key" + i)[0].value;
            //             var valueLine = document.getElementsByName("offer_line_value" + i)[0].value;
            //             if (keyLine.length > 0) {
            //                 extraList[keyLine] = valueLine;
            //             }
            //         }
            //     }
            // }
            // document.getElementById('linkcountries-extra').innerHTML = JSON.stringify(extraList);

            var urls = [];

            var lines = document.getElementsByName("new_link_line");
            lines.forEach(function(item, i, arr) {
                if(item.querySelector("[name=user] select") !== null) {
                    urls[urls.length] = {
                        'user_id': item.querySelector("[name=user] select").value,
                        'is_main': item.querySelector("[name=is_main] [type=radio]").checked,
                        'key': item.querySelector("[name=key] input").value,
                        'value': item.querySelector("[name=value] input").value,
                        'url': item.querySelector("[name=url] input").value,
                        'label': item.querySelector("[name=label] input").value
                    };
                }
            });

            //изменения для старых полей
            var lines = document.getElementsByName("link_line");
            lines.forEach(function(item, i, arr) {
                if(item.querySelector("[name=user] select") !== null) {
                    urls[urls.length] = {
                        'link_id' : item.querySelector("[name=id]").innerHTML,
                        'user_id': item.querySelector("[name=user] select").value,
                        'is_main': item.querySelector("[name=is_main] [type=radio]").checked,
                        'key': item.querySelector("[name=key] input").value,
                        'value': item.querySelector("[name=value] input").value,
                        'url': item.querySelector("[name=url] input").value,
                        'label': item.querySelector("[name=label] input").value
                    };
                }
            });

            document.getElementsByName("new_urls")[0].value = JSON.stringify(urls);

            let form = document.getElementById('w0');
            form.submit();
        }
    </script>

    <?php if(isset($_GET['id'])){ ?>
        <div>
            <table class="table table-striped table-bordered" id="table_links">
                <tr id="table_link_row_1">
                    <td><?= Yii::t('app', 'id'); ?></td>
                    <td style="width:15%;"><?= Yii::t('app', 'user'); ?></td>
                    <td style="width:5%;"><?= Yii::t('app', 'is_main'); ?></td>
                    <td><?= Yii::t('app', 'key'); ?></td>
                    <td><?= Yii::t('app', 'value'); ?></td>
                    <td><?= Yii::t('app', 'url'); ?></td>
                    <td><?= Yii::t('app', 'label'); ?></td>
                    <td><?= Yii::t('app', 'actions'); ?></td>
                </tr>
                <?php foreach ($links as $link) { ?>
                    <tr name="link_line">
                        <td name="id"><?=$link->id;?></td>
                        <td name="user" iduser="<?=$link->user_id;?>"><?= $listUsers[$link->user_id]; ?></td>
                        <td name="is_main">
                            <center>
                                <input type="radio" name="is_main_check" value="<?=$link->is_main;?>" <?php if($link->is_main) print "checked"; ?> disabled>
                            </center>
                        </td>
                        <td name="key"><?= $link->key; ?></td>
                        <td name="value"><?= $link->value; ?></td>
                        <td name="url"><?= $link->url; ?></td>
                        <td name="label"><?= $link->label ?? Yii::t('app', 'undefined'); ?></td>
                        <td>
                            <a class="btn btn-success" onclick="showLineDeeplink(this);" style="margin-left:15px; color:#ffffff; background-color: #ffab00;"><?= Yii::t('app', 'show_deeplink'); ?></a>
                            <a class="btn btn-success" href="/visits/index?app_id=<?=$model['app_id'];?>&sort=-id&links=<?= $link->id; ?>" style="margin-left:15px; color:#ffffff;"><i class="ik ik-bar-chart-line" style="margin-right: 0 !important;"></i></a>
                            <a class="btn btn-primary" onclick="edit_row_link(this);" style="margin-left:15px; color:#ffffff;"><i class="ik ik-edit" style="margin-right: 0 !important;"></i></a>
                            <a class="btn btn-danger" onclick="delete_row_link(this, <?=$link->id;?>);" style="margin-left:15px; color:#ffffff;"><i class="ik ik-delete" style="margin-right: 0 !important;"></i></a></td>
                    </tr>
                <?php } ?>
            </table>


            <div id="userlist" style="display:none;">
                <select id="user_link" class="form-control">
                    <?php foreach ($listUsers as $key => $value) { ?>
                        <option value="<?=$key;?>"><?=$value;?></option>
                    <?php } ?>
                </select>
            </div>


            <datalist id="labelList">
                <?php foreach ($allLabels as $label) { ?>
                    <option value="<?=$label['name'];?>"></option>
                <?php } ?>
            </datalist>

            <script>
                <?php if(isset($errorDeeplinks) && $errorDeeplinks){ ?>
                initErrorLinks();
                function initErrorLinks(){
                    var erroLinks = JSON.parse('<?=$errorDeeplinks;?>');
                    erroLinks.forEach(function(item, i, arr) {
                        add_new_link(item['user_id'], item['key'], item['value'], item['url'], item['label']);
                    });
                }
                <?php } ?>

                function add_new_link(user_id = -1, key = -1, value = -1, url = -1, label = -1) {
                    var table = document.getElementById('table_links');

                    var row = document.createElement("TR");
                    row.setAttribute("name", "new_link_line");
                    table.appendChild(row);
                    //document.getElementsByName("")

                    var td0 = document.createElement("TD"); td0.setAttribute("name", "id"); td0.innerHTML = "New";
                    var td1 = document.createElement("TD"); td1.setAttribute("name", "user");
                    var tdIsMain = document.createElement("TD"); tdIsMain.setAttribute("name", "is_main"); tdIsMain.setAttribute("name", "is_main");
                    tdIsMain.innerHTML = "<center><input type='radio' name='is_main_check' <?php if(!$isAccessEdit) print 'disabled'; ?>></center>";
                    var td2 = document.createElement("TD"); td2.setAttribute("name", "key");
                    var td3 = document.createElement("TD"); td3.setAttribute("name", "value");
                    var td4 = document.createElement("TD"); td4.setAttribute("name", "url");
                    var td6 = document.createElement("TD"); td6.setAttribute("name", "label");
                    var td5 = document.createElement("TD");

                    row.appendChild(td0);
                    row.appendChild(td1);
                    row.appendChild(tdIsMain);
                    row.appendChild(td2);
                    row.appendChild(td3);
                    row.appendChild(td4);
                    row.appendChild(td6);
                    row.appendChild(td5);

                    if(user_id == -1) {
                        td1.innerHTML = document.getElementById('userlist').innerHTML;
                        td2.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" list=\"keyParams\">";
                        td3.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" style=\"max-width:80px;\">";
                        td4.innerHTML = "<input type='text' placeholder='...' class=\"form-control\">";
                        td5.innerHTML = "<a class=\"btn btn-danger\" onclick=\"delete_row_link(this);\" style=\"margin-left:15px; color:#ffffff;\"><i class=\"ik ik-delete\" style=\"margin-right: 0 !important;\"></i></a>";
                        td6.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" list=\"labelList\">";
                    }else{
                        td1.innerHTML = document.getElementById('userlist').innerHTML; td1.querySelector("#user_link>option[value='"+user_id+"']").selected = true;
                        td2.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\""+key+"\" list=\"keyParams\">";
                        td3.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\""+value+"\" style='border: 1px solid #ff0000;'>";
                        td3.innerHTML += "<center style='color:red;'>Данное значение уже используется</center>";
                        td4.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\""+url+"\">";
                        td5.innerHTML = "<a class=\"btn btn-danger\" onclick=\"delete_row_link(this);\" style=\"margin-left:15px; color:#ffffff;\"><i class=\"ik ik-delete\" style=\"margin-right: 0 !important;\"></i></a>";
                        td6.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\""+label+"\" list=\"labelList\">";
                    }
                }

                function delete_row_link(element, linkid = -1){
                    if(confirm('Удалить?')) {
                        if(element.closest("[name=new_link_line]") !== null) element.closest("[name=new_link_line]").remove();
                        if(element.closest("[name=link_line]") !== null){
                            element.closest("[name=link_line]").remove();
                            document.location.href = '/linkcountries/deletelink?id='+linkid;
                        }
                    }
                }

                function edit_row_link(element){
                    let row = element.closest("[name=link_line]");

                    let user = row.querySelector("td[name=user]");

                    user.innerHTML = document.getElementById('userlist').innerHTML;
                    user.querySelector("#user_link>option[value='"+user.getAttribute("iduser")+"']").selected = true;

                    if(<?=$isAccessEdit ? 1:0;?>){
                        row.querySelector("td[name=is_main] [type=radio]").removeAttribute("disabled");
                    }

                    let key = row.querySelector("td[name=key]");
                    key.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\""+key.innerText+"\" list=\"keyParams\">";

                    let value = row.querySelector("td[name=value]");
                    value.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\""+value.innerText+"\">";

                    let url = row.querySelector("td[name=url]");
                    url.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\""+url.innerText+"\">"

                    let label = row.querySelector("td[name=label]");
                    if(label.innerText == "<?=Yii::t('app', 'undefined');?>"){
                        label.innerText = "";
                    }
                    label.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\""+label.innerText+"\" list=\"labelList\">"
                    //element.remove();
                    //btn btn-light
                    element.disabled = true;
                    element.setAttribute("class", "btn btn-light");
                    element.setAttribute("style", "margin-left: 15px; color: #929294;");
                    element.setAttribute("onClick", "");

                }

            </script>
        </div>
    <?php } ?>

    <br>
    <a class="btn btn-success" onClick="save();" style="color:white;"><?=Yii::t('app', 'save');?></a>

    <?php if (isset($_GET['id'])) { ?>
        <a class="btn btn-primary" onClick="add_new_link();"
           style="margin-left:15px; color:#ffffff;"><?= Yii::t('app', 'add_link'); ?></a>
    <?php } ?>
    <?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success', 'style' => 'visibility: hidden;']) ?>

   
    <?php ActiveForm::end(); ?>

</div>


<datalist id="keyParams">
    <option value="camp_name"></option>
</datalist>


<script>
    var countInputDeep = 0;

    function addNewLineDeeplink(key, deeplinkName, deeplinkUrl) {
        if (!deeplinkName)
            deeplinkName = '';

        if(!deeplinkUrl)
            deeplinkUrl = '';

        if (!key)
            key = '';

        countInputDeep = countInputDeep + 1;
        var element = document.createElement('div');
        element.classList.add("item_deep");

        var options = "";


        //var selected = '<select class="form-control" style="width:200px;" name="deeplink_line_key' + countInput + '" value="' + key + '">'+options+'</select>';
        var inner = '<div style="display:flex;">' +
            '<div style="padding-bottom:10px;display:flex; align-items: center;">' +
            '<input type="text" class="form-control" style="width:200px;" id="deep_key" name="deeplink_line_key'+countInputDeep+'" value="'+key+'" placeholder="<?=Yii::t('app', 'key');?>"> ' +
            '&nbsp;&nbsp;=&nbsp;&nbsp;' +
            '<input type="text" class="form-control" style="width:400px;" id="deep_value" name="deeplink_line_value'+countInputDeep+'" value="'+deeplinkName+'" placeholder="<?=Yii::t('app', 'value');?>">' +
            '&nbsp;&nbsp;=&nbsp;&nbsp;' +
            '<input type="text" class="form-control" style="width:400px;" id="deep_link" name="deeplink_line_url'+countInputDeep+'" value="'+deeplinkUrl+'" placeholder="<?=Yii::t('app', 'url');?>">' +
            '&nbsp;&nbsp;' +
            '<a class="btn_show_deep" style="cursor:pointer; color:blue;">[<?=Yii::t('app', 'show_deeplink');?>]</a>' +
            '&nbsp;&nbsp;' +
            '<a class="btn_delete_deep" style="cursor:pointer; color:red;">[<?=Yii::t('app', 'delete');?>]</a>' +
            '</div>' +
            '</div>';
        /*
        var inner = '<div style="display:flex;">' +
            '<div style="padding-bottom:10px;display:flex; align-items: center;">' +
            selected +
            '&nbsp;&nbsp;=&nbsp;&nbsp;<input class="form-control" type="text" style="width:400px;" name="deeplink_line_value' + countInput + '" value="' + offerName + '">' +
            '&nbsp;&nbsp;<a class="btn_delete" style="cursor:pointer;"><?=Yii::t('app', 'delete');?></a>' +
            '</div>' +
            '</div>';
            */
        element.innerHTML = inner;

        //element.querySelector(".btn_delete_deep").addEventListener('click', deleteLineDeeplink, false);
        //element.querySelector(".btn_show_deep").addEventListener('click', showLineDeeplink, false);
        //document.querySelector("#deeplinkList").appendChild(element);
    }

    function showLineDeeplink(item) {
        item = item.closest("tr[name=link_line]");
        let deepKey = "";
        let deepValue = "";
        let deepLink = "";
        if(item.querySelector("td[name=key] input") !== null) {
            deepKey = item.querySelector("td[name=key] input").value;
            deepValue = item.querySelector("td[name=value] input").value;
            deepLink = item.querySelector("td[name=url] input").value;
        }else{
            deepKey = item.querySelector("td[name=key]").innerHTML;
            deepValue = item.querySelector("td[name=value]").innerHTML;
            deepLink = item.querySelector("td[name=url]").innerHTML;
        }



        if(deepKey.length <= 0 || deepValue.length <= 0 || deepLink <= 0){
            alert("<?=Yii::t('app', 'field_required');?>");
            return;
        }

        if(deepKey === 'camp_name') {
            $('#campaign_name').show();
        } else {
            $('#campaign_name').hide();
        }

        if((deepKey+deepValue).indexOf("?") !== -1
            || (deepKey+deepValue).indexOf("&") !== -1
            || (deepKey+deepValue).indexOf("=") !== -1
            || (deepKey+deepValue).indexOf("http") !== -1
            || (deepKey+deepValue).indexOf("?") !== -1
            || (deepKey+deepValue).indexOf(":") !== -1
            || (deepKey+deepValue).indexOf("/") !== -1
            || (deepKey+deepValue).indexOf(" ") !== -1
        ){
            alert("<?=Yii::t('app', 'block_deep');?>");
            return;
        }

        let genDeeplink = "app://"+deepKey+"="+deepValue;

        for(var i=1; i<=10; i++){
            if(deepLink.indexOf("{sub_"+i+"}") > 0){
                if(genDeeplink.length <= 0){
                    genDeeplink = "app://sub_"+i+"=ЗНАЧЕНИЕ";
                }else{
                    genDeeplink += "&sub_"+i+"=ЗНАЧЕНИЕ";
                }
            }
        }

        var link = deepLink;
        var arr = link.match(/{sub_(\d+)}/g);
        var result = "";
        let check = false;
        let separator = "/";
        if(arr) {
            for (let i = 20; i > 0; i--) {
                let value = '0';
                for (let a = 0; a < arr.length; a++) {
                    if (arr[a].match(/\d+/) == '' + i) {
                        check = true;
                        arr.splice(a, 1);
                        value = 'SUB' + i;
                        break;
                    }
                }
                if (check) {
                    result = value + separator + result;
                }
            }
            let wow = new RegExp('\\' + separator + '$');
            resultCampaignName = result.replace(wow, "");
            document.querySelector("#show_curr_campaign_name").value = deepValue+separator+resultCampaignName;
        }else{
            document.querySelector("#show_curr_campaign_name").value = deepValue;
        }

        document.querySelector("#show_curr_link_deep").value = deepLink;
        document.querySelector("#show_curr_deeplink").value = genDeeplink;
        $('#btn_open_invoice_payout').click();
    }

</script>




<?php if (isset($offerList)) foreach ($offerList as $key => $value) { ?>
    <script>addNewLineOffer("<?=$key;?>", "<?=$value;?>");</script>
<?php } ?>

<?php if (isset($deeplinkList)) foreach ($deeplinkList as $key => $value) {

    foreach($value as $k2 => $v2){
        ?>
        <script>addNewLineDeeplink("<?=$key;?>", "<?=$k2;?>", "<?=$v2;?>");</script>
    <?php }} ?>



<script>
    "use strict";
    $(document).ready(function() {
        //$(".select2").select2();
    });

    let cacheURL = null;
    let cacheDeeplink = null;
    let cacheCampaignName = null;

    function changeValueSUB(){
        if(cacheURL == null) cacheURL = document.querySelector("#show_curr_link_deep").value;
        if(cacheDeeplink == null) cacheDeeplink = document.querySelector("#show_curr_deeplink").value;
        if(cacheCampaignName == null) cacheCampaignName = document.querySelector("#show_curr_campaign_name").value;

        let subInputList = [];
        document.querySelector("#show_curr_deeplink").value = cacheDeeplink;
        document.querySelector("#show_curr_campaign_name").value = cacheCampaignName;

        for(let i=0; i<6; i++){
            subInputList[i] = document.querySelector("#sub_"+(i+1)+"_deep").value;
            if(subInputList[i].length <= 0)
                subInputList[i] = "ЗНАЧЕНИЕ";
            document.querySelector("#show_curr_deeplink").value = document.querySelector("#show_curr_deeplink").value.replace("sub_"+(i+1)+"=ЗНАЧЕНИЕ", "sub_"+(i+1)+"="+subInputList[i]);

            if(subInputList[i] == "ЗНАЧЕНИЕ")
                subInputList[i] = "SUB"+(i+1);
            document.querySelector("#show_curr_campaign_name").value =  document.querySelector("#show_curr_campaign_name").value.replace("SUB"+(i+1), subInputList[i]);
        }
    }
</script>

<button id="btn_open_invoice_payout" type="button" class="btn btn-secondary" data-toggle="modal"
        data-target="#invoice_payout" style="display: none;">Show deeplink
</button>
<div class="modal fade" id="invoice_payout" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongLabel"
     aria-hidden="true">
    <div class="modal-dialog" id="invoice_payout_modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title_share_add_transactions"><?=Yii::t('app', 'show_deeplink');?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-12">
                        <table>
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <?=Yii::t('app', 'SUB 1');?>:
                                        <input type="text" id="sub_1_deep" onkeyup="changeValueSUB()" style="width:70%" placeholder="Значение">
                                    </div>
                                    <div class="form-group">
                                        <?=Yii::t('app', 'SUB 2');?>:
                                        <input type="text" id="sub_2_deep" onkeyup="changeValueSUB()" style="width:70%" placeholder="Значение">
                                    </div>
                                    <div class="form-group">
                                        <?=Yii::t('app', 'SUB 3');?>:
                                        <input type="text" id="sub_3_deep" onkeyup="changeValueSUB()" style="width:70%" placeholder="Значение">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <?=Yii::t('app', 'SUB 4');?>:
                                        <input type="text" id="sub_4_deep" onkeyup="changeValueSUB()" style="width:70%" placeholder="Значение">
                                    </div>
                                    <div class="form-group">
                                        <?=Yii::t('app', 'SUB 5');?>:
                                        <input type="text" id="sub_5_deep" onkeyup="changeValueSUB()" style="width:70%" placeholder="Значение">
                                    </div>
                                    <div class="form-group">
                                        <?=Yii::t('app', 'SUB 6');?>:
                                        <input type="text" id="sub_6_deep" onkeyup="changeValueSUB()" style="width:70%" placeholder="Значение">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <form class="forms-sample">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?=Yii::t('app', 'your_link');?>:
                                <input type="text" id="show_curr_link_deep" style="width:100%" value="Не задано" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="interval_date_block">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?=Yii::t('app', 'your_depplink');?>:
                                <input type="text" id="show_curr_deeplink" style="width:100%" value="Не задано" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="campaign_name">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?=Yii::t('app', 'your_campaign_name');?>:
                                <input type="text" id="show_curr_campaign_name" style="width:100%" value="Не задано" disabled>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div id="preloader_preview_invoice" style="display:none;">
                    <div class="parent" style="width:50px; height:30px;">
                        <div id="xLoader">
                            <div class="audio-wave" style="bottom: 50%;">
                                <span></span><span></span><span></span><span></span><span></span></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close_invoice">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    $(window).keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
</script>
<!--<script src="/theme/js/form-advanced.js"></script>-->
