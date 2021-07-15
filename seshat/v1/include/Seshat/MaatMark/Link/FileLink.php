<?php

namespace Seshat\MaatMark\Link;

use Seshat\MaatMark;
use Seshat\MaatMark\LinkHandler;

class FileLink extends LinkHandler {
	const FILE_PATH   = 'wiki/';
	const IMAGE_PATH  = 'wiki/';

	/** File types associated with file extensions */
	public static $file_types = array(
		'png'=>'img','gif'=>'img','jpe'=>'img','jpeg'=>'img','jpg'=>'img','svg'=>'img',
		'mp3'=>'audio','mov'=>'video',
		'pdf'=>'doc','doc'=>'doc','rtf'=>'doc','txt'=>'doc','htm'=>'doc','html'=>'doc',
		'zip'=>'file');
	public static $mime_types = array(
		'png' => 'image/png',
		'gif' => 'image/gif',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'svg' => 'image/svg+xml',
		'mp3' => 'audio/mpeg',
		'mov' => 'video/quicktime',
		'pdf' => 'application/pdf',
		'doc' => 'application/msword',
		'rtf' => 'application/rtf',
		'txt' => 'text/plain',
		'htm' => 'text/html',
		'html' => 'text/html',
		'zip' => 'application/zip');

	public $ext;			//<! File extension, e.g. ".png"
	public $file_type;	//<! @see file_types
	public $file_name;	//<! Name of file
	public $file_path;	//<! Absolute path to file, including file name
	public $file_url;		//<! Relative URL to file
	public $file_page;	//<! Relative URL to page in wiki for file
	public $alt;			//<! Alt title text
	public $type;			//<! Frame or Thumb
	public $align;			//<! Left, right or center
	public $upright;		//<! Resize proportions for image
	public $size;			//<! Size of image
	public $mime;			//<! Mime of file

	function __construct(&$maat,$title,$namespace,$name,$parenthesis,$section,$text) {
		$this->name = ucfirst($name);
		parent::__construct($maat,$title,$namespace,$name,$parenthesis,$section,$text);
		if($this->title===false) $this->title = $name;
		$this->ext = '';
		$this->file_type = '';
		$this->file_name = '';
		$this->file_path = '';
		$this->file_url = '';
		$this->file_page = '/page/'.$this->link;
		$this->alt = '';
		$this->type = '';
		$this->align = false;
		$this->upright = false;
		$this->size = false;
		$this->mime = false;
		if(($p=strrpos($this->name,'.'))!==false) {
			$this->ext = strtolower(substr($this->name,$p+1));
			if(isset(self::$file_types[$this->ext])) {
				$this->file_type = self::$file_types[$this->ext];
				$this->file_name = $this->name;
				if($this->file_type=='img') {
					$this->file_path = DIR_RESOURCE.self::IMAGE_PATH.$this->file_name;
					$this->file_url = '/'.self::IMAGE_PATH.$this->file_name;
				} else {
					$this->file_path = DIR_RESOURCE.self::FILE_PATH.$this->file_name;
					$this->file_url = '/'.self::FILE_PATH.$this->file_name;
				}
				$this->mime = self::$mime_types[$this->ext];
			}
		}
		if($this->title) {
			$params = $this->title;
			while(true) {
				if(strpos($params,'|')!==false) list($p,$params) = explode('|',$params,2);
				else list($p,$params) = array($params,'');
				if($p=='frame') $this->type = 'frame';
				elseif($p=='thumb') $this->type = 'thumb';
				elseif($p=='left') $this->align = 'left';
				elseif($p=='center') $this->align = 'center';
				elseif($p=='right') $this->align = 'right';
				elseif(strpos($p,'px')!==false && preg_match('/(\d+)x?(\d*)px/',$p,$m)) $this->size = array((int)$m[1],(int)$m[2]);
				elseif(!strncmp($p,'upright',7)) $this->upright = $p[7]=='='? floatval(substr($p,8)) : 1.0;
				elseif(!strncmp($p,'link=',5)) $this->link = substr($p,5);
				elseif(!strncmp($p,'alt=',4)) $this->alt = substr($p,4);
				else {
					if($params) $p .= '|'.$params;
					$this->title = $p;
					break;
				}
			}
		}
	}

	public function expand() {
		$ret = '';
		$title = $this->title;
		if($this->maat && $title)
			$title = $this->maat->parseInline($title);
		switch($this->file_type) {
			case 'img':
				$cl = '';
				$st = '';
				$sz = '';
				$w = false;
				$h = false;
				$s = file_exists($this->file_path)? getimagesize($this->file_path) : false;
				if($this->size) {
					if($this->size[0] && $this->size[1]) list($w,$h) = $this->size;
					elseif($s) {
						if($this->size[0]) { $w = $this->size[0];$h = $this->size[0]*$s[1]/$s[0]; }
						else { $w = $this->size[1]*$s[0]/$s[1];$h = $this->size[1]; }
					} elseif($this->size[0]) $w = $this->size[0];
				} elseif($this->type=='thumb' || $this->upright>0.0) {
					$w = $this->upright>0.0? round(220*$this->upright*0.075)*10 : 220;
					if($s) $h = $w*$s[1]/$s[0];
				} elseif($s) list($w,$h) = $s;
				if($w!==false) {
					$w = (int)round($w);
					$st .= "width:{$w}px;";
					$sz .= " width=\"{$w}\"";
				}
				if($h!==false) {
					$h = (int)round($h);
					$sz .= " height=\"{$h}\"";
				}

				$ret = "<img src=\"{$this->file_url}\" alt=\"{$this->alt}\"{$sz}>";
				if($this->link) $ret = "<a href=\"{$this->file_page}\">{$ret}</a>";

				if($this->type=='frame' || $this->type=='thumb') {
					$cl = 'thumb';
					$ret = "{$ret}<div>{$title}</div>";
				}
				if($this->align=='left' || $this->align=='right') $cl .= ' thumb-'.$this->align;

				if($cl) $cl = " class=\"{$cl}\"";
				if($st) $st = " style=\"{$st}\"";
				$ret = "<div{$cl}{$st}>{$ret}</div>";

				if($this->align=='center') $ret = "<div class=\"img-center\">{$ret}</div>";
				break;
			case 'doc':
				$cl = '';
				if($this->ext=='pdf') $cl = ' class="doc-pdf"';
				$ret = "<a href=\"{$this->file_url}\"{$cl}>{$title}</a>";
//					if($this->link) $ret .= "<a href=\"{$this->file_page}\" class=\"doc\"></a>";
				break;
			case 'file':
				$ret = "<a href=\"{$this->file_url}\">{$title}</a>";
//					if($this->link) $ret .= "<a href=\"{$this->file_page}\" class=\"file\"></a>";
				break;
		}
		return $ret;
	}

	public function getURL($a=false,$n=true) {
		return 'index.php?m=wiki&p=file'.($n? '&wiki='.$this->link : '');
	}

	public function editURL($a=false,$n=true) {
		return 'index.php?m=wiki&p=file&a=edit'.($n? '&wiki='.$this->link : '');
	}
}

