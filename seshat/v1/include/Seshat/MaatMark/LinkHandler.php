<?php

namespace Seshat\MaatMark;

use Seshat\MaatMark;

class LinkHandler {
	/** All loaded link handlers */
	private static $handlers = array(''=>1);
	private static $alias    = array(         //<! Namespace aliases
		'Image'=>'File'
	);

	protected $maat;        //<! Ref to the Markup-object creating this handler
	private $html;          //<! 
	public $page;           //<! The entire formatted page, e.g. "Namespace:Name_(parenthesis)#section"
	public $title;          //<! Title of page, visible text for link, e.g. [[Page|Title of page]]
	public $namespace;      //<! Namespace:Name_(parenthesis)#section
	public $name;           //<! 
	public $parenthesis;    //<! 
	public $section;        //<! 
	public $link;           //<! The entire formatted page, e.g. "Namespace:Name_(parenthesis)#section"

	function __construct(&$maat,$title,$namespace,$name,$parenthesis,$section,$text) {
		$this->maat = &$maat;
		$this->html = false;
		$this->page = $namespace? $namespace.':'.$name : $name;
		if($parenthesis) $this->page .= "_({$parenthesis})";
		$this->title = $title;
		$this->namespace = $namespace;
		$this->name = $name;
		$this->parenthesis = $parenthesis;
		$this->section = $section;
		$this->link = $section? $this->page.'#'.$section : $this->page;
	}

	final public static function &parse(&$maat,$text) {
		$name = $text;
		if($name && $name[0]==':') $name = substr($name,1);
		if(($n=strpos($name,'|'))===false) $title = false;
		else {
			$title = trim(substr($name,$n+1));
			$name = substr($name,0,$n);
		}
		if($name && $name[0]==':') $name = substr($name,1);
		if(($n=strpos($name,'#'))!==false) {
			$section = self::encodePageName(substr($name,$n+1));
			$name = substr($name,0,$n);
		} else $section = '';
		if(($n=strpos($name,'('))!==false) {
			if(($n2=strrpos($name,')',$n))!==false) $p = substr($name,$n+1,$n2-($n+1));
			else $p = substr($name,$n+1);
			$parenthesis = self::encodePageName($p);
			$name = substr($name,0,$n);
		} else $parenthesis = '';
		$name = self::encodePageName($name);
		if(($n=strpos($name,':'))!==false) {
			$namespace = ucfirst(substr($name,0,$n));
			if(isset(self::$alias[$namespace])) $namespace = self::$alias[$namespace];
			$name = substr($name,$n+1);
		} else $namespace = '';
		if($title==='') $title = $name;

//echo "<!-- namespace='$namespace',name='$name',section='$section',parenthesis='$parenthesis',title='$title' -->";

		$handler = 'PageLink';
		if(isset(self::$handlers[$namespace])) {
			if(self::$handlers[$namespace]==2) $handler = $namespace.'Link';
		} else {
			if(file_exists(DIR_INCLUDE.'Seshat/MaatMark/Link/'.$namespace.'Link.php')) {
				self::$handlers[$namespace] = 2;
				$handler = $namespace.'Link';
			} else self::$handlers[$namespace] = 1;
		}
		$class = 'Seshat\\MaatMark\\Link\\'.$handler;
		$ret = new $class($maat,$title,$namespace,$name,$parenthesis,$section,$text);
		return $ret;
	}

	final public static function encodePageName($name) {
		if(!$name) return '';
		return trim(preg_replace('/[ \-\§\[\]\{\}\(\)\<\>\|\+\?\^\~\/\\\"\']+/','_',$name),'_');
	}

	protected function expand() {
		return "<a href=\"/page/{$this->link}\">{$this->title}</a>";
	}

	final public function getHTML() {
		if($this->html===false)
			$this->html = $this->expand();
		return $this->html;
	}

	public function getURL($a=false,$n=true) {
		return 'index.php?m=wiki'.($n? '&wiki='.$this->page : '');
	}

	public function editURL($a=false,$n=true) {
		return 'index.php?m=wiki&a=edit'.($n? '&wiki='.$this->page : '');
	}
}

