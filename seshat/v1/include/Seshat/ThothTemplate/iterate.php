<?php

use Seshat\ThothTemplate;

return function(&$thoth,$param) {
	$vars = &$thoth->getVars();
	$n = count($param);
	if($n<1) return '';
	$key = $param[0];
	$value = $n>=2? stripcslashes($param[1]) : false;
	$parse = strpos($value,'{*')!==false;
	if(!isset($vars[$key])) return '';
	$var = $vars[$key];
	if(!is_array($var)) $var = array($key=>$var);
	$ret = '';
ThothTemplate::log("Value[{$value}]");
	foreach($var as $k=>$v) {
		$vars['#'] = $k;
		$vars['='] = $v;
		if($value) $ret .= $parse? $thoth->parse($value) : $value;
		else $ret .= $v;
	}
	return $ret;
};

