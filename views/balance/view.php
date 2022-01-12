<?php

use yii\helpers\Html;

$this->title = Yii::t('app', 'partner_balance'); 
?>

<style>
  .alert-balance {
      display:none;
      position:fixed;
      top:80px;
      right:40px;
      width:fit-content;
      z-index:100;
  }
</style>

<div class="alert alert-success alert-plus alert-balance" role="alert"></div>
<div class="alert alert-danger alert-minus alert-balance" role="alert"></div>

<div class="page-header">
    <div class="row align-items-end">
        <div class="col-lg-8">
            <div class="page-header-title">
                <i class="ik ik-dollar-sign bg-blue"></i>
                <div class="d-inline">
                    <h5>Баланс партнеров</h5>
                    <span>Здесь отображается список партнеров</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="dt-responsive">

            <div class="mb-25" id="partner-balance-form">
                <h5>Управление балансом</h5>
                <form class="form-inline balance-form">
                    <select class="form-control" name="id" id="b-partner-name">
                        <?php foreach($partners as $partner) : ?>
                            <option value="<?=$partner->partner_id?>"
                                    data-name="<?=$partner->user->display_name?>"
                                    <?php if(isset($_GET['id']) && $_GET['id'] == $partner->partner_id) echo 'selected'; ?>
                            >
                                <?=$partner->user->display_name?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input id="123"
                           class="form-control"
                           name="count"
                           type="number"
                           min="0"
                           placeholder="0"
                           step="0.01"
                           style="margin-left: 5px;"
                        <?php if(isset($_GET['id'])) echo 'autofocus'; ?>>
                    <button class="btn btn-warning" data-operator="-" style="margin-left: 5px;" type="button">
                        -
                    </button>
                    <button class="btn btn-success" data-operator="+" style="margin-left: 5px" type="button">
                        +
                    </button>
                </form>
            </div>
            <div id="partner-limit-form">
                <h5>Управление лимитом</h5>
                <form class="form-inline limit-form">
                    <select class="form-control" name="id" id="l-partner-name">
                        <?php foreach($partners as $partner) : ?>
                            <option value="<?=$partner->partner_id?>"
                                    data-name="<?=$partner->user->display_name?>"
                                    <?php if(isset($_GET['id']) && $_GET['id'] == $partner->partner_id) echo 'selected'; ?>
                            >
                                <?=$partner->user->display_name?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input class="form-control" name="count" type="number" min="0" placeholder="0" step="10" style="margin-left: 5px;">
                    <button class="btn btn-info" style="margin-left: 5px;" type="button">
                        Установить
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="dt-responsive" style="padding-left:20px; padding-right:20px; padding-bottom:20px;">



            <table class="table table-striped table-bordered detail-view">
                <thead>
                    <tr>
                        <td>ID</td>
                        <td>Партнер</td>
                        <td>Баланс ($)</td>
                        <td>Лимит ($)</td>
                        <td>Статус</td>
                        <td>Действия</td>
                        <td>Последнее изменение</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($partners as $partner) : ?>
                        <tr id="<?=$partner->partner_id?>">
                            <td>
                                <?=$partner->partner_id?>
                            </td>
                            <td>
                                <b><?=Html::a($partner->user->display_name, ['/profile?id=' . $partner->partner_id . '&partner=1'], ['style' => 'color:blue;']);?></b>
                            </td>
                            <td class="p-balance">
                                <?php
                                if($partner->balance >= 100) {
                                    $style = 'color: green';
                                } elseif($partner->balance > 0 && $partner->balance < 100) {
                                    $style = 'color: orange';
                                } else {
                                    $style = 'color: red';
                                }
                                ?>
                                <b style="<?=$style ?? ''?>"><?=$partner->balance?></b>
                            </td>
                            <td  class="p-limit">
                                <?=$partner->money_limit?>
                            </td>
                            <td class="p-status">
                                <?=$partner->is_banned ? 'Заблокирован' : 'Активен'?>
                            </td>
                            <td>
                                <a href="#partner-balance-form" class="btn btn-info change-link" data-id="<?=$partner->partner_id?>" data-select="b-partner-name">
                                    Изменить баланс
                                </a>
                                <a href="#partner-limit-form" class="btn btn-info change-link" data-id="<?=$partner->partner_id?>" data-select="l-partner-name">
                                    Изменить лимит
                                </a>
                            </td>
                            <td class="p-date">
                                <?=date('d.m.Y', strtotime($partner->last_update))?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<script>
    $('.balance-form').on('click', 'button', function(e) {
        e.preventDefault;
        const id = $(this).siblings('[name=id]').children("option:selected").val(),
              partner_name = $(this).siblings('[name=id]').children("option:selected").data('name'),
              count = $(this).siblings('[name=count]').val(),
              operator = $(this).data('operator');

        if(!$.isNumeric(count) || count < 0.01) {
            alert('Минимальное значение не ниже 0.01!');
            return false;
        }

        $.ajax({
            url: '/balance/change-balance',
            type: 'GET',
            data: {
                id: id,
                count: count,
                operator: operator
            },
            success: function(res) {
                $('.alert-balance').hide();
                let result = JSON.parse(res);

                let $balance = $('#' + id + ' .p-balance'),
                    $date = $('#' + id + ' .p-date'),
                    $alert = $('.' + result.alert_class);
                $balance.html('<b>' + result.balance + '</b>');
                if(result.balance >= 100) {
                    $balance.css('color', 'green');
                } else if(result.balance > 0 && result.balance < 1000) {
                    $balance.css('color', 'orange');
                } else {
                    $balance.css('color', 'red');
                }
                $date.text(result.last_update);
                $alert.html(`Баланс пользователя <b>${partner_name}</b> изменен. Текущий баланс: <b>${result.balance}</b>. Предыдущий баланс <b>${result.old_balance}</b>.`).show();
                if(result.is_banned) {
                    $('#' + id + ' .p-status').text('Заблокирован');
                } else {
                    $('#' + id + ' .p-status').text('Активен');
                }
            },
            error: function(res) {
                alert('Error!');
            }
        });
        return false;
    });

    $('.limit-form').on('click', 'button', function(e) {
        e.preventDefault;
        const id = $(this).siblings('[name=id]').children("option:selected").val(),
            partner_name = $(this).siblings('[name=id]').children("option:selected").data('name'),
            count = $(this).siblings('[name=count]').val();

        $.ajax({
            url: '/balance/change-limit',
            type: 'GET',
            data: {
                id: id,
                count: count,
            },
            success: function(res) {
                $('.alert-balance').hide();
                let result = JSON.parse(res);

                let $limit = $('#' + id + ' .p-limit'),
                    $date = $('#' + id + ' .p-date'),
                    $alert = $('.alert-plus');
                $limit.html(result.limit);
                $date.text(result.last_update);
                $alert.html(`Лимит пользователя <b>${partner_name}</b> изменен. Текущий лимит: <b>${result.limit}</b>.`).show();
                if(result.is_banned) {
                    $('#' + id + ' .p-status').text('Заблокирован');
                } else {
                    $('#' + id + ' .p-status').text('Активен');
                }
            },
            error: function() {
                alert('Error!');
            }
        });
        return false;
    });

    $('.change-link').on('click', function() {
        let formSelector = $(this).attr("href");
        let offset = $(formSelector).offset().top - 70;
        $("html, body").animate({
            scrollTop: offset + "px"
        }, {
            duration: 500,
            easing: "swing"
        });
        const id = $(this).data('id'),
            select = $(this).data('select');
        $('#' + select + ' option[value='+id+']').prop('selected', true);

        setTimeout(function() { document.querySelector(formSelector + " input[type=number]").focus(); }, 0);
    });

</script>