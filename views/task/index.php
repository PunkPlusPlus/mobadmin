<?php
use webvimark\modules\UserManagement\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;
?>

<?= Html::beginForm("/task/create", 'post'); ?>
<?php
try {
    $id = $app->id;
} catch (\Exception $e) {
    $id = "";
}
?>
<input type="text" name="id" value="<?= $id ?? -1 ?>" style="visibility: hidden">
<div style="width: 60%; padding: 30px;">

    <?php
    try {
        echo "<h3>Расшар аккаунтов на приложение: $app->name</h3>";
    } catch (\Exception $e) {
        echo "<h3>Расшар аккаунтов</h3>";
    }
    ?>
    <br>
    <?php
    if (User::hasPermission('share_accounts_manually')) :
    ?>
        <input type="text" class="form-control" required  value="<?= $data['uuid'] ?? $_SESSION["params"]['uuid'] ??  ""; ?>" name="uuid" placeholder="uuid" style="width: 40%; margin: 10px">
        <input type="text" aria-multiline="true" required  class="form-control" value="<?= $data['app_id'] ?? $_SESSION['params']['app_id'] ??  ""; ?>" name="app_id" placeholder="app_id" style="width: 40%; margin: 10px">
    <?php endif; ?>
        <div id="inputs" style="">
            <textarea name="ids" class="form-control" required  id="ids" cols="5" rows="8" style="resize: none; margin: 10px; width: 40%"><?php echo $params['ids'] ?? "" ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="margin: 10px">Create task</button>
</div>

<?php
if (isset($response) && !User::hasPermission('share_accounts_manually')) {
    if (isset($response['Error'])) {
        echo "<div style='width: 20%; font-size: 2ch; margin-left: 40px; padding: 30px;' class='alert-danger'>Error</div>";
    } else {
        echo "<div style='width: 20%; font-size: 2ch; margin-left: 40px; padding: 30px;' class='alert-success'>Success</div>";
    }
}
?>

<?= Html::endForm(); ?>

<br>

<div style="width: 60%; padding: 30px;">
    <hr>
    <?php if (User::hasPermission('share_accounts_manually')) : ?>
    <?= Html::beginForm(['task/get-one'], 'get', ['data-pjax' => '', 'class' => 'form-inline']); ?>

    <input type="text" class="form-control" value="<?php echo $_GET['task_id'] ?? "" ?>" name="task_id" placeholder="task_id" style="width: 40%; margin: 10px">

    <?= Html::submitButton('Get task', ['class' => 'btn btn-primary', 'name' => 'get-task']) ?>
    <?= Html::endForm() ?>
    <?php endif; ?>

</div>
    <?php if (User::hasPermission('share_accounts_manually')) : ?>
<span style="width: 40%; padding: 30px;">
    <div style="padding: 15px; border-radius: 5px; height: 25ch; margin-left: 30px;">
        <span style="width: 45%; float: left">
            <?php
            try {
                echo DetailView::widget([
                    'model' => $response
                ]);
            } catch (\Exception $e) {
            }
            ?>
        </span>
    </div>
</span>
    <?php endif; ?>
</div>



