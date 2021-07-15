<?php

namespace Seshat\MaatMark\Tag;

use Seshat\MaatMark;
use Seshat\MaatMark\LinkHandler;
use Seshat\MaatMark\TagHandler;

class RefTag extends TagHandler {
	public function expand() {
		$refs = &$this->maat->getData('refs');
		$num = count($refs)+1;
		$name = false;
		$url = false;
		$text = '';
		if($this->params) {
			MaatMark::parseParams($this->params,$params);
			if(isset($params['id'])) $name = $params['id'];
			if(isset($params['name'])) $name = $params['name'];
			if($name) $name = LinkHandler::encodePageName(strtolower($name));
			if($name && isset($refs[$name])) $num = $refs[$name][0];
		}
		$id = $name? $name : $num;
		$text = $this->content;
		$n = 0;
		if(!isset($refs[$id])) $refs[$id] = array($num,$name,$id,$text,$n);
		else $n = ++$refs[$id][4];
		return "<a href=\"#footnote-{$id}\" id=\"ref-{$id}".($n==0? '' : '-'.$n)."\" class=\"ref\"><sup>[{$num}]</sup></a>";
	}
}

