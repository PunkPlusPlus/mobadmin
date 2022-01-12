<?php
require_once ('scripts/authorization.php');

session_start();
if($_SESSION['admin'] != $hash) {
    header("Location: auth.php");
    exit;
}
require_once ('scripts/create_company_new.php');
?>

<title>AddApp</title>
<head>
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
</head>
<body>


<style>
    th {
        font-weight: normal;
        border-bottom: 2px solid #6678b1;
        border-right: 13px solid #fff;
        border-left: 13px solid #fff;
        color: black;
        padding: 8px 2px;
    }
    td {
        border-right: 13px solid #fff;
        border-left: 13px solid #fff;
        color: black;

</style>
<title>Authorization</title>
<fieldset  style="display: flex;  justify-content: center; width: 20%; margin: auto">
    <legend align="center">Application data</legend>
    <form  action="/privacy/scripts/create_company_new.php" method="get">
        <table width="30%" cellspacing="0" cellpadding="4">
            <tr>
                <td align="right" width="100"><label for="domain">Domain:</label></td>
                <td><input type="text"  name="domain" maxlength="50" size="20"/></td>
            </tr>
            <tr>
                <td align="right" width="100"><label for="AppName">AppName:</label></td>
                <td><input type="text" name="app_name" required maxlength="50" size="20"/></td>
            </tr>
            <tr>
                <td align="right" width="100"><label for="CompName">CompanyName:</label></td>
                <td><input type="text" name="company_name" required maxlength="50" size="20"/></td>
            </tr>
            <tr>
                <td align="right" width="100"><label for="email">Email:</label></td>
                <td><input type="text" name="email" required maxlength="50" size="20"/></td>
            </tr>
            <tr>
                <td align="right" width="100"><label for="date">Date:</label></td>
                <td><input type="text" name="date" value="<?=$date = date("d/m/y")?>"/></td>
            </tr>
            <tr>
                <td></td>
                <td><input name="add_company" type="submit" value="Добавить"></td>
            </tr>
        </table>
    </form>
</fieldset>


<fieldset style="margin: auto; justify-content: center; display: flex; width: 75%">
    <legend align="center">Application list</legend>
        <table align="center">
            <tr>
                <td>md5</td>
                <td>Домен</td>
                <td>Название приложения</td>
                <td>Имя компании</td>
                <td>Электронная почта</td>
                <td>Дата создания</td>
            </tr>


<?php
$newData = [];
foreach ($dbjson['company'] as $key => $value){
    $value["old"]=$key;
    $newData[] = $value;
}

usort($newData, 'sortdate');

function redate($rd) {
    $exp = explode('/',$rd);
    return sprintf('%d-%d-%d', 2000+$exp[2],$exp[1],$exp[0]);
}

function sortdate($date1, $date2){

    $date1=strtotime(redate($date1['date']));
    $date2=strtotime(redate($date2['date']));

    return -($date1<=>$date2);
}


foreach ($newData as $key => $value)
    {
        $date = $value['date'] ?? $date = date("d-m-y");
        $domain = $value['domain'] ?? $_SERVER['HTTP_HOST'];

        print "<tr>
                    <td>".$value['old']."</td>
                    <td>".$domain."</td>
                    <td>".$value['app_name']."</td>
                    <td>".$value['company_name']."</td>
                    <td>".$value['email']."</td>
                    <td>".$date."</td>
                    <td><a href='https://$domain/privacy/index.php?compid=".$key."'><button type='submit'>Переход по ссылке</button></a></td>
                    <td> 
                    <button id=\"btn\"  data-clipboard-text=\"https://$domain/privacy/index.php?compid=".$key."\">Копировать ссылку</button>
                    </td>
                    <td><a href='/privacy/create_company_view.php?deleteid=".$key."'><button type='submit'>Удалить</button></a></td>
              </tr>";

    }


?>

        </table>
</fieldset>
</body>

<script>

 var btns = document.querySelectorAll('button');
    var clipboard = new ClipboardJS(btns);

clipboard.on('success', function(e) {
    console.log(e['text']);
    alert('Ссылка скопирована в буфер обмена');
});

clipboard.on('error', function(e) {
    console.log(e['text']);
    alert('Ошибка копирования!');
});

</script>



