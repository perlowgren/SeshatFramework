<?php

namespace Seshat;

use Seshat\AnubisAjax;
use Seshat\IsisDB;

use Facebook\Facebook;
use Facebook\FacebookResponse;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

require_once '../include/seshat-conf.php';

$db = IsisDB::open('SQLite3',DB_SQLITE3);
$set = new AnubisAjax($db);

$fb = new Facebook([
	'app_id' => FB_APP_ID,
	'app_secret' => FB_APP_SECRET,
	'default_graph_version' => 'v2.2',
]);

$helper = $fb->getJavaScriptHelper();
try {
	$access_token = (string)$helper->getAccessToken();
} catch(FacebookResponseException $e) {
	// When Graph returns an error
	AnubisAjax::error(500,'Graph returned an error: '.$e->getMessage());
} catch(FacebookSDKException $e) {
	// When validation fails or other local issues
	Setsession::error(500,'Facebook SDK returned an error: '.$e->getMessage());
}
if(!isset($access_token)) {
	AnubisAjax::error(500,'No cookie set or no OAuth data could be obtained from cookie.');
}

try {
	// Returns a `FacebookResponse` object
	$response = $fb->get('/me?fields=id,name,email',$access_token);
} catch(FacebookResponseException $e) {
	AnubisAjax::error(500,'Graph returned an error: '.$e->getMessage());
} catch(FacebookSDKException $e) {
	AnubisAjax::error(500,'Facebook SDK returned an error: '.$e->getMessage());
}

$user = $response->getGraphUser();


function generate_password($length=8,$chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
	for($i=0,$n=strlen($chars),$result=''; $i<$length; ++$i)
		$result .= $chars[rand(0,$n-1)];
	return $result;
}

$name = $user->getName();
$email = strtolower($user->getEmail());
$data = $db->row('SELECT uid,user,pass,email,lang,data,auth FROM user WHERE user=? OR email=?',array($name,$email));
if($data) {

	$uid = $data['uid'];
	$name = $data['user'];
	$email = $data['email'];
	$pass = $data['pass'];
	$lang = $data['lang'];
	$_SESSION['lang'] = $lang;

	$user = $data['user'];
	unset($data['pass']);
	$_SESSION['user'] = $data;

} else {
	$tm = time();
	$user = $name;
	$pass = generate_password();
	$lang = AnubisAjax::getLanguage();
	$db->exec('INSERT INTO user (uid,user,pass,email,lang,data,auth,created,changed) VALUES (NULL,?,?,?,?,?,?,?,?)',
					array($user,md5($pass),$email,$lang,'',AUTH_USER,$tm,$tm));
	$uid = $db->insertID();
	$_SESSION['user'] = array('uid'=>$uid,'user'=>$user,'email'=>$email,'data'=>'','auth'=>AUTH_USER);

	$name = mb_encode_mimeheader($name,'UTF-8','Q');
	$app_title = _('app-title');
	$app_name = mb_encode_mimeheader(_('app-name'),'UTF-8','Q');
	$app_email = _('app-email');
	$app_theme = _('app-theme');
	$noreply_email = _('noreply-email');

	AnubisAjax::sendEmailTemplate("{$app_name} <{$noreply_email}>","{$name} <{$email}>",'register',false,false,
		array('uid'=>$uid,'user'=>$user,'email'=>$email,'password'=>$pass,
				'app-title'=>$app_title,'app-name'=>$app_name,'app-email'=>$app_email,'app-theme'=>$app_theme,
				'https-host'=>HTTPS_HOST,'url-theme'=>HTTPS_HOST."/theme/{$app_theme}/"));
}

AnubisAjax::output("{\"code\":200,\"message\":\"OK\",\"user\":\"{$user}\",\"email\":\"{$email}\"}");

