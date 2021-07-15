<?php

namespace Seshat\MaatMark\Tag;

use Seshat\MaatMark;
use Seshat\MaatMark\TagHandler;

class StyleTag extends TagHandler {
	function __construct(&$maat,$tag,$params,$content) {
		parent::__construct($maat,$tag,$params,$content);
		$maat->addData('style',trim($content));
	}

	public function expand() {
		return '';
	}
}


?>