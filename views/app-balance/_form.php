<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\AppBalance */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="app-balance-form">

    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"form-line\">\n{input}</div>\n{hint}\n{error}",
        ]
    ]); ?>

    <?php if($model->app_id === -1) : ?>
        <?= $form->field($model, 'app_id')->textInput() ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'count')->textInput(['maxlength' => true, 'type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'status')
                     ->dropDownList([ 'account' => 'Аккаунт', 'bug' => 'Баг', 'partner' => 'Партнер' ])
                     ->label(Yii::t('app', 'Status')) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'partner_id')
                     ->dropDownList($partners_array, ['prompt' => '', 'disabled' => 'disabled'])
                     ->label(Yii::t('app', 'Partner')) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <script>
        if($('#appbalance-status').val() === 'partner') {
            $('#appbalance-partner_id').attr('disabled', false);
        }
    </script>
</div>

<script>
    $('#appbalance-status').on('change', function() {
        let $partnerSelect = $('#appbalance-partner_id');
        if($(this).val() === 'partner') {
            $partnerSelect.attr('disabled', false);
        } else {
            $partnerSelect.attr('disabled', true);
            $partnerSelect.val('');
        }
    });
</script>