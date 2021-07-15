<?php
/** Wiki-engine
 * 
 * @file include/Seshat/WadjetWiki.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-05
 * @date Created: 2015-01-31
 */

namespace Seshat;

use Seshat\Ra;
use Seshat\MaatMark\LinkHandler;

define('WIKI_READ',      AUTH_NONE);       //!< 
define('WIKI_WRITE',     AUTH_ADMIN);      //!<
define('WIKI_ADMIN',     AUTH_ADMIN);      //!< 

class WadjetWiki {
	/** File types accepted as Wiki-pages */
	static private $file_types = array('md','txt');

	private $db;          //!< Database object (class/IsisDB.php)
	private $action;      //!< Action to perform
	private $pid;         //!< Page ID in db
	private $rid;         //!< Page history ID (revision ID) in db
	private $uid;         //!< User ID in db
	private $url_name;    //!< The name of the page in the URL
	private $name;        //!< Name of page, stored in db
	private $prefix;      //!< Namespace prefix of page, stored in db
	private $page;        //!< Name of page, url-formatted, stored in db
	private $lang;        //!< Language of page, stored in db
	private $read;        //!< Authentication level for reading page
	private $write;       //!< Authentication level for writing to
	private $version;     //!< Version of page
	private $text;        //!< Original pre-formatted page text 
	private $html;        //!< Generated HTML page
	private $message;     //!< Wiki message
	private $rename;      //!< New name, if name differs from value in db
	private $link;        //!< LinkHandler instance

	/** Constructor; initiate parameters */
	public function __construct($name=false,$pid=0) {
		$this->pid         = 0;
		$this->rid         = 0;
		$this->uid         = 0;
		$this->url_name    = '';
		$this->lang        = Ra::getLanguage();
		$this->action      = 'page';

		if(Ra::getPage()=='wiki') {
			if(($a=Ra::getArgument(2))!==false) {
				$this->pid      = intval(Ra::getArgument(1));
				$this->url_name = $a;
				$this->action   = Ra::getArgument(0);
			} elseif(($a=Ra::getArgument(1))!==false) {
				$this->url_name = $a;
				$this->action   = Ra::getArgument(0);
			} else {
				$this->url_name = Ra::getArgument(0);
			}
		}
		if($pid!==0) $this->pid = intval($pid);
		elseif(isset($_REQUEST['pid'])) $this->pid = intval($_REQUEST['pid']);
		if($name!==false) $this->url_name = $name;
		elseif(isset($_REQUEST['name'])) $this->url_name = $_REQUEST['name'];
		if(isset($_REQUEST['lang'])) $this->lang = $_REQUEST['lang'];
		if(isset($_REQUEST['action'])) $this->action = $_REQUEST['action'];

		$this->name        = $this->url_name;
		$this->prefix      = '';
		$this->page        = $this->url_name;
		$this->read        = WIKI_READ;
		$this->write       = WIKI_WRITE;
		$this->version     = 0;
		$this->text        = '';
		$this->html        = '';
		$this->message     = '';
		$this->rename      = false;

		if($this->pid || $this->page) {
			$page = false;
			if($this->pid) $page = Ra::row('SELECT pid,rid,uid,name,prefix,page,lang,read,write,version FROM wiki WHERE pid=?',array($this->pid));
			if(!$page || ($page['lang']!=$this->lang && $this->action=='page')) {
				$result = Ra::query('SELECT pid,rid,uid,name,prefix,page,lang,read,write,version FROM wiki WHERE name=? OR page=?',array($this->page,$this->page));
//error_log('WadjetWiki::__construct('.count($result).')');
				if($result) {
					$langs = array();
					while(($row=$result->fetchArray(SQLITE3_ASSOC))) $langs[$row['lang']] = $row;
					if(isset($langs[$this->lang])) $page = $langs[$this->lang];
					elseif($this->action=='page') {
						if($this->lang!='en' && isset($langs['en'])) $page = $langs['en'];
						elseif(count($langs)) $page = array_shift($langs);
					}
//error_log('WadjetWiki::__construct('.$page['lang'].','.$page['name'].')');
				}
			}
			if($page) {
				$this->rename    = $this->name;
				$this->pid       = intval($page['pid']);
				$this->rid       = intval($page['rid']);
				$this->uid       = intval($page['uid']);
				$this->name      = $page['name'];
				$this->prefix    = $page['prefix'];
				$this->page      = $page['page'];
				$this->lang      = $page['lang'];
				$this->read      = intval($page['read']);
				$this->write     = intval($page['write']);
				$this->version   = intval($page['version']);
			} else {
				$this->pid       = 0;
			}
		}
		if(!$this->page) $this->page = 'Start Page'; // TODO!! self::start;
		$_SESSION['url']        = '/page/'.$this->page;
		$_SESSION['wiki-page']  = $this->page;

		$maat = null;
		$this->link             = LinkHandler::parse($maat,$this->name);

//echo "<!-- pid: ".$this->pid.", name: ".$this->name.", page: ".$this->page." -->";
	}

	public function getPageID() { return $this->pid; }
	public function getRevisionID() { return $this->rid; }
	public function getAuthorID() { return $this->uid; }
	public function getRealName() { return $this->name; }
	public function getNameSpace() { return $this->prefix; }
	public function getPageName() { return $this->page; }
	public function getPageLanguage() { return $this->lang; }
	public function getAction() { return $this->action; }
	public function hasReadPermission() { return Ra::hasPermission($this->pid===0? WIKI_READ : $this->read); }
	public function hasWritePermission() { return Ra::hasPermission($this->pid===0? WIKI_WRITE : $this->write); }
	public function hasAdminPermission() { return Ra::hasPermission(WIKI_ADMIN); }
	public function getPageVersion() { return $this->version; }
	public function getText() { return $this->text; }
	public function getHTML() { return $this->html; }
	public function getMessage() { return $this->message; }
	public function &getLinkHandler() { return $this->link; }

	public function install($path,$subdirs=true) {
//error_log("WadjetWiki::installPages({$path})");
		if(strlen($path)>0 && is_dir($path) && ($dh=opendir($path))) {
			if($path[strlen($path)-1]!=='/') $path = $path.'/';
			while(($file=readdir($dh))!==false) {
//error_log("WadjetWiki::installPages(file: {$file})");
				if($file[0]=='.') continue;
				if(is_dir($path.$file)) {
					if($subdirs) $this->install($path.$file,$subdirs);
					else continue;
				} else {
					$i = strrpos($file,'.');
					if(!$i || !in_array(substr($file,$i+1),self::$file_types)) continue;
					$lang = 'en';
					if($i>3 && $file[$i-3]=='-') {
						$lang = substr($file,$i-2,2);
						$i -= 3;
					}
					$this->savePage(array(
						'name'=>substr($file,0,$i),
						'text'=>file_get_contents($path.$file),
						'lang'=>$lang
					));
				}
			}
			closedir($dh);
		}
	}

	/** Get an URL for page with given action to perform */
	public function getURL($action=false) {
		if(!$action) return "/page/{$this->page}";
		return "/wiki/{$action}/{$this->pid}/{$this->page}";
	}

	public function handleAction($action=false) {
		if($action) $this->action = $action;
		switch($this->action) {
			case 'page':
				$link = $this->getLinkHandler();
//echo "<!-- page: '{$link->page}' == '{$this->url_name}' -->";
				if($this->url_name!=$link->page) Ra::location('/page/'.$link->page);
				if(!$this->loadPage()) {
					if(!$this->hasWritePermission()) Ra::error(404);
					else $this->loadText();
				}
				break;

			case 'edit':
				$this->loadText();
				break;

			case 'save':
				$this->savePage();
				break;

			case 'settings':
				$this->savePageSettings();
				break;

			case 'delete':
				$this->deletePage();
				break;
		}
	}

	/** Load the wiki page */
	public function loadPage() {
		if(!$this->hasReadPermission()) Ra::error(401);
		if(!$this->pid || !$this->rid) return false;
		$this->html = Ra::column("SELECT html FROM wiki WHERE pid={$this->pid}");
		if(!$this->html) {
			$text = Ra::column("SELECT text FROM wiki_history WHERE rid={$this->rid}");
			if(!$text) return false;
			$t1 = microtime(true);
			$maat = new MaatMark();
			$this->html = $maat->parse($text);
			$links = $maat->getLinkedPages();
			$links = implode("\n",$links);
			$t2 = microtime(true);
			Ra::exec('UPDATE wiki SET html=?,links=? WHERE pid=?',array($this->html,$links,$this->pid));
			Ra::exec('UPDATE wiki_history SET time=? WHERE rid=?',array(intval(($t2-$t1)*1000000),$this->rid));
		}
//echo "<!-- HTML: {$this->html} -->";
		$this->message = _('page-loaded');
		$this->action = 'page';
		return true;
	}

	/** Load the page markup text */
	public function loadText() {
		if(!$this->hasWritePermission()) Ra::error(401);
		$text = Ra::column("SELECT text FROM wiki_history WHERE rid={$this->rid}");
		$this->text = $text? htmlentities($text) : '';
		$this->message = _('page-text-loaded');
		$this->action = 'edit';
	}

	/** Save page to database */
	public function savePage($data=false) {
		if(!$this->hasWritePermission()) Ra::error(401);
		if(is_array($data)) {
			$this->pid      = isset($data['pid'])? intval($data['pid']) : 0;
			$this->rid      = 0;
			$this->name     = isset($data['name'])? $data['name'] : '';
			$this->version  = 0;
			$this->text     = isset($data['text'])? $data['text'] : '';
			if(isset($data['lang']))
				$this->lang  = $data['lang'];
			$maat = null;
			$this->link     = LinkHandler::parse($maat,$this->name);
		}
		elseif(is_string($data) && $data && isset($_REQUIRE[$data])) $this->text = $_REQUIRE[$data];
		else $this->text = file_get_contents('php://input');
		if(!$this->text && $this->pid) $this->deletePage();
		elseif(!$this->name) $this->message = _('page-name-missing');
		elseif(!$this->text) $this->message = _('page-content-missing');
		else {
			if($this->rename && $this->rename!=$this->name) $this->name = $this->rename;
			$tm = time();
			$created = $tm;
			$changed = $tm;
			$link = $this->getLinkHandler();
			$this->prefix = $link->namespace;
			$this->page = $link->page;
//error_log('WadjetWiki::savePage(pid: '.$this->pid.', name: '.$this->page.')');
//			if($this->pid && $this->lang!=Ra::getLanguage()) $this->pid = 0;

			if($this->pid) {
				Ra::exec('UPDATE wiki SET name=?,prefix=?,page=?,html=?,links=?,changed=? WHERE pid=?',
					array($this->name,$this->prefix,$this->page,'','',$changed,$this->pid));
			} else {
				Ra::exec('INSERT INTO wiki (pid,uid,name,prefix,page,lang,html,links,read,write,created,changed) VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?)',
					array(Ra::getUserID(),$this->name,$this->prefix,$this->page,$this->lang,'','',$this->read,$this->write,$tm,$changed));
				$this->pid = Ra::insertID();
				$this->version = 0;
			}

			$this->version++;
			Ra::exec('INSERT INTO wiki_history (rid,pid,uid,name,text,version,created,time) VALUES (NULL,?,?,?,?,?,?,?)',
				array($this->pid,Ra::getUserID(),$this->name,$this->text,$this->version,$changed,0));
			$this->rid = Ra::insertID();
			Ra::exec("UPDATE wiki SET rid=?,version=? WHERE pid=?",array($this->rid,$this->version,$this->pid));

//echo "<p> pid: {$this->pid}, rid: {$this->rid}, version: {$this->version}, name: {$this->name}, page: {$this->page} </p>";

			file_put_contents(DIR_DATA.'wiki/'.$this->name.($this->lang=='en'? '' : '-'.$this->lang).'.md',$this->text);
			$_SESSION['url'] = '/page/'.$this->page;
			$_SESSION['wiki-page'] = $this->page;
			$this->message = _('page-saved');
		}
		$this->action = 'edit';
	}

	/** Write page settings */
	public function pageSettings() {
		if(!$this->hasAdminPermission()) Ra::error(401);
		$read = isset($_REQUEST['read'])? $_REQUEST['read']+0 : WIKI_READ;
		$write = isset($_REQUEST['write'])? $_REQUEST['write']+0 : WIKI_WRITE;
		if($this->pid && ($read!=$this->read || $write!=$this->write)) {
			$db->exec('UPDATE wiki SET read=?,write=? WHERE pid=?',
				array($this->read,$this->write,$this->pid));
			$this->message = _('page-settings-changed');
		}
		$this->action = 'edit';
	}

	/** Delete page from database */
	public function deletePage() {
		if(!$this->hasAdminPermission()) Ra::error(401);
		if($this->pid) {
			$db->exec("DELETE FROM wiki WHERE pid={$this->pid}");
			$db->exec("DELETE FROM wiki_history WHERE pid={$this->pid}");
			$this->message = _('page-deleted');
		}
		$this->action = 'edit';
	}
}
