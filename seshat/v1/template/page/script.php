<?php

header('Content-Type: application/javascript; charset=UTF-8');

$seconds = 2592000;
$expires = gmdate('D, d M Y H:i:s',time()+$seconds);
header("Expires: {$expires} GMT");
header('Pragma: cache');
header("Cache-Control: max-age={$seconds}");

?>
{* include page/script.js }

