<?php

use Seshat\AnubisAjax;
use Seshat\IsisDB;
use Seshat\WadjetWiki;

require_once '../include/seshat-conf.php';

$_REQUEST['action'] = 'save';

$db = IsisDB::open('SQLite3',DB_SQLITE3);
$set = new AnubisAjax($db);
$wiki = new WadjetWiki();
$wiki->savePage();
$msg = $wiki->getMessage();

AnubisAjax::output('{'.
	'"pid":'.$wiki->getPageID().','.
	'"rid":'.$wiki->getRevisionID().','.
	'"name":"'.addslashes($wiki->getRealName()).'",'.
	'"page":"'.addslashes($wiki->getPageName()).'",'.
	'"lang":"'.$wiki->getPageLanguage().'",'.
	'"message":"'.($msg? addslashes($msg) : 'OK').'"'.
'}');

