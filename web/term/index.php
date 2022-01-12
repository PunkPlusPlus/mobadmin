<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once('scripts/localization.php');
require_once ('scripts/main.php');
require_once ('scripts/create_company_new.php');


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/html"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="./style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&family=Lilita+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>

<body>
    <div class="block1" style="background-color:#E1E8E9; max-width:950px auto;">

    <div style="font-family: 'Lilita One', cursive;background-color: #343a40!important;" >

    <div style="max-width: 950px; margin: 0 auto;">
        <nav class="navbar navbar-dark bg-dark d-flex justify-content">
            <span class="navbar-brand mb-0 h1">Terms and Conditions</span>
                <form name="language_form" method="get" action="/privacy/index.php">
                    <select name="language" onchange="submit()">
                        <option value="none" hidden="">Select language</option>
                        <option value="eng">Eng</option>
                        <option value="aze">Aze</option>
                        <option value="uzb">Uzb</option>
                    </select>
                    <input name="compid" type="hidden" value="<?=$_GET['compid']??''?>"/>
                </form>
        </nav>
    </div>
        <div class="header_bg"></div>
        <!-- <img src="./img/обрезанное.png"> -->
    </div>

<?php

$str = str_replace('{company_name}', $company_name, $data['privacy_text']);
$str = str_replace('{email}', $email, $str);
$str = str_replace('{date}', $date, $str);

echo $str;

?>




<footer>
	<div style="font-family: 'Lilita One', cursive;"  class="page-footer font-big">
	       <div class="footer-copyright text-center py-3 bg-dark text-white text-uppercase">© 2020 <span class="company_name"><?=$company_name?></span>.All rights reserved</div>
    </div>
</footer>
</body>
</html>