<?php

use app\basic\PrintHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $apps */
/* @var $total */

$this->title = Yii::t('app', 'Apps Balance');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="app-balance-index">

    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-dollar-sign bg-blue"></i>
                    <div class="d-inline">
                        <h5><?=$this->title;?></h5>
                        <span><?=Yii::t('app', 'Statistic')?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="dt-responsive" style="padding-left:20px; padding-right:20px;">

                <div style="float:right; padding-bottom: 20px;">


                </div>

                <table id="stats-table" class="table table-striped table-bordered nowrap">
                    <tr>
                        <td>
                            <b><?=Yii::t('app', 'App')?></b>
                        </td>
                        <td>
                            <b><?=Yii::t('app', 'Account')?></b>
                        </td>
                        <td>
                            <b><?=Yii::t('app', 'Bug')?></b>
                        </td>
                        <td>
                            <b><?=Yii::t('app', 'Partner')?></b>
                        </td>
                        <td>
                            <b><?=Yii::t('app', 'Total')?></b>
                        </td>

                        <td>
                            <b><?=Yii::t('app', 'Actions')?></b>
                        </td>
                    </tr>

                    <?php if(!empty($apps)) : ?>
                        <tbody id="table1">
                        <?php foreach($apps as $app) : ?>
                            <tr>
                                <td>
                                    <?=Html::a($app['name'], ['/apps/view?id=' . $app['id']], ['class' => 'btn btn-link btn-rounded pl-0'])?>
                                </td>
                                <td>
                                    <?= PrintHelper::printCount($app['account']); ?>
                                </td>
                                <td>
                                    <?= PrintHelper::printCount($app['bug']); ?>
                                </td>
                                <td>
                                    <?= PrintHelper::printCount($app['partner']); ?>
                                </td>
                                <td>
                                    <?= PrintHelper::printCount($app['profit'], 'b'); ?>
                                </td>
                                <td>
                                    <?= Html::a(Yii::t('app', 'View all records'), ['/app-balance/view', 'id' => $app['id']], ['class' => 'btn btn-primary']) ?>
                                    <?= Html::a(Yii::t('app', 'Add record'), ['/app-balance/create', 'app_id' => $app['id']], ['class' => 'btn btn-success']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tr>
                            <td><b><?=Yii::t('app', 'Total')?></b></td>
                            <td><?= PrintHelper::printCount($total['account'], 'b'); ?></td>
                            <td><?= PrintHelper::printCount($total['bug'], 'b'); ?></td>
                            <td><?= PrintHelper::printCount($total['partner'], 'b'); ?></td>
                            <td><?= PrintHelper::printCount($total['all'], 'b'); ?></td>
                        </tr>
                    <?php else : ?>
                            <tr>
                                <td colspan="6"><?=Yii::t('app', 'No records found')?></td>
                            </tr>
                        </tbody>
                    <?php endif; ?>


                </table>

            </div>
        </div>
    </div>

</div>

