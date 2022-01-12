<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BlockingSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="blocking-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'block_type') ?>

    <?= $form->field($model, 'block_method') ?>

    <?= $form->field($model, 'block_value') ?>

    <?= $form->field($model, 'block_position') ?>

    <?php // echo $form->field($model, 'block_params') ?>

    <?php // echo $form->field($model, 'active') ?>

    <?php // echo $form->field($model, 'deleted') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
