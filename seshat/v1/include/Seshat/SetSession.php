<?php
/** Session, database and user handler.
 * 
 * @file include/Seshat/SetSession.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-05
 * @date Created: 2015-04-14
 */

namespace Seshat;

use SessionHandlerInterface;
use Seshat\IsisDB;
use Seshat\WadjetWiki;

class SetSession implements SessionHandlerInterface {
	private static $set = false;  //!< Reference to the first instance of SetSession

	protected $db;                //!< Reference to instance of IsisDB
	protected $uid;               //!< User ID
	protected $user;              //!< User data
	protected $auth;              //!< Authentication level of user
	protected $theme;             //!< Theme
	protected $lang;              //!< Language of text output
	protected $strings;           //!< Text strings in $lang-language
	protected $mime;              //!< Output Mime-type

	/** Constructor; initiate parameters
	 * @param &$db Reference to IsisDB object
	 * @param $mime Output mime, default for SetSession is to act as a RestAPI and output JSON */
	public function __construct(&$db,$mime='application/json') {
		if(!self::$set) self::$set = &$this;

		$this->db        = false;
		if($db instanceof IsisDB)
			$this->db     = &$db;
		$this->uid       = 0;
		$this->user      = null;
		$this->auth      = AUTH_NONE;
		$this->theme     = null;
		$this->lang      = null;
		$this->strings   = false;
		$this->mime      = $mime;
		$this->error     = false;

		if($this->db->isNew()) $this->install();
		session_set_save_handler($this,false);
		session_start();

		if(isset($_SESSION['user'])) {
			$this->user   = &$_SESSION['user'];
			$this->uid    = intval($this->user['uid']);
			$this->auth   = intval($this->user['auth']);
		}

		if(!isset($_SESSION['theme']))
			$_SESSION['theme'] = _('app-theme');
		$this->theme  = &$_SESSION['theme'];

		if(!isset($_SESSION['lang'])) {
			if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $lang = _('app-lang');
			else $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);
/*			if(($p=strpos($lang,'-'))!==false) $lang = substr($lang,0,$p);
			if(($p=strpos($lang,'_'))!==false) $lang = substr($lang,0,$p);
			if(($p=strpos($lang,','))!==false) $lang = substr($lang,0,$p);
			if(($p=strpos($lang,';'))!==false) $lang = substr($lang,0,$p);*/
			$_SESSION['lang'] = $lang;
		}
		$this->lang = &$_SESSION['lang'];
	}

	public static function getUserID() { return self::$set? self::$set->uid : 0; }
	public static function getUserData($d) { return self::$set && isset(self::$set->user[$d])? self::$set->user[$d] : ''; }

	public static function setPermission($p=AUTH_NONE) { if(self::$set && self::$set->auth<$p) self::error(401); }
	public static function hasPermission($p) { return self::$set && self::$set->auth>=$p; }
	public static function getAuth() { return self::$set? self::$set->auth : AUTH_NONE; }

	public static function getTheme() { return self::$set? self::$set->theme : _('app-theme'); }
	public static function getLanguage() { return self::$set? self::$set->lang : _('app-lang'); }

	public static function getStrings() { return self::$set? self::$set->strings : null; }
	public static function getString($id) {
		if(self::$set) {
			if(self::$set->strings===false) self::loadStrings();
			if(isset(self::$set->strings[$id]) &&
				($tr=self::$set->strings[$id])!=='') return $tr;
		}
		return $id;
	}

	public static function loadStrings($name='seshat',$lang=false) {
		if(!self::$set) return;
		if($lang===false) $lang = self::$set->lang;
		if(!file_exists($f=DIR_INCLUDE."{$name}-{$lang}.php")) {
			if($lang!='en') self::loadStrings($name,'en');
		} else {
			$f = array_merge(Seshat_config(),require $f);
			if(!is_array(self::$set->strings)) self::$set->strings = $f;
			else self::$set->strings = array_merge(self::$set->strings,$f);
			if(self::$set->strings===false) self::$set->strings = array();
		}
	}

	public static function log($msg) { error_log($msg); }

	/** Close session and db */
	public static function quit() {
		if(self::$set) {
			self::$set->close();
			if(self::$set->db)
				self::$set->db->close();
		}
		exit;
	}

	/** Write output text and quit. */
	public static function output($text,$mime=false) {
		if(!$mime && self::$set) $mime = self::$set->mime;
		header("Content-Type: {$mime}; charset=UTF-8");
		echo $text;
		self::quit();
	}

	/** Write an error page. */
	protected function errorPage($code=500,$error=false) {
		if($this->mime=='application/json')
			$error = '{"code":'.$code.',"error":"'.$error.'"}';
		self::output($error);
	}

	/** Send a HTTP header and error data.
	 * @param $code HTTP-status-code, e.g. 400.
	 * @param $error Error code name */
	public static function error($code=500,$error=false) {
		$code = $code+0;
		$header = self::httpStatusHeader($code);
		if($error===false) $error = $header;
		if(self::$set) self::$set->errorPage($code,$error);
		else self::output($error);
	}

	/** Send a HTTP header.
	 * @param $code HTTP-status-code, e.g. 200. */
	public static function httpStatusHeader($code=200) {
		require_once DIR_INCLUDE.'http.php';
		$header = http_status_header($code);
		if($header===false) self::error(500,_('Unknown HTTP Header Code').': '.$code);
		return substr($header,9);
	}

	public static function sendEmail($from,$to,$subject,$message) {
		require_once DIR_INCLUDE.'email.php';
		return send_email($from,$to,$subject,$message);
	}

	public static function sendEmailTemplate($from,$to,$template,$theme=false,$lang=false,$vars=array()) {
		if(!$theme) $theme = isset($_SESSION['theme'])? $_SESSION['theme'] : _('app-theme');
		if(!$lang) $lang = isset($_SESSION['lang'])? $_SESSION['lang'] : 'en';
		$plain = self::parseTemplate(DIR_TEMPLATE."email/{$template}-{$lang}.txt",$vars);
		$html  = self::parseTemplate(DIR_TEMPLATE."email/{$template}-{$lang}.html",$vars);
		if($plain===false && $html===false) {
			if($lang=='en') return false;
			return self::sendEmailTemplate($from,$to,$template,$theme,'en',$vars);
		}
		$subject = isset($vars['subject'])? $vars['subject'] : _('app-name');
		$message = array('plain'=>$plain,'html'=>$html);
		if(isset($vars['embed'])) $message['embed'] = $vars['embed'];
		return self::sendEmail($from,$to,$subject,$message);
	}

	public static function parseTemplate($path,&$vars) {
		if(!file_exists($path)) return false;
		$thoth = new ThothTemplate();
		$text = file_get_contents($path);
		return $thoth->process($text,$vars);
	}

	private function install() {
		$tm = time();
		$auth = $this->auth;
		$this->auth = AUTH_ADMIN; // Use admin permissions during install
		require_once DIR_INCLUDE.'install.php';
		install_db($this->db);
		$wiki = new WadjetWiki();
		$wiki->install(DIR_DOC.'wiki/');
		$this->auth = $auth;
	}

	public static function isNewDB() { return self::$set? self::$set->db->isNew() : false; }
	public static function exec($sql,$p=false) { if(self::$set) self::$set->db->exec($sql,$p); }
	public static function query($sql,$p=false) { return self::$set? self::$set->db->query($sql,$p) : false; }
	public static function row($sql,$p=false) { return self::$set? self::$set->db->row($sql,$p) : false; }
	public static function column($sql,$p=false) { return self::$set? self::$set->db->column($sql,$p) : false; }
	public static function insertID() { return self::$set? self::$set->db->insertID() : false; }
	public static function sql($s) { return self::$set? self::$set->db->sql($s) : false; }

	public function open($path,$name) {
		return true;
	}

	public function close() {
		return true;
	}

	public function read($sid) {
		$tm = time();
		$data = $this->db->column('SELECT data FROM session WHERE sid=?',array($sid));
		if($data===null || $data===false) {
			$this->db->exec('INSERT INTO session (sid,data,created,changed) VALUES (?,?,?,?)',array($sid,'',$tm,$tm));
			return '';
		} else {
			$this->db->exec('UPDATE session SET changed=? WHERE sid=?',array($tm,$sid));
			return $data;
		}
	}

	public function write($sid,$data) {
		$tm = time();
		$this->db->exec('UPDATE session SET data=?,changed=? WHERE sid=?',array($data,$tm,$sid));
		return true;
	}

	public function destroy($sid) {
		$this->db->exec('DELETE FROM session WHERE sid=?',array($sid));
		return true;
	}

	public function gc($maxlifetime) {
		$tm = time();
		$this->db->exec('DELETE FROM session WHERE changed+?<?',array(SESSION_LIFETIME,$tm));
		return true;
	}
}

/** String to session language
 * This overloads the gettext function _() since gettext isn't used by Seshat.
 */
function _($id) {
	return SetSession::getString($id);
}

