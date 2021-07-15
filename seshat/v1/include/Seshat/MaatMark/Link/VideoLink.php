<?php

namespace Seshat\MaatMark\Link;

use Seshat\MaatMark;
use Seshat\MaatMark\LinkHandler;

class VideoLink extends LinkHandler {
	/** File types associated with file extensions */
	public static $video_hosts = array('youtube','google');

	public $align;			//<! Left, right or center
	public $aspect;		//<! Aspect ratio
	public $size;			//<! Size of image

	function __construct(&$markup,$title,$namespace,$name,$parenthesis,$section,$text) {
		parent::__construct($markup,$title,$namespace,$name,$parenthesis,$section,$text);
		global $sc;
		if($this->title===false) $this->title = $name;
		$this->host = '';
		$this->align = false;
		$this->aspect_ratio = array(16,9);
		$this->size = false;
		if($this->title) {
			$params = $this->title;
			while(true) {
				if(strpos($params,'|')!==false) list($p,$params) = explode('|',$params,2);
				else list($p,$params) = array($params,'');
				if(in_array($p,self::$video_hosts)) $this->host = $p;
				elseif($p=='left') $this->align = 'left';
				elseif($p=='center') $this->align = 'center';
				elseif($p=='right') $this->align = 'right';
				elseif(strpos($p,'px')!==false && preg_match('/(\d+)x?(\d*)px/',$p,$m)) $this->size = array((int)$m[1],(int)$m[2]);
				elseif(strpos($p,':')!==false && preg_match('/(\d+):(\d+)/',$p,$m)) $this->aspect_ratio = array((int)$m[1],(int)$m[2]);
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
		$st = '';
		$w = false;
		$h = false;

//		$title = $this->title;
//		if($this->maat && $title)
//			$title = $this->maat->parseInline($title);

		if($this->size) {
			if($this->size[0] && $this->size[1]) list($w,$h) = $this->size;
			elseif($this->size[0]) $w = $this->size[0];
		} else $w = 320;
		if($h===false) $h = (int)round(($w*$this->aspect_ratio[1])/$this->aspect_ratio[0]);

		if($this->align=='left' || $this->align=='right') $st .= 'float:'.$this->align;
		if($st) $st = ' style="'.$st.'"';

		switch($this->host) {
			case 'youtube':
//				$ret = '<iframe type="text/html" width="'.$w.'" height="'.$h.'" src="http://www.youtube.com/embed/'.$this->name.'?version=3&&rel=1&fs=1&showsearch=0&showinfo=1&iv_load_policy=1&wmode=transparent" frameborder="0"></iframe>';
				$ret = '<embed width="'.$w.'" height="'.$h.'"'.$st.' src="http://www.youtube.com/v/'.$this->name.'" type="application/x-shockwave-flash"></embed>';
				break;
			case 'google':
				break;
		}

		if($this->align=='center') $ret = '<div class="sc_img_center">'.$ret.'</div>';
		return $ret;
	}
}

