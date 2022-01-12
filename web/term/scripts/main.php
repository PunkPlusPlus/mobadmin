<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$db_txt = file_get_contents($_SERVER['DOCUMENT_ROOT']."/privacy/db.txt",true);
$dbjson = json_decode($db_txt, true);


$company_id = $_GET['compid'] ?? "campid_0";
if(!isset($dbjson['company'][$company_id])){
    echo "Такой компании не существует";
    exit();
}
$domain = $dbjson['company'][$company_id]['domain'] ?? $_SERVER['HTTP_HOST'];
$app_name = $dbjson['company'][$company_id]['app_name'];
$company_name = $dbjson['company'][$company_id]['company_name'];
$email = $dbjson['company'][$company_id]['email'];
$date = $dbjson['company'][$company_id]['date'];

?>