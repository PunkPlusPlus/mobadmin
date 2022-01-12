<?php

use app\models\Notifications;

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\RevolutAccounts;
use app\models\Cards;
use app\controllers\NotificationsController;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 */

$this->title = "Профиль партнера";
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<style>
    body{
        font-size: 14px;
    }
    .row .col-sm-6.text-right div.form-inline.pull-right{
        justify-content: flex-end;
    }
    .row .col-sm-4.text-right div.form-inline{
        justify-content: flex-end;
    }
    .help-block {
        font-size: 12px !important;
        color: #555;
    }
</style>

<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-file-text bg-blue"></i>
                    <div class="d-inline">
                        <h5>Профиль партнера</h5>
                        <span><?=Yii::t('app', 'user_profile');?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-6 col-sm-12">
            <div class="widget bg-primary">
                <div class="widget-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="state">
                            <h6><?=$userInfo['email'];?></h6>
                            <h2><?=$userInfo['display_name'];?></h2>
                        </div>
                        <div class="icon">
                            <i class="ik ik-user"></i>
                        </div>
                    </div>
                    <?php
                    try {
                        echo 'Баланс: <b>$' . \app\controllers\BalanceController::get($userInfo['id']) . '</b>';
                    } catch (Exception $e) {
                        echo 'Нет статуса партнера';
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php if(User::hasPermission('change_balance')) : ?>
            <div class="ml-auto col-md-6 col-sm-12 text-right">
                <?=Html::a('Изменить баланс', ['/balance/view', 'id' => $userInfo['id']], ['class' => 'btn btn-success'])?>
            </div>
        <?php endif; ?>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card">
                <ul class="nav nav-pills custom-pills" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active show" id="pills-timeline-tab" data-toggle="pill" href="#current-month" role="tab" aria-controls="pills-timeline" aria-selected="true" style="">Статистика</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#last-month" role="tab" aria-controls="pills-profile" aria-selected="false" style="">Уведомления</a>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade active show" id="current-month" role="tabpanel" aria-labelledby="pills-timeline-tab">
                        <div class="card-body">

                            <div class="row">
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
                            </div>

                            <div class="row clearfix">

                                <table class="table table-striped table-bordered nowrap">
                                    <tr>
                                        <td><b>Apps</b></td>
                                        <td><b>Installs</b></td>
                                        <td><b>Price</b></td>
                                    </tr>

                                    <?php if(isset($partnerData['apps'])) : ?>
                                        <?php foreach($partnerData['apps'] as $app) : ?>
                                            <tr>

                                                <td>
                                                    <?= Html::a($app['name'], ['/apps/view', 'id' => $app['id']], ['style' => 'color:blue']) ?>
                                                </td>
                                                <td>
                                                    <?=$app['installs'] ?? '0'?>
                                                </td>
                                                <td>
                                                    <?=$app['profit'] ? '-$'.$app['profit'] : '0'?>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <tr>
                                        <td><b>Total</b></td>
                                        <td><b><?=$statsData['total_installs'] ? $statsData['total_installs'] : 0 ?></b></td>
                                        <td><b><?=$statsData['total_profit'] ? '-$'.$statsData['total_profit'] : 0?></b></td>
                                    </tr>
                                </table>

                             </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="last-month" role="tabpanel" aria-labelledby="pills-profile-tab">
                        <div class="card-body">
                            <div class="row clearfix">

                                <div class="col-xl-4 col-lg-6 col-md-12">
                                    <!--<span style="font-weight: bold;">--><?//=Yii::t('app', 'notification');?><!--</span>-->
                                    <!--<br><br>-->

                                    <b>Telegram:</b>
                                    <?php
                                    $notififcationModel = new Notifications();
                                    $form = ActiveForm::begin([
                                        'action' => '/notifications/add',
                                        'id' => 'telegram',
                                        'fieldConfig' => [
                                            'template' => "<div class='form-group row'>{label} \n 
                                                                    <div class='col-xl-7'>
                                                                        {input} 
                                                                    </div> \n 
                                                                    {hint} \n 
                                                                    <div class='col-xl-7 offset-xl-5'> 
                                                                        {error} 
                                                                    </div>
                                                                </div>",
                                            'labelOptions' => ['class' => 'col-lg-5 col-form-label'],
                                        ]
                                    ]) ?>
                                    <?= Html::activeHiddenInput($notififcationModel, 'user_id', ['value' => $userInfo['id'], 'id' => 'tg_user_id']) ?>
                                    <?= Html::activeHiddenInput($notififcationModel, 'source_id', ['value' => "2", 'id' => 'tg_source_id']) ?>
                                    <?= $form->field($notififcationModel, 'source_key1')
                                        ->input('text', ['placeholder' => "Чат ID", 'id' => 'tg_chat_id'])
                                        ->label('Чат ID:') ?>
                                    <?= $form->field($notififcationModel, 'app_id')
                                        ->dropDownList($apps, ['id' => 'tg_app'])
                                        ->label('Приложение:') ?>
                                    <?= $form->field($notififcationModel, 'comment')
                                        ->textarea(['placeholder' => "Комментарий", 'id' => 'tg_comment'])
                                        ->label('Комментарий:') ?>

                                    <div class="form-group row">
                                        <div class="col-xl-7 offset-xl-5">
                                            <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
                                        </div>
                                    </div>
                                    <?php ActiveForm::end() ?>

                                </div>

                                <div class="col-xl-8 col-lg-12">
                                    <table class="table table-striped table-bordered">
                                        <tr>
                                            <td><?=Yii::t('app', 'source');?></td>
                                            <td><?=Yii::t('app', 'source_key1');?></td>
                                            <td><?=Yii::t('app', 'app');?></td>
                                            <td><?=Yii::t('app', 'status');?></td>
                                            <td><?=Yii::t('app', 'comment');?></td>
                                            <td><?=Yii::t('app', 'actions');?></td>
                                        </tr>
                                        <?php foreach($notifications as $notify){ ?>
                                            <tr>
                                                <td><?=NotificationsController::$source[$notify['source_id']]?></td>
                                                <td><?=$notify['source_key1']?></td>
                                                <td><?=$notify->app->name ?? 'Все'?></td>
                                                <td><?=$notify['is_main'] ? Yii::t('app', 'main') : Yii::t('app', 'additional') ?></td>
                                                <td><?=$notify['comment']?></td>
                                                <td>
                                                    <a href="<?=\yii\helpers\Url::to(['/notifications/delete', 'id' => $notify['id']])?>" class="btn btn-danger" onclick="return confirm('Вы уверены?')">
                                                        Удалить
                                                    </a>

                                                    <?php
                                                    if($notify['source_id'] === 2 && $notify['is_main'] !== 1) {
                                                        echo Html::a('Сделать основным',
                                                            [
                                                                '/notifications/make-main',
                                                                'id' => $notify['id'],
                                                                'user_id' => $notify['user_id']
                                                            ],
                                                            ['class' => 'btn btn-primary my-1']
                                                        );
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </table>

                                    <p class="text-info">На основной telegram канал будут приходить уведомления о балансе</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--
<div class="col-md-4">
    <b>Email:</b>
    <?php /*
    $form = ActiveForm::begin([
        'action' => '/notifications/add',
        'id' => 'email',
        'fieldConfig' => [
            'template' => "<div class='form-group row'>{label} \n <div class='col-md-8'>{input} </div> \n {hint} \n <div class='col-md-8 offset-md-4'> {error} </div></div>",
            'labelOptions' => ['class' => 'col-md-4 col-form-label'],
        ]
    ]) ?>
    <?= Html::activeHiddenInput($notififcationModel, 'user_id', ['value' => $userInfo['id'], 'id' => 'email_user_id']) ?>
    <?= Html::activeHiddenInput($notififcationModel, 'source_id', ['value' => "4", 'id' => 'email_source_id']) ?>
    <?= $form->field($notififcationModel, 'source_key1')
             ->input('email', ['placeholder' => "mail@gmail.com", 'id' => 'email_source'])
             ->label('Email:') ?>
    <?= $form->field($notififcationModel, 'app_id')
             ->dropDownList($apps, ['id' => 'email_app'])
             ->label('Приложение:') ?>
    <?= $form->field($notififcationModel, 'comment')
             ->textarea(['placeholder' => "Комментарий", 'id' => 'email_comment'])
             ->label('Комментарий:') ?>

    <div class="form-group row">
        <div class="col-md-8 offset-md-4">
            <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); */?>
</div>

<div class="col-md-4">
    <b>SMS:</b>
    <?php /*
    $form = ActiveForm::begin([
        'action' => '/notifications/add',
        'id' => 'sms',
        'fieldConfig' => [
            'template' => "<div class='form-group row'>{label} \n <div class='col-md-8'>{input} </div> \n {hint} \n <div class='col-md-8 offset-md-4'> {error} </div></div>",
            'labelOptions' => ['class' => 'col-md-4 col-form-label'],
        ]
    ]) ?>
    <?= Html::activeHiddenInput($notififcationModel, 'user_id', ['value' => $userInfo['id'], 'id' => 'sms_user_id']) ?>
    <?= Html::activeHiddenInput($notififcationModel, 'source_id', ['value' => "5", 'id' => 'sms_source_id']) ?>
    <?= $form->field($notififcationModel, 'source_key1')
             ->input('text', ['placeholder' => "+7 999 999 99 99", 'id' => 'sms_phone'])
             ->label('Телефон:') ?>
    <?= $form->field($notififcationModel, 'app_id')
             ->dropDownList($apps, ['id' => 'sms_app'])
        ->label('Приложение:') ?>
    <?= $form->field($notififcationModel, 'comment')
             ->textarea(['placeholder' => "Комментарий", 'id' => 'sms_comment'])
             ->label('Комментарий:') ?>

    <div class="form-group row">
        <div class="col-md-8 offset-md-4">
            <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); */ ?>
</div>
-->