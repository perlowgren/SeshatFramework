<?php

use Seshat\AnubisAjax;
use Seshat\IsisDB;
use Seshat\WadjetWiki;

require_once '../include/seshat-conf.php';

$_REQUEST['action'] = 'save';

$db = IsisDB::open('SQLite3',DB_SQLITE3);
$set = new AnubisAjax($db);
$wiki = new WadjetWiki();
$link = $wiki->getLinkHandler();

if(!$wiki->hasWritePermission()) AnubisAjax::error(401);

$msg = '';

$data = file_get_contents('php://input');
if(!$data) $msg = 'No data';
else {
	$index = strpos($data,'base64,')+7;
	if($index===false) $msg = 'Not base64 encoded data';
	else {
		$file = base64_decode(substr($data,$index));
		if(!$file) $msg = 'No data';
		else file_put_contents(DIR_RESOURCE."wiki/{$link->name}",$file);
	}
}

AnubisAjax::output('{'.
	'"name":"'.addslashes($wiki->getRealName()).'",'.
	'"page":"'.addslashes($wiki->getPageName()).'",'.
	'"message":"'.($msg? addslashes($msg) : 'OK').'"'.
'}');

