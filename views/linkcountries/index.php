<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\LinkCountriesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список связей';
$this->params['breadcrumbs'][] = $this->title;
?>
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать связь', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

	<?php
		$data = $dataProvider;
	?>
	
	 <?= GridView::widget([
        'dataProvider' => $data,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'country_code',
            //'user_id',
			[                                              
				'label' => 'user_name',
				'value' => function($data, $otherData) use ($otherData) {
					//print("<script>alert('".$otherData['allUsers'][1]."');</script>");
					//return $otherData['allUsers'][1];
					
				    foreach($otherData['allUsers'] as $key => $value){
						if($key == $data['user_id']){
							return $value;
						}
				    }
				    return 0;
				    
				}, 
                'format' => 'html'
			],
            'binom_key',
            'yametrica_key',
            //'extra:ntext',		
			
            ['class' => 'yii\grid\ActionColumn'],	
        ],
    ]); ?>
	

    <?php Pjax::end(); ?>
