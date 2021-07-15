<?php

namespace Seshat\MaatMark\Link;

use Seshat\MaatMark;
use Seshat\MaatMark\LinkHandler;

class PageLink extends LinkHandler {
	function __construct(&$maat,$title,$namespace,$name,$parenthesis,$section,$text) {
		$name = ucfirst($name);
		if($title===false) $title = $text;
		parent::__construct($maat,$title,$namespace,$name,$parenthesis,$section,$text);
	}

	public function expand() {
		$title = $this->title;
		if($this->maat && $title)
			$title = $this->maat->parseInline($title);
		return "<a href=\"/page/{$this->link}\">{$title}</a>";
	}
}

