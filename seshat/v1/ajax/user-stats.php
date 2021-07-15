<?php

use Seshat\AnubisAjax;
use Seshat\IsisDB;

require_once '../include/seshat-conf.php';

$db = IsisDB::open('SQLite3',DB_SQLITE3);
$set = new AnubisAjax($db);

header('Cache-Control: no-cache');
header('Pragma: no-cache');

$mail = 0;
$log = 0;
$forum = 0;
/*if($sess->uid) {
	$a['mail'] = $db->column('SELECT count(*) FROM user_mail WHERE uid=? AND status=0',array($sess->uid));
	$a['log'] = $db->column('SELECT count(*) FROM user_log WHERE uid=? AND status=0',array($sess->uid));
	$a['forum'] = $db->column('SELECT count(*) FROM forum_new WHERE uid=?',array($sess->uid));
}*/

AnubisAjax::output("{\"mail\":{$mail},\"log\":{$log},\"forum\":{$forum}}");

