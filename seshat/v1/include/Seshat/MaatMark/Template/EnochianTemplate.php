<?php

namespace Seshat\MaatMark\Template;

use Seshat\MaatMark;
use Seshat\MaatMark\GlyphTemplate;

class EnochianTemplate extends GlyphTemplate {
	public static $enochian = array(
		'A'=>'&#x30041;', // Un (A)
		'B'=>'&#x30042;', // Pa (B)
		'C'=>'&#x30043;', // Veh (C,K)
		'K'=>'&#x3004B;',
		'D'=>'&#x30044;', // Gal (D)
		'E'=>'&#x30045;', // Graph "Graupha" (E)
		'F'=>'&#x30046;', // Or "Orh" (F)
		'G'=>'&#x30047;', // Ged (G,J)
		'J'=>'&#x3004A;',
		'H'=>'&#x30048;', // Na "Nach" (H)
		'I'=>'&#x30049;', // Gon (I)
		'L'=>'&#x3004C;', // Ur "Our, Ourh" (L)
		'M'=>'&#x3004D;', // Tal "Stall, Xtall" (M)
		'N'=>'&#x3004E;', // Drux "Droux" (N)
		'O'=>'&#x3004F;', // Med (O)
		'P'=>'&#x30050;', // Mals "Machls" (P)
		'Q'=>'&#x30051;', // Ger "Gierh" (Q)
		'R'=>'&#x30052;', // Don (R)
		'S'=>'&#x30053;', // Fam (S)
		'T'=>'&#x30054;', // Gisg (T)
		'U'=>'&#x30055;', // Van (U,V)
		'V'=>'&#x30056;',
		'X'=>'&#x30058;', // Pal (X)
		'Y'=>'&#x30059;', // Gon (Y)
		'Z'=>'&#x3005A;', // Ceph "Keph" (Z)
	);

	public function expand() {
		return $this->expandAlphabet('enochian',self::$enochian,false,false,false,true);
	}
}


