<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$db2 = require __DIR__ . '/db2.php';
$config = [
    'id' => 'basic',
    'timeZone' => 'Etc/UTC',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    //'language' => 'ru-RU',
    'components' => [
        'assetManager' => [
            'bundles' => [
//                'yii\bootstrap\BootstrapAsset' => [
//                    'css' => [],
//                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js'=>[]
                ],
                'yii\web\JqueryAsset' => [
                    'js'=>[]
                ],
            ],
        ],

        'monolog' => [
            'class' => '\Mero\Monolog\MonologComponent',
            'channels' => [
                'main' => [
                    'handler' => [
                        [
                            'type' => 'stream',
                            'path' => '@app/runtime/logs/main_' . date('Y-m-d') . '.log',
                           // 'path' => '/var/www/main/data/www/logs/main_' . date('Y-m-d') . '.log',
                            'level' => 'debug, info',
                        ]
                    ],
                    'processor' => [],
                ],
            ],
        ],
        'i18n' => [
            //example: https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n
            'translations'=>[
                // app* - это шаблон, который определяет, какие категории
                // обрабатываются источником. В нашем случае, мы обрабатываем все, что начинается с app
                'app*'=>[
                    'class'=>yii\i18n\PhpMessageSource::className(),
                    //
                    'basePath'=>'@app/localizations',
                    // исходный язык
                    'sourceLanguage'=>'ru',
                    // определяет, какой файл будет подключаться для определённой категории
                    'fileMap'=>[
                        'app'=>'main.php',
                        'app/error'=>'error.php',
                    ],
                ],
            ]
        ],

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '78rak^&Po@@s',
	    'parsers' => [
                 'application/json' => 'yii\web\JsonParser',
            ],

            'baseUrl' =>''
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
			'class' => 'webvimark\modules\UserManagement\components\UserConfig',
			// Comment this if you don't want to record user logins
			'on afterLogin' => function($event) {
				\webvimark\modules\UserManagement\models\UserVisitLog::newVisitor($event->identity->id);
			}
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
//        'log' => [
//            'traceLevel' => 0,//YII_DEBUG ? 3 : 0,
//            'targets' => [
//                [
//                    'class' => 'yii\log\FileTarget',
//                    'logFile' => '/var/www/main/data/www/main.log',
//                    'levels' => ['error', 'warning', 'info'],
//                    'logVars' => [],
//                    'categories' => [
//                        'system.*',
//                    ],
//                ],
//            ],
//        ],
        'db' => $db,
        'db2' => $db2,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                //'profile' => 'user-management/user/view',

                'api/v5' => 'apiold',
                'api/v5/auth' => 'apiold/auth',
                'api/v5/get-url' => 'apiold/get-url',
                'api/v5/log/send' => 'logs/send',
                'api/v5/dev' => 'amy/device',

                'api/v6' => 'apiv6',
                'api/v6/auth' => 'apiv6/auth',
                'api/v6/get-url' => 'apiv6/get-url',
                'api/open-url' => 'apiv6/open-url',
                'api/v6/log/send' => 'logs/send',
                'api/v6/dev' => 'amy/device',

		'api/v7' => 'apinew',
                'api/v7/auth' => 'apinew/auth',
                'api/v7/get-url' => 'apinew/get-url',
                'api/v7/option' => 'apinew/option',
                'api/v7/log/send' => 'logs/send',
                'api/v7/dev' => 'amy/device',

                'logview' => 'log/show',
            ],
        ],
    ],
    'on beforeRequest' => function ($event) {
        Yii::$app->language = Yii::$app->request->cookies->getValue('language', 'ru-RU');
    },
	
	'modules'=>[
		'user-management' => [
			'class' => 'webvimark\modules\UserManagement\UserManagementModule',

			// 'enableRegistration' => true,

			// Add regexp validation to passwords. Default pattern does not restrict user and can enter any set of characters.
			// The example below allows user to enter :
			// any set of characters
			// (?=\S{8,}): of at least length 8
			// (?=\S*[a-z]): containing at least one lowercase letter
			// (?=\S*[A-Z]): and at least one uppercase letter
			// (?=\S*[\d]): and at least one number
			// $: anchored to the end of the string

			//'passwordRegexp' => '^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$',
			

			// Here you can set your handler to change layout for any controller or action
			// Tip: you can use this event in any module
			'on beforeAction'=>function(yii\base\ActionEvent $event) {
					if ( $event->action->uniqueId == 'user-management/auth/login' )
					{
						$event->action->controller->layout = 'loginLayout.php';
					};
				},
		],
		'apiv1' => [
		    'class' => 'app\modules\PartnersApi\v1\Module',
		],

        'api' => [
            'class' => 'app\modules\AppsApi\Module',
        ],
	],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['components']['monolog'] = [];
    $config['components']['monolog'] = [
        'class' => '\Mero\Monolog\MonologComponent',
        'channels' => [
            'main' => [
                'handler' => [
                    [
                        'type' => 'stream',
                        'path' => '@app/runtime/logs/debug_' . date('Y-m-d') . '.log',
                        'level' => 'debug, info',
                    ]
                ],
                'processor' => [],
            ],
        ],
    ];

    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1'],
        'allowedIPs' => ['*'],
    ];
}

return $config;
