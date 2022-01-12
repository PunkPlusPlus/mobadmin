<?php

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\RevolutAccounts;
use app\models\Cards;
use app\models\AppsAccess;
use app\models\Apps;
use app\basic\debugHelper;
use app\controllers\NotificationsController;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 */

$this->title = "Мой профиль";
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
</style>

<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-file-text bg-blue"></i>
                    <div class="d-inline">
                        <h5>Мой профиль</h5>
                        <span><?=Yii::t('app', 'user_profile');?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-right">
                <?php $user_id = isset($_GET['id']) ? $_GET['id'] : User::getCurrentUser()->id; ?>
                <?= Html::a('Профиль партнера', ["/profile", 'partner' => '1', 'id' => $user_id], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-5">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <img src="/images/noicon.png?v=2" class="rounded-circle" width="150">
                        <h4 class="card-title mt-10"><?=$userInfo['display_name'];?></h4>
                        <p class="card-subtitle"><?=$userInfo['email'];?></p>
                    </div>
                </div>
                <hr class="mb-0">
                <div class="card-body">
                    <small class="text-muted d-block">Заработная плата</small>
                    <h6><?= $userProfile['salary']; ?> ₽</h6>
                    <small class="text-muted d-block">Премия</small>
                    <h6><?=$statsInstalls['total_bonus'];?> ₽</h6>
                    <small class="text-muted d-block">Итого</small>
                    <h6><?= $userProfile['salary']+$statsInstalls['total_bonus'] ?> ₽</h6>
                    <hr>
                    <small class="text-muted d-block">Условия</small>
                    <h6><b><?= $userProfile['bonus_factor']*1000 ?> ₽</b> за каждые 1000 установок</h6>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-7">
            <div class="card">
                <ul class="nav nav-pills custom-pills" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active show" id="pills-timeline-tab" data-toggle="pill" href="#current-month" role="tab" aria-controls="pills-timeline" aria-selected="true" style="">Статистика</a>
                    </li>
                    <li class="nav-item">
                       <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#last-month" role="tab" aria-controls="pills-profile" aria-selected="false" style="">История выплат</a>
                    </li>
					
					<?php if (User::hasPermission('BLOCK')) { ?>
                    <li class="nav-item">
                       <a class="nav-link" id="pills-controlprof-tab" data-toggle="pill" href="#controlprof" role="tab" aria-controls="pills-profile" aria-selected="false" style="">Управление</a>
                    </li>
					<?php } ?>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade active show" id="current-month" role="tabpanel" aria-labelledby="pills-timeline-tab">
                        <div class="card-body">
                            <div class="row clearfix">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="widget bg-warning">
                                        <div class="widget-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="state">
                                                    <h6>Премия за текущий месяц</h6>
                                                    <h2><?= $statsInstalls['total_bonus']; ?> ₽</h2>
                                                </div>
                                                <div class="icon">
                                                    <i class="ik ik-award"></i>
                                                </div>
                                            </div>
                                            Период: с <?=$statsInstalls['from']; ?> по <?=$statsInstalls['to']; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="widget bg-primary">
                                        <div class="widget-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="state">
                                                    <h6>Установок за текущий месяц</h6>
                                                    <h2><?= $statsInstalls['total_installs']; ?></h2>
                                                </div>
                                                <div class="icon">
                                                    <i class="ik ik-smartphone"></i>
                                                </div>
                                            </div>
                                            Период: с <?=$statsInstalls['from']; ?> по <?=$statsInstalls['to']; ?>
                                        </div>
                                    </div>
                                </div>
                                <iframe src="https://app.datadoghq.eu/graph/embed?token=0a59097616879cd308fcf078f8dca5630357a894a69f26efe26b59c63cf1c2ec&height=300&width=600&legend=false" width="600" height="300" frameborder="0"></iframe>
                            </div>
                        </div>
						
						
                    </div>
					<?php if (User::hasPermission('BLOCK')) { ?>
                    <div class="tab-pane fade" id="last-month" role="tabpanel" aria-labelledby="pills-profile-tab">
                        <div class="card-body">
								<table class="table table-striped table-bordered">
								<thead>
								<tr>
									<th>Период</th>
									<th>Заработная плата</th>
									<th>Премия</th>
									<th>Условия</th>
								</tr>
								</thead>
								<tr>
									<td> с 01/08/2020 по 31/08/2020</td>
									<td> 30 000 руб.</td>
									<td> 3876 руб.</td>
									<td> 70 ₽ за каждые 1000 установок</td>
								</tr>
								</table>
                        </div>
                    </div>
					<?php } ?>
					
					
					<?php if (User::hasPermission('BLOCK')) { ?>
                    <div class="tab-pane fade" id="controlprof" role="tabpanel" aria-labelledby="pills-controlprof-tab">
                        <a class="btn btn-primary" href="" style="margin:20px;">Подбить итоги по пользователю</a>
                    </div>
					<?php } ?>
					
					
                </div>
            </div>
        </div>
    </div>
</div>




