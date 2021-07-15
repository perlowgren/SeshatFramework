<?php

namespace Seshat\MaatMark\Link;

use Seshat\MaatMark;

class SpecialLink extends PageLink {
	public function getURL($a=false,$n=true) {
		global $sc;
		return ($a? $sc->url : $sc->url_base).'index.php?m=wiki&p=special'.($n? '&wiki='.$this->link : '');
	}
}

