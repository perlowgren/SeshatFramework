<?php

namespace Seshat\MaatMark\Template;

use Seshat\MaatMark;
use Seshat\MaatMark\GlyphTemplate;

class SymbolTemplate extends GlyphTemplate {
	public static $symbol = array(
		'caduceus'=>'&#x2624;',             'caduceus-2'=>'&#x269A;',           'ankh'=>'&#x2625;',
		'orthodox-cross'=>'&#x2626;',       'chi-rho'=>'&#x2627;',              'cross-of-lorraine'=>'&#x2628;',
		'cross-of-jerusalem'=>'&#x2629;',   'star-and-crescent'=>'&#x262A;',    'yinyang'=>'&#x262F;',
		'dharma-wheel'=>'&#x2638;',         'wsyriac-cross'=>'&#x2670;',        'esyriac-cross'=>'&#x2671;',
		'anchor'=>'&#x2693;',               'staff-of-aesculapius'=>'&#x2695;', 'scales'=>'&#x2696;',
		'alembic'=>'&#x2697;',              'pentagram'=>'&#x26E4;',            'rpentagram'=>'&#x26E5;',
		'lpentagram'=>'&#x26E6;',           'ipentagram'=>'&#x26E7;',           'greek-cross'=>'&#x271A;',
		'latin-cross'=>'&#x271D;',          'latin-cross-2'=>'&#x1F546;',       'latin-cross-3'=>'&#x1F547;',
		'maltese-cross'=>'&#x2720;',        'hexagram'=>'&#x2721;',             'hexagram-2'=>'&#x1F52F;',
		'crescent-moon'=>'&#x1F319;',       'new-moon'=>'&#x1F31A;',            'first-quarter-moon'=>'&#x1F31B;',
		'last-quarter-moon'=>'&#x1F31C;',   'full-moon'=>'&#x1F31D;',           'sun'=>'&#x1F31E;',
		'crystal-ball'=>'&#x1F52E;',        'celtic-cross'=>'&#x1F548;',        'aum'=>'&#x1F549;',
		'dove'=>'&#x1F54A;',
	);

	public function expand() {
		return $this->expandGlyphs('symbol',self::$symbol);
	}
}


