<?php

use Seshat\ThothTemplate;

return function(&$thoth,$param) {
	$vars = &$thoth->getVars();
	$n = count($param);
	if($n<2) return '';
	$result = trim($param[0]);
	if(strpos($result,'{*')!==false) $result = $thoth->parse($result);
	if($result && strcasecmp($result,'false')!==0) $value = $param[1];
	elseif($n>2) $value = $param[2];
	else return '';
	$ret = '';
	$vars['#'] = $result;
	$vars['='] = $value;
	$ret .= strpos($value,'{*')!==false? $thoth->parse($value) : $value;
	return $ret;
};

