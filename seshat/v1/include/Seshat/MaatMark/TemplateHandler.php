<?php

namespace Seshat\MaatMark;

use Seshat\Ra;
use Seshat\MaatMark;
use Seshat\MaatMark\LinkHandler;

class TemplateHandler {
	private static $handlers = array();
	private static $alias = array(
		'Astr'=>'Astrology',
		'Astro'=>'Astrology',
		'Elem'=>'Element',
		'Enoch'=>'Enochian',
		'Geo'=>'Geomancy',
		'Hebr'=>'Hebrew',
		'Horo'=>'Horoscope',
		'Sym'=>'Symbol',
	);

	protected $maat;        //<! 
	private $html;          //<! 
	public $templ;          //<! 
	public $params;         //<! 
	public $data;           //<! 

	function __construct(&$maat,$templ,$params) {
		$this->maat = &$maat;
		$this->html = false;
		$this->templ = strtolower($templ);
		$this->params = $params;
	}

	final public static function &parse(&$maat,$text) {
		/*if(($n1=strpos($text,'{{'))!==false && ($n2=strpos($text,'}}'))!==false && $n1<$n2) {
			$text = $maat->parseInline($text);
		}*/
		if(strpos($text,'|')!==false) {
			$params = explode('|',$text);
			$templ = array_shift($params);
		} else {
			$templ = $text;
			$params = false;
		}

		$handler = false;
		$data = null;
		$h = ucfirst($templ);
		if(isset(self::$alias[$h])) $h = self::$alias[$h];
		if(isset(self::$handlers[$h])) {
			$h = self::$handlers[$h];
			if($h!==false) $handler = $h;
		} else {
			if(strpos($h,'/')!==false || strpos($h,'\\')!==false) return false;
			if(file_exists(DIR_INCLUDE.'Seshat/MaatMark/Template/'.$h.'Template.php')) {
				$handler = $h.'Template';
				self::$handlers[$h] = $handler;
			} else {
				$link = LinkHandler::parse($maat,'Template:'.$h);
				$pid = Ra::column('SELECT pid FROM wiki WHERE page=? LIMIT 1',array($link->page));
				if($pid) {
//echo "<pre>TemplateHandler: {$handler} (pid: ".htmlspecialchars(print_r($pid,true)).")</pre>\n";
					$handler = 'PageTemplate';
					self::$handlers[$h] = $handler;
					if($link->page!=$h && !isset(self::$handlers[$link->page]))
						self::$handlers[$link->page] = $handler;
				} else {
					self::$handlers[$h] = false;
				}
			}
		}
		if(!$handler) {
			$ret = "<a href=\"/page/Template:{$templ}\">[Template:{$templ}]</a>";
		} else {
			$class = 'Seshat\\MaatMark\\Template\\'.$handler;
			$class = new $class($maat,$templ,$params);
			$ret = $class->content();
		}
//echo "<pre>TemplateHandler: {$handler} (h: {$h}, templ: {$templ}), ret:\n".htmlspecialchars(print_r($ret,true))."</pre>\n";
		return $ret;
	}

	protected function content() {
		return $this->maat->addRef($this,'templ');
	}

	protected function expand() {
		return '';
	}

	final public function getHTML() {
		if($this->html===false)
			$this->html = $this->expand();
		return $this->html;
	}
}


