<?php

use yii\widgets\DetailView;
?>


    <a href="/task/index" class="btn btn-primary">Back</a>

<?php


$dataProvider = new \yii\data\ArrayDataProvider([
        'allModels' => $info,
        'sort' => [
            'attributes' => ['id'],
        ],
       // 'sort'=> ['defaultOrder' => ['topic_order' => SORT_ASC]],
    ]);

echo \yii\grid\GridView::widget([
   'dataProvider' => $dataProvider
]);
?>

