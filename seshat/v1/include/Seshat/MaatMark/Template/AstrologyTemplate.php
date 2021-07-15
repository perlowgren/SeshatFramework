<?php

namespace Seshat\MaatMark\Template;

use Seshat\MaatMark;
use Seshat\MaatMark\GlyphTemplate;

class AstrologyTemplate extends GlyphTemplate {
	public static $astrology = array(
		// Elements:
		'fire'=>'&#x1F702;',
		'air'=>'&#x1F701;',
		'water'=>'&#x1F704;',
		'earth'=>'&#x1F703;',

		// Zodiac:
		'aries'=>'&#x2648;',
		'taurus'=>'&#x2649;',
		'gemini'=>'&#x264A;',
		'cancer'=>'&#x264B;',
		'leo'=>'&#x264C;',
		'virgo'=>'&#x264D;',
		'libra'=>'&#x264E;',
		'scorpio'=>'&#x264F;',
		'sagittarius'=>'&#x2650;',
		'capricorn'=>'&#x2651;',
		'aquarius'=>'&#x2652;',
		'pisces'=>'&#x2653;',

		// Planets:
		'sol'=>'&#x2609;',               'luna'=>'&#x263D;',           'mercury'=>'&#x263F;',         'venus'=>'&#x2640;',
		'mars'=>'&#x2642;',              'jupiter'=>'&#x2643;',        'saturn'=>'&#x2644;',          'uranus'=>'&#x2645;',
		'neptune'=>'&#x2646;',           'pluto'=>'&#x2647;',

		'caput-draconis'=>'&#x260A;',    'cauda-draconis'=>'&#x260B;', 'lilith'=>'&#x26B8;',          'retro'=>'&#x211E;',
		'fortune'=>'&#x2297;',           'ceres'=>'&#x26B3;',          'pallas'=>'&#x26B4;',          'juno'=>'&#x26B5;',
		'vesta'=>'&#x26B6;',             'asc'=>'A<sup>sc</sup>',      'ascendant'=>'A<sup>sc</sup>', 'mc'=>'M<sup>c</sup>',
		'medium-coeli'=>'M<sup>c</sup>',
		'sun'=>'&#x2609;',               'moon'=>'&#x263D;',           'rmoon'=>'&#x263D;',           'lmoon'=>'&#x263E;',
		'tellus'=>'&#x1F728;',

		// Aspects:
		'conjunction'=>'&#x260C;',
		'semi-sextile'=>'&#x26BA;',
		'decile'=>'&#x27C2;',
		'novile'=>'&#x004E;',
		'semi-square'=>'&#x2220;',
		'septile'=>'&#x2721;',
		'quintile'=>'&#x0051;',
		'trine'=>'&#x25B3;',
		'square'=>'&#x25A1;',
		'sextile'=>'&#x26B9;',
		'sesquiquadrate'=>'&#x26BC;',
		'biquintile'=>'&#x0062;&#x0051;',
		'quincunx'=>'&#x26BB;',
		'opposition'=>'&#x260D;',

		// Moon phases:
		'new-moon'=>'&#x1F311;',
		'crescent-moon'=>'&#x1F312;',
		'first-quarter-moon'=>'&#x1F313;',
		'gibbous-moon'=>'&#x1F314;',
		'full-moon'=>'&#x1F315;',
		'disseminating-moon'=>'&#x1F316;',
		'last-quarter-moon'=>'&#x1F317;',
		'balsamic-moon'=>'&#x1F318;',
	);

	public function expand() {
		return $this->expandGlyphs('astrology',self::$astrology);
	}
}


