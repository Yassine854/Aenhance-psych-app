<?php

$ch = curl_init('https://test.clictopay.com/payment/rest/register.do');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_CAINFO, 'C:/xampp/php/extras/ssl/cacert.pem');
$out = curl_exec($ch);

if ($out === false) {
    echo 'ERR: ' . curl_errno($ch) . ' ' . curl_error($ch) . PHP_EOL;
} else {
    echo 'OK' . PHP_EOL;
}

curl_close($ch);
