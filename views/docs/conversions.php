При редактирование связи между страной и приложением в URL ссылки требуется добавить следующий параметр:
<div class="alert alert-info" role="alert">
    <b>{appsflyer_device_id}</b>
</div>
<br>
Например до добавления параметра у вас стояла следующая ссылка:
<div class="alert alert-info" role="alert">
    http://sitehigh.com/?type=landing&user_id={sub_1}&click_id={sub_2}&country={country}
</div>
<br>
После добавления, она должна иметь следующий вид:
<div class="alert alert-info" role="alert">
    http://sitehigh.com/?type=landing&user_id={sub_1}&click_id={sub_2}&country={country}<b>&deviceid={appsflyer_device_id}</b>
</div>
<br>

<b style="display:none">Для Бинома. Указать следующий URL в поле "S2S Postback"</b>
<div class="alert alert-success" style="display:none" role="alert">
   <input type="text" value="https://pb.profitnetwork.app/postback?binom=1&service=appsflyer&key={t1}&name=dep&amount={payout}&currency=USD" style="width:100%;" disabled>
</div>


Далее вам требуется настроить postback в вашем трекере/партнерке на данный URL:
<div class="alert alert-info" role="alert">
    <input type="text" value="https://pb.profitnetwork.app/postback?service=appsflyer&key={deviceid}&name={event_name}&amount={amount}&currency={currency}" style="width:100%;" disabled>
</div>
<br>
<table class="table table-striped table-bordered">
    <tr>
        <td><b>Название ключа</b></td>
        <td><b>Возможные значения</b></td>
        <td><b>Описание</b></td>
    </tr>
    <tr>
        <td>service</td>
        <td>appsflyer</td>
        <td>Название сервиса через который прокидываются конверсии</td>
    </tr>
    <tr>
        <td>key</td>
        <td>Всегда уникально</td>
        <td>Уникальный идентификатор запуска (передается через специальный параметр в вашем URL - {appsflyer_device_id})</td>
    </tr>
    <tr>
        <td>name</td>
        <td>Любое значение</td>
        <td>Название конверсии (может быть любым, обычно пишут: af_reg <- при регистрации и af_deposit <- при депозите)</td>
    </tr>
    <tr>
        <td>amount</td>
        <td>Любые цифры</td>
        <td>Сумма которую вам заплатил партнер за данную конверсию</td>
    </tr>
    <tr>
        <td>currency</td>
        <td>USD, EUR, RUB и т.д.</td>
        <td>Валюта в которой вам выплачивает партнер (например: USD, EUR, RUB и т.д.)</td>
    </tr>
</table>
