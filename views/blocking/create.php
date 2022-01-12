<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Blocking */
/* @var $arrayBlockType */
/* @var $arrayBlockMethod */

$this->title = Yii::t('app', 'Create Blocking');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Blockings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blocking-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'arrayBlockType' => $arrayBlockType,
        'arrayBlockMethod' => $arrayBlockMethod,
        'id'=>$id,
    ]) ?>

</div>
