<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\VisitsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="visits-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'device_id') ?>

    <?= $form->field($model, 'user_ip') ?>

    <?= $form->field($model, 'country_code') ?>

    <?= $form->field($model, 'isp') ?>

    <?php // echo $form->field($model, 'proxy') ?>

    <?php // echo $form->field($model, 'ip_data') ?>

    <?php // echo $form->field($model, 'cloaking') ?>

    <?php // echo $form->field($model, 'binom_response') ?>

    <?php // echo $form->field($model, 'server_response') ?>

    <?php // echo $form->field($model, 'date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
