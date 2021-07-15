<?php
/** Thoth Template Engine
 * 
 * PHP is a template engine in itself, there isn't really any purpose in replacing
 * PHP-template syntax with an alternative template language to just reformat into
 * clean PHP syntax. Instead ThothTemplate replace static template tags in a manner similar
 * to the C preprocessor, and insert text, variables etc.
 * 
 * For each template script, which is a PHP-file with template tags, can be added
 * language scripts and configuration scripts; if the template is named "index.php",
 * then the language file for English is named "index.en.php" and should return an
 * array containing string data; likewise the configuration file is named
 * "index.conf.php" and contains an array containing cofiguration data. Each
 * language may have a global file named ".en.php"  for English, and there may be
 * a global configurations file named ".conf.php"; keys in global data are over
 * written by template specific data, e.g. if "key"=>"value1" in ".en.php" and
 * "key"=>"value2" in "index.en.php", then "key" is set to "value2". Neither
 * configurations nor language files are required for templates, and language
 * files defaults to English if missing for other languages.
 * 
 * Templates use a syntax marked with {* [tag] [arg1[|arg2[|arg3[|...]]] } tags,
 * and there are two ways to write tags; one is to write commands; the other is
 * to use shorthand symbols. There can be any amount of white space in between the tag
 * brackets the tag, though the values in the arguments list are parsed intact and
 * white space is used verbatim.
 * 
 * Arguments may contain any amount of nested tags, and also PHP-code
 * 
 * Text tags: {* * name } or {* text name } replaces the tag with the text string
 * "name", listed in the languange script. Any number of optional arguments may be
 * added for the format of the string; which may be:
 *  - markdown/markup: Use the MaatMark class to format the string into HTML
 *  - date: The value is formatted into a date string, and is expected to be a timestamp
 *  - time: The value is formatted into a time string, and is expected to be a timestamp
 *  - html: Format value with PHP function "htmlentities()", e.g. "&" is replaced by "&amp;"
 *  - url:  Format value with PHP function "rawurlencode()", e.g. "&" is replaced by "%26"
 *  - regex: Format string to be used as a regex string, e.g. in a regex match
 *  - base64: Base 64 encode string
 *  - string/escape: Adds backslashes so the string may be used inside a string
 *  - unescape: Unescape string, e.g. "\n" is replaced with a new line char.
 * 
 * Configuration tags: {* ! name } or {* conf name } replaces the tag with
 * configuration data. Same arguments may be used as for text tags to format string.
 * 
 * Get variable tags: {*~ name} or {* get name } replaces the tag with a variable
 * set in the template. Same arguments may be used as for text tags to format string.
 * 
 * Set variable tags: {* : name|value } or {* set name|value } sets the template
 * variable "name" to "value". If more than one argument is given, variable is
 * stored as an array containing the arguments instead of a string.
 * 
 * Tips:
 * 
 * It's not possible to enter a pipe-sign '|' into an argument since it's the argument
 * separator; however, if one is needed in the resulting value, a nested tag may be used,
 * e.g. {*:var|value{*!pipe}value} where "pipe" is set to "|" in the config file, will
 * render "var" to "value|value"; likewise setting "or" to "||"  and "lcbr" to "{" it
 * can be used in expressions: {*:ifexpr|<?php if($a {!or} $b) {!lcbr} } will set
 * "ifexpr" to "<?php if($a || $b) {".
 * 
 * @file include/Seshat/ThothTemplate.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-21
 * @date Created: 2015-01-31
 */

namespace Seshat;

class ThothTemplate {
	/** Sepcial variables that are set internally and can be used in tags
	 * 
	 * Behaviour of special variables may seem strange in nested tags, since they
	 * have no local span and so are set globaly. Once set they retain the same
	 * value until set again.
	 * 
	 * Numerical special variables, e.g. '0'-'9', contains regex matches, and
	 * are set by functions such as 'grep' */
	private static $special = array(
		'0'=>'','1'=>'','2'=>'','3'=>'','4'=>'','5'=>'','6'=>'','7'=>'','8'=>'','9'=>'',
		'!'=>'',           //!< The entire template-tag of the current match, e.g. "{*~tag}"
		'_'=>'',           //!< The returned value of the tag
		'~'=>'',           //!< The "get" key
		':'=>'',           //!< The "set" key
		'+'=>'',           //!< The "append" key
		'&'=>'',           //!< The function name
		'<'=>'',           //!< The include file name
		'#'=>'',           //!< Index key in iterations, set by function call 'iterate' etc.
		'='=>'',           //!< Value in iterations, set by function call 'iterate' etc.
		'PAGE'=>'',        //!< 
		'LANGUAGE'=>'',    //!< 
		'THEME'=>'',       //!< 
		'TEMPLATE'=>'',    //!< 
		'PATH'=>'',        //!< 
		'TIME'=>'',        //!< 
		'MISSING'=>'',     //!< 
		'LOG'=>''          //!< 
	);
	private static $functions = array(); //!< Registry of loaded functions that can be called in tags
	private static $log = '';            //!< Log data

	private $vars;                       //!< Registry of variables that can be used in tags
	private $missing;                    //!< List of variables that have been used in tags but are missing in the vars-data

	public static function log($text) { self::$log .= $text."\n"; }
	public static function addFunction($name,$func) { self::$functions[$name] = $func; }
	public static function isSpecialVariable($var) { return isset(self::$special[$var]); }

	private static function cleanup($php) {
		return preg_replace('/\s*\?>\s*<\?php\s*/m',"\n\n",$php);
	}

	private static function getHeader() {
		return "<?php\n\nuse Seshat\Ra;\n\n?>";
	}

	/** Constructor; initiate parameters */
	public function __construct() {
	}

	public function setVar($var,$value) { $this->vars[$var] = $value; }
	public function getVar($var) { return isset($this->vars[$var])? $this->vars[$var] : ''; }
	public function &getVars() { return $this->vars; }
	public function issetVar($var) { return isset($this->vars[$var]); }
	public function unsetVar($var) { unset($this->vars[$var]); }

	private function init($template,$path,&$vars) {
		self::$special['PAGE'] = Ra::getPage();
		self::$special['LANGUAGE'] = Ra::getLanguage();
		self::$special['THEME'] = Ra::getTheme();
		self::$special['TEMPLATE'] = $template;
		self::$special['PATH'] = $path;
		self::$special['TIME'] = date('Y-m-d H:i:s');
		self::$special['MISSING'] = &$this->missing;
		self::$special['LOG'] = &self::$log;
		self::$log = '';
		$this->vars = &$vars;
		$this->missing = array();
	}

	public function export($template,&$vars) {
		$path = DIR_TEMPLATE.'page/'.$template.'.php';
		$file = file_get_contents($path);
		$this->init($template,$path,$vars);
		if(file_exists($f=DIR_TEMPLATE.'page-conf.php')) $this->vars = array_merge($this->vars,($arr = require $f));
		if(file_exists($f=DIR_TEMPLATE.'page/'.$template.'-conf.php')) $this->vars = array_merge($this->vars,($arr = require $f));
		if(file_exists($f=DIR_TEMPLATE.'page-'.Ra::getLanguage().'.php') ||
			file_exists($f=DIR_TEMPLATE.'page-en.php')) $this->vars = array_merge($this->vars,($arr = require $f));
		if(file_exists($f=DIR_TEMPLATE.'page/'.$template.'-'.Ra::getLanguage().'.php') ||
			file_exists($f=DIR_TEMPLATE.'page/'.$template.'-en.php')) $this->vars = array_merge($this->vars,($arr = require $f));
		if(file_exists($f=DIR_TEMPLATE.'page/'.$template.'.css')) $this->vars['__css-templates__'][] = 'page/'.$template.'.css';
		if(file_exists($f=DIR_TEMPLATE.'page/'.$template.'.js')) $this->vars['__js-templates__'][] = 'page/'.$template.'.js';
		foreach(array('css-files','css-templates','js-files','js-templates') as $v)
			if(isset($this->vars[$v])) $this->vars['__'.$v.'__'] = array_merge($this->vars['__'.$v.'__'],$this->vars[$v]);
		foreach(self::$special as $k=>&$v) $this->vars[$k] = &$v;
		return self::cleanup(self::getHeader().$this->parse($file));
	}

	public function process($text,&$vars) {
		$this->init('text','.',$vars);
		foreach(self::$special as $k=>&$v) $this->vars[$k] = &$v;
		return self::cleanup($this->parse($text));
	}

	public function parse($template) {
		if(strpos($template,'{*')===false) return $template;
		return preg_replace_callback('/(?:'.
			'\{\*\s*'.
				'([\w\-\_]+|\~|\:|\&|<|#|\/\/|\/\*)\s*'.
				'((?:[^\{\}]*?(\{(?:(?>[^\{\}]+)|(?3))+\})*)*)'.
			'\s*+\}'.
		')\n?/m',array($this,'replace'),$template);
//		if(preg_last_error()) trigger_error('preg: '.preg_last_error());
	}

	private function replace($match) {
		self::$special['!'] = $match[0];
		$tag = $match[1];
		$param = $match[2];
//self::log("Tag[{$tag}]: {$param}");
//echo '<pre>replace: tag['.$tag.'] param['.$param."]</pre>\n";
		switch($tag) {
			case '~':
			case 'get':return $this->get($param);

			case ':':
			case 'set':return $this->set($param);

			case '+':
			case 'append':return $this->append($param);

			case '&':
			case 'call':return $this->call($param);

			case '<':
			case 'include':return $this->incl($param);

			case '#':
			case '//':
			case '/*':return '';

			default:return $this->call($tag.'|'.$param);
		}
	}

	private function parseTag($tag,$recursive=true) {
		if(strpos($tag,'{*')!==false) {
			preg_match_all('/(?<=\A|\|)(?:[^\|\{]*?(\{((?>[^\{\}]+)|(?1))*\})*)+(?=\z|\|)/m',$tag,$m);
//			if(preg_last_error()) trigger_error('preg: '.preg_last_error());
			$m = array_shift($m);
		}
		elseif(strpos($tag,'|')!==false) $m = explode('|',$tag);
		else $m = false;
//echo '<pre>tag: v['.(is_array($v)? implode(',',$v) : $v).'] m[';
//var_dump($m);
//echo "]</pre>\n";
		if($recursive && is_array($m))
			foreach($m as &$p) {
//echo '<pre>p: ';
//var_dump($p);
//echo "</pre>\n";
				if(strpos($p,'{*')!==false && $recursive) $p = $this->parse($p);
			}
		if($m) $v = array_shift($m);
		else $v = $tag;
		$v = trim($v);
		return array($v,$m);
	}

	private function formatValue(&$value,$format) {
		static $formats = array('template'=>5,'templ'=>5,'thoth'=>5,
		                        'markup'=>10,'markdown'=>10,'mu'=>10,'md'=>10,'maat'=>10,
		                        'date'=>15,'time'=>16,'html'=>20,'url'=>21,'regex'=>25,'base64'=>30,
		                        'split'=>34,'join'=>35,'json'=>36,'string'=>40,'escape'=>40,'unesacpe'=>41,'trim'=>45);
		$n = '';
		if(!is_array($format)) $format = array($format);
//self::log("FormatValue: ".implode(';',$format));
		foreach($format as $f) {
			$i = trim($f);
			if(!isset($formats[$i])) $n = $f;
			else {
				switch($formats[$i]) {
					case  5:
						if(strpos($value,'{*')!==false) $value = $this->parse($value);
						break;
					case 10:
						$maat = new MaatMark();
						$value = $maat->parse($value,$n);
						break;
//					case 15:$value = Ra::formatDate($value);break;
//					case 16:$value = Ra::formatTime($value);break;
					case 20:$value = htmlentities($value,ENT_QUOTES,'UTF-8');break;
					case 21:$value = rawurlencode($value);break;
					case 25:$value = addcslashes($value,'\'"|*.+?[]()^$/\\');break;
					case 30:$value = base64_encode($value);break;
					case 34:if(is_string($value) && $n) $value = explode($n,$value);break;
					case 35:if(is_array($value)) $value = implode($n,$value);break;
					case 36:
						if(is_string($value)) $value = json_decode($value,true);
						else $value = json_encode($value);
						break;
					case 40:$value = addcslashes($value,"\\\'\"\r\n\t");break;
					case 41:$value = stripcslashes($value);break;
					case 45:$value = trim($value);break;
				}
			}
//self::log("FormatValue[{$f}]: {$value}");
		}
	}

	private function get($tag,$default='') {
		list($key,$value) = $this->parseTag($tag);
//self::log("Get[{$key}]: ".(is_array($value)? implode(', ',$value) : $value));
		self::$special['~'] = $key;
		if(!isset($this->vars[$key])) {
			$this->missing[] = $key;
			return $default;
		}
		$get = $this->vars[$key];
		if($get && $value) $this->formatValue($get,$value);
		if(!$get) $get = '';
		self::$special['_'] = $get;
		return $get;
	}

	private function set($tag) {
		list($key,$value) = $this->parseTag($tag);
		self::$special[':'] = $key;
		if(!$value) $this->vars[$key] = '';
		else {
			$set = array_shift($value);
			if($set && $value) $this->formatValue($set,$value);
			$this->vars[$key] = $set;
			self::$special['_'] = $set;
		}
	}

	private function append($tag) {
		list($key,$value) = $this->parseTag($tag);
		self::$special['+'] = $key;
		if(!$value) $this->vars[$key] = '';
		else {
			$append = array_shift($value);
			if($append && $value) $this->formatValue($append,$value);
			if(isset($this->vars[$key])) {
				$var = $this->vars[$key];
				if(is_string($var) && is_string($append)) $append = $var.$append;
				elseif(is_numeric($var) && is_numeric($append)) $append = $var+$append;
				elseif(is_array($var) || is_array($append)) {
					if(!is_array($var)) $var = array($var);
					if(!is_array($append)) $append = array($append);
					$append = array_merge($var,$append);
				}
			}
			$this->vars[$key] = $append;
			self::$special['_'] = $append;
		}
	}

	private function call($tag) {
		list($key,$value) = $this->parseTag($tag,false);
		self::$special['&'] = $key;
		$key = strtr(strtolower($key),'-','_');
//self::log("Call[{$key}]: {$value[0]}");
		$func = false;
		if(preg_match('/^[\w\_]+$/',$key)) {
			if(isset(self::$functions[$key])) $func = &self::$functions[$key];
			else {
				$script = DIR_INCLUDE.'Seshat/ThothTemplate/'.$key.'.php';
				if(file_exists($script)) $func = require_once $script;
				self::$functions[$key] = &$func;
			}
		}
		$ret = '';
		if(is_callable($func)) {
//var_dump($func);
			$ret = $func($this,$value);
		}
		self::$special['_'] = $ret;	
		return $ret;
	}

	private function incl($tag) {
		list($key,$value) = $this->parseTag($tag);
		self::$special['<'] = $key;
		$key = strtr($key,'\\','/');
		if($key[0]=='/' || strpos($key,'..')!==false) return '';
		$file = DIR_TEMPLATE.$key;
//echo '<pre>incl: file['.$file."]</pre>\n";
		if(!file_exists($file)) return '[include NOT exists: '.$file.']';//return '';
		$incl = $this->parse(file_get_contents($file));
		if($incl && $value) $this->formatValue($incl,$value);
		self::$special['_'] = $incl;
		return $incl;
	}
}


