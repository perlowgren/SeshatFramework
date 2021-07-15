<?php

namespace Seshat\MaatMark\Template;

use Seshat\MaatMark;
use Seshat\MaatMark\GlyphTemplate;

class GeomancyTemplate extends GlyphTemplate {
	public static $geomancy = array(
		'via'=>'&#xF580;',
		'populus'=>'&#xF581;',
		'conjunctio'=>'&#xF582;',
		'carcer'=>'&#xF583;',
		'fortuna-major'=>'&#xF584;',
		'fortuna-minor'=>'&#xF585;',
		'acquisitio'=>'&#xF586;',
		'amissio'=>'&#xF587;',
		'laetitia'=>'&#xF588;',
		'tristitia'=>'&#xF589;',
		'puella'=>'&#xF58A;',
		'puer'=>'&#xF58B;',
		'albus'=>'&#xF58C;',
		'rubeus'=>'&#xF58D;',
		'caput-draconis'=>'&#xF58E;',
		'cauda-draconis'=>'&#xF58F;',
	);

	public function expand() {
		return $this->expandGlyphs('geomancy',self::$geomancy);
	}
}


