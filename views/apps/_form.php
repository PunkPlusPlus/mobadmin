<?php

use app\basic\ApiHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\basic\debugHelper;
use app\models\Zones;
use app\models\Linkcountries;
use webvimark\modules\UserManagement\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\Apps */
/* @var $form yii\widgets\ActiveForm */
if (User::hasPermission('edit_all_apps')) {
    $allUsers = User::find()->all();
    $listUsers[-1] = Yii::t('app', 'all_users');
    foreach ($allUsers as $user) {
        $listUsers[$user->id] = $user->display_name;
    }
}
$listUsers[User::getCurrentUser()->id] = User::getCurrentUser()->display_name;

if (User::hasPermission('edit_all_apps')) {
    $allUsers = User::find()->all();
    foreach ($allUsers as $user) {
        //debugHelper::print($user->roles[0]->name);
        try {
            if ($user->roles[0]->name == "Developer") {
                $listDeveloper[$user->id] = $user->display_name;
            }
        }catch (\Exception $e){

        }
    }
}


$zones = Zones::find()
    ->all();

$listCountry = [
    'all' => Yii::t('app', 'all_country')
];


foreach ($zones as $zone) {
    $listCountry[$zone->zone] = "" . strtoupper($zone->zone) . " - " . $zone->country;
}
$listCountry['-1'] = "Naming link";


$linkCountries = Linkcountries::find()
    ->where(['=', 'app_id', $model->id])
    ->andWhere(['archived' => 0])
    ->all();

$statuses = [
    '-1' => Yii::t('app', 'banned'),
    '0' => Yii::t('app', 'no_published'),
    '1' => Yii::t('app', 'published'),
    '2' => Yii::t('app', 'pending'),
    '3' => Yii::t('app', 'testing'),
    '4' => Yii::t('app', 'ready'),
    '5' => Yii::t('app', 'revision'),
];
?>

<style>
    .jsonfy {display: grid;}
    pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
    .string { color: green; }
    .number { color: darkorange; }
    .boolean { color: blue; }
    .null { color: magenta; }
    .key { color: red; }

    .jsonCode {
        background: #f4f4f4;
        border: 1px solid #ddd;
        border-left: 3px solid #f36d33;
        color: #666;
        page-break-inside: avoid;
        font-family: monospace;
        overflow: auto;
        width: 500px;
        display: block;
        word-wrap: break-word;
    }
</style>

<?php $form = ActiveForm::begin([
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"form-line\">\n{input}</div>\n{hint}\n{error}",
    ],
]); ?>
<div class="row">
    <?php $col_class = User::hasRole('Admin') ? 'col-md-4' : 'col-md-6'; ?>
    <div class="<?=$col_class?>">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => Yii::t('app', 'name')]) ?>
    </div>
    <div class="<?=$col_class?>">
        <?= $form->field($model, 'package')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'package')]) ?>
    </div>

    
        <div class="<?=$col_class?>">
             <?php
            if ($model->published == 4) {
                $rev = false;
                $no_pub = true;
                $testing = true;
            } else {
                $rev = true;
                $no_pub = false;
                $testing = false;
            }
            if ($model->published == 5) {
                $rev = true;
            }

            ?>
            <?php
             if (User::hasPermission('change_statuses')) {
             ?>
            <?= $form->field($model, 'published')
                ->dropDownList(
                    $statuses,
                    [
                        'class' => 'form-control select2 fabienpof',
                        'options' => [
                            '1' => ['disabled' => true],
                            '2' => ['disabled' => true],
                            '4' => ['disabled' => true],
                            '5' => ['disabled' => $rev],
                            '0' => ['disabled' => $no_pub],
                            '3' => ['disabled' => $testing]
                        ]
                    ]
                ); ?>
                 <?php
             }
 ?>

        </div>

    <?php
    if ($model->published != 3){
        $premission = false;
    } else {
        $premission = true;
    }
    if (User::hasPermission('change_statuses')) {
    ?>

        <div class="<?=$col_class?>">
            <br>
            <button type="submit" <?php if (!$premission) echo 'disabled'; ?> name="ready" onclick="question();" value="ready" class="<?php if(!$premission) {echo 'btn-secondary';} else echo 'btn-success'  ?> btn-lg">Готово к проливу</button>
            <p></p>
        </div>

        <?php
        }
 ?>

        <script>
            function question() {
                if (confirm('Are you shure?')) {

                } else {
                    event.preventDefault();
                }
            }
        </script>
   
</div>

<div class="row">
    <div class="col-md-3">
        <?= $form->field($model, 'created_code_user_id')
            ->dropDownList(
                $listDeveloper,
                [
                    'prompt' => Yii::t('app', 'created_code_user_id').'...',
                    'class' => 'form-control select2 fabienpof'
                ]
            ); ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'builder_code_user_id')
            ->dropDownList(
                $listDeveloper,
                [
                    'prompt' => Yii::t('app', 'builder_code_user_id').'...',
                    'class' => 'form-control select2 fabienpof'
                ]
            ); ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'file')->fileInput(['class' => '']) ?>
        <?= $model->apk ?? '' ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'keystore_file')->fileInput(['class' => '']) ?>
        <?= $model->keystore ?? '' ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?= $form->field($model, 'github')->textInput() ?>
    </div>
</div>

<div class="<?=$col_class?>">
    <?= $form->field($model, 'appsflyer')->textInput(['maxlength' => true, 'placeholder' => 'Appsflyer key']) ?>
</div>

<div class="<?=$col_class?>">
    <?= $form->field($model, 'fb_app_id')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'fb_app_id')]) ?>
</div>
<div class="<?=$col_class?>">
    <?= $form->field($model, 'app_secret')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'app_secret')]) ?>
</div>
<div class="<?= $col_class ?>">
     <?= $form->field($model, 'uuid')->textInput(['maxlength' => true, 'placeholder' => "Uiid"]) ?>
</div>


<?= $form->field($model, 'note')->textarea(['rows' => 5, 'placeholder' => 'Здесь ты можешь оставить любые заметки']) ?>


<input type="text" style="display:none;" name="prices">
<input type="text" style="display:none;" name="params">

<style>
    #table_params, #table_prices{
        white-space:nowrap;
    }
    #table_params tbody tr>td,
    #table_prices tbody tr>td {
        padding: 5px .7rem;
    }
    #table_params input,
    #table_prices input{
        margin: 5px 0;
    }
</style>

<?php if (isset($_GET['id'])) { ?>
    <h5>
        Параметры:
        <a class="btn btn-link" onClick="add_new_param();" style="color:blue;"><?= Yii::t('app', 'add_params'); ?></a>
    </h5>
    <table class="table table-striped table-bordered" id="table_params">
        <tr id="table_param_row_1">
            <td style="width:20%;"><?= Yii::t('app', 'user'); ?></td>
            <td><?= Yii::t('app', 'country'); ?></td>
            <td><?= Yii::t('app', 'key'); ?></td>
            <td><?= Yii::t('app', 'value'); ?></td>
            <td><?= Yii::t('app', 'access_level'); ?></td>
            <td>Для бота</td>
            <td><?= Yii::t('app', 'actions'); ?></td>
        </tr>
        <?php
        $paramArr = [];
        foreach ($params as $param) {
            $countPos = count($paramArr[$param->user_id] ?? []);
            $paramArr[$param->user_id][$countPos] = [
                "id" => $param->id,
                "user_id" => $param->user_id,
                "key" => $param->key,
                "value" => $param->value,
                "linkcountry_id" => $param->linkcountry_id,
                "country_code" => $param->countries->country_code ?? '',
                "access_level" => $param->access_level,
                "is_for_bot" => $param->is_for_bot,
            ];
        }
        ?>

        <?php
        foreach($paramArr as $user){
            foreach($user as $param){
                ?>
                <tr name="param_line">
                    <td name="param_id" style="display:none;"><?= $param['id']; ?></td>
                    <td name="user" iduser="<?= $param['user_id']; ?>"><?= $listUsers[$param['user_id']]; ?></td>
                    <?php
                    $allLinkCountry = [];
                    $allLinkCountry[-1] = Yii::t('app', 'all_country');
                    foreach ($linkCountries as $countryLink) {
                        foreach ($listCountry as $key => $value) {
                            if ($key === $countryLink['country_code']) {
                                $allLinkCountry[$countryLink['id']] = $value;
                            }
                        }
                    }
                    try {
                        if ($allLinkCountry[$param['linkcountry_id']] == "Все страны") {
                            print '<td name="country_id" idcountry="-1">';
                            print '<img src="/assets/all.png">';
                        } else {
                            print '<td name="country_id" idcountry="' . $param['linkcountry_id'] . '">';
                            print '<img src="' . Yii::$app->runAction('media/getflag', ['country_code' => $param['country_code']]) . '">';
                        }
                    }catch (\Exception $e){
                        print '<td name="country_id" idcountry="-1">';
                    }

                    ?>
                    </td>
                    <td name="key"><?= $param['key']; ?></td>

                    <td name="value" data-json="<?=ApiHelper::isJSON($param['value']) ? 1 : 0?>">
                        <?php if(ApiHelper::isJSON($param['value'])) { ?>
                            <div class="jsonfy"><pre><?= $param['value']; ?></pre></div>
                        <?php } else { ?>
                            <p style="overflow-x: scroll; overflow: hidden; width:100px; white-space: nowrap; margin: 0; "><?= $param['value']; ?></p>
                        <?php } ?>
                    </td>
                    <td name="access_level" idaccesslevel="<?= $param['access_level']; ?>">Только разработчики</td>
                    <td name="is_for_bot" idisforbot="<?= $param['is_for_bot']; ?>"><?= $param['is_for_bot'] ? 'Да' : 'Нет'; ?></td>
                    <td>
                        <a class="btn btn-primary" onclick="edit_row_param(this);"
                           style="color:#ffffff;"><?= Yii::t('app', 'edit'); ?></a>
                        <a class="btn btn-danger" onclick="delete_row_param(this, <?= $param['id']; ?>);"
                           style="margin-left:15px; color:#ffffff;"><?= Yii::t('app', 'delete'); ?></a>
                    </td>
                </tr>
            <?php }} ?>
    </table>

    <h5>
        Стоимость инсталлов:
        <?php if (isset($_GET['id'])) { ?>
            <a class="btn btn-link" onClick="add_new_price();"
               style="color:blue;"><?= Yii::t('app', 'set_price'); ?></a>
        <?php } ?>
    </h5>
    <table class="table table-striped table-bordered" id="table_prices">
        <tr id="table_price_row_1">
            <td style="width:20%;"><?= Yii::t('app', 'user'); ?></td>
            <td><?= Yii::t('app', 'country'); ?></td>
            <td><?= Yii::t('app', 'price'); ?></td>
            <td><?= Yii::t('app', 'actions'); ?></td>
        </tr>
        <?php foreach ($prices as $price) { ?>
            <tr name="price_line">
                <td name="price_id" style="display:none;"><?= $price->id; ?></td>
                <td name="user" iduser="<?= $price->user_id; ?>"><?= $listUsers[$price->user_id]; ?></td>
                <td name="country"
                    idcountry="<?= $price->country_code; ?>"><?= $listCountry[$price->country_code]; ?></td>
                <td name="price"><?= $price->price; ?></td>
                <td>
                    <a class="btn btn-primary" onclick="edit_row_price(this);"
                       style="margin-left:15px; color:#ffffff;"><?= Yii::t('app', 'edit'); ?></a>
                    <a class="btn btn-danger" onclick="delete_row_price(this, <?= $price->id; ?>);"
                       style="margin-left:15px; color:#ffffff;"><?= Yii::t('app', 'delete'); ?></a>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>


<script>
    function save() {

        //сохранение цен
        var prices = [];

        var lines = document.getElementsByName("new_price_line");
        lines.forEach(function (item, i, arr) {
            if (item.querySelector("[name=user] select") !== null) {
                prices[prices.length] = {
                    'user_id': item.querySelector("[name=user] select").value,
                    'country_code': item.querySelector("[name=country] select").value,
                    'price': item.querySelector("[name=price] input").value
                };
            }
        });

        //изменения для старых полей
        lines = document.getElementsByName("price_line");
        lines.forEach(function (item, i, arr) {
            if (item.querySelector("[name=user] select") !== null) {
                prices[prices.length] = {
                    'price_id': item.querySelector("[name=price_id]").innerHTML,
                    'user_id': item.querySelector("[name=user] select").value,
                    'country_code': item.querySelector("[name=country] select").value,
                    'price': item.querySelector("[name=price] input").value
                };
            }
        });

        document.getElementsByName("prices")[0].value = JSON.stringify(prices);
        //end сохранение цен

        //сохранение параметров
        var params = [];

        var lines = document.getElementsByName("new_param_line");
        lines.forEach(function (item, i, arr) {
            if (item.querySelector("[name=user] select") !== null) {
                params[params.length] = {
                    'user_id': item.querySelector("[name=user] select").value,
                    'country_id': item.querySelector("[name=country_id] select").value,
                    'key': item.querySelector("[name=key] input").value,
                    'value': item.querySelector("[name=value] .param-value").value,
                    'access_level': item.querySelector("[name=access_level] select").value,
                    'is_for_bot': item.querySelector("[name=is_for_bot] select").value,
                };
            }
        });
        console.log(lines);
        //изменения для старых полей
        lines = document.getElementsByName("param_line");
        lines.forEach(function (item, i, arr) {
            if (item.querySelector("[name=user] select") !== null) {
                params[params.length] = {
                    'user_id': item.querySelector("[name=user] select").value,
                    'param_id': item.querySelector("[name=param_id]").innerHTML,
                    'country_id': item.querySelector("[name=country_id] select").value,
                    'key': item.querySelector("[name=key] input").value,
                    'value': item.querySelector("[name=value] .param-value").value,
                    'access_level': item.querySelector("[name=access_level] select").value,
                    'is_for_bot': item.querySelector("[name=is_for_bot] select").value,
                };
            }
        });

        document.getElementsByName("params")[0].value = JSON.stringify(params);
        //end сохранение параметров


        let form = document.getElementById('w0');
        form.submit();
    }
</script>

<datalist id="dataParams">
    <option value="one_signal_key"></option>
    <option value="dont_save_link"></option>
    <option value="logger"></option>
    <option value="app_metrica_key"></option>
    <option value="user_agent"></option>
    <option value="target_browser"></option>
    <option value="branch_active"></option>
    <option value="apps_flyer_key"></option>
    <option value="activate_send_conversion_data"></option>
    <option value="fb_app_id"></option>
    <option value="branch_key"></option>
</datalist>



<br>
<a class="btn btn-success" onClick="save();" style="color:white;"><?= Yii::t('app', 'save'); ?></a>
<?= Html::submitButton(Yii::t('app', 'save'), ['class' => 'btn btn-success', 'style' => 'visibility: hidden;']) ?>



<?php ActiveForm::end(); ?>

<div id="userlist" style="display:none;">
    <select id="user_price" class="form-control">
        <?php foreach ($listUsers as $key => $value) { ?>
            <option value="<?= $key; ?>"><?= $value; ?></option>
        <?php } ?>
    </select>
</div>

<div id="countryList" style="display:none;">
    <select id="country_price" class="form-control">
        <?php foreach ($listCountry as $key => $value) { ?>
            <option value="<?= $key; ?>"><?= $value; ?></option>
        <?php } ?>
    </select>
</div>

<div id="countryIdList" style="display:none;">
    <select id="country_param" class="form-control">
        <?php
        print '<option value="-1">' . Yii::t('app', 'all_country') . '</option>';
        foreach ($linkCountries as $countryLink) {
            foreach ($listCountry as $key => $value) {
                if ($key === $countryLink['country_code'] && $value != "Все страны") {
                    print '<option value="' . $countryLink['id'] . '">' . $value . '</option>';
                }
            }
        }
        ?>
    </select>
</div>


<div id="accessLevelList" style="display:none;">
    <select id="accesslevel_param" class="form-control">
        <option value="0">Только разработчики</option>
    </select>
</div>

<div id="isForBotList" style="display:none;">
    <select id="isforbot_param" class="form-control">
        <option value="0">Нет</option>
        <option value="1">Да</option>
    </select>
</div>

<script>
    function add_new_price(user_id = -1, country_code = -1, price = -1) {
        var table = document.getElementById('table_prices');

        var row = document.createElement("TR");
        row.setAttribute("name", "new_price_line");
        table.appendChild(row);
        //document.getElementsByName("")

        var td1 = document.createElement("TD");
        td1.setAttribute("name", "user");
        var td2 = document.createElement("TD");
        td2.setAttribute("name", "country");
        var td3 = document.createElement("TD");
        td3.setAttribute("name", "price");
        var td4 = document.createElement("TD");

        row.appendChild(td1);
        row.appendChild(td2);
        row.appendChild(td3);
        row.appendChild(td4);
        if (user_id == -1) {
            td1.innerHTML = document.getElementById('userlist').innerHTML;
            td2.innerHTML = document.getElementById('countryList').innerHTML;
            td3.innerHTML = "<input type='text' placeholder='...' class=\"form-control\">";
            td4.innerHTML = "<a class=\"btn btn-danger\" onclick=\"delete_row_price(this);\" style=\"margin-left:15px; color:#ffffff;\">Удалить</a>";
        } else {
            td1.innerHTML = document.getElementById('userlist').innerHTML;
            td1.querySelector("#user_price>option[value='" + user_id + "']").selected = true;
            td2.innerHTML = document.getElementById('countryList').innerHTML;
            td1.querySelector("#country_price>option[value='" + user_id + "']").selected = true;
            td3.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\"" + value + "\" style='border: 1px solid #ff0000;'>";
            td3.innerHTML += "<center style='color:red;'>Данное значение уже используется</center>";
            td4.innerHTML = "<a class=\"btn btn-danger\" onclick=\"delete_row_price(this);\" style=\"margin-left:15px; color:#ffffff;\">Удалить</a>";
        }
    }


    function delete_row_price(element, priceid = -1) {
        if (element.closest("[name=new_price_line]") !== null) element.closest("[name=new_price_line]").remove();
        if (element.closest("[name=price_line]") !== null) {
            element.closest("[name=price_line]").remove();
            document.location.href = '/apps/deleteprice?id=' + priceid;
        }
    }


    function edit_row_price(element) {
        let row = element.closest("[name=price_line]");

        let user = row.querySelector("td[name=user]");

        user.innerHTML = document.getElementById('userlist').innerHTML;
        user.querySelector("#user_price>option[value='" + user.getAttribute("iduser") + "']").selected = true;

        let country = row.querySelector("td[name=country]");
        country.innerHTML = document.getElementById('countryList').innerHTML;
        country.querySelector("#country_price>option[value='" + country.getAttribute("idcountry") + "']").selected = true;

        let price = row.querySelector("td[name=price]");
        price.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" value=\"" + price.innerText + "\">";

        //element.remove();
        //btn btn-light
        element.disabled = true;
        element.setAttribute("class", "btn btn-light");
        element.setAttribute("style", "margin-left: 15px; color: #929294;");
        element.setAttribute("onClick", "");

    }

    function add_new_param(user_id = -1, countryId = -1, access_level = -1) {
        var table = document.getElementById('table_params');

        var row = document.createElement("TR");
        row.setAttribute("name", "new_param_line");
        table.appendChild(row);
        //document.getElementsByName("")

        var td1 = document.createElement("TD");
        td1.setAttribute("name", "user");
        var td2 = document.createElement("TD");
        td2.setAttribute("name", "country_id");
        var td3 = document.createElement("TD");
        td3.setAttribute("name", "key");
        var td4 = document.createElement("TD");
        td4.setAttribute("name", "value");
        var td5 = document.createElement("TD");
        td5.setAttribute("name", "access_level");
        var td6 = document.createElement("TD");
        td6.setAttribute("name", "is_for_bot");
        var td7 = document.createElement("TD");


        row.appendChild(td1);
        row.appendChild(td2);
        row.appendChild(td3);
        row.appendChild(td4);
        row.appendChild(td5);
        row.appendChild(td6);
        row.appendChild(td7);

        if (user_id == -1) {
            td1.innerHTML = document.getElementById('userlist').innerHTML;
            td2.innerHTML = document.getElementById('countryIdList').innerHTML;
            td3.innerHTML = "<input type='text' placeholder='...' class=\"form-control\"  list=\"dataParams\">";
            td4.innerHTML = "<input type='text' placeholder='...' class=\"form-control param-value\">";
            td5.innerHTML = document.getElementById('accessLevelList').innerHTML;
            td6.innerHTML = document.getElementById('isForBotList').innerHTML;
            td7.innerHTML = "<a class=\"btn btn-danger\" onclick=\"delete_row_param(this);\" style=\"margin-left:15px; color:#ffffff;\">Удалить</a>";
        } else {
            td1.innerHTML = document.getElementById('userlist').innerHTML;
            td1.querySelector("#user_price>option[value='" + user_id + "']").selected = true;
            td2.innerHTML = document.getElementById('countryIdList').innerHTML;
            td2.querySelector("#country_param>option[value='" + user_id + "']").selected = true;
            td3.innerHTML = "<input type='text' placeholder='...' class=\"form-control\"  list=\"dataParams\" value=\"" + value + "\" style='border: 1px solid #ff0000;'>";
            td4.innerHTML = "<input type='text' placeholder='...' class=\"form-control param-value\" value=\"" + value + "\" style='border: 1px solid #ff0000;'>";
            td5.innerHTML = document.getElementById('accessLevelList').innerHTML;
            td5.querySelector("#accesslevel_param>option[value='" + access_level + "']").selected = true;
            td6.innerHTML = document.getElementById('isForBotList').innerHTML;
            td7.innerHTML = "<a class=\"btn btn-danger\" onclick=\"delete_row_param(this);\" style=\"margin-left:15px; color:#ffffff;\">Удалить</a>";
        }
    }


    function delete_row_param(element, priceid = -1) {
        if (element.closest("[name=new_param_line]") !== null) element.closest("[name=new_param_line]").remove();
        if (element.closest("[name=param_line]") !== null) {
            element.closest("[name=param_line]").remove();
            document.location.href = '/apps/deleteparam?id=' + priceid;
        }
    }

    function edit_row_param(element) {
        let row = element.closest("[name=param_line]");

        let user = row.querySelector("td[name=user]");

        user.innerHTML = document.getElementById('userlist').innerHTML;
        user.querySelector("#user_price>option[value='" + user.getAttribute("iduser") + "']").selected = true;

        let country = row.querySelector("td[name=country_id]");
        country.innerHTML = document.getElementById('countryIdList').innerHTML;
        country.querySelector("#country_param>option[value='" + country.getAttribute("idcountry") + "']").selected = true;

        let key = row.querySelector("td[name=key]");
        key.innerHTML = "<input type='text' placeholder='...' class=\"form-control\" list=\"dataParams\" value=\"" + key.innerText + "\">";

        let value = row.querySelector("td[name=value]");

        if(parseInt(value.dataset.json)) {
            value.innerHTML = "<textarea placeholder='...' rows='6' class=\"form-control param-value jsonCode\">" + value.innerText + "</textarea>";
        } else {
            value.innerHTML = "<input type='text' placeholder='...' class=\"form-control param-value\" value='" + value.innerText + "'>";
        }


        let access_level = row.querySelector("td[name=access_level]");
        access_level.innerHTML = document.getElementById('accessLevelList').innerHTML;
        access_level.querySelector("#accesslevel_param>option[value='" + access_level.getAttribute("idaccesslevel") + "']").selected = true;

        let is_for_bot = row.querySelector("td[name=is_for_bot]");
        is_for_bot.innerHTML = document.getElementById('isForBotList').innerHTML;
        is_for_bot.querySelector("#isforbot_param>option[value='" + is_for_bot.getAttribute("idisforbot") + "']").selected = true;

        //element.remove();
        //btn btn-light
        element.disabled = true;
        element.setAttribute("class", "btn btn-light");
        element.setAttribute("style", "color: #929294;");
        element.setAttribute("onClick", "");

    }
</script>


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
