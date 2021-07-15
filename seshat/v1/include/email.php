<?php

function send_email($from,$to,$subject,$message) {
	$headers = array(
		'MIME-Version: 1.0',
		'Date: ' . date('r', $_SERVER['REQUEST_TIME']),
		'X-Mailer: PHP v'.phpversion(),
		'X-Originating-IP: '.$_SERVER['SERVER_ADDR']
	);
	if(is_array($from)) {
		if(isset($from['from'])) $headers[] = 'From: '.$from['from'];
		if(isset($from['reply-to'])) $headers[] = 'Reply-To: '.$from['reply-to'];
		if(isset($from['return-path'])) $headers[] = 'Return-Path: '.$from['return-path'];
		if(isset($from['cc'])) $headers[] = 'Cc: '.$from['cc'];
		if(isset($from['bcc'])) $headers[] = 'Bcc: '.$from['bcc'];
	} else $headers[] = 'From: '.$from;

	$quoted_printable = '';
	$plain_text = false;
	$html_text = false;
	$embed = false;
	if(isset($message['plain'])) $plain_text = $message['plain'];
	if(isset($message['html'])) $html_text = $message['html'];
	if(isset($message['embed'])) $embed = $message['embed'];
	if(($plain_text && $html_text) || $embed) {
		if(function_exists('quoted_printable_encode')) {
			$quoted_printable = "Content-Transfer-Encoding: quoted-printable\r\n";
			if($plain_text) $plain_text = quoted_printable_encode($plain_text);
			if($html_text) $html_text = quoted_printable_encode($html_text);
		}
		$tm = time();
		$mime_boundary = "----=_Part_{$tm}.".md5($tm);
		$mime_boundary_header = chr(34) . $mime_boundary . chr(34);
		$notice_text = "This is a multi-part message in MIME format.";
		$message = "{$notice_text}\r\n\r\n";
		if($plain_text) $message .= "--{$mime_boundary}\r\n".
					"Content-Type: text/plain; charset=UTF-8\r\n".
					"{$quoted_printable}\r\n".
					"{$plain_text}\r\n\r\n";
		if($html_text) {
			if($embed) {
				$related_boundary = "----=_Related_{$tm}.".md5($tm);
				$message .= "--{$mime_boundary}\r\n".
					"Content-Type: multipart/related; boundary=\"{$related_boundary}\"\r\n\r\n".
					"--{$related_boundary}\r\n".
					"Content-Type: text/html; charset=UTF-8\r\n".
					"{$quoted_printable}\r\n".
					"{$html_text}\r\n\r\n";
				foreach($embed as $e) {
					$file = DIR_RESOURCE.$e['file'];
					if(!file_exists($file)) continue;
					$file = file_get_contents($file);
					$base64 = chunk_split(base64_encode($file));
					$name = $e['name'];
					$mime = $e['mime'];
					$cid = $e['cid'];
					$message .= "--{$related_boundary}\r\n".
						"Content-Type: {$mime}; name=\"{$name}\"\r\n".
						"Content-Disposition: inline; filename=\"{$name}\"\r\n".
						"Content-Location: {$name}\r\n".
						"Content-ID: <{$cid}>\r\n".
						"Content-Transfer-Encoding: base64\r\n\r\n".
						$base64."\r\n";
				}
				$message .= "--{$related_boundary}--\r\n\r\n";
			} else {
				$message .= "--{$mime_boundary}\r\n".
					"Content-Type: text/html; charset=UTF-8\r\n".
					"{$quoted_printable}\r\n".
					"{$html_text}\r\n\r\n";
			}
		}
		$message .= "--{$mime_boundary}--";

		$headers[] = 'Content-Type: multipart/alternative;';
		$headers[] = "\tboundary=".$mime_boundary_header;
	} elseif($plain_text) {
		$message = $plain_text;
		$headers[] = 'Content-Type: text/plain; charset=UTF-8';
	} elseif($html_text) {
		$message = $html_text;
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
	} else return false;

	$headers = implode("\r\n",$headers);

	if(is_array($to)) {
		$i = 0;
		foreach($to as $n)
			if(mail($n,$subject,$message,$headers)) ++$i;
		return $i;
	} else return mail($to,$subject,$message,$headers);
}

