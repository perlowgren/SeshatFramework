<?php
/** Very simple database handler for MySQL and SQLite3.
 * 
 * @file include/Seshat/IsisDB.php  
 * @author Per Löwgren
 * @date Modified: 2015-04-05
 * @date Created: 2015-01-31
 */

namespace Seshat;

use mysqli;
use SQLite3;

define('CHLOE_MYSQL',1);
define('CHLOE_SQLITE3',2);

abstract class IsisDB {
	protected $type;
	protected $db;
	protected $result;
	protected $is_new;

	public static function open($type,$db) {
		switch(strtolower($type)) {
			case CHLOE_MYSQL:
			case 'mysql':return new Isis_MySQL($db);

			case CHLOE_SQLITE3:
			case 'sqlite3':return new Isis_SQLite3($db);

			default:return null;
		}
	}

	/** Close database */
	abstract public function close();

	public function isNew() { return $this->is_new; }

	abstract public function exec($sql,$p=false);
	abstract public function query($sql,$p=false);
	abstract public function row($sql,$p=false);
	abstract public function column($sql,$p=false);
	abstract public function insertID();
	abstract public function error();

	/** Format string to db, i.e. replacing ' with '' */
	abstract public function sql($s);
}

class Isis_MySQL extends IsisDB {
	public function __construct($db) {
		$this->is_new = false;
		$this->db = new mysqli($db['host'],$db['user'],$db['password'],$db['db']);
	}

	/** Close database */
	public function close() {
		$db = &$this->db;
		$db->close();
		unset($db);
		$db = null;
	}

	public function exec($sql,$p=false) {
		if($p===false) $this->db->query($sql);
		else $this->query($sql,$p);
	}

	public function query($sql,$p=false) {
		if($p===false) return $this->db->query($sql);
		$stmt = $this->db->prepare($sql);
		for($i=0,$n=count($p); $i<$n; ++$i) {
			$v = $p[$i];
			$t = is_int($v)? 'i' : 's';
			if(is_array($v) || is_object($v)) $v = json_encode($v);
			$stmt->bind_param($t,$v);
		}
		$stmt->execute();
		return $stmt->get_result();
	}

	public function row($sql,$p=false) { return ($result=$this->query($sql,$p))? $result->fetch_array(MYSQLI_ASSOC) : null; }
	public function column($sql,$p=false) { return ($result=$this->query($sql,$p)) && ($row=$result->fetch_array(MYSQLI_NUM))? $row[0] : null; }
	public function insertID() { return $this->db->insert_id; }
	public function error() { return $this->db->error; }
	public function sql($s) { return str_replace('\'','\'\'',$s); }
}

class Isis_SQLite3 extends IsisDB {
	public function __construct($db) {
		$this->is_new = (!file_exists($db) || filesize($db)===0)? 1 : 0;
		$this->db = new SQLite3($db);
		$this->db->busyTimeout(60000);
	}

	/** Close database */
	public function close() {
		$db = &$this->db;
		$db->close();
		unset($db);
		$db = null;
	}

	public function exec($sql,$p=false) {
		if($p===false) $this->db->exec($sql);
		else $this->query($sql,$p);
	}

	public function query($sql,$p=false) {
		if($p===false) return $this->db->query($sql);
		$stmt = $this->db->prepare($sql);
		for($i=0,$n=count($p); $i<$n; ++$i) {
			$v = $p[$i];
			$t = is_int($v)? SQLITE3_INTEGER : SQLITE3_TEXT;
			if(is_array($v) || is_object($v)) $v = json_encode($v);
			$stmt->bindValue($i+1,$v,$t);
		}
		return $stmt->execute();
	}

	public function row($sql,$p=false) {
		if($p===false) return $this->db->querySingle($sql,true);
		$result = $this->query($sql,$p);
		return $result->fetchArray(SQLITE3_ASSOC);
	}

	public function column($sql,$p=false) {
//echo "IsisDB: $sql\n";
		if($p===false) return $this->db->querySingle($sql);
		$result = $this->query($sql,$p);
		$arr = $result->fetchArray(SQLITE3_NUM);
		return $arr[0];
	}

	public function insertID() { return $this->db->lastInsertRowID(); }
	public function error() { return $this->db->lastErrorMsg(); }
	public function sql($s) { return str_replace('\'','\'\'',$s); }
}

