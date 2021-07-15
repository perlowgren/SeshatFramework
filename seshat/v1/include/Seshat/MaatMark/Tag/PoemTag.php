<?php

namespace Seshat\MaatMark\Tag;

use Seshat\MaatMark;
use Seshat\MaatMark\TagHandler;

class PoemTag extends TagHandler {
	public function format(&$param,&$text) {
		$style = '';
		if($param) {
			$param = Markup::parse_params($param,$arr);
			if(isset($arr['style'])) $style = ' style="'.$arr['style'].'"';
		}
		$text = strtr(trim($text,"\n"),array("\n "=>"<br />\n&nbsp;","\n"=>"<br />\n",'  '=>'&nbsp;&nbsp;'));
		return '<p class="sc_poem"'.$style.'>'.$text.'</p>';
	}
}


?>