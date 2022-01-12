<?php
session_start();

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://hcaptcha.com/siteverify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "response=CLIENT-RESPONSE&secret=0x843Db3510C84f3846009FA674FD34dcf9F44546A");

$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);


$hash =  "b9934789d88ccdd697a9fe11403e5f62";
function auth($passwordUser, $loginUser, $hash)
{
    $hCaptchaResponse = $_POST['h-captcha-response'];
    $data = array(
        'secret' => "0x843Db3510C84f3846009FA674FD34dcf9F44546A",
        'response' => $hCaptchaResponse
    );
    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($verify);
    $responseJson = json_decode($response);
    if($responseJson->success) {

    }else{
        print "<script>
let isBoss = confirm('You didn\'t pass captcha');
window.location.href = '/privacy/auth.php';
</script>";
        exit;
    }

    $hashUser = md5($loginUser.$passwordUser.'asdwqc324c3212');

    if($hash == $hashUser)
    {
        $_SESSION['admin'] = $hash;
        header("Location: /privacy/create_company_view.php");
        exit;
    } else
        echo "<script>alert('Login or password entered incorrectly!');</script>";

}

if(isset($_POST['enter']) && isset($_POST['login']) && isset($_POST['pass'])){
    auth($_POST['pass'], $_POST['login'], $hash);

}

?>