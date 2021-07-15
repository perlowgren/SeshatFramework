<?php

namespace Seshat;

use Seshat\AnubisAjax;
use Seshat\IsisDB;
use Seshat\MaatMark;

require_once '../include/seshat-conf.php';

$db = IsisDB::open('SQLite3',DB_SQLITE3);
$set = new AnubisAjax($db);

mb_internal_encoding('UTF-8');

if(!isset($_GET['name']) || !$_GET['name'])
	AnubisAjax::error(400,"Missing name field");
if(!isset($_GET['email']) || !$_GET['email'])
	AnubisAjax::error(400,"Missing email field");

$name = mb_encode_mimeheader($_GET['name'],'UTF-8','Q');
$email = $_GET['email'];
$cc = isset($_GET['cc']);

$plain = file_get_contents('php://input');

$maat = new MaatMark();
$html = $maat->parse($plain);

$contact_name = mb_encode_mimeheader(_('contact-name'),'UTF-8','Q');;
$contact_email = _('contact-email');
$app_theme = _('app-theme');
$noreply_email = _('noreply-email');

$from = array(
	'from'=>"{$name} <{$noreply_email}>",
	'reply-to'=>"{$name} <{$email}>"
);
if($cc) $from['cc'] ="{$name} <{$email}>";
$to = "{$contact_name} <{$contact_email}>";

$template = 'contact';
$vars = array(
	'app-title'=>_('app-title'),
	'time'=>date('Y-m-d H:i:s'),
	'plain'=>$plain,
	'html'=>$html,
);
AnubisAjax::sendEmailTemplate($from,$to,$template,false,false,$vars);

$message = _('contact-message-sent');

AnubisAjax::output("{\"code\":200,\"message\":\"{$message}\"}");

