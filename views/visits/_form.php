<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Visits */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="visits-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'device_id')->textInput() ?>

    <?= $form->field($model, 'user_ip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'country_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'isp')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'proxy')->textInput() ?>

    <?= $form->field($model, 'ip_data')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'cloaking')->textInput() ?>

    <?= $form->field($model, 'binom_response')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'server_response')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'date')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
