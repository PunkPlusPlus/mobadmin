<?php

use app\models\Apps;
use yii\helpers\Html;

?>

<?php

if($data['app'] && $data['app']->fb_app_id && $data['app']->app_secret) {
    $app = $data['app'];
?>
<div class="card-body">
    <div class="form-control" style="padding: inherit">
        <h3>Sending</h3>
        <?= Html::beginForm(['fb/execute?id='.$app->id], 'get') ?>
        <br>
        <div style="margin-left: 1%">
            <p>Количество событий</p>
            <input type="number" value="<?php echo $data['limit'] ?? 0 ?>" name="limit" class="form-control" style="width: 30%">
        </div>
        <br>
        <div style="display:flex;">
            <div class="col-sm-6 col-xl-2 mb-30">
                <h6 class="sub-title"><?=Yii::t('app', 'date_start');?></h6>
                <input type="text" required name="from" class="form-control" id="datepicker_start">
            </div>
            <div class="col-sm-6 col-xl-2 mb-30">
                <h6 class="sub-title"><?=Yii::t('app', 'date_end');?></h6>
                <input type="text" required name="to" class="form-control" id="datepicker_end">
            </div>
        </div>
        <p></p>

        <p></p>
        <button type="submit" class="btn btn-primary">Отправить</button>
        <p></p>
        <?= Html::endForm() ?>
    </div>
    <?php
    } else {
        ?>
        <div class="card-body">
            <div class="form-control" style="padding: inherit">
                <h3>Sending</h3>
                <?= Html::beginForm(['fb/execute'], 'get') ?>
                <div style="margin-left: 1%">
                    <p><h6>app_id</h6></p>
                    <input type="text" placeholder="facebook app id" value="<?php echo $data['app_id'] ?? '';?>" required name="app_id" class="form-control" style="width: 30%;">
                    <p><h6>app_secret</h6></p>
                    <input type="text" placeholder="facebook app secret" value="<?php echo $data['secret'] ?? '';?>" required name="app_secret" class="form-control" style="width: 30%;">
                    <br>
                    <p>Количество событий</p>
                    <input type="number" value="<?php echo $data['limit'] ?? 0 ?>" name="limit" class="form-control" style="width: 30%">
                </div>
                <br>


                <div style="display:flex;">
                    <div class="col-sm-6 col-xl-2 mb-30">
                        <h6 class="sub-title"><?=Yii::t('app', 'date_start');?></h6>
                        <input type="text" required name="from" class="form-control" id="datepicker_start">
                    </div>
                    <div class="col-sm-6 col-xl-2 mb-30">
                        <h6 class="sub-title"><?=Yii::t('app', 'date_end');?></h6>
                        <input type="text" required name="to" class="form-control" id="datepicker_end">
                    </div>
                </div>
                <p></p>
                <button type="submit" class="btn btn-primary">Отправить</button>
                <?= Html::endForm() ?>
            </div>
        </div>
        <?php
    }
    ?>
    <br><br>
    <div class="card-body">
        <h3>Result</h3>

            <?php
            $res = $data['response'] ?? null;
            $general_count = 0;
            $err = 0;
            if ($res != null) {
                $general_count = count($res);
                $count = 0;
                for ($i = 0; $i < count($res); $i++) {
                    if ($res[$i]['success'] ?? false == true) {
                        $count++;
                    }
                }
                $err = $general_count - $count;
                ?>
        <div class="alert-success" style="font-size: large; padding: 0.5%">
            <?php
                echo "Успешно отправлено " . $count . " событий(я).";
            }
            ?>
        </div>
        <br>
            <?php
            if ($general_count !== 0) {
                ?>
        <div class="alert-warning" style="font-size: large; padding: 0.5%">
            <?php
                echo 'Не отправлено ' . $err . " событий(я).";
            }
            ?>
        </div>

        <br>
        <?php
        if ($err > 0) {
        ?>
        <div class="alert-danger" style="font-size: large; padding: 0.5%">
            <?php

                if (isset($res[0]['drop_reason'])) {
                    echo $res[0]['drop_reason'];
                } else {
                    echo $res[0]['error']['message'];
                    echo "<br>";
                    echo $res[0]['error']['type'];
                    echo "<br>";
                    echo 'Code: ' . $res[0]['error']['code'];
                }

            ?>
        </div>
        <?php } ?>
    </div>
</div>
<script>
    $(document).ready(function () {
        moment.updateLocale('en', {
            week: {dow: 1} // Monday is the first day of the week
        });
        var params = window
            .location
            .search
            .replace('?','')
            .split('&')
            .reduce(
                function(p,e){
                    var a = e.split('=');
                    p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                    return p;
                },
                {}
            );

        $('#datepicker_start').datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'ru',
            //calendarWeeks:true,
        });

        $('#datepicker_end').datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'ru',
            //calendarWeeks:true,
        });

        var dateStart = params['from'];
        var dateEnd = params['to'];


        if(dateStart == undefined) dateStart = '<?=$data['from'] ?? date("d/m/Y", strtotime('-1 dayss'));?>';
        if(dateEnd == undefined) dateEnd = '<?=$data['to'] ?? date("d/m/Y");?>';

        $('#datepicker_start').val(dateStart);
        $('#datepicker_end').val(dateEnd);
    });
</script>




