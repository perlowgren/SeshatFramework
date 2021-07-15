<?php

namespace Seshat\MaatMark\Template;

use Seshat\Ra;
use Seshat\MaatMark;
use Seshat\MaatMark\LinkHandler;
use Seshat\MaatMark\TemplateHandler;

class PageTemplate extends TemplateHandler {
	private $link;
	private $lang;
	private $pid;
	private $rid;
	private $html;

	function __construct(&$maat,$templ,$params) {
		parent::__construct($maat,$templ,array());
		$this->html = '';
		if(is_array($params))
			foreach($params as $p) {
				if(($n=strpos($p,'='))!==false) {
					$k = trim(substr($p,0,$n));
					$v = trim(substr($p,$n+1));
					$this->params[$k] = $v;
				} else $this->params[] = $p;
			}
		$templ = ucfirst($templ);
		$this->link = LinkHandler::parse($maat,'Template:'.$templ);
		if($maat) $maat->addLinkedPage($this->link->page);
		$result = Ra::query('SELECT pid,rid,lang,read,write FROM wiki WHERE page=?',array($this->link->page));
		if($result) {
			$lang = Ra::getLanguage();
			$langs = array();
			while(($row=$result->fetchArray(SQLITE3_ASSOC))) $langs[$row['lang']] = $row;
			if(isset($langs[$lang])) $page = $langs[$lang];
			elseif($lang!='en' && isset($langs['en'])) $page = $langs['en'];
			elseif(count($langs)) $page = array_shift($langs);
			$this->lang = $page['lang'];
			$this->pid = $page['pid'];
			$this->rid = $page['rid'];
		}
	}

	public function content() {
		$text = '';
		if($this->rid) {
			$text = Ra::column("SELECT text FROM wiki_history WHERE rid={$this->rid}");
//			$text = preg_replace_callback('/\{\{\{(\w+)\}\}\}/', ...); <- replace template parameters
//echo "<pre>PageTemplate: \n{$text}</pre>\n";
/*			try {
				if(strpos($text,"\n")!==false)
					$this->html = $this->maat->parseSections($text);
				else
					$this->html = $this->maat->parseInline($text);
			} catch(Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}*/
		}
//		return $this->html;
		return $text;
	}
}


