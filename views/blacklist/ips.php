<?php
 use yii\widgets\DetailView;
 
 echo "<h1>Повторяющиеся адреса</h1>";
 
 ?>
 
<div id="carouselExampleControls" class="carousel slide carousel-fade" data-ride="carousel" data-interval="false">
    <div class="carousel-inner">
        <div class="carousel-item active" style="width: 50%; margin: 0 auto">
            <div class="d-block w-100" style="height: 67vh">
                <h3>Второй разряд</h3>
                <?php
                echo \yii\grid\GridView::widget([
                    'dataProvider' => $providerFirst,
                    'columns' => [
                        [
                            'attribute' => 'ip',
                            'value' => function($model) {
                                return $model;
                            }
                        ]
                    ]
                ]);
                ?>
            </div>
        </div>
        <div class="carousel-item" style="width: 50%; margin: 0 auto; height: 100%">
            <div class="d-block w-100" style="height: 67vh">
		<h3>Третий разряд</h3>
                <?php
                echo \yii\grid\GridView::widget([
                    'dataProvider' => $providerSecond,
                    'columns' => [
                        [
                            'attribute' => 'ip',
                            'value' => function($model) {
                                return $model;
                            }
                        ]
                    ]
                ]);
                ?>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true" style="background-color: #0a73bb"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true" style="background-color: #0a73bb"></span>
        <span class="sr-only">Next</span>
    </a>
</div>

