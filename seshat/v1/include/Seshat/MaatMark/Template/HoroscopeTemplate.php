<?php

namespace Seshat\MaatMark;

use Seshat\MaatMark;

class TemplateHandler {
	public static $templates = array(
		'hebr'=>1,
		'hebrew'=>'hebr',
		'enoch'=>1,
		'enochian'=>'enoch',
		'astr'=>'astro',
		'astro'=>1,
		'astrology'=>'astro',
		'alchemy'=>1,
		'geo'=>1,
		'gemancy'=>'geo',
		'sym'=>1,
		'symbol'=>'sym',
		'date'=>1,
		'horoscope'=>1,
		'moon'=>1,
	);
	public static $sizes = array('mini'=>'12','small'=>'16','big'=>'32','large'=>'48','huge'=>'64');

	public static $hebrew_chars = 'A E B G Dh H O U V Z Ch T I Y K L M N S Aa As Ng Ph Ts Tz Q R Sh Th';
	public static $hebrew = array(
		'&#1488;',0,'&#1488;',0, // Aleph (A,E)
		'&#1489;',0, // Beth (B)
		'&#1490;',0, // Gimel (G)
		'&#1491;',0,0, // Daleth (D,Dh)
		'&#1492;',0, // He (H)
		'&#1493;',0,'&#1493;',0,'&#1493;',0, // Vav (OUV)
		'&#1494;',0, // Zain (Z)
		'&#1495;',0,0, // Cheth (Ch)
		'&#1496;',0, // Teth (T)
		'&#1497;',0,'&#1497;',0, // Yod (I,Y)
		'&#1499;',0, // Kaph (K)
		'&#1500;',0, // Lamed (L)
		'&#1502;',0, // Mem (M)
		'&#1504;',0, // Nun (N)
		'&#1505;',0, // Samekh (S)
		'&#1506;',0,0,'&#1506;',0,0,'&#1506;',0,0, // Ayin (Aa,As,Ng)
		'&#1508;',0,0, // Pe (P,Ph)
		'&#1510;',0,0,'&#1510;',0,0, // Tzaddi (Ts,Tz)
		'&#1511;',0, // Qoph (Q)
		'&#1512;',0, // Resh (R)
		'&#1513;',0,0, // Shin (Sh)
		'&#1514;',0,0, // Tau (Th)
	);
	public static $hebrew_final_chars = 'K M N Ph Ts Tz';
	public static $hebrew_final = array('&#1498;',0,'&#1501;',0,'&#1503;',0,'&#1507;',0,0,'&#1509;',0,0,'&#1509;',0);

	public static $enoch = array(
		'e'=>1,'a'=>1,'f'=>1,'d'=>1,'g'=>1,'j'=>'g','c'=>'k','k'=>1,'b'=>1,'n'=>1,'q'=>1,'p'=>1,'l'=>1,
		'h'=>1,'y'=>'i','i'=>1,'m'=>1,'t'=>1,'s'=>1,'u'=>'v','v'=>1,'w'=>'v','z'=>1,'r'=>1,'o'=>1,'x'=>1);

	public static $astro = array(
//		'aries'=>'&#x2648;','taurus'=>'&#x2649;','gemini'=>'&#x264A;','cancer'=>'&#x264B;','leo'=>'&#x264C;','virgo'=>'&#x264D;',
//		'libra'=>'&#x264E;','scorpio'=>'&#x264F;','sagittarius'=>'&#x2650;','capricorn'=>'&#x2651;','aquarius'=>'&#x2652;','pisces'=>'&#x2653;',
		'aries'=>1,'taurus'=>1,'gemini'=>1,'cancer'=>1,'leo'=>1,'virgo'=>1,
		'libra'=>1,'scorpio'=>1,'sagittarius'=>1,'capricorn'=>1,'aquarius'=>1,'pisces'=>1,

//		'sol'=>'&#x2609;','luna'=>'&#x263D;','mercury'=>'&#x263F;','venus'=>'&#x2640;','mars'=>'&#x2642;','jupiter'=>'&#x2643;','saturn'=>'&#x2644;',
//		'uranus'=>'&#x2645;','neptune'=>'&#x2646;','pluto'=>'&#x2647;',
//		'caput-draconis'=>'&#x260A;','cauda-draconis'=>'&#x260B;','lilith'=>'&#x26B8;','retro'=>'&#x211E;','fortune'=>'&#x2297;',
//		'ceres'=>'&#x26B3;','pallas'=>'&#x26B4;','juno'=>'&#x26B5;','vesta'=>'&#x26B6;',
//		'asc'=>'A<sup>sc</sup>','ascendant'=>'A<sup>sc</sup>','mc'=>'M<sup>c</sup>','medium-coeli'=>'M<sup>c</sup>',
//		'sun'=>'&#x2609;','moon'=>'&#x263D;','rmoon'=>'&#x263D;','lmoon'=>'&#x263E;','tellus'=>'&#x1F728;',
		'sol'=>1,'luna'=>1,'mercury'=>1,'venus'=>1,'mars'=>1,'jupiter'=>1,'saturn'=>1,'uranus'=>1,'neptune'=>1,'pluto'=>1,
		'caput-draconis'=>1,'cauda-draconis'=>1,'lilith'=>1,'retro'=>1,'fortune'=>1,
		'chiron'=>1,'ceres'=>1,'pallas'=>1,'juno'=>1,'vesta'=>1,
		'asc'=>'A<sup>sc</sup>','ascendant'=>'A<sup>sc</sup>','mc'=>'M<sup>c</sup>','medium-coeli'=>'M<sup>c</sup>',
		'sun'=>'sol','moon'=>'luna','rmoon'=>'luna','lmoon'=>'lluna','tellus'=>1,

		'conjunction'=>1,'semi-sextile'=>1,'decile'=>1,'novile'=>1,'semi-square'=>1,'septile'=>1,'quintile'=>1,'trine'=>1,'square'=>1,'sextile'=>1,
		'sesquiquadrate'=>1,'biquintile'=>'&#x0062;&#x0051;','quincunx'=>1,'opposition'=>1,

		'new-moon'=>'&#x1F311;','crescent-moon'=>'&#x1F312;','first-quarter-moon'=>'&#x1F313;','gibbous-moon'=>'&#x1F314;',
		'full-moon'=>'&#x1F315;','disseminating-moon'=>'&#x1F316;','last-quarter-moon'=>'&#x1F317;','balsamic-moon'=>'&#x1F318;',

		'fire'=>1,'air'=>1,'water'=>1,'earth'=>1,
	);
	public static $alchemy = array(
//		'fire'=>'&#x1F702;','air'=>'&#x1F701;','water'=>'&#x1F704;','earth'=>'&#x1F703;',
//		'salt'=>'&#x1F714;','mercury'=>'&#x263F;','sulfur'=>'&#x1F70D;','sulphur'=>'&#x1F70D;',
//		'gold'=>'&#x2609;','silver'=>'&#x263D;','quicksilver'=>'&#x263F;','copper'=>'&#x2640;','iron'=>'&#x2642;','tin'=>'&#x2643;','lead'=>'&#x2644;',
		'fire'=>1,'air'=>1,'water'=>1,'earth'=>1,
		'salt'=>1,'mercury'=>1,'sulphur'=>1,'sulfur'=>'sulphur',
		'gold'=>'sol','silver'=>'luna','quicksilver'=>'mercury','copper'=>'venus','iron'=>'mars','tin'=>'jupiter','lead'=>'saturn',
	);

	public static $geo = array(
		'puer'=>1,'amissio'=>1,'albus'=>1,'populus'=>1,'via'=>1,'fortuna-major'=>1,'fortuna-minor'=>1,'conjunctio'=>1,
		'puella'=>1,'rubeus'=>1,'acquisitio'=>1,'carcer'=>1,'tristitia'=>1,'laetitia'=>1,'caput-draconis'=>1,'cauda-draconis'=>1,
	);

	public static $symbol = array(
		'pentagram'=>'&#x26E4;','rpentagram'=>'&#x26E5;','lpentagram'=>'&#x26E6;','ipentagram'=>'&#x26E7;',
		'caduceus'=>1,'ankh'=>'&#x2625;','eye-of-horus'=>1,'hexagram'=>'&#x2721;','peace'=>'&#x262E;','love'=>'&#x2661;',
		'yinyang'=>'&#x262F;','trigram1'=>'&#x2630;','trigram2'=>'&#x2631;','trigram3'=>'&#x2632;',
		'trigram4'=>'&#x2633;','trigram5'=>'&#x2634;','trigram6'=>'&#x2635;','trigram7'=>'&#x2636;','trigram8'=>'&#x2637;',
	);

	public $template;

	function __construct($templ,&$params) {
		global $sc;
		$templ = strtolower($templ);
		$n = self::$templates[$templ];
		if($n===1) $n = $templ;
		$this->template = '';
		if($n) {
			$n = 'templ_'.$n;
			$this->$n($params);
		} else {
			$this->template = '';
		}
	}

	final public static function &parse($text) {
		
	}

	private function param_style(&$params,&$t,&$sz) {
		$t = '';
		$sz = '24';
		foreach($params as $i=>$p) {
			if(isset(self::$sizes[$p])) $sz = self::$sizes[$p];
			else break;
			unset($params[$i]);
		}
	}

	private function templ_hebr(&$params) {
		$this->param_style($params,$t,$sz);
		foreach($params as $p) {
			if($t) $t .= ' ';
			$t .= '<b title="'.$p.'">';
			for($i=0,$len=strlen($p),$l=1; $i<$len; $i+=$l,$l=1) {
				$c = $p[$i];
				$n = false;
				if($c!=' ') {
					if($i+1<$len) {
						$c2 = $p[$i+1];
						if($c2!=' ') $n = strpos(self::$hebrew_chars,$c.$c2);
					}
					if($n===false) $n = strpos(self::$hebrew_chars,$c);
					else { $c .= $c2;$l = 2; }
				}
				if($n!==false) {
					$f = ($len>$l && ($i+$l==$len || strpos(" \t\n\r",$p[$i+$l])!==false))?
								strpos(self::$hebrew_final_chars,$c) : false;
//echo "c=$c, n=$n, f=$f<br />";
					$t .= $f!==false? self::$hebrew_final[$f] : self::$hebrew[$n];
				} else $t .= $c;
			}
			$t .= '</b>';
		}
		$this->template .= '<s class="md-sz'.$sz.' md-hebrew"><b>'.trim($t).'</b></s>';
	}

	private function templ_enoch(&$params) {
		$this->param_style($params,$t,$sz);
		foreach($params as $p) {
			if($t) $t .= ' ';
			$t .= '<b title="'.strtoupper($p).'">';
			for($i=strlen($p)-1,$p=strtolower($p); $i>=0; --$i) {
				$c = $p[$i];
				if(isset(self::$enoch[$c])) {
					if(($a=self::$enoch[$c])===1) $a = $c;
					$t .= '<i class="md-'.$a.'"></i>';
				} else $t .= $c;
			}
			$t .= '</b>';
		}
		$this->template .= '<s class="md-sz'.$sz.' md-enochian">'.trim($t).'</s>';
	}

	private function templ_astro(&$params) {
		$this->param_style($params,$t,$sz);
		foreach($params as $p) {
			$i = strtolower(trim($p));
			if(isset(self::$astro[$i]) && ($a=self::$astro[$i])) {
				$t .= '<b title="'.ucfirst($i).'">';
				if($a[0]=='&') $t .= $a;
				else {
					if($a===1) $a = $i;
					$t .= '<i class="md-'.$a.'"></i>';
				}
				$t .= '</b>';
			}
		}
		$this->template .= '<s class="md-sz'.$sz.' md-astrology">'.$t.'</s>';
	}

	private function templ_alchemy(&$params) {
		$this->param_style($params,$t,$sz);
		foreach($params as $p) {
			$i = strtolower(trim($p));
			if(isset(self::$alchemy[$i]) && ($a=self::$alchemy[$i])) {
				$t .= '<b title="'.ucfirst($i).'">';
				if($a[0]=='&') $t .= $a;
				else {
					if($a===1) $a = $i;
					$t .= '<i class="md-'.$a.'"></i>';
				}
				$t .= '</b>';
			}
		}
		$this->template .= '<s class="md-sz'.$sz.' md-alchemy">'.$t.'</s>';
	}

	private function templ_geo(&$params) {
		$this->param_style($params,$t,$sz);
		foreach($params as $p) {
			$i = strtolower(trim($p));
			if(isset(self::$geo[$i]) && ($a=self::$geo[$i])) {
				$t .= '<b title="'.ucfirst($i).'">';
				if($a[0]=='&') $t .= $a;
				else {
					if($a===1) $a = $i;
					$t .= '<i class="md-'.$a.'"></i>';
				}
				$t .= '</b>';
			}
		}
		$this->template .= '<s class="md-sz'.$sz.' md-geomancy">'.$t.'</s>';
	}

	private function templ_sym(&$params) {
		$this->param_style($params,$t,$sz);
		foreach($params as $p) {
			$i = strtolower(trim($p));
			if(isset(self::$symbol[$i]) && ($s=self::$symbol[$i])) {
				$t .= '<b title="'.ucfirst($i).'">';
				if($s[0]=='&') $t .= $s;
				else {
					if($s===1) $s = $i;
					$t .= '<i class="md-'.$s.'"></i>';
				}
				$t .= '</b>';
			}
		}
		$this->template .= '<s class="md-sz'.$sz.' symbol">'.$t.'</s>';
	}

/*	private function templ_date(&$params) {
		global $sc;
		$t = strtotime($params[0]);
		$this->template = $t!==false? $sc->formatDate($t) : '';
	}*/

	private function templ_horoscope(&$params) { $this->template = ''; }

	private function templ_moon(&$params) { $this->template = ''; }
}


