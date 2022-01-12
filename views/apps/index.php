<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use webvimark\modules\UserManagement\models\User;
use app\basic\debugHelper;
use yii\helpers\ArrayHelper;
use app\controllers\GpscraperController;
use app\models\AuthAssignment;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AppsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'my_app');
$this->params['breadcrumbs'][] = $this->title;
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
                        <h5><?= Yii::t('app', 'my_app'); ?></h5>
                        <span><?= Yii::t('app', 'my_app_desc'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

                <style>
                    .logo_app {
                        width: 45px;
                        height: 45px;
                        display: inline-block;
                        background-size: contain;
                        background-repeat: no-repeat;
                    }
                </style>

                <div style="float:right; padding-bottom: 20px;">
                    <?php if (User::hasPermission('app_add')) { ?>
                        <?= Html::a(Yii::t('app', 'add_app'), ['/apps/create'], ['class' => 'btn btn-success']) ?>
                    <?php } ?>

                    <?php if (User::hasPermission('edit_all_apps')) { ?>
                        <label></label>
                    <?php } ?>
                </div>

                <?php
               $array = [
                    ['id' => '-1', 'name' => Yii::t('app', 'banned')],
                    ['id' => '0', 'name' => Yii::t('app', 'no_published')],
                    ['id' => '1', 'name' => Yii::t('app', 'published')],
                    ['id' => '2', 'name' => Yii::t('app', 'pending')],
                    ['id' => '4', 'name' => Yii::t('app', 'ready')],
                    ['id' => '5', 'name' => Yii::t('app', 'revision')],
                    ['id' => '3', 'name' => Yii::t('app', 'testing')],
                ];

                $listDevelopers = AuthAssignment::find()->select("user_id")->where(["item_name"=>"Developer"])->all();
                //$listDevelopers = ArrayHelper::map($listDevelopers, );
                $devel = [];
                foreach ($listDevelopers as $item){
                    $devel[] = $item["user_id"];
                }

                $developers = \app\models\Users::find()->select("id, display_name")->where(['id' => $devel])->all();
                $develNames = [];
                foreach ($developers as $developer) {
                    $develNames[$developer->id] = $developer->display_name;
                }

                ?>

                <?php if (!User::hasPermission('view_all_apps')) { ?>
                <?= GridView::widget(['dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [//['class' => 'yii\grid\SerialColumn'],
                        'id',
                        ['attribute' => Yii::t('app', 'logo'),
                            'value' => function ($model) {
                                $logoUrl = GpscraperController::getLogo($model->package);
                                $logoImage = "<span class='logo_app' style='background-image: url(" . $logoUrl . "?v=2);'></span>";
                                return $logoImage;
                            },
                            'format' => 'html'],
                        ['attribute' => 'name',
                            'value' => function ($model) {
                                return Html::a($model->name, ['/apps/view?id=' . $model->id], ['class' => 'btn btn-link btn-rounded']);
                            },
                            'format' => 'html'],
                        //'name',
                        'package',
                        ['attribute' => 'published',
                            'value' => function ($model) {
                                switch ($model->published) {
                                    case -1:
                                        return Yii::t('app', 'banned');
                                        break;
                                    case 0:
                                        return Yii::t('app', 'no_published');
                                        break;
                                    case 1:
                                        return Yii::t('app', 'published');
                                        break;
                                    case 3:
                                        return Yii::t('app', 'testing');
                                        break;
                                    case 4:
                                        return Yii::t('app', 'ready');
                                        break;
                                    case 5:
                                        return Yii::t('app', 'revision');
                                        break;
                                }
                            },
                            'format' => 'raw',
                            'filter' => ArrayHelper::map($array, 'id', 'name')],


                        ['attribute' => Yii::t('app', 'link'),
                            'value' => function ($model) {
                                return '<a href="https://play.google.com/store/apps/details?id=' . $model->package . '" style="color:blue;" target="_blank">https://play.google.com/store/apps/details?id=' . $model->package . '</a>';
                            },
                            'format' => 'html'],
                        ['attribute' => Yii::t('app', 'actions'),
                            'value' => function ($model) {
                                return Html::a(Yii::t('app', 'open_app'), ['/apps/view?id=' . $model->id], ['class' => 'btn btn-info']);
                            },
                            'format' => 'html'],],]); ?>
                <?php } else { ?>
                <?= GridView::widget(['dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [//['class' => 'yii\grid\SerialColumn'],
                        'id',
                        [
                            'attribute' => Yii::t('app', 'user'),
                            'value' => function ($model) {
                                $listUser = "";
                                $maxAccess = count($model->linkcountries);
                                $groupUserList = [];

                                foreach ($model->linkcountries as $value) {

                                    foreach ($value->links as $link) {
                                        $userInfo = $link->user;
                                        $userInfoId = $userInfo->id ?? 0;
                                        $userInfoUsername = $userInfo->username ?? "";
                                        $userInfoDisplayName = $userInfo->display_name ?? "";
                                        $is_partner = \app\models\PartnerBalance::find()->where(['partner_id' => $userInfoId])->count();
                                        $is_partner = $is_partner ? '&partner=' . $is_partner : '';

                                        if (!isset($groupUserList[$userInfoId]) && $link->archived == 0 && strripos($userInfoUsername, "ProfitNetwork") === false) {
                                            $maxAccess--;
                                            if (strlen($listUser) > 0)
                                                $listUser .= "<hr>";
                                            $listUser .= Html::a($userInfoDisplayName, ['/profile?id=' . $userInfoId . $is_partner], ['class' => 'btn btn-link btn-rounded']);
                                            $groupUserList[$userInfoId] = 1;
                                        }
                                        //if($maxAccess > 0)
                                        //    $listUser .= ", ";
                                    }
                                }
                                return $listUser;
                            },
                            'format' => 'html',
                            'filter' => Html::dropDownList(
                                "user_id",
                                $_GET['user_id'] ?? "",
                                ArrayHelper::map(User::find()->all(), 'id', 'display_name'),
                                ['prompt' => 'Выберите пользователя', 'class' => 'form-control']
                            )
                        ],

                        [
                            'attribute' => Yii::t('app', 'logo'),
                            'value' => function ($model) {
                                $logoUrl = GpscraperController::getLogo($model->package);
                                if ($logoUrl == "not found") {
                                    $logoUrl = "/images/noicon.png";
                                }
                                $logoImage = "<span class='logo_app' style='background-image: url(" . $logoUrl . "?v=2);'></span>";
                                return $logoImage;
                            },
                            'format' => 'html'
                        ],
                        [
                            'attribute' => 'name',
                            'value' => function ($model) {
                                return Html::a($model->name, ['/apps/view?id=' . $model->id], ['class' => 'btn btn-link btn-rounded']);
                            },
                            'format' => 'html'
                        ],
                        //'name',
                        //                            [
                        //                                'attribute' => Yii::t('app', 'date_last_check'),
                        //                                'value' => function ($model) {
                        //                                    return $model->lastchecked_time;
                        //                                },
                        //                                'format' => 'raw',
                        //                                'filter' => ArrayHelper::map($array, 'id', 'name')
                        //                            ],


                        [
                            'attribute' => 'published',
                            'value' => function ($model) {
                                switch ($model->published) {
                                    case -1:
                                        return Yii::t('app', 'banned');
                                        break;
                                    case 2:
                                        return Yii::t('app', 'pending');
                                        break;
                                    case 0:
                                        return Yii::t('app', 'no_published');
                                        break;
                                    case 1:
                                        return Yii::t('app', 'published');
                                        break;
                                    case 3:
                                        return Yii::t('app', 'testing');
                                        break;
                                    case 4:
                                        return Yii::t('app', 'ready');
                                        break;
                                    case 5:
                                        return Yii::t('app', 'revision');
                                        break;
                                }
                            },
                            'format' => 'raw',
                            'filter' => ArrayHelper::map($array, 'id', 'name')
                        ],
                        [
                            'attribute' => Yii::t('app', 'information'),
                            'value' => function ($model) {

                                if (isset($model->created_time)) {

                                    switch ($model->published) {
                                        case 0:
                                            //приложение еще не опубликовано
                                            $now = new DateTime(); // текущее время на сервере
                                            try {
                                                $date = DateTime::createFromFormat("Y-m-d H:i:s", $model->created_time);
                                                $interval = $now->diff($date);
                                                return "Добавлено: " . $interval->format('%a') . " дн. " . $interval->h . " ч.";
                                            } catch (\Exception $e) {
                                                return "Ошибка чтения даты";
                                            }
                                            break;
                                        case 2:
                                            //приложение отправлено на публикацию
                                            $now = new DateTime();
                                            try {
                                                $date = DateTime::createFromFormat("Y-m-d H:i:s", $model->upload_time);
                                                $interval = $now->diff($date);
                                                return "Публикуется: " . $interval->format('%a') . " дн. " . $interval->h . " ч.";
                                            } catch (\Exception $e) {
                                                return "Ошибка чтения даты";
                                            }
                                            break;
                                        case 1:
                                            //приложение уже опубликовано
                                            $now = new DateTime();
                                            try {
                                                $date = DateTime::createFromFormat("Y-m-d H:i:s", $model->published_time);
                                                $interval = $now->diff($date);
                                                return "Активно: " . $interval->format('%a') . " дн. " . $interval->h . " ч.";
                                            } catch(\Exception $e) {
                                                return "Ошибка чтения даты";
                                            }
                                            break;
                                        case -1:
                                            //приложение забанено, пишем сколько оно было активным
                                            if (isset($model->published_time)) {
                                                $now = DateTime::createFromFormat("Y-m-d H:i:s", $model->published_time);
                                                $date = DateTime::createFromFormat("Y-m-d H:i:s", $model->banned_time);
                                                try {
                                                    $interval = $now->diff($date);
                                                    return "Было активным: " . $interval->format('%a') . " дн. " . $interval->h . " ч.";
                                                } catch (\Exception $e) {
                                                    return "Ошибка чтения даты";
                                                }
                                                
                                            } else {
                                                return "Ошибка чтения даты";
                                            }
                                            break;
                                        case 3:
                                            //Приложение тестируется. Пишем сколько оно на тесте
                                            $now = new DateTime();
                                            try {
                                                $date = DateTime::createFromFormat("Y-m-d H:i:s", $model->testing_time);
                                                $interval = $now->diff($date);
                                                return "Тестируется: " . $interval->format('%a') . " дн. " . $interval->h . " ч.";
                                            } catch(\Exception $e) {
                                                return "Ошибка чтения даты";
                                            }
                                            break;
                                        case 4:
                                            //Приложение готово к проливу
                                            $now = new DateTime();
                                            try {
                                                $date = DateTime::createFromFormat("Y-m-d H:i:s", $model->ready_time);
                                                $interval = $now->diff($date);
                                                return "Пролив идёт: " . $interval->format('%a') . " дн. " . $interval->h . " ч.";
                                            } catch(\Exception $e) {
                                                return "Ошибка чтения даты";
                                            }
                                            break;
                                        case 5:
                                            //Сколько времени приложение на доработке
                                            $now = new DateTime();
                                            try {
                                                $date = DateTime::createFromFormat("Y-m-d H:i:s", $model->revision_time);
                                                $interval = $now->diff($date);
                                                return "Доработка: " . $interval->format('%a') . " дн. " . $interval->h . " ч.";
                                            } catch(\Exception $e) {
                                                return "Ошибка чтения даты";
                                            }
                                            break;
                                    }
                                }

                                return "Дата неизвестна";
                            },
                            'format' => 'html'
                        ],


                        [
                            'attribute' => "created_code_user_id",
                            'value' => function ($model) use ($develNames) {
                                if($model->created_code_user_id) {

                                    return $develNames[$model->created_code_user_id];
                                } else {
                                    return '(не задано)';
                                }
                            },
                            'format' => 'html',
                            'filter' => Html::dropDownList(
                                "created_code_user_id",
                                $_GET['created_code_user_id'] ?? "",
                                ArrayHelper::map(User::find()->where(["In","id",$devel,0])->all(), 'id', 'display_name'),
                                ['prompt' => 'Выберите пользователя', 'class' => 'form-control'])
                        ],

                        [
                            'attribute' => "builder_code_user_id",
                            'value' => function ($model) use ($develNames) {
                                if($model->builder_code_user_id) {
                                    return $develNames[$model->builder_code_user_id];
                                } else {
                                    return '(не задано)';
                                }
                            },
                            'format' => 'html',
                            'filter' => Html::dropDownList(
                                "builder_code_user_id",
                                $_GET['builder_code_user_id'] ?? "",
                                ArrayHelper::map(User::find()->where(["In","id",$devel,0])->all(), 'id', 'display_name'),
                                ['prompt' => 'Выберите пользователя', 'class' => 'form-control'])
                        ],

                        [
                            'attribute' => Yii::t('app', 'actions'),
                            'value' => function ($model) {
                                return Html::a(Yii::t('app', 'open_app'), ['/apps/view?id=' . $model->id], ['class' => 'btn btn-info']);
                            },
                            'format' => 'html'
                        ],
                    ],]); ?>
                <?php } ?>


            </div>
        </div>
    </div>

</div>

