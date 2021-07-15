<?php

namespace Seshat\MaatMark\Template;

use Seshat\MaatMark;
use Seshat\MaatMark\GlyphTemplate;

class ElementTemplate extends GlyphTemplate {
	public static $element = array(
		// Elements:
		'fire'=>'&#x1F702;',
		'air'=>'&#x1F701;',
		'water'=>'&#x1F704;',
		'earth'=>'&#x1F703;',
	);

	public function expand() {
		return $this->expandGlyphs('element',self::$element);
	}
}


