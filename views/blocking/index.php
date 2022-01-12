<?php


use app\models\BlockType;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BlockingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $arrayBlockType */
/* @var $arrayBlockMethod */

$this->title = Yii::t('app', 'Blockings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">

    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-inbox bg-blue"></i>
                    <div class="d-inline">
                        <h5><?=$this->title;?></h5>
                        <span><?=$this->title;?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav class="breadcrumb-container" aria-label="breadcrumb">
                    <?=\yii\widgets\Breadcrumbs::widget([
                        'tag'=>'ol',
                        'activeItemTemplate' => '<li class="breadcrumb-item active">{link}</li>',
                        'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],

                    ]) ?>
                </nav>
            </div>
        </div>
    </div>


    <div class="blocking-index card p20">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr>
    <p>
        <?php
        $buttonsArr = [];
        $tabsArr = [];

        if(!Yii::$app->request->get('BlockingSearch')){
            $allBlockActive = 'active';
        } else {
            $allBlockActive = '';
        }

        $tabsArr[] =sprintf(
                '<li class="nav-item">%s</li>',
                Html::a(Yii::t('app', 'All Blocking'), ['index'], ['class' => 'nav-link ' . $allBlockActive]) /* active */
        );

        $buttonsArr[] = Html::a(Yii::t('app', 'Create blocking:'), 'javascript:void(0);', ['class' => 'btn btn-default btn-small m5']);
        $buttonData = BlockType::find()->where(['active'=>'yes'])->andWhere(['deleted'=>'no'])->all();
            if($buttonData) {
                foreach ($buttonData as $button) {
                    $buttonsArr[] = Html::a(Yii::t('app', '.. ' . $button->name ), ['create','id'=>$button->id], ['class' => 'btn btn-info btn-small m5']);

                    $classActive = '';
                    if(Yii::$app->request->get('BlockingSearch')){
                        if((Yii::$app->request->get('BlockingSearch'))['block_type'] == $button->id){
                            $classActive = 'active';
                        }
                    }

                    $tabsArr[] =sprintf('<li class="nav-item">%s</li>',
                        Html::a(Yii::t('app', $button->name), ['index', 'BlockingSearch'=>['block_type'=>$button->id]], ['class' => 'nav-link ' . $classActive]) /* active */
                    );
                }
            }

        $buttonsArr[] = Html::a(Yii::t('app', 'Choose..'), ['create'], ['class' => 'btn btn-success btn-small m5']);
        ?>

    <div class="row"><?=implode('', $buttonsArr)?></div>
    <hr>
    <div class="row">
        <ul class="nav nav-pills"><?=implode('', $tabsArr)?></ul>
    </div>
    <br>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php // echo sprintf('<pre>%s</pre>',print_r($_GET,1))?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            /*['class' => 'yii\grid\SerialColumn'],*/
            /*'id',*/
            [
                'attribute' => 'id',
                'label' => Yii::t('app', 'ID'),
                'format' => 'html',
                'content' => function($data){
                    return  Html::a(Html::tag('i','',['class'=>'ik ik-edit']) . ' ' . $data->id,
                    Yii::$app->urlManager->createUrl(['blocking/update', 'id'=>$data->id]),
                    ['class'=>'btn btn-link']);
            }

            ],

            [
                'attribute' => 'block_type',
                'label' => Yii::t('app', 'Block type'),
                'filter' => Html::dropDownList('BlockingSearch[block_type]', (isset($_GET['BlockingSearch']['block_type']) ? $_GET['BlockingSearch']['block_type'] : null), $arrayBlockType,['class'=>'form-control']),
                'format' => 'html',
                'value' => function($data){
                    return $data->blockType->name;
                }

            ],
            /*'block_type',*/
            [
                'attribute' => 'block_method',
                'label' => Yii::t('app', 'Block method'),
                'filter' => Html::dropDownList('BlockingSearch[block_method]', (isset($_GET['BlockingSearch']['block_method']) ? $_GET['BlockingSearch']['block_method'] : null), $arrayBlockMethod,['class'=>'form-control']),
                'format' => 'html',
                'value' => function($data){
                    return $data->blockMethod->name;
                }

            ],

            /*'block_method',*/
            'block_value',
            /*'block_position',*/
            'block_params',
            /*'active',*/
            [
                'attribute' => 'active',
                /*'label' => Yii::t('app', 'Block method'),*/
                'filter' => Html::dropDownList(
                        'BlockingSearch[active]',
                        (isset($_GET['BlockingSearch']['active']) ? $_GET['BlockingSearch']['active'] : null),
                        [
                            ''=>Yii::t('app', 'Choose'),
                            'yes'=>Yii::t('app', 'Yes'),
                            'no'=>Yii::t('app', 'No')

                        ],
                        ['class'=>'form-control', 'style'=>'width:80px;' ]
                ),
                'format' => 'html',

            ],
            //'deleted',

        ],
    ]); ?>


</div>
</div>
