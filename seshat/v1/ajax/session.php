<?php

namespace Seshat;

use Seshat\AnubisAjax;
use Seshat\IsisDB;

require_once '../include/seshat-conf.php';

$db = IsisDB::open('SQLite3',DB_SQLITE3);
$set = new AnubisAjax($db,'text/plain');

$err = array();
$json = file_get_contents('php://input');
$json = json_decode($json,true);

if(isset($json['action'])) {
	$action = $json['action'];
	if($action=='logout') {
		if(isset($_SESSION['user']))
			unset($_SESSION['user']);
		if(isset($_COOKIE['access-token']))
			setcookie('access-token','',time()-3600);
		if(isset($_SESSION['access-token']))
			unset($_SESSION['access-token']);
	} elseif($action=='login') {
		$user = strval($json['user']);
		$email = strtolower($user);
		$pass = strval($json['pass']);
		$data = $db->row('SELECT uid,user,pass,email,lang,data,auth FROM user WHERE user=? OR email=?',array($user,$email));
		if(!$data) $err[] = _('no-such-user');
		elseif($data['pass']!==md5($pass)) $err[] = _('password-missmatch');
		else {
			unset($data['pass']);
			$_SESSION['lang'] = $data['lang'];
			$_SESSION['user'] = $data;
		}
	}
}

if(isset($json['lang'])) {
	$lang = $json['lang'];
	if(!preg_match('/^\w\w$/',$lang)) $err[] = _('invalid-language-code');
	else {
		$_SESSION['lang'] = $lang;
		$uid = AnubisAjax::getUserID();
		if($uid)
			$db->exec('UPDATE user SET lang=? WHERE uid=?',array($lang,$uid));
	}
}

if(isset($json['theme'])) {
	$theme = $json['theme'];
	if(!preg_match('/^[\w-_]+$/',$theme)) $err[] = _('invalid-theme');
	else $_SESSION['theme'] = $theme;
}

AnubisAjax::output(count($err)? implode(';',$err) : 'OK');

