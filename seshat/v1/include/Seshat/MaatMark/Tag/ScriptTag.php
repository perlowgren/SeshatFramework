<?php

namespace Seshat\MaatMark\Tag;

use Seshat\MaatMark;
use Seshat\MaatMark\TagHandler;

class ScriptTag extends TagHandler {
	function __construct(&$maat,$tag,$params,$content) {
		parent::__construct($maat,$tag,$params,$content);
		$maat->addData('script',trim($content));
	}

	public function expand() {
		return '';
	}
}


?>