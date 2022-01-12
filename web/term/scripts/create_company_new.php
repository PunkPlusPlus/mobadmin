<?php

$db_txt = file_get_contents($_SERVER['DOCUMENT_ROOT']."/privacy/db.txt",true);
$dbjson = json_decode($db_txt, true);


if (isset($_GET['add_company'])){
    $domain = $_GET['domain'];
    $app_name = $_GET['app_name'];
    $company_name = $_GET['company_name'];
    $email = $_GET['email'];
    $date = $_GET['date'];

    $urlprivacy  = create_company($domain, $app_name, $company_name, $email, $date);
}

if(isset($_GET['deleteid'])){
    delete_company($_GET['deleteid']);
}

$db_txt = file_get_contents($_SERVER['DOCUMENT_ROOT']."/privacy/db.txt",true);
$dbjson = json_decode($db_txt, true);

function create_company($new_domain, $new_app_name, $new_company_name, $new_email, $new_date)
{
    $db_txt = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/privacy/db.txt", true);
    $dbjson = json_decode($db_txt, true);
    $countCompany = count($dbjson['company']);
    $newCompany = [
        'app_name' => $new_app_name, 'company_name' => $new_company_name, 'email' => $new_email, 'date' => $new_date
    ];

    if (strlen($new_domain) > 1) {
        $newCompany['domain'] = $new_domain;
    }

    if (strlen($new_date) > 1) {
        $newCompany['date'] = $new_date;
    }

    //array_push($dbjson, $newCompany);
    $dbjson['company'][generate_company_name($new_app_name,$new_company_name,$new_email,$new_date)] = $newCompany;

    $text = json_encode($dbjson);
    file_put_contents('../db.txt', $text);

    //print_r($dbjson);
    $back_url = $_SERVER['HTTP_HOST'] . '/privacy/index.php' . '?compid=' . $countCompany;

    // header('Location:'.'/privacy/index.php'.'?compid='.$countCompany);
    header("Location: ../create_company_view.php");
}

function delete_company($company_id)
{

    $db_txt = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/privacy/db.txt", true);
    $dbjson = json_decode($db_txt, true);

    unset($dbjson['company'][$company_id]);

    $text_new = json_encode($dbjson);
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/privacy/db.txt", $text_new);


}

function generate_company_name($app_name,$company_name,$email,$date){

    $name = md5($app_name.$company_name.$email.$date.'jWw&1^N3z^@gw!');
    $rest = substr($name, 0, strlen($name)/2);
    return $rest;

}


