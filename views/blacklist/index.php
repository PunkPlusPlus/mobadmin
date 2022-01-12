<?php
    use yii\bootstrap\ActiveForm;
    use yii\helpers\Html;
    use app\components\BlackListComponent;
?>
<h3 class="title">Add user to black/white list</h3>
<br>

<?= Html::beginForm(['blacklist/index'], 'post') ?>
    <div style="margin: 2%">
        <p><h6>IDFA</h6></p>
        <input type="text" placeholder="idfa" value="<?php echo $model->idfa ?? '';?>" required name="idfa" class="form-control" style="width: 30%;">
        <p><h6>Action</h6></p>
        <select name="list" id="list" class="custom-select" style="width: 30%">
            <option value="1">Add to black list</option>
            <option value="0">Add to white list</option>
            <option value="-1">Delete from all lists</option>
        </select>
        <br>
        <button type="submit" style="margin-top: 3ch" class="btn btn-primary">Отправить</button>

        <div class="info" style="margin-top: 10ch">
            <div class="alert-danger" style="width: 30%; font-size: medium; padding: 1ch; border-radius: 0.5ch">
            <a href="/blacklist/black"><?php
                    echo 'Пользователей в черном списке: ' . BlackListComponent::getCount(1);
                    ?>
                </a>
            </div>
            <div class="alert-info" style="width: 30%; font-size: medium; padding: 1ch; margin-top: 1ch; border-radius: 0.5ch">
            <a href="/blacklist/white">
                    <?php
                    echo 'Пользователей в белом списке: ' . BlackListComponent::getCount(0);
                    ?>
                </a>
            </div>
        </div>


    </div>


<?= Html::endForm() ?>



