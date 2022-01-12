<?php
 require_once('scripts/authorization.php');
    session_start();
?>


<head>
    <title>Авторизация</title>
    <script src="https://hcaptcha.com/1/api.js" async defer></script>
</head>

<form id="sendForm" align="center"  method="post">
    <input type="text" name="login" placeholder="Enter your login"/><br/>
    <br/>
    <input type="password" name="pass" placeholder="Enter your password"/><br/>
    <br/>
    <input  type="submit" name="enter" value="Login"/>
    <div class="h-captcha" data-sitekey="fee7b658-4c2f-4c63-965e-e5f90e6d726b" data-theme="dark" data-size="compact"></div>
</form>




