<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DevicesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Устройства';
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- Basic Examples -->
                    <div class="card">
                        <div class="header">
                            <h2><?= Html::encode($this->title) ?></h2>
                        </div>
						<style>
						.row{
							width:100%;
						}
						</style>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                    <thead>
                                        <tr>
                                            <th style="display:none;">Sort</th>
                                            <th>Модель</th>
                                            <th>Язык</th>
                                            <th>Дата первого входа</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th style="display:none;">Sort</th>
                                            <th>Модель</th>
                                            <th>Язык</th>
                                            <th>Дата первого входа</th>
                                            <th>Действия</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
										<?php 
											$i=0;
											foreach($model as $data){
												$i++;
												print '<tr>
													<th style="display:none;">'.$i.'</th>
													<td>'.$data['device_model'].'</td>
													<td>'.$data['language'].'</td>
													<td>'.$data['date_reg'].'</td>
													<td>
													<center>
														<div class="btn-group">
															<button type="button" class="btn btn-primary waves-effect" onClick="javascript:location.href= \'/devices/view?id='.$data['id'].'\';">Детали устройства</button>
														</div>
													</center>
													</td>
												</tr>';
											} 
										 ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- #END# Basic Examples -->