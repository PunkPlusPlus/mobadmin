<?php
echo time();
echo '<hr>';
echo date("Y-m-d H:i:s");
echo '<hr>';
echo date("Y-m-d H:i:s", time());
echo '<hr>';



$trafficarmorVerified = false;
$countryVerified = true;
$blockingVerified = false;

$xx = [
'trafficarmor_verified'=>$trafficarmorVerified,
'country_verified'=>$countryVerified,
'blocking_verified'=>$blockingVerified,
];

echo json_encode($xx);