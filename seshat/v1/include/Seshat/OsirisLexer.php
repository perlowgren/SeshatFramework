<?php
/** Genric Syntax-Highlight-processor
 * 
 * @file include/Seshat/OsirisLexer.php  
 * @author Per Löwgren
 * @date Modified: 2015-04-01
 * @date Created: 2015-01-31
 */

namespace Seshat;

class OsirisLexer {
	const C_MULTILINE_COMMENT	= '\/\*(?s).*?(?-s)\*\/';
	const C_INLINE_COMMENT		= '\/\/.*';
	const HASH_COMMENT			= '\#.*?\n';
	const C_STRING					= '(?<!\\\\)\".*?(?<!\\\\)\"';
	const C_CHAR					= '(?<!\\\\)\'.*?(?<!\\\\)\'';
	const NUMBER					= '(?<!\w)(?i)[+-]?((?:\d*\.\d+|\d+\.\d*)(?:e[+-]?\d+)?[fl]?|(?:0x[0-9a-f]+|\d+)(?:u?l|l?u)?)(?-i)(?!\w)';
	const FUNC						= '(?<!\w|[\$\%\@])(\w+)(?=\()';
	const DEFINE					= '(?<!\w|[\$\%\@])([A-Z_0-9]{2,})(?!\w)';
	const PL_IDENTIFIER			= '(?<!\w)([\$\%\@]?)(\w+)';

	protected static $keywords = array(
		'and','or','xor','for','do','while','foreach','as','return','die','exit','if','then','else',
		'elseif','new','delete','try','throw','catch','finally','class','function','string',
		'array','object','resource','var','bool','boolean','int','integer','float','double',
		'real','string','array','global','const','static','public','private','protected',
		'published','extends','switch','true','false','null','void','this','self','struct',
		'char','signed','unsigned','short','long'
	);

	const C		= 1;
	const STR	= 2;
	const NR		= 3;
	const FN		= 4;
	const DEF	= 5;
	const ID		= 6;

	private static function replace($match) {
//echo '<pre>';
//var_dump($match);
//echo '</pre>';
		$s = $match[0];
		$n = count($match);
		$class = false;
		if($n>self::ID+1 && $match[self::ID+1]) {
			$s1 = $match[self::ID];
			$s2 = $match[self::ID+1];
			if($s1) return '<span class="op">'.$s1.'</span><span class="var">'.$s2.'</span>'; // Variable
			elseif(in_array($s2,self::$keywords)) $class = 'kw'; // Keyword
			else $class = 'id';
		}
		elseif($n>self::DEF && $match[self::DEF]) $class = 'def'; // Define
		elseif($n>self::FN && $match[self::FN]) $class = 'func'; // Function
		elseif($n>self::NR && $match[self::NR]) $class = 'nr'; // Number
		elseif($n>self::STR && $match[self::STR]) $class = 'str'; // String
		elseif($n>self::C && $match[self::C]) $class = 'c'; // Comment
		else return $s;
//echo 'Class: '.$class.'<br /><br />';
		return '<span class="'.$class.'">'.$s.'</span>';
	}

	public static function process($code,$lang='generic') {
		$s = preg_replace_callback(
			/*$r=*/'/'.
				'('.self::C_MULTILINE_COMMENT.'|'.self::C_INLINE_COMMENT.'|'.self::HASH_COMMENT.')|'. // Comment
				'('.self::C_STRING.'|'.self::C_CHAR.')|'. // String
				self::NUMBER.'|'. // Number
				self::FUNC.'|'. // Function
				self::DEFINE.'|'. // Define
				self::PL_IDENTIFIER. // Identifier (variable, keyword, const)
			'/m','self::replace',$code);
		// Replace tabs with four spaces
		$s = str_replace("\t",'   ',$s);

//echo '<p>Regex: '.htmlspecialchars($r).'</p>';

		return '<pre class="sh">'.$s.'</pre>';
	}
}

