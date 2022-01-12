<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Blocking */
/* @var $form yii\widgets\ActiveForm */
/* @var $arrayBlockType array */
/* @var $arrayBlockMethod array */
/* @var $id int */

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



<div class="blocking-form card p20">

    <div class="row">
        <?if(!$model->isNewRecord):?>
            <div class="p20 pull-right col-md-4 offset-8">
                <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger pull-right',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        <?endif;?>
    </div>
    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->isNewRecord && intval($id) > 0  && isset($arrayBlockType[$id]) ):?>
        <?= $form->field($model, 'block_type')->hiddenInput()->label(false) ?>


        <div class="form-group field-blocking-block_type required">
            <label class="control-label" for="blocking-block_type">Block Type</label>
            <?=Html::textInput('block_type_defined',$arrayBlockType[$id],['class'=>'form-control', 'readonly'=>'readonly']);?>
            <div class="help-block"></div>
        </div>

    <?php else: ?>
        <?= $form->field($model, 'block_type')->dropDownList($arrayBlockType) ?>
    <?php endif;?>

    <?= $form->field($model, 'block_method')->dropDownList($arrayBlockMethod) ?>

    <?= $form->field($model, 'block_value')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'block_params')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'block_position')->textInput() ?>

    <?= $form->field($model, 'active')->dropDownList([ 'yes' => 'Yes', 'no' => 'No', ], ['prompt' => '']) ?>

    <?/*= $form->field($model, 'deleted')->dropDownList([ 'yes' => 'Yes', 'no' => 'No', ], ['prompt' => '']) */?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
</div>
