<?php

use webvimark\modules\UserManagement\models\User;
use yii\helpers\Html;
use app\basic\PrintHelper;

/* @var $this yii\web\View */
/* @var $records app\models\AppBalance */
/* @var $app app\models\Apps */
/* @var $total_sum */

$this->title = 'Баланс приложения '.$app->name;

\yii\web\YiiAsset::register($this);
?>
<div class="app-balance-view">

    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-dollar-sign bg-blue"></i>
                    <div class="d-inline">
                        <h5><?=$app->name;?></h5>
                        <span><?=Yii::t('app', 'App balance')?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

                <div style="float:right; padding-bottom: 20px;">

                    <?= Html::a('Перейти к приложению', ['/apps/view', 'id' => $app->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('app', 'Add record'), ['/app-balance/create', 'app_id' => $app->id], ['class' => 'btn btn-success']) ?>

                </div>

                <table id="stats-table" class="table table-striped table-bordered nowrap">
                    <tr>
                        <td>
                            <b><?=Yii::t('app', 'Date')?></b>
                        </td>
                        <td>
                            <b><?=Yii::t('app', 'Operation type')?></b>
                        </td>
                        <td>
                            <b><?=Yii::t('app', 'Sum')?></b>
                        </td>
                        <td>
                            <b><?=Yii::t('app', 'Comment')?></b>
                        </td>

                        <td>
                            <b><?=Yii::t('app', 'Actions')?></b>
                        </td>
                    </tr>

                    <tbody id="table1">

                    <?php if(!empty($records)) : ?>
                        <?php foreach($records as $record) : ?>
                            <tr>
                                <td>
                                    <?php $date = date('d M Y, H:i', strtotime($record->created_at) ) ?>
                                    <?=$date;?>
                                </td>
                                <td>
                                    <?php
                                    switch($record->status) {
                                        case 'account':
                                            echo 'За аккаунт';
                                            break;
                                        case 'bug':
                                            echo 'За баги';
                                            break;
                                        case 'partner':
                                            echo 'От партнера ';
                                            if(isset($record->partner)) {
                                                echo Html::a($record->partner->display_name, ['/profile', 'id' => $record->partner->id, 'partner' => 1], ['style' => 'color: blue']);
                                            }
                                            break;
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?= PrintHelper::printCount($record->count, 'b'); ?>
                                </td>
                                <td>
                                    <?=$record->comment;?>
                                </td>
                                <td>
                                    <a class="btn btn-primary" href="/app-balance/update?id=<?=$record->id;?>" style="margin-left:15px; color:#ffffff;" title="Изменить">
                                        <i class="ik ik-edit" style="margin-right: 0 !important;"></i>
                                    </a>
                                    <a class="btn btn-danger" href="/app-balance/delete?id=<?=$record->id;?>" onclick="return confirm('Вы уверены?')" style="margin-left:15px; color:#ffffff;" title="Удалить">
                                        <i class="ik ik-delete" style="margin-right: 0 !important;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                        <tr>
                            <td colspan="2"><b><?=Yii::t('app', 'Total')?></b></td>
                            <td><?= PrintHelper::printCount($total_sum, 'b'); ?></td>
                        </tr>
                    <?php else: ?>
                            <tr>
                                <td colspan="5"><?=Yii::t('app', 'No records found')?></td>
                            </tr>
                        </tbody>
                    <?php endif; ?>

                </table>

            </div>
        </div>
    </div>

</div>
