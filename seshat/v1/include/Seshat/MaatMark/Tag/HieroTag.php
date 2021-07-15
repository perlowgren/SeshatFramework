<?php

namespace Seshat\MaatMark\Tag;

use Seshat\MaatMark;
use Seshat\MaatMark\TagHandler;

use Horus\HorusHiero;
use Horus\HorusNode;

require_once DIR_PLUGIN.'horus-hiero/phonemes.php';

class HieroTag extends TagHandler {
	public function expand() {
		$font = 'NewGardiner';
		$height = 38;
		$lines = false;
		if($this->params) {
			MaatMark::parseParams($this->params,$params);
			if(isset($params['font'])) $font = intval($params['font']);
			if(isset($params['height'])) $height = intval($params['height']);
			if(isset($params['lines'])) $lines = true;
		}

		require_once DIR_PLUGIN."horus-hiero/metrics-{$font}.php";

//echo "<p>HieroTag: {$this->content}</p>";
		$text = HorusHiero::parse($this->content,array(
			'font'=>$font,
			'format'=>HIERO_MANUEL_DE_CODAGE,
			'output'=>'html',
			'font-url'=>"/horus-hiero/{$font}/",
			'height'=>$height,
			'lines'=>$lines,
		));
//echo "<p>{$text}</p>";
		return $text;
	}
}

?>