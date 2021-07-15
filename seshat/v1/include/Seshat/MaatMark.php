<?php
/** Markup-engine
 * 
 * @file include/Seshat/MaatMark.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-05
 * @date Created: 2015-01-31
 */

namespace Seshat;

use Seshat\OsirisLexer;
use Seshat\MaatMark\LinkHandler;
use Seshat\MaatMark\TagHandler;
use Seshat\MaatMark\TemplateHandler;
use Seshat\MaatMark\DokuWikiTable;

define('MM_SECTIONS',      0x00000001); //!< Parse sections (if unset only inline parsing)
define('MM_SMILEYS',       0x00000002); //!< Use smileys
define('MM_STYLES',        0x00000004); //!< Parse font-styles (bold, italic etc.)
define('MM_TAGS',          0x00000008); //!< Parse HTML-tags
define('MM_LINKS',         0x00000010); //!< Parse links
define('MM_PREFORMATTED',  0x00000020); //!< Parse preformatted
define('MM_TEMPLATES',     0x00000040); //!< Use templates
define('MM_QUOTES',        0x00000080); //!< Parse quotes
define('MM_TABLES',        0x00000100); //!< Parse tables
define('MM_NL2BR',         0x00000200); //!< Insert a <br> in paragraphs containing \n


class MaatMark {
	const REF_PATTERN = '\{\^([\w\:\,]+)\}';
	const LIST_PATTERN = '\d+\.|[\*\-\+\;\:]';

	private static $html_tags = 'abbr|b|big|blockquote|br|caption|center|cite|code|dd|del|div|dl|dt|em|h1|h2|h3|h4|h5|h6|hr|i|ins|li|ol|p|pre|rb|rp|rt|s|small|span|strike|strong|sub|sup|table|td|th|tr|tt|u|ul|var';
	private static $html_block_tags = 'abbr|b|big|blockquote|caption|center|cite|code|dd|del|div|dl|dt|em|h[1-6]|i|ins|ol|p|pre|rb|rp|rt|s|small|span|strike|strong|sub|sup|table|td|th|tr|tt|u|ul|var';

	private static $masks = array(
		'all'=>0x0fffffff,
		'inline'=>MM_SMILEYS|MM_STYLES|MM_TAGS|MM_LINKS|MM_PREFORMATTED
	);
	private static $protocols = array('http','https','ftp','ftps','news');
	private static $protocol_refs = null;
/*	private static $smileys = array(':)'=>1,':-)'=>1,':('=>2,':-('=>2,';)'=>3,';-)'=>3,':P'=>4,':-P'=>4,':.'=>5,':O'=>6,':/'=>7,'B)'=>8,'B-)'=>8,
				':|'=>9,':-|'=>9,':*('=>10,':D'=>11,':-D'=>11,':*'=>12,':$'=>13,':-$'=>13,':['=>14,':-['=>14,'X|'=>15,'8)'=>16,'8-)'=>16,'&gt;:)'=>17,':O)'=>18);
	private static $smiley_refs = null;*/
	private static $styles = array(
		1=>'i',2=>'b',4=>'u',8=>'s',16=>'sup',32=>'sub',64=>'big',128=>'small',
		'//'=>1,'**'=>2,'__'=>4,'~~'=>8,'^^'=>16,',,'=>32,'++'=>64,'--'=>128);
	private static $ltgt = array('<'=>'&lt;','>'=>'&gt;');

	private $flags;          //!< Parsing and processing flags
	private $ref;            //!< Array containing all refs
	private $ref_index;      //!< Ref index, to avoid counting the ref-array
	private $data;           //!< Page data, e.g. css, js etc.
	private $style;          //!< Flags for current state style of inline parsing.

	private $link_objects;   //<! Cache of link handler objects
	private $templ_objects;  //<! Cache of template handler objects
	private $link_pages;     //<! Array of unique pages linked to in article

	public function __construct() {
	}

	public function parse($text,$flags=0x0fffffff) {
		if(self::$protocol_refs==null) {
			foreach(self::$protocols as $p)
				self::$protocol_refs[$p.'://'] = '{^url:'.$p.'}';
		}
/*		if(self::$smiley_refs==null) {
			foreach(self::$smileys as $s=>$n)
				self::$smiley_refs[$s] = '{^smiley:'.$n.'}';
		}*/

		$this->flags = isset(self::$masks[$flags])? self::$masks[$flags] : intval($flags);
		$this->ref = array();
		$this->ref_index = 0;
		$this->data = array();
		$this->style = 0;

		$this->link_objects = array();
		$this->templ_objects = array();
		$this->link_pages = array();

		$text = preg_replace('/\R/u',"\n",$text);

		$text = self::stripHTMLComments($text);
		$text = $this->extractRefs($text);

		if($this->flags&MM_PREFORMATTED)
			$text = $this->parsePreformatted($text);

		if($this->flags&MM_TEMPLATES)
			$text = $this->parseTemplates($text);

//		if($this->flags&MM_SMILEYS)
//			$text = strtr($text,self::$smiley_refs);

		if($this->flags&MM_SECTIONS)
			$text = $this->parseSections($text);
		else
			$text = $this->parseInline($text);

		$ret = '';
		if(isset($this->data['style']))
			$ret .= "\n<style type=\"text/css\">\n".implode("\n\n",$this->data['style'])."\n</style>\n";

		$ret .= $this->injectRefs($text);
//		$ret .= $text;

		if(isset($this->data['script']))
			$ret .= "\n<script type=\"text/javascript\">\n".implode("\n\n",$this->data['script'])."\n</script>\n";

		$ret = $this->cleanup($ret);

//echo '<pre>Refs: ';
//var_dump($this->ref);
//echo '</pre>';
//echo '<pre>'.htmlspecialchars($ret).'</pre>';
		return $ret;
	}

	public function getLinkedPages() { return $this->link_pages; }

	public function addLinkedPage($page) {
		if(!isset($this->link_pages[$page]))
			$this->link_pages[$page] = $page;
	}

	public function &getData($id) {
		if(!isset($this->data[$id])) $this->data[$id] = array();
		return $this->data[$id];
	}

	public function addData($id,$value) {
		if(!isset($this->data[$id])) $this->data[$id] = array();
//echo '<pre>addData('.htmlspecialchars($id).')</pre>';
		$this->data[$id][] = $value;
	}

	public static function stripHTML($text) {
		$ret = preg_replace('/<.*?>/','',$text);
		$ret = str_replace('<','&lt;',$ret);
		return $ret;
	}

	public static function stripHTMLComments($text) {
		$ret = preg_replace('/<!--.*?-->/','',$text);
		return $ret;
	}

	public static function cleanup($text) {
		$ret = preg_replace('/(?<=\n)\n+/','',$text);
		return $ret;
	}

	public static function parseParams($str,&$arr) {
		preg_match_all('/(\\w+)\\s*(?:=\\s*("[^"]*"|\'[^\']*\'|\w*))?/',$str,$matches,PREG_SET_ORDER);
		$arr = array();
		foreach($matches as $m) {
			if(!isset($m[2])) $v = true;
			else {
				$v = $m[2];
				$n = strlen($v);
				if(($v[0]=='"' && $v[$n-1]=='"') || ($v[0]=='\'' && $v[$n-1]=='\'')) $v = substr($v,1,$n-2);
			}
			$arr[$m[1]] = $v;
		}
	}

	public function addRef($ref,$tag='ref',$value=false) {
		if(!$ref) return '';
		$this->ref[] = &$ref;
		$id = ++$this->ref_index;
//echo "<pre>Add ref: {$id}, {$tag}\n".htmlspecialchars(print_r($ref,true))."</pre>";
		if($value!==false) $id = $id.','.$value;
		return "{^{$tag}:{$id}}";
	}

	public function addSectionRef($ref,$tag='ref',$value=false) {
		$ret = $this->addRef($ref,$tag,$value);
		return "\n{$ret}\n\n";
	}

	public function extractRef($match) {
		return $this->addRef(htmlspecialchars($match[1]));
	}

	private function extractRefs($text) {
		$ret = preg_replace_callback('/'.self::REF_PATTERN.'/',array($this,'extractRef'),$text);
		// Replace url protocols with refs.
		$ret = strtr($ret,self::$protocol_refs);
		return $ret;
	}

	private function injectRefs($text) {
//echo '<pre>injectRefs('.$text.')</pre>';
		$ret = preg_replace_callback('/'.self::REF_PATTERN.'/',array($this,'injectRef'),$text);
//echo '<pre>Ret: '.$ret.'</pre>';
		return $ret;
	}

	private function injectRef($match) {
		$tag = $match[1];
		$id = '';
		$value = 0;
		if(($p=strpos($tag,':'))!==false) {
			$id = substr($tag,$p+1);
			$tag = substr($tag,0,$p);
			if(($p=strpos($id,','))!==false) {
				$value = substr($id,$p+1);
				$id = substr($id,0,$p);
			}
		}
//echo '<p>injectRef(tag: '.$tag.', id: '.$id.')</p>';
		if($tag=='smiley') return "<s class=\"smiley-{$id}\"></s>"; // Smileys must be declared in the css.
		elseif($tag=='url') return $id.'://';
		elseif($tag=='hr') return '<hr />';
		else {
			$ref = &$this->ref[$id-1];
//echo "<pre>Inject ref: $id, $tag\n".htmlspecialchars(print_r($ref,true))."</pre>";
			if(is_string($ref)) $str = $ref;
			elseif($tag=='link') $str = $ref->getHTML();
			elseif($tag=='tag') $str = $ref->getHTML();
			elseif($tag=='templ') $str = $ref->getHTML();
			else $str = '';
//echo '<p>injectRef(tag: '.$tag.', id: '.$id.', str: '.$str.')</p>';
			if(strpos($str,'{^')!==false) $str = $this->injectRefs($str);
			if($tag=='quote') return "\n<blockquote>{$str}</blockquote>\n";
			elseif($tag=='header') {
				if(strpos($str,'<')!==false) $str = self::stripHTML($str);
				$nm = LinkHandler::encodePageName($str);
				return "<a name=\"{$nm}\" id=\"{$nm}\"></a><h{$value}>{$str}</h{$value}>\n";
			}
			return $str;
		}
		return '';
	}

	public function parsePreformatted($text) {
		$ret = $text;
		if(!$ret) return $ret;
		if($this->flags&MM_SECTIONS)
			$ret = preg_replace_callback('/'.
					'^((?:\> ?)*\'\'\'|```) *(\w*)(\n.*?)^\\1$'. // Block (3)
				'/ms',array($this,'matchPreformatted'),$ret);

		$ret = preg_replace_callback('/'.
				'(\'\'|`)(.*?)\\1'. // Inline (2)
			'/m',array($this,'matchPreformattedInline'),$ret);

//echo "<pre>parsePreformatted:\nBEFORE:\n{$text}\nAFTER:\n{$ret}</pre>";
		return $ret;
	}

	private function matchPreformatted($match) {
		static $block_tags = array('\'\'\''=>'div','```'=>'pre');
		$n = count($match);
		$quote = false;
		$pre = $match[1];
		$lang = $match[2];
		$text = $match[3];
		if($pre[0]=='>') {
			$quote = rtrim($pre,'\'`');
			$pre = ltrim($pre,'> ');
			$text = str_replace("\n{$quote}","\n","\n".$text);
		}
		if($lang) {
			$ret = $this->addRef(OsirisLexer::process($text,$lang),'code');
		} else {
			$tag = $block_tags[$pre];
			if($pre=='```') $text = strtr($text,self::$ltgt);
			elseif($pre=='\'\'\'') $text = str_replace("\n","<br>\n",trim($text));
			$ret = $this->addRef("<{$tag}>{$text}</{$tag}>\n",'pre');
		}
		if($quote) $ret = "{$quote}{$ret}";
		else $ret = "\n{$ret}\n\n";
		return $ret;
	}

	private function matchPreformattedInline($match) {
		static $inline_tags = array('\'\''=>'span','`'=>'code');
		$n = count($match)>4? 4 : 1;
		$pre = $match[$n];
		$text = strtr($match[$n+1],self::$ltgt);
		$tag = $inline_tags[$pre];
		return $this->addRef("<{$tag}>{$text}</{$tag}>",'ipre');
	}

	public function parseTemplates($text) {
		$ret = '';
		if(!$text) return $ret;

//echo '<pre>parseTemplates: '.$text.'</pre>';

		$ret = preg_replace_callback('/'.
				'\{\{((?:.*?(?R)?)*)\}\}'.
			'/ms',array($this,'matchTemplate'),$text);

//echo "<pre>parseTemplates:\nBEFORE:\n{$text}\nAFTER:\n{$ret}</pre>";

		return $ret;
	}

	private function matchTemplate($match) {
		return TemplateHandler::parse($this,$match[1]);
	}

	const TBL  = 1;   // MediaWiki Table (2)
	const TAG  = 1;   // Tag (3)
	const QUOT = 1;   // Quote (1)
	const LI   = 1;   // List (1)

	const H1   = 1;   // Header (2)
	const H2   = 3;   // Header (2)
	const LN   = 5;   // Line (1)

	public function parseSections($text) {
		$ret = $text."\n\n";

		$ret = preg_replace('/'.
				'^\n([\-\*] ?){3,}\n'. // HR
			'/m',"\n{^hr}\n",$ret);

		// If making any changes in this regex, be very careful to update
		// all backreferences in the match, as well as in the regex itself.

		$ret = preg_replace_callback('/'.
				'^(\s*\{\|(.*?(?'.self::TBL.')?)*\n\s*\|\}$)'. // Table (TBL)
			'/ms',array($this,'parseTable'),$ret);

		$ret = preg_replace_callback('/'.
				'^<(\w+)\b(.*?\>(?:.*?(?'.self::TAG.')?)*?<\/\\'.(self::TAG).'>)\n'. // Tag block
			'/ms',array($this,'parseTag'),$ret);

		$ret = preg_replace_callback('/'.
				'^(>.*?\n)+'. // Quote
			'/m',array($this,'parseQuote'),$ret);

		$ret = preg_replace_callback('/'.
				'^(?> *('.self::LIST_PATTERN.')) (?:.*?(?=\z|\n\n(?! |'.self::LIST_PATTERN.' )))'. // List (LI)
			'/ms',array($this,'parseList'),$ret);

		$ret = preg_replace_callback('/'.
				'^([#=]{1,6})[ \t]*(.+?)[ \t]*\\'.self::H1.'?\n|'. // Header (H1)
				'^(?!#)(.+?)\n(=+|-+)\n|'. // Header (H2)
				'^(.*?)\n'. // Line (LN)
			'/m',array($this,'parseSection'),$ret);

//		return '<pre>'.$ret.'</pre>';
		return $ret;

/*		// If making any changes in this regex, be very careful to update
		// all backreferences in the match, as well as in the regex itself.
		$ret = preg_replace_callback('/'.
				'^(?s)(<(\w+)\b(.*?\>(?:.*?(?'.self::TAG.')?)*?<\/\\'.(self::TAG+1).'>))(?-s)\n|'. // Tag block
//				'^(\'\'\'|```) *(\w*)(?s)(\n.*?)(?-s)^\\'.self::PRE.'\n|'. // Preformatted
				'^(>.*?\n)+|'. // Quote
				'^\n([\-\*] ?){3,}\n|'. // HR
				'^(?> *('.self::LIST_PATTERN.')) (?s:.*?(?=\z|\n\n(?! |'.self::LIST_PATTERN.' )))|'. // List (LI)
				'^([#=]{1,6})[ \t]*(.+?)[ \t]*\\'.self::H1.'?\n|'. // Header (H1)
				'^(?!#)(.+?)\n(=+|-+)\n|'. // Header (H2)
				'^((?s:(?>[\^\|]).*?)+(?<=[\^\|])\n)+|'. // Table 1 (T1)
				'^(\s*\{\|(?s)(.*?(?'.self::T2.')?)*\n\s*\|\}$)(?-s)|'. // Table 2 (T2)
				'^(.*?)\n'. // Line (LN)
			'/m',array($this,'parseSection'),$text."\n\n");
//		return '<pre>'.$ret.'</pre>';
		return $ret;*/
	}

	public function parseTable($match) {
		$text = $match[0];
		preg_match_all('/'.
				'\A\s*\{\|(.*)(\n\s*\|\+.*)?|'. // Table start & caption (2)
				'^\s*(\|\})\s*\z|'. // End of table (1)
				'(^\s*\|\-.*\n)?^\s*([\|\!](?>.*?\|)?(?!\|))(\n(?s)(?:.*?(\{\|(?:.*(?7)?)*?\n\s*\|\})?.*?(?=\n\s*[\|\!]))(?-s)|.*)'.
			'/m',$text,$matches,PREG_SET_ORDER);
//echo "<pre>Table: \n".$text."\n".htmlspecialchars(print_r($matches,true));
		$table = '';
		$tr = 0;
		foreach($matches as $m) {
			$n = count($m);
			if($n<=3) {
				if(!$m[1]) $style = '';
				else {
					$t = $m[1].'|';
					$this->parseTable2Style($t,$style);
				}
				$table .= "<table{$style}>\n";
				if(isset($m[2]) && $m[2]) {
					$caption = trim(substr(trim($m[2]),2));
					$this->parseTable2Style($caption,$style);
					$caption = $this->parseInline($caption);
					$table .= "<caption{$style}>{$caption}</caption>\n";
				}
			} elseif($n>4) {
				$row = trim($m[4]);
				if($row || $tr==0) {
					if(!$row) $style = '';
					else if(strlen($row)>2) {
						$row = trim(substr(2)).'|';
						$this->parseTable2Style($row,$style);
					}
					if($tr>0) $table .= "</tr>\n";
					$table .= "<tr{$style}>\n";
					++$tr;
				}
				$s = trim($m[5]);
				$t = $s[0]=='!'? 'th' : 'td';
				$s = substr($s,1);
				$cell = $m[6];
				if($cell && $cell[0]=="\n") $cells = array($s.$cell);
				else $cells = explode($t=='th'? '!!' : '||',$s.$cell);
				foreach($cells as $cell) {
					$this->parseTable2Style($cell,$style);
					if($cell && $cell[0]=="\n") $cell = "\n".trim($this->parseSections($cell))."\n";
					else $cell = trim($this->parseInline($cell));
					$table .= "<{$t}{$style}>{$cell}</{$t}>\n";
				}
			} else {
				if($tr>0) $table .= "</tr>\n";
				$table .= "</table>\n";
			}
		}

//echo strtr($table,array('<'=>'&lt;','>'=>'&gt;')).'</pre>';

		return $this->addSectionRef($table,'table');
	}

	public function parseTable2Style(&$text,&$style) {
		if(($n=strpos($text,'|'))===false ||
			(($n1=strpos($text,"\n"))!==false && $n>$n1)) $style = '';
		else {
			$style = ' '.trim(substr($text,0,$n));
			$text = substr($text,$n+1);
		}
	}

	public function parseTag($match) {
		$text = $match[0];
		$tag = $match[1];
		if(strpos(self::$html_block_tags,$tag)!==false)
			return $this->addSectionRef($text);
		$content = $match[2];
		$n1 = strpos($content,'>');
		$n2 = strrpos($content,'<');
		$params = substr($content,0,$n1);
		$content = substr($content,$n1+1,$n2-$n1-1);
//echo "<p>parseTag(".htmlspecialchars($tag).',\''.htmlspecialchars($params).'\',\''.htmlspecialchars($content)."')</p>";
		return $this->addSectionRef(TagHandler::parse($this,$tag,$params,$content),'tag');
	}

	public function parseQuote($match) {
		$text = $match[0];
		$ret = preg_replace('/^\> ?/m','',$text);
//echo "<p>parseQuote1(".htmlspecialchars($ret).")</p>";
//		$ret = $this->parsePreformatted($ret);
		$ret = $this->parseSections($ret);
//echo "<p>parseQuote2(".htmlspecialchars($ret).")</p>";
		return $this->addSectionRef($ret,'quote');
	}

	public function parseList($match) {
		$text = $match[0];
		preg_match_all('/'.
				'^(?>( *)('.self::LIST_PATTERN.')) +(.*?(?=\z|\n *(?:'.self::LIST_PATTERN.')))'. // List (LI)
			'/ms',$text,$matches,PREG_SET_ORDER);
		$ret = '';
		for($index=0,$len=count($matches); $index<$len; ++$index)
			$ret .= $this->parseListItems($matches,$index,$len,0,'');
//echo "<pre>List: \n{$text}\n"/*.htmlspecialchars(print_r($matches,true))."\n"*/."HTML:\n".strtr($ret,array('<'=>'&lt;','>'=>'&gt;'))."</pre>\n";
		return $this->addSectionRef($ret,'list');
	}

	public function parseListItems($matches,&$index,$len,$level,$ind) {
		static $list_types = array('*'=>'ul','+'=>'ul','-'=>'ul','#'=>'ol',';'=>'dl',':'=>'dl');
		static $list_items = array('*'=>'li','+'=>'li','-'=>'li','#'=>'li',';'=>'dt',':'=>'dd');
		$items = '';
		$list = false;
		$change = false;
		$close = false;
		$closed = false;
		for($start=$index; $index<$len; ++$index) {
			$m = $matches[$index];
			$n = (strlen($m[1])>>1)+1;
			if($n>$level && !$list) $level = $n;
			elseif($n<$level) {
				if($index>$start) --$index;
				break;
			}
			$num = false;
			$t = $m[2];
			$item = trim($m[3]);
			if(!isset($list_types[$t])) {
				$num = rtrim($t,'.');
				$t = '#';
			}
			$lt = $list_types[$t];
			if(!$list) {
				$list = $lt;
				$li = $list_items[$t];
			} else if($lt!=$list && $n==$level) {
				$change = true;
				break;
			}
			if($n>$level) {
				$ret = $this->parseListItems($matches,$index,$len,$n,"{$ind}\t");
				$items .= "\n{$ret}";//.(" 1 [{$level}]{$list}, {$index}/{$len}");
				$close = "{$ind}\t</{$li}>\n";
				continue;
			}
			if($close) {
				$items .= $close;
				$close = false;
			}
			$li = $list_items[$t];
			if(strpos($item,"\n")!==false) {
				$item = trim($this->parseSections(preg_replace('/^ +/m','',$item)));
				$items .= "{$ind}\t<{$li}>\n{$item}";//.(" 2 [{$level}]{$list}, {$index}/{$len}");
				$close = "\n{$ind}\t</{$li}>\n";
			} else {
				$item = $this->parseInline($item);
				$items .= "{$ind}\t<{$li}>{$item}";//.(" 3 [{$level}]{$list}, {$index}/{$len}");
				$close = "</{$li}>\n";
			}
		}
		if($close) $items .= $close;
		$ret = '';
		if($list && count($items))
			$ret .= "{$ind}<{$list}>\n{$items}{$ind}</{$list}>\n";
		if($change && $index<$len)
			$ret .= $this->parseListItems($matches,$index,$len,$level,$ind);
		return $ret;
	}

	public function parseSection($match) {
		static $p = '';

//echo "<p>parseSection(".htmlspecialchars($match[0]).")</p>";

		$ret = '';
		$s = $match[0];
		$n = count($match);
//echo '<pre>P1: '.$p.' (s: "'.$s.'", n: '.$n.')</pre>';
		if($s=="\n" || $n<=self::LN) {
			if($p) {
				if(preg_match('/\A(\s*'.self::REF_PATTERN.'\s*)\z/',$p)===1) $ret .= $p;
				else $ret .= $this->parseInline(trim($p),true);
				$p = '';
			}
			if($s=="\n") {
//echo '<pre>P: '.$ret.'</pre>';
				return $ret.$s;
			}
		}
		if($n>self::LN && $match[self::LN]) // Line
			$p .= $s;
		elseif($n>self::H2+1 && $match[self::H2] && $match[self::H2+1]) // Header
			$ret .= $this->parseHeader($match[self::H2+1],$match[self::H2]);
		elseif($n>self::H1+1 && $match[self::H1] && $match[self::H1+1]) // Header
			$ret .= $this->parseHeader(strlen($match[self::H1]),$match[self::H1+1]);
		elseif($n>self::HR && $match[self::HR]) // HR
			$ret .= "<hr>\n";
		//else $ret .= $this->addRef($match[0]); // HTML
		return $ret;
	}

	public function parseHeader($h,$text) {
		if(is_string($h)) $h = $h[0]=='='? 1 : ($h[0]=='-'? 2 : 6);

//echo "<p>parseHeader(".htmlspecialchars($text).")</p>";

		return $this->addSectionRef($this->parseInline($text),'header',$h);
	}

//	const I_PRE   =  1; // Preformatted (2)
	const I_STYLE =  1; // Style (1)
	const I_TAG   =  2; // Tag (4)
	const I_SPACE =  6; // Space (1)
	const I_LINK  =  7; // Link (1)
//	const I_TMPL  = 10; // Template (2)
	const I_BR    =  8; // Break (1)

	public function parseInline($text,$p_wrap=false) {
		$ret = '';
		if(!$text) return $ret;

		$this->style = 0;

//echo '<pre>parseInline: '.$text.'</pre>';

		$ret = preg_replace_callback('/'.
//				'(\'\'|`)(.*?)\\'.self::I_PRE.'|'. // Preformatted
				'([\/\*_~\^,\+\-]{2})|'. // Style
				'(?s)(<(\w+)\b(.*?)(\/>|>(?:.*?(?'.self::I_TAG.')?)*<\/\\'.(self::I_TAG+1).'>))(?-s)|'. // Tag
				'([ \t]{2,})|'. // Space
				'(\[(?:.*?(?'.self::I_LINK.')?)*\]|\{\^url\:\w+\}[^\s]+)|'. // Link
//				'(\{\{((?:.*?(?'.self::I_TMPL.')?)*)\}\})|'. // Template
				'(  \n|\\{1,3}\n|\\\\)'. // Break
			'/m',array($this,'matchInline'),$text);

		if($this->style)
			for($i=1; $i<16; $i<<=1)
				if($this->style&$i) $ret .= '</'.self::$styles[$i].'>';

		if($ret && $p_wrap) return "\n<p>".$ret."</p>\n";
		return $ret;
	}

	public function matchInline($match) {
		$ret = '';
		$n = count($match);

//echo '<pre>'.htmlspecialchars(print_r($match,true)).'</pre>';

		if($n>self::I_BR && $match[self::I_BR]) // Break
			$ret .= $this->matchBreak($match[self::I_BR]);
//		elseif($n>self::I_TMPL+1 && $match[self::I_TMPL]) // Space
//			$ret .= $this->matchTemplate($match[self::I_TMPL+1]);
		elseif($n>self::I_LINK && $match[self::I_LINK]) // Space
			$ret .= $this->matchLink($match[self::I_LINK]);
		elseif($n>self::I_SPACE && $match[self::I_SPACE]) // Space
			$ret .= ' ';
		elseif($n>self::I_TAG+3 && $match[self::I_TAG+1]) // Tag
			$ret .= $this->matchTag($match[self::I_TAG+1],$match[self::I_TAG+2],$match[self::I_TAG+3]); //'Tag['.$match[self::I_TAG+1].': '.htmlentities($match[0]).']';
		elseif($n>self::I_STYLE && $match[self::I_STYLE]) // Style
			$ret .= $this->matchStyle($match[self::I_STYLE]);
//		elseif($n>self::I_PRE+1 && $match[self::I_PRE]) // Preformatted
//			$ret .= $this->matchPre($match[self::I_PRE],$match[self::I_PRE+1]);
		return $ret;
	}

	private function matchStyle($style) {
		if(!isset(self::$styles[$style])) return '(?&gt;)'.$style.'(&lt;?)';
		$i = self::$styles[$style];
		$tag = self::$styles[$i];
		if($tag=='br') return "<br>\n";
		if($this->style&$i) {
			$this->style &= ~$i;
			return '</'.$tag.'>';
		}
		$this->style |= $i;
		return '<'.$tag.'>';
	}

	private function matchTag($tag,$params,$content) {
		$single = $content=='/>';
		if($content=='/>') $content = false;
		else $content = substr($content,1,strrpos($content,'<')-1);
//echo "<p>matchTag(".htmlspecialchars($tag).',\''.htmlspecialchars($params).'\',\''.htmlspecialchars($content)."')</p>";
		if(strpos(self::$html_tags,$tag)!==false) {
			return $content===false? "<{$tag}{$params}/>" : "<{$tag}{$params}>".$this->parseInline($content)."</{$tag}>";
		} else {
			return $this->addRef(TagHandler::parse($this,$tag,$params,$content),'tag');
		}
	}

	private function matchLink($link) {
//echo "<p>matchLink({$link})</p>";
		$l = strlen($link);
		if($link[0]=='[' && $link[$l-1]==']') {
			if($link[1]=='[' && $link[$l-2]==']') {
				$link = LinkHandler::parse($this,substr($link,2,-2));
				if(!isset($this->link_objects[$link->page])) {
					$this->link_objects[$link->page] = array();
					$this->addLinkedPage($link->page);
				}
				$this->link_objects[$link->page][] = &$link;
				return $this->addRef($link,'link');
			} elseif(strpos($link,'{^url:')===1) {
				$url = substr($link,1,-1);
				$label = $url;
				if(($n=strpos($link,' '))!==false) {
					$label = trim(substr($url,$n));
					$url = substr($url,0,$n);
					if($label) $label = $this->parseInline($label);
				}
				return '<a href="'.$url.'" target="_blank">'.$label.'</a>';
			} else return $link;
		} else return '<a href="'.$link.'" target="_blank">'.$link.'</a>';
	}

	private function matchBreak($br) {
//echo "<p>matchBreak($br)</p>";
		if($br=="\n" && !($this->flags&MM_NL2BR)) return "\n";
		elseif($br=="\\\n") return "\n";
		elseif($br=="\\\\\\\n") return "<br>\n<br>\n";
		return "<br>\n";
	}
}

