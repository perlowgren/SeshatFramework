<?php

namespace Seshat\MaatMark;

use Seshat\MaatMark;

class TagHandler {
	private static $handlers = array(''=>1);
	private static $alias    = array(
		'Image'=>'File'
	);

	protected $maat;        //<! 
	private $html;          //<! 
	public $tag;            //<! 
	public $params;         //<! 
	public $content;        //<! 
	public $single;         //<! 

	function __construct(&$maat,$tag,$params,$content) {
		$this->maat = &$maat;
		$this->html = false;
		$this->tag = $tag;
		$this->params = $params;
		$this->content = $content;
	}

	final public static function &parse(&$maat,$tag,$params,$content) {
		$ret = false;
		$handler = false;
		$tag = strtolower($tag);
		$id = ucfirst($tag);
		$cl = '.';
		if(isset(self::$handlers[$tag])) {
			if(self::$handlers[$tag]==2) $handler = $id.'Tag';
		} else {
			if(strpos($id,'/')!==false || strpos($id,'\\')!==false) return $ret;
			if(file_exists(DIR_INCLUDE."Seshat/MaatMark/Tag/{$id}Tag.php")) {
				self::$handlers[$tag] = 2;
				$handler = $id.'Tag';
			} else self::$handlers[$tag] = 1;
		}
//echo "<p>TagHandler({$tag},{$id},{$handler})</p>";
		if($handler===false) {
			$ret = $content===false? "&lt;{$tag}{$params}/&gt;" : "&lt;{$tag}{$params}&gt;".$maat->parseInline($content)."&lt;/{$tag}&gt;";
		} else {
			$class = "Seshat\\MaatMark\\Tag\\{$handler}";
			$ret = new $class($maat,$tag,$params,$content);
		}
		return $ret;
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


