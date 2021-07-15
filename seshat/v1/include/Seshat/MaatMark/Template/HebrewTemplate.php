<?php

namespace Seshat\MaatMark\Template;

use Seshat\MaatMark;
use Seshat\MaatMark\GlyphTemplate;

class HebrewTemplate extends GlyphTemplate {
	public static $hebrew = array(
		'A'=>'&#1488;',   // Aleph (A,E)
		'E'=>'&#1488;',
		'B'=>'&#1489;',   // Beth (B)
		'G'=>'&#1490;',   // Gimel (G)
		'D'=>'&#1491;',   // Daleth (D,Dh)
		'Dh'=>'&#1491;',
		'H'=>'&#1492;',   // He (H)
		'O'=>'&#1493;',   // Vav (OUV)
		'U'=>'&#1493;',
		'V'=>'&#1493;',
		'Z'=>'&#1494;',   // Zain (Z)
		'C'=>'&#1495;',   // Cheth (C,Ch)
		'Ch'=>'&#1495;',
		'T'=>'&#1496;',   // Teth (T)
		'I'=>'&#1497;',   // Yod (I,Y)
		'Y'=>'&#1497;',
		'K'=>'&#1499;',   // Kaph (K)
		'L'=>'&#1500;',   // Lamed (L)
		'M'=>'&#1502;',   // Mem (M)
		'N'=>'&#1504;',   // Nun (N)
		'S'=>'&#1505;',   // Samekh (S)
		'Aa'=>'&#1506;',  // Ayin (Aa,As,Ng)
		'As'=>'&#1506;',
		'Ng'=>'&#1506;',
		'P'=>'&#1508;',   // Pe (P,Ph)
		'Ph'=>'&#1508;',
		'Ts'=>'&#1510;',  // Tzaddi (Ts,Tz)
		'Tz'=>'&#1510;',
		'Q'=>'&#1511;',   // Qoph (Q)
		'R'=>'&#1512;',   // Resh (R)
		'Sh'=>'&#1513;',  // Shin (Sh)
		'Th'=>'&#1514;',  // Tau (Th)
	);
	public static $hebrew_finals = array(
		'&#1499;'=>'&#1498;', // Kaph
		'&#1502;'=>'&#1501;', // Mem
		'&#1504;'=>'&#1503;', // Nun
		'&#1508;'=>'&#1507;', // Ayin
		'&#1510;'=>'&#1509;'  // Tzaddi
	);

	public function expand() {
		return $this->expandAlphabet('hebrew',self::$hebrew,self::$hebrew_finals,true,true,false);
	}
}


