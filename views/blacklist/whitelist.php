<?php
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;
use app\models\Blacklist;
use yii\grid\GridView;

?>
<a class="btn btn-primary" href="/blacklist/index" style="color:#ffffff;"><- Back</a>
<hr>
<?php
$dataProvider = new ActiveDataProvider([
    'query' => Blacklist::find()->where(['block' => 0]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'idfa',
        //test
        [
            'attribute' => 'country',
            'value' => function ($model) {
                try {
                    $device = \app\models\Devices::find()->where(['idfa' => $model->idfa])->andWhere('link_id != 0')->one();
                    $visit = \app\models\Visits::find()->where(['device_id' => $device->id])->one();
                    $country = $visit->filterlog->country;//                $link = \app\models\Links::find()->where(['id' => $device->link_id])->one();
//                $linkcountry = \app\models\Linkcountries::find()->where(['id' => $link->linkcountry_id])->one();
//                $params = \app\basic\ApiHelper::getCountryInfo()
                    //$params = \app\basic\ApiHelper::getParams($device->app_id, '115', $linkcountry->id);
                    if (true) {
                        $linkInfo = ' <img src="' . Yii::$app->runAction('media/getflag', ['country_code' => strtolower($country)]) . '" width="26px"> ' . $country;
                    }
                    return $linkInfo;
                } catch (\Exception $e) {
                    return 'Not found';
                }
            },
            'format' => 'raw'
        ],

        [
            'attribute' => 'device',
            'value' => function ($model) {
                try {
                    $device = \app\models\Devices::find()->where(['idfa' => $model->idfa])->andWhere('link_id != 0')->one();
                    $visit = \app\models\Visits::find()->where(['device_id' => $device->id])->one();
                    $deviceName = explode(";", $visit->filterlog->ua)[2];
                    $deviceModel = explode("/", $deviceName)[0];
                    $deviceModel = str_replace("Build", "", $deviceModel);
                    return '<a href="/devices/view?id=' . $device->id . '" style="color:blue;">' . $deviceModel . '</a>';
                }catch (\Exception $e){
                    return 'Not found';
                }
            },
            'format' => 'raw'
        ],


    ]

]);

