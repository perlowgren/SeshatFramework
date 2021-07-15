<?php

require_once '../include/seshat-conf.php';

header('Content-type: text/plain; charset=UTF-8');

$name = $_REQUEST['name'];
$path = DIR_DOC.'wiki/';
$file_types = array('md','txt');
$text = '';
foreach($file_types as $ext)
	if(file_exists($file=$path.$name.'.'.$ext)) {
		$text = file_get_contents($file);
		break;
	}

echo $text;
exit;

