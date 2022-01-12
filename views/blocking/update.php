<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Blocking */
/* @var $arrayBlockType */
/* @var $arrayBlockMethod */

$this->title = Yii::t('app', 'Update Blocking: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Blockings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="blocking-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'arrayBlockType' => $arrayBlockType,
        'arrayBlockMethod' => $arrayBlockMethod,
   ]) ?>

</div>
