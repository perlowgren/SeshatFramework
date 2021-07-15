<?php
/** AnubisAjax extends SetSession, and thus handles sessions and database, 
 * and is expected to be run within a script called by Ajax.
 * 
 * @file include/Seshat/AnubisAjax.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-17
 * @date Created: 2016-01-17
 */

namespace Seshat;

use Seshat\SetSession;
use Seshat\ThothTemplate;

class AnubisAjax extends SetSession {

	/** Constructor; initiate parameters
	 * @param &$db Reference to IsisDB object
	 * @param $mime Output mime, default for AnubisAjax is to output JSON */
	public function __construct(&$db,$mime='application/json') {
		parent::__construct($db,$mime);

		//Request identified as ajax request
		if(!isset($_SERVER['HTTPS']) ||
				strtolower($_SERVER['HTTPS'])!='on')
			self::error(400,'Only requests by HTTPS');

		//Request identified as ajax request
		if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
				strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])!='xmlhttprequest')
			self::error(400,'Request must be Ajax');

		//HTTP_REFERER verification
		if(!isset($_SERVER['HTTP_REFERER']) ||
				strpos($_SERVER['HTTP_REFERER'],HTTPS_HOST)!==0)
			self::error(403,'Referer must be same host');

		if(!isset($_COOKIE['access-token']) || !isset($_SESSION['access-token']) ||
				$_COOKIE['access-token']!=$_SESSION['access-token'])
			self::error(403,'Access token missing or incorrect ('.$_COOKIE['access-token'].'!='.$_SESSION['access-token'].')');
	}
}

