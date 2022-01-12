<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use webvimark\modules\UserManagement\models\User;
use app\models\Logs;

//print '<pre>';
//print_r(User::getCurrentUser());
//exit();
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="/images/favicon.ico?v5" type="image/x-icon">
    <link rel="icon" href="/images/favicon.ico?v5" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/images/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/images/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">

    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800" rel="stylesheet">


    <link rel="stylesheet" href="/theme/plugins/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/theme/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/theme/plugins/icon-kit/dist/css/iconkit.min.css">
    <link rel="stylesheet" href="/theme/plugins/ionicons/dist/css/ionicons.min.css">
    <link rel="stylesheet" href="/theme/plugins/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="/theme/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="/theme/plugins/tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="/theme/plugins/weather-icons/css/weather-icons.min.css">
    <link rel="stylesheet" href="/theme/plugins/c3/c3.min.css">
    <link rel="stylesheet" href="/theme/plugins/owl.carousel/dist/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="/theme/plugins/owl.carousel/dist/assets/owl.theme.default.min.css">

    <link rel="stylesheet" href="/theme/dist/css/theme.css">

    <link rel="stylesheet" href="/theme/preloader.css">
    <link rel="stylesheet" href="/theme/css/preloaderanim.css">
    <link rel="stylesheet" href="/theme/plugins/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="/theme/plugins/jquery-toast-plugin/dist/jquery.toast.min.css">
    <script src="/theme/src/js/vendor/modernizr-2.8.3.min.js"></script>


    <link rel="stylesheet" href="/theme/datetimepicker/bootstrap-datetimepicker.min.css">

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script>window.jQuery || document.write('<script src="src/js/vendor/jquery-3.3.1.min.js"><\/script>')</script>
    <!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>-->
    <!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>-->

    <style>
        /* The switch - the box around the slider */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>

</head>

<body>
<?php $this->beginBody() ?>

<style>
    .pagination > li {
        padding: 5px;
    }

    /* disabled */
    .pagination > li > a.prev,
    .pagination > li > a.next {
    }

    .pagination > li > a {
        outline: initial !important;
        box-shadow: none !important;
        line-height: 18px;
        min-width: 30px;
        text-align: center;
        height: 30px;
        padding: 6px 0px;
        border: none;
        background-color: #eaeaea;
        color: #3e5569;
        border-radius: 30px;
        -webkit-border-radius: 30px;
        -moz-border-radius: 30px;

        position: relative;
        display: block;
        margin-left: -1px;
        border: 1px solid #dee2e6;


    }

    .pagination .active a {
        background-color: #007bff;
        color: #fff;
    }

    .pagination .last a {
        color: #fff;
        background-color: #3e5569;
    }

    .pagination .first a {
        color: #fff;
        background-color: #3e5569;
    }

    .pagination .disabled {
        display: none;
    }
</style>

<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
    your browser</a> to improve your experience.</p>
<![endif]-->

<div class="wrapper">
    <header class="header-top" header-theme="light">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <div class="top-menu d-flex align-items-center">
                    <button type="button" class="btn-icon mobile-nav-toggle d-lg-none"><span></span></button>
                    <!--                    <div class="header-search">-->
                    <!--                        <div class="input-group">-->
                    <!--                            <span class="input-group-addon search-close"><i class="ik ik-x"></i></span>-->
                    <!--                            <input type="text" class="form-control">-->
                    <!--                            <span class="input-group-addon search-btn"><i class="ik ik-search"></i></span>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                    <button type="button" id="navbar-fullscreen" class="nav-link"><i class="ik ik-maximize"></i>
                    </button>
                    <?php if (false) { ?>
                        &nbsp; &nbsp; &nbsp;<b style="color:red;">В связи с обновлением серверов на Digital Ocean,
                            возможны некоторые сбои в работе сайта c 16.04.2020 22:00:00 (UTC) по 17.04.2020 06:00:00
                            (UTC)</b>
                    <?php } ?>
                </div>
            </div>
        </div>
    </header>

    <div class="page-wrap">


        <div class="app-sidebar colored">
            <div class="sidebar-header">


                <a class="header-brand" href="/">
                    <!--                    <div class="logo-img">-->
                    <!--                        <img src="/theme/src/img/brand-white.svg" class="header-brand-img" alt="lavalite">-->
                    <!--                    </div>-->
		    <div class="logo-img">
                         <img src="/images/logo-white.png" class="header-brand-img" width="25" alt="lavalite">
                     </div>

                    <span class="text">Joy App</span>
                </a>
                <button type="button" class="nav-toggle"><i data-toggle="expanded"
                                                            class="ik ik-toggle-right toggle-icon"></i></button>
                <button id="sidebarClose" class="nav-close"><i class="ik ik-x"></i></button>
            </div>

            <style>
                #blink {
                    -webkit-animation: blink 1s linear infinite;
                    animation: blink 1s linear infinite;
                }

                @-webkit-keyframes blink {
                    100% {
                        color: rgba(34, 34, 34, 0);
                    }
                }

                @keyframes blink {
                    100% {
                        color: rgba(34, 34, 34, 0);
                    }
                }
            </style>
            <div class="sidebar-content">
                <div class="nav-container">
                    <nav id="main-menu-navigation" class="navigation-main">

                        <?php if(User::hasRole('partner') && false) : ?>

                            <div class="nav-lavel" style="color:white;">
                                <div style="font-size:15px;">
                                    <div class="text-center">
                                        <?=User::getCurrentUser()->display_name;?>
                                        <br>
                                        Ваш баланс:
                                        <b style="color:#ADFF2F;">
                                            <?php
                                            if(User::hasRole('Admin', false)){
                                                print "<span style='font-size:35px;line-height: 20px;position: relative;top: 8px;'>∞</span>";
                                            }else{
                                                print "$".\app\controllers\BalanceController::get(User::getCurrentUser()->id);
                                            }
                                            ?>
                                        </b>
                                        <br><a href="<?=\yii\helpers\Url::to(['/docs/', 'page' => 'balance'])?>" style="color:#ffd58c;">Пополнить</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="nav-lavel">Навигация</div>
                        <?php
                        /*
                            $menu['Главная страница'] = [
                                'type' => 'normal',
                                'url' => '/site/index',
                                'css' => 'nav-item',
                                'before_text' => '<i class="ik ik-bar-chart-2"></i>',
                            ];
                        */

                        //                        $menu['Главная страница'] = [
                        //                            'url' => '/',
                        //                            'css' => 'nav-item',
                        //                            'before_text' => '<i class="ik ik-home"></i>',
                        //                        ];

                        $menu[Yii::t('app', 'my_app')] = [
                            'url' => '/apps/index?sort=-id',
                            'css' => 'nav-item',
                            'before_text' => '<i class="ik ik-home"></i>',
                        ];
                        if (User::hasPermission('push_events')) {
                            $menu[Yii::t('app', 'push_ev')] = [
                                'url' => '/fb',
                                'css' => 'nav-item',
                                'before_text' => '<i class="ik ik-facebook"></i>',
                            ];
                        }

			if (User::hasPermission('share_accounts_manually')) {
                             $menu[Yii::t('app', 'Шеринг аккаунтов')] = [
 
                                 'css' => 'nav-item',
                                 'before_text' => '<i class="ik ik-share-2"></i>',
                                 'submenu' => [
                                         0 => [
                                                 'name' => 'Пошарить РК ФБ',
                                                 'url' => '/task',
                                         ],
                                         1 => [
                                                 'name' => 'Все задачи',
                                                 'url' => '/task/get-all?sort=-id'
                                         ]
                                 ]
                             ];
                         }


                        if (User::hasPermission('change_app_balance')) {
                            $menu[Yii::t('app', 'Apps Balance')] = [
                                'url' => '/app-balance',
                                'css' => 'nav-item',
                                'before_text' => '<i class="ik ik-dollar-sign"></i>',
                            ];
                        }

                        if (User::hasRole(['employee', 'Developer', 'manager', 'support', 'partner', 'Buyer'])) {
                            $profile_text = User::hasRole(['partner', 'Buyer']) ?
                                            Yii::t('app', 'partner_profile') :
                                            Yii::t('app', 'my_profile');
                            $menu[$profile_text] = [
                                'url' => '/profile',
                                'css' => 'nav-item',
                                'before_text' => '<i class="ik ik-user"></i>',
                            ];
                            if (User::hasPermission('blacklist')) {
                                $menu[Yii::t('app', 'Black list')] = [
                                    'url' => '/blacklist',
                                    'css' => 'nav-item',
                                    'before_text' => '<i class="ik ik-lock"></i>',
                                ];                                
                            }

                            
                        }

                        if (User::hasPermission('change_balance')) {
                            $menu[Yii::t('app', 'partner_balance')] = [
                                'url' => '/balance/view',
                                'css' => 'nav-item',
                                'before_text' => '<i class="ik ik-dollar-sign"></i>',
                            ];
                        }

                        if (User::hasPermission('view_stats_partners')) {
                            $menu[Yii::t('app', 'stats')] = [
                                'url' => '/stats/partners',
                                'css' => 'nav-item',
                                'before_text' => '<i class="ik ik-bar-chart-line"></i>',
                            ];
                        }

//                        if (User::hasPermission('logs_view_all')) {
//                            $menu[Yii::t('app', 'logs')] = [
//                                'url' => '/logview',
//                                'css' => 'nav-item',
//                                'before_text' => '<i class="ik ik-home"></i>',
//                            ];
//                        }


                        $menu[Yii::t('app', 'docs')] = [
                            'icon' => '<i class="ik ik-book-open"></i>',
                            'before_text' => '<i class="ik ik-book-open"></i>',
                            'submenu' => [
                                0 => [
                                    'name' => Yii::t('app', 'get_conversions'),
                                    'url' => '/docs?page=conversions',
                                    'css' => 'nav-item',
                                ],
                                1 => [
                                    'name' => Yii::t('app', 'fb_integration'),
                                    'url' => '/docs?page=fb_integration',
                                    'css' => 'nav-item',
                                ],
//                                2 => [
//                                    'name' => Yii::t('app', 'partner_balance'),
//                                    'url' => '/docs?page=balance',
//                                    'css' => 'nav-item',
//                                ],

                            ]
                        ];

                        if (User::hasPermission('edit_filterblock')) {
                            $menu[Yii::t('app', 'settings')] = [
                                'icon' => '<i class="ik ik-settings"></i>',
                                'before_text' => '<i class="ik ik-settings"></i>',
                                'submenu' => [
                                    0 => [
                                        'name' => Yii::t('app', 'main_params'),
                                        'url' => '/settings',
                                        'css' => 'nav-item',
                                    ],
                                    1 => [
                                        'name' => Yii::t('app', 'global_filter'),
                                        'url' => '/blocking',
                                        'css' => 'nav-item',
                                        'before_text' => '<i class="ik ik-home"></i>',
                                    ]
                                ]
                            ];

                        }
                        if (User::hasPermission('create_users')) {
                            $menu['Управление'] = [
                                'icon' => '<i class="ik ik-dollar-sign"></i>',
                                'before_text' => '<i class="ik ik-user"></i>',
                                'submenu' => [
                                    0 => [
                                        'name' => 'Пользователи',
                                        'url' => '/user-management/user',
                                        'css' => 'nav-item',
                                    ],
                                    1 => [
                                        'name' => 'Роли',
                                        'url' => '/user-management/role',
                                        'css' => 'nav-item',
                                    ],
                                    2 => [
                                        'name' => 'Права доступа',
                                        'url' => '/user-management/permission',
                                        'css' => 'nav-item',
                                    ],
                                    3 => [
                                        'name' => 'Группы прав',
                                        'url' => '/user-management/auth-item-group',
                                        'css' => 'nav-item',
                                    ],
                                    4 => [
                                        'name' => 'Логи заходов',
                                        'url' => '/user-management/user-visit-log',
                                        'css' => 'nav-item',
                                    ],
                                ]
                            ];
                        }


                        $menu[Yii::t('app', 'exit_account')] = [
                            'url' => '/user-management/auth/logout',
                            'css' => 'nav-item',
                            'before_text' => '<i class="ik ik-log-out"></i>',
                        ];


                        $currURI = $_SERVER['REQUEST_URI'];

                        foreach ($menu as $key => $value) {
                            $value['before_text'] = $value['before_text'] ?? '';
                            $value['after_text'] = $value['after_text'] ?? '';
                            if (isset($value['submenu'])) {
                                $i = 0;
                                foreach ($value['submenu'] as $subKey => $subValue) {
                                    //for($i=0; $i<count($value['submenu']); $i++){
                                    $subValue['before_text'] = $subValue['before_text'] ?? '';
                                    $subValue['after_text'] = $subValue['after_text'] ?? '';
                                    if ($i == 0) {
                                        print '
													<div class="nav-item has-sub">
														<a href="javascript:void(0)">
															' . $value['before_text'] . '<span>' . $key . '</span> ' . $value['after_text'] . '
														</a>
													<div class="submenu-content" style="">';
                                    }

                                    if ($currURI == $subValue['url'])
                                        $classActive = 'active';
                                    else
                                        $classActive = '';

                                    print '<a href="' . $subValue['url'] . '" class="menu-item ' . $classActive . '" onClick="PreloaderContent();">' . $subValue['name'] . '</a>';

                                    if ($i == (count($value['submenu']) - 1)) {
                                        print '</div></div>';
                                    }
                                    $i++;
                                    //}
                                }
                            } else {
                                if ($currURI == $value['url'])
                                    $classActive = 'active';
                                else
                                    $classActive = '';
                                print '<div class="nav-item ' . $classActive . '"><a href="' . $value['url'] . '" onClick="PreloaderContent();">' . $value['before_text'] . '<span>' . $key . '</span>' . $value['after_text'] . '</a></div>';
                            }
                        }
                        ?>

                    </nav>
                </div>

                <!--                --><?php
                //                if (User::hasPermission('view_logs_all')) {
                //                    $logsWarning = Logs::find()
                //                        ->where(['is_read' => 0])
                //                        ->andWhere(['priority' => 1])
                //                        ->count();
                //
                //                    $logsDanger = Logs::find()
                //                        ->where(['is_read' => 0])
                //                        ->andWhere(['priority' => 2])
                //                        ->count();
                //
                //                    if ($logsWarning > 99)
                //                        $countWarningLog = 99;
                //                    else
                //                        $countWarningLog = $logsWarning;
                //
                //                    if ($logsDanger > 99)
                //                        $countDangerLog = 99;
                //                    else
                //                        $countDangerLog = $logsDanger;
                //
                //                    if ($countWarningLog > 0) {
                //                        $alertnotifyWarning = '<span class="badge badge-warning" style="margin-top:3px;" id="">Предупреждение ' . $countWarningLog . '</span>';
                //                    } else {
                //                        $alertnotifyWarning = '';
                //                    }
                //                    if ($countDangerLog > 0) {
                //                        $alertnotifyDanger = '<span class="badge badge-danger" style="margin-top:3px;" id="blink">Ошибка ' . $countDangerLog . '</span>';
                //                    } else {
                //                        $alertnotifyDanger = '';
                //                    }
                //                    print "<div style='position: absolute;margin-bottom: 30px;margin-right: 30px;bottom: 0;right: 0;display: flex;flex-flow: column;'>";
                //                    print_r($alertnotifyDanger);
                //                    print_r($alertnotifyWarning);
                //                    print "</div>";
                //                }
                //                ?>

                <div style="
                position: absolute;
                bottom: 0;
                text-align: center;
                width: 100%;">
                    <center>
                        <label style="color:white;"><?=Yii::t('app', 'lang_interface');?>:</label>
                        <br>
                        <select class="form-control" style="max-width:150px;" onchange="changeLanguage(this)">
                            <option value="ru-RU" <?php if(Yii::$app->request->cookies->getValue('language', 'ru-RU') == "ru-RU") print "selected"; ?>>Russia</option>
                            <option value="en-US" <?php if(Yii::$app->request->cookies->getValue('language', 'ru-RU') == "en-US") print "selected"; ?>>English</option>
                        </select>

                        <script>
                            function changeLanguage(sel){
                                window.location.href = "/site/change-language?lang="+sel.value;
                            }
                        </script>
                    </center>
                    <br>
                    <label style="color:white;"><?=Yii::t('app', 'server_time');?>:<br> <?=date("Y-d-m - H:i:s");?></label>
                </div>
            </div>
        </div>

        <div class="main-content" id="preloader_page" style="display:none;">
            <center>
                <div class="parent" style="width:50px; height:25px; display:inline-block;">
                    <div id="xLoader">
                        <div class="audio-wave"><span></span><span></span><span></span><span></span><span></span></div>
                    </div>
                </div>

            </center>
        </div>

        <div class="main-content" id="main-content">

            <?= Alert::widget([
                'options' => [
                    'class' => 'show',
                ],
                'closeButton' => false
            ]) ?>

            <?= $content; ?>
        </div>


        <!--        <footer class="footer">-->
        <!--            <div class="w-100 clearfix">-->
        <!--                <span class="text-center text-sm-left d-md-inline-block">Copyright © 2020 Afla Group. All Rights Reserved.</span>-->
        <!--            </div>-->
        <!--        </footer>-->

    </div>
</div>

<script>
    function PreloaderContent() {
        $('#preloader_page').css("display", "block");
        $('#main-content').css("display", "none");
    }
</script>

<script src="/theme/plugins/popper.js/dist/umd/popper.min.js"></script>
<script src="/theme/plugins/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/theme/plugins/perfect-scrollbar/dist/perfect-scrollbar.min.js"></script>
<script src="/theme/plugins/screenfull/dist/screenfull.js"></script>
<script src="/theme/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/theme/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/theme/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="/theme/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="/theme/plugins/moment/moment.js"></script>
<script src="/theme/plugins/tempusdominus-bootstrap-4/build/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="/theme/plugins/d3/dist/d3.min.js"></script>
<script src="/theme/plugins/c3/c3.min.js"></script>
<!--<script src="/theme/plugins/select2/dist/js/select2.min.js"></script>-->
<!--<script src = "//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" defer ></script>-->
<script src="/theme/datetimepicker/bootstrap-datetimepicker.min.js"></script>



<!--<script src="/theme/js/charts.js"></script>-->
<script src="/theme/dist/js/theme.min.js"></script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
