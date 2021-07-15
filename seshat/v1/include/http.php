<?php

function http_status_header($code=500) {
	static $http_status = array(
		200=>'HTTP/1.0 200 OK',
		201=>'HTTP/1.0 201 Created',
		202=>'HTTP/1.0 202 Accepted',
		203=>'HTTP/1.1 203 Non-Authoritative Information',
		204=>'HTTP/1.0 204 No Content',
		205=>'HTTP/1.0 205 Reset Content',
		206=>'HTTP/1.0 206 Partial Content',
		300=>'HTTP/1.0 300 Multiple Choices',
		301=>'HTTP/1.0 301 Moved Permanently',
		302=>'HTTP/1.0 302 Found',
		303=>'HTTP/1.1 303 See Other',
		304=>'HTTP/1.0 304 Not Modified',
		305=>'HTTP/1.1 305 Use Proxy',
		306=>'HTTP/1.0 306 Switch Proxy',
		307=>'HTTP/1.1 307 Temporary Redirect',
		400=>'HTTP/1.0 400 Bad Request',
		401=>'HTTP/1.0 401 Unauthorized',
		402=>'HTTP/1.0 402 Payment Required',
		403=>'HTTP/1.0 403 Forbidden',
		404=>'HTTP/1.0 404 Not Found',
		405=>'HTTP/1.0 405 Method Not Allowed',
		406=>'HTTP/1.0 406 Not Acceptable',
		407=>'HTTP/1.0 407 Proxy Authentication Required',
		408=>'HTTP/1.0 408 Request Timeout',
		409=>'HTTP/1.0 409 Conflict',
		410=>'HTTP/1.0 410 Gone',
		411=>'HTTP/1.0 411 Length Required',
		412=>'HTTP/1.0 412 Precondition Failed',
		413=>'HTTP/1.0 413 Request Entity Too Large',
		414=>'HTTP/1.0 414 Request-URI Too Long',
		415=>'HTTP/1.0 415 Unsupported Media Type',
		416=>'HTTP/1.0 416 Requested Range Not Satisfiable',
		417=>'HTTP/1.0 417 Expectation Failed',
		418=>'HTTP/1.0 418 I\'m a teapot',
		500=>'HTTP/1.0 500 Internal Server Error',
		501=>'HTTP/1.0 501 Not Implemented',
		502=>'HTTP/1.0 502 Bad Gateway',
		503=>'HTTP/1.0 503 Service Unavailable',
		504=>'HTTP/1.0 504 Gateway Timeout',
		505=>'HTTP/1.0 505 HTTP Version Not Supported'
	);
	if(!isset($http_status[$code])) return false;
	$header = $http_status[$code];
	header($header,true,$code);
	return $header;
}

