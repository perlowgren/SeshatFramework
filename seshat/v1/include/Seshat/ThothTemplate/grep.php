<?php

use Seshat\ThothTemplate;

return function(&$thoth,$param) {
	$vars = &$thoth->getVars();
	$n = count($param);
	if($n<1) return '';
	$match = $param[0];
	$format = $n>=2? stripcslashes($param[1]) : false;
	$parse = strpos($format,'{*')!==false;
	$ret = '';
	foreach($vars as $k=>$v) {
		if(!ThothTemplate::isSpecialVariable($k)) {
			if(preg_match($match,$k,$m)) {
				if(!$format) $ret .= $v;
				else {
					$vars['#'] = $k;
					$vars['='] = $v;
					for($i=0,$n=count($m); $i<$n && $i<10; ++$i) $vars[''.$i] = $m[$i];
					$ret .= $parse? $thoth->parse($format) : $format;
				}
			}
		}
	}
	return $ret;
};

