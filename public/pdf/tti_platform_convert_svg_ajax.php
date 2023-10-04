<?php


$b64 = $_POST['uri'];
$keyname = $_POST['keyname'];

$filename_path = $keyname.".jpg";
$base64_string = str_replace('data:image/png;base64,', '', $b64);
$base64_string = str_replace(' ', '+', $base64_string);
$decoded = base64_decode($base64_string);
file_put_contents($filename_path,$decoded);

exit;