<form>

    <table>
        <tr>
            <td>
                Ссылка на приложение:
                <br><br><br>
            </td>
            <td>
                <input id="app_url" class="form-control" style="min-height: 20px !important; margin-bottom: 5px;">
                (Пример ссылки: <b>https://play.google.com/store/apps/details?id=awe.joyfuldev.crazyroll</b>)
                <br><br>
            </td>
        </tr>
        <tr>
            <td>Пакет вашего приложения:&nbsp;&nbsp;</td>
            <td><input id="app_package" class="form-control" style="opacity: 0.8; color:black; min-height: 20px !important; margin-bottom: 5px;" disabled value="Укажите ссылку"></td>
        </tr>
        <tr>
            <td>Название класса:</td>
            <td><input id="app_classname" class="form-control" style="opacity: 0.8; color:black; min-height: 20px !important; margin-bottom: 5px;" disabled value="Укажите ссылку"></td>
        </tr>
        <tr>
            <td>Hash-ключ:</td>
            <td><input id="app_hash" class="form-control" style="opacity: 0.8; color:black; min-height: 20px !important; margin-bottom: 5px;" disabled value="Укажите ссылку"></td>
        </tr>
    </table>
</form>
<br><br>
После получения APP ID, копируем и указываем его в настройках ГЕО в текущей админке:
<div class="alert alert-info" role="alert">
    <b>Выбор приложения -> Добавить/редактировать страну -> Добавить параметр -> fb_app_id</b>
</div>
<b>Готово!</b>


<script>
    $(document).on("input", "#app_url",function(ev){
        genDeepLink();
    });

    function genDeepLink(){
        var currLink = document.getElementById("app_url").value;
        var arr = currLink.split("?id=");
        if(arr.length == 2){
            document.getElementById("app_package").value = arr[1];
            document.getElementById("app_classname").value = "MainActivity";
            document.getElementById("app_hash").value = "Не требуется";
        }else{
            document.getElementById("app_package").value = "Укажите ссылку";
            document.getElementById("app_classname").value = "Укажите ссылку";
            document.getElementById("app_hash").value = "Укажите ссылку";
        }
    }
</script>