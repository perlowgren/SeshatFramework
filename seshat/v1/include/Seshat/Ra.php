<?php
/** Ra extends SetSession, and thus handles sessions and database, with focus on
 * exporting templates.
 * 
 * @file include/Seshat/Ra.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-05
 * @date Created: 2015-01-31
 */

namespace Seshat;

use Seshat\SetSession;
use Seshat\ThothTemplate;

class Ra extends SetSession {
	private static $alias  = array(   //!< List of aliases for pages
		''=>'index',
		'page'=>'wiki'
	);

	private static $ra     = false;   //!< Reference to the first instance of Ra

	private $page;                //!< Page name
	private $args;                //!< Arguments in URL from apache rewite (not query string)
	private $path;                //!< Path to exported template in cache

	/** Constructor; initiate parameters
	 * @param &$db Reference to IsisDB object
	 * @param $mime Output mime, default for Ra is to export HTML-templates */
	public function __construct(&$db,$mime='text/html') {
		parent::__construct($db,$mime);

		if(!self::$ra) self::$ra = &$this;

		$this->args     = explode('/',trim($_SERVER['REDIRECT_URL'],'/'));
		$this->page     = trim(array_shift($this->args),'-');
		if(isset(self::$alias[$this->page]))
			$this->page  = self::$alias[$this->page];

//echo "page: {$this->page}\n";

		if(isset($_GET['error'])) self::error(intval($_GET['error']));

		self::export('',false,false,$this->auth>=AUTH_ADMIN && isset($_GET['export']));

		$_SESSION['url'] = '/'.$this->page;
		if(!isset($_COOKIE['access-token']) || !isset($_SESSION['access-token']) ||
				$_COOKIE['access-token']!=$_SESSION['access-token']) {
			$access_token = md5(rand());
			setcookie('access-token',$access_token,0,'/','',true,true);
			$_SESSION['access-token'] = $access_token;
		}
	}

	/** Write an error page. */
	protected function errorPage($code=500,$error=false) {
		require self::export('error');
		self::quit();
	}

	/** Redirect using Location-header */
	public static function location($url) {
		header('Location: '.$url);
		self::quit();
	}

	public static function getPage() { return self::$ra? self::$ra->page : ''; }
	public static function getArgument($n) { return self::$ra && isset(self::$ra->args[$n])? self::$ra->args[$n] : false; }
	public static function getPagePath() { return self::$ra? self::$ra->path : ''; }

	/** Check if a page need to be exported, and if so, export template to cache.
	 * @param $page Name of page to be exported (can also be an array of pages)
	 * @param $lang Language, i.e. 'en' (can also be an array of languages)
	 * @param $force Force export, less checking of timestamps for files
	 * @return A string containing the path to the exported script in the cache */
	public static function export($page='',$theme=false,$lang=false,$force=false) {
		if(!$page && self::$ra) $page = self::$ra->page;
		elseif(is_array($page)) {
			foreach($page as $p) self::export($p,$theme,$lang,$force);
			return;
		}
		if(!$theme) $theme = Ra::getTheme();
		if(!$lang) $lang = Ra::getLanguage();
		elseif(is_array($lang)) {
			foreach($lang as $l) self::export($page,$theme,$l,$force);
			return;
		}
		$cache = DIR_DATA."cache/{$theme}-{$page}-{$lang}.php";
		$st = @stat($cache);
		if($st===false) $force = true;
		if($force || Ra::hasPermission(AUTH_ADMIN)) {
			$path = strtr($page,'-','/');
			if(is_dir(DIR_TEMPLATE."page/{$path}")) $path = "{$path}/index";
			$templ = @stat(DIR_TEMPLATE."page/{$path}.php");
			if($templ===false) self::error(404);
			if($force===false) {
				$tm = $st['mtime'];
				$templ_conf = @stat(DIR_TEMPLATE."page/{$path}-conf.php");
				$templ_lang = @stat(DIR_TEMPLATE."page/{$path}-{$lang}.php");
				if($templ_lang===false)
					$templ_lang = @stat(DIR_TEMPLATE."page/{$path}-en.php");
				$templ_js = @stat(DIR_TEMPLATE."page/{$path}.js");
				$templ_css = @stat(DIR_TEMPLATE."page/{$path}.css");
				$force = ($templ['mtime']>$tm ||
					($templ_conf!==false && $templ_conf['mtime']>$tm) ||
					($templ_lang!==false && $templ_lang['mtime']>$tm) ||
					($templ_js!==false && $templ_js['mtime']>$tm) ||
					($templ_css!==false && $templ_css['mtime']>$tm));
			}
		}
		if(self::$ra) {
			self::$ra->page      = $page;
			self::$ra->lang      = $lang;
			self::$ra->path      = $cache;
		}
		if($force) {
			$thoth = new ThothTemplate();
			$vars = array();
			$php = $thoth->export($path,$vars);
			if($php) file_put_contents($cache,$php);
		}
		return $cache;
	}
}

