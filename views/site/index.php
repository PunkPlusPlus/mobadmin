<?php

/* @var $this yii\web\View */

$this->params['breadcrumbs'][] = $this->title;
$this->title = 'Главная страница AflaGroup App';
?>


<div class="container-fluid">

    <div class="card">
        <div class="card-body">
            <a>Курс валют на <b><?=date("d.m.Y");?></b> (Курс взят с <a href="https://www.ecb.europa.eu/home/html/index.en.html" style="color:blue; cursor: pointer;" target="_blank">европейского центрального банка</a>)</b>:

        </div>
    </div>

    <div class="row clearfix">
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="widget">
                <div class="widget-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="state">

                            <small class="text-small mt-10 d-block">
                                Конвертер:<br>
                            </small>
                        </div>
                        <div class="icon">
                            <i class="ik ik-credit-card"></i>
                        </div>
                    </div>
                    <!--                    <small class="text-small mt-10 d-block">Afla Group</small>-->
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 70%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header row">
        <div class="col col-sm-3">
            <div class="card-options d-inline-block">
                Последние изменения
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="list-item-wrap">



            <div class="list-item quick-view-opened">
                <div class="item-inner">
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="item_checkbox" name="item_checkbox" value="option1" checked disabled>
                        <span class="custom-control-label">&nbsp;</span>
                    </label>
                    <div class="list-title"><a href="javascript:void(0)">Обновление 1.5</a></div>
                </div>

                <div class="qickview-wrap">
                    <div class="desc">

                        <p>-</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>