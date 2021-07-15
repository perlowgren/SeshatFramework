<?php

namespace Seshat\MaatMark\Tag;

use Seshat\MaatMark;
use Seshat\MaatMark\TagHandler;

class ReferencesTag extends TagHandler {
	private static $letters = 'abcdefghijklmnopqrstuvwxyz';

	public function expand() {
		$refs = &$this->maat->getData('refs');
		if(!count($refs)) return '';
		$list = array();
		foreach($refs as &$r) {
			list($num,$name,$id,$text,$n) = $r;
			if($text) $text = $this->maat->parseInline($text);
			if($n==0) $a = "<a href=\"#ref-{$id}\">^</a> ";
			else for($i=0,$a='^ '; $i<=$n; ++$i)
				$a .= "<a href=\"#ref-{$id}".($i==0? '' : '-'.$i)."\"><sup>".self::$letters[$i]."</sup></a> ";
			$list[] = "\t<li><a id=\"footnote-{$id}\"></a>{$a}{$text}</li>";
		}
		$refs = array();
		return "<ol class=\"ref\">\n".implode("\n",$list)."\n</ol>\n";
	}
}

