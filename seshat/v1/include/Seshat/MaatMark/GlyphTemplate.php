<?php

namespace Seshat\MaatMark;

use Seshat\MaatMark;
use Seshat\MaatMark\TemplateHandler;

class GlyphTemplate extends TemplateHandler {
	private static $sizes = array('mini'=>'0.8em','small'=>'0.9em','big'=>'1.5em','large'=>'2em','huge'=>'3em');
	private static $color_names = ' aliceblue antiquewhite aqua aquamarine azure beige bisque black blanchedalmond blue '.
		'blueviolet brown burlywood cadetblue chartreuse chocolate coral cornflowerblue cornsilk crimson cyan darkblue darkcyan '.
		'darkgoldenrod darkgray darkgreen darkkhaki darkmagenta darkolivegreen darkorange darkorchid darkred darksalmon '.
		'darkseagreen darkslateblue darkslategray darkturquoise darkviolet deeppink deepskyblue dimgray dodgerblue firebrick '.
		'floralwhite forestgreen fuchsia gainsboro ghostwhite gold goldenrod gray green greenyellow honeydew hotpink indianred '.
		'indigo ivory khaki lavender lavenderblush lawngreen lemonchiffon lightblue lightcoral lightcyan lightgoldenrodyellow '.
		'lightgray lightgreen lightpink lightsalmon lightseagreen lightskyblue lightslategray lightsteelblue lightyellow lime '.
		'limegreen linen magenta maroon mediumaquamarine mediumblue mediumorchid mediumpurple mediumseagreen mediumslateblue '.
		'mediumspringgreen mediumturquoise mediumvioletred midnightblue mintcream mistyrose moccasin navajowhite navy oldlace '.
		'olive olivedrab orange orangered orchid palegoldenrod palegreen paleturquoise palevioletred papayawhip peachpuff peru '.
		'pink plum powderblue purple rebeccapurple red rosybrown royalblue saddlebrown salmon sandybrown seagreen seashell '.
		'sienna silver skyblue slateblue slategray snow springgreen steelblue tan teal thistle tomato turquoise violet wheat '.
		'white whitesmoke yellow yellowgreen ';

	protected function paramStyle(&$style) {
		$style = '';
		$n = count($this->params);
		foreach($this->params as $i=>$p) {
			if($i==$n-1) break;
			$p = strtolower($p);
			$size = false;
			$color = false;
			if(isset(self::$sizes[$p])) $size = self::$sizes[$p];
			elseif(strpos(self::$color_names," {$p} ")!==false) $color = $p;
			elseif(preg_match('/\A(\d+(?:\.\d+)?(?:em|px|\%)|\#[0-9a-fA-F]{6})\z/',$p)) {
				if($p[0]=='#') $color = $p;
				else $size = $p;
			} else break;
			if(!$style) $style = ' style="';
			if($size) $style .= "font-size:{$size};";
			elseif($color) $style .= "color:{$color};";
			unset($this->params[$i]);
			++$n;
		}
		if($style) $style .= '"';
	}

	public function expandGlyphs($class,$glyphs,$case=false) {
		$this->paramStyle($style);
		$t = '';
		foreach($this->params as $p) {
			$p = trim($p);
			if(!$case) $p = strtolower($p);
			if(isset($glyphs[$p])) {
				$g = $glyphs[$p];
				$s = ucfirst($p);
				$t .= "<span title=\"{$s}\">{$g}</span>";
			} else $t .= $p;
		}
		return "<s class=\"astrology\"{$style}>{$t}</s>";
	}

	public function expandAlphabet($class,$letters,$finals=false,$case=false,$dbl=false,$r2l=false) {
		$this->paramStyle($style);
		$t = '';
		foreach($this->params as $p) {
			if($t) $t .= ' ';
			$t .= "<span title=\"{$p}\">";
			if(!$case) $p = strtoupper($p);
			for($i=0,$len=strlen($p),$l=1; $i<$len; $i+=$l,$l=1) {
				$s = false;
				$c = $p[$r2l? $len-$i-1 : $i];
				if(!ctype_space($c)) {
					if($dbl && $i+1<$len) {
						$c2 = $p[$r2l? $len-$i-2 : $i+1];
						if(!ctype_space($c2) && isset($letters[$c2=($r2l? $c2.$c : $c.$c2)])) {
							$c = $c2;
							$s = $letters[$c];
							$l = 2;
						}
					}
					if($s===false && isset($letters[$c])) $s = $letters[$c];
					if($s!==false) {
						if($finals && ($i+$l==$len || ctype_space($p[$i+$l])) && isset($finals[$s]))
							$s = $finals[$s];
						$t .= $s;
					}
				}
				if($s===false) $t .= $c;
			}
			$t .= '</span>';
		}
		$t = trim($t);
		return "<s class=\"{$class}\"{$style}>{$t}</s>";
	}
}


