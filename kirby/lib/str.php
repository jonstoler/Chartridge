<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * String
 * 
 * A set of handy string methods
 *
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Str {

  /**
   * Converts a string to a html-safe string
   *
   * <code>
   * 
   * echo str::html('some <em>über crazy</em> stuff');
   * // output: some <em>&uuml;ber crazy</em> stuff
   * 
   * echo str::html('some <em>über crazy</em> stuff', false);    
   * // output: some &lt;em&gt;&uuml;ber crazy&lt;/em&gt; stuff
   * 
   * </code>   
   *
   * @param  string  $string
   * @param  boolean $keepTags True: lets stuff inside html tags untouched. 
   * @return string  The html string
   */  
  static public function html($string, $keepTags = true) {
    return html::encode($string, $keepTags);
  }

  /**
   * Removes all html tags and encoded chars from a string
   *
   * <code>
   * 
   * echo str::unhtml('some <em>crazy</em> stuff');
   * // output: some uber crazy stuff
   * 
   * </code>
   * 
   * @param  string  $string
   * @return string  The html string
   */  
  static public function unhtml($string) {
    return html::decode($string);
  }

  /**
   * Converts a string to a xml-safe string
   * Converts it to html-safe first and then it
   * will replace html entities to xml entities
   *
   * <code>
   *
   * echo str::xml('some über crazy stuff');
   * // output: some &#252;ber crazy stuff 
   *  
   * </code>
   * 
   * @param  string  $text
   * @param  boolean $html True: convert to html first
   * @return string
   */  
  static public function xml($text, $html = true) {
    return xml::encode($text, $html);
  }

  /**
   * Removes all xml entities from a string
   * and convert them to html entities first
   * and remove all html entities afterwards.
   *
   * <code>
   * 
   * echo str::unxml('some <em>&#252;ber</em> crazy stuff');
   * // output: some &uuml;ber crazy stuff
   * 
   * </code>
   * 
   * @param  string  $string
   * @return string
   */  
  static public function unxml($string) {
    return xml::decode($string);
  }

  /**
   * Parses a string by a set of available methods
   *
   * Available methods:
   * - json
   * - xml
   * - url
   * - query
   * - php
   *
   * <code>
   * 
   * str::parse('{"test":"cool","super":"genious"}');
   * // output: array(
   * //  'test' => 'cool',
   * //  'super' => 'genious'
   * // );
   * 
   * str::parse('<xml><entries><cool>nice</cool></entries></xml>', 'xml');
   * // output: array(
   * //    'entries' => array(
   * //        'cool' => 'nice'
   * //    )
   * // );
   * 
   * </code>
   * 
   * @param  string  $string
   * @param  string  $mode
   * @return string
   */  
  static public function parse($string, $mode = 'json') {

    if(is_array($string)) return $string;

    switch($mode) {
      case 'json':
        $result = (array)@json_decode($string, true);
        break;
      case 'xml':
        $result = xml::parse($string);
        break;
      case 'url':
        $result = (array)@parse_url($string);
        break;
      case 'query':
        if(url::hasQuery($string)) {
          $string = static::split($string, '?');
          $string = a::last($string);
        }
        @parse_str($string, $result);
        break;
      case 'php':
        $result = @unserialize($string);
        break;
      case 'date':
        $result = strtotime($string);
      default:
        $result = $string;
        break;
    }

    return $result;

  }

  /**
   * Encode a string (used for email addresses)
   *
   * @param  string  $string
   * @return string
   */  
  static public function encode($string) {
    $encoded = '';
    $length = static::length($string);
    for($i=0; $i<$length; $i++) {
      $encoded .= (rand(1, 2)==1) ? '&#' . ord($string[$i]) . ';' : '&#x' . dechex(ord($string[$i])) . ';';
    }
    return $encoded;
  }

  /**
   * Generates an "a mailto" tag
   * 
   * <code>
   * 
   * echo str::email('bastian@getkirby.com');
   * echo str::email('bastian@getkirby.com', 'mail me');
   * 
   * </code>
   * 
   * @param string $href The url for the a tag
   * @param mixed $text The optional text. If null, the url will be used as text
   * @param array $attr Additional attributes for the tag
   * @return string the generated html
   */
  static public function email($email, $text = false, $attr = array()) {
    return html::email($email, $text, $attr);
  }

  /**
   * Generates an a tag
   * 
   * @param string $href The url for the a tag
   * @param mixed $text The optional text. If null, the url will be used as text
   * @param array $attr Additional attributes for the tag
   * @return string the generated html
   */
  static public function link($href, $text = null, $attr = array()) {
    return html::a($href, $text, $attr);
  }

  /**
   * Returns an array with all words in a string
   * 
   * @param string $string
   */
  static public function words($string) {
    preg_match_all('/(\pL{4,})/iu', $string, $m);
    return a::first($m);
  }

  /**
   * Returns an array with all sentences in a string
   * 
   * @param string $string
   * @return string
   */
  static public function sentences($string) {
    return preg_split('/(?<=[.?!])\s+/', $string, -1, PREG_SPLIT_NO_EMPTY);
  }

  /**
   * Returns an array with all lines in a string
   * 
   * @param string $string
   * @return array
   */
  static public function lines($string) {
    return str::split($string, PHP_EOL);
  }

  /**
   * Limits the string by a given method and length
   * 
   * @param string $string
   * @param string $type
   * @param int    $length
   * @param string $rep
   * @return string
   */
  static public function limit($string, $type, $length, $rep = '…') {

    if($length == 0) return $string;

    switch($type) {
      case 'chars':
        if(static::length($string) <= $length) return $string;
        $string = static::substr($string, 0, $length);
        return $string . $rep;
        break;
      case 'words':
        preg_match('/^\s*+(?:\S++\s*+){1,'.$length.'}/u', $string, $matches);
        if(static::length($string) == static::length($matches[0])) $rep = '';
        return rtrim($matches[0]) . $rep;
        break;
      case 'sentences':
        $sentences = preg_split('/(?<=[.?!])\s+/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
        $sentences = array_slice($sentences, 0, $length+1);
        $last      = a::last($sentences);
        $offset    = a::last($last);
        return str::substr($string, 0, $offset);
        break;
      case 'lines':
        $lines = static::lines($string);
        $lines = (count($lines) <= $length) ? $lines : array_slice($lines, 0, $length);
        return implode(PHP_EOL, $lines);
        break;
    }

  } 

  /**
   * Shortens a string and adds an ellipsis if the string is too long
   *
   * <code>
   * 
   * echo str::short('This is a very, very, very long string', 10);
   * // output: This is a…
   * 
   * echo str::short('This is a very, very, very long string', 10, '####');
   * // output: This i####
   * 
   * </code>
   * 
   * @param  string  $string The string to be shortened
   * @param  int     $chars The final number of characters the string should have
   * @param  string  $rep The element, which should be added if the string is too long. Ellipsis is the default.
   * @return string  The shortened string  
   */  
  static public function short($string, $length, $rep = '…') {
    return static::limit($string, 'chars', $length, $rep);
  }

  /**
   * Shortens a URL
   * It removes http:// or https:// and uses str::short afterwards
   * 
   * <code>
   * 
   * echo str::shorturl('http://veryveryverylongurl.com', 30);
   * // output: veryveryverylongurl.com
   *
   * </code>
   *
   * @param  string  $url The URL to be shortened
   * @param  int     $chars The final number of characters the URL should have
   * @param  boolean $base True: only take the base of the URL. 
   * @param  string  $rep The element, which should be added if the string is too long. Ellipsis is the default.
   * @return string  The shortened URL  
   */  
  static public function shorturl($string, $length, $base = false, $rep = '…') {
    return url::short($string, $length, $base, $rep);
  }

  /** 
   * Creates an excerpt of a string
   * It removes all html tags first and then uses str::short
   *
   * @param  string  $string The string to be shortened
   * @param  int     $chars The final number of characters the string should have
   * @param  boolean $removehtml True: remove the HTML tags from the string first 
   * @param  string  $rep The element, which should be added if the string is too long. Ellipsis is the default.
   * @return string  The shortened string  
   */
  static public function excerpt($string, $chars = 140, $removehtml = true, $rep='…') {
    if($removehtml) $string = strip_tags($string);
    $string = trim($string);    
    $string = str_replace(PHP_EOL, ' ', $string);
    if(str::length($string) <= $chars) return $string;
    return ($chars==0) ? $string : static::substr($string, 0, strrpos(static::substr($string, 0, $chars), ' ')) . $rep;
  }

  /**
   * The widont function makes sure that there are no 
   * typographical widows at the end of a paragraph –
   * that's a single word in the last line
   * 
   * @param string $string
   * @return string
   */
  static public function widont($string = '') {
    return preg_replace('|([^\s])\s+([^\s]+)\s*$|', '$1&nbsp;$2', $string);
  }

  /** 
   * An UTF-8 safe version of substr()
   * 
   * @param  string  $str
   * @param  int     $start
   * @param  int     $end 
   * @return string  
   */
  static public function substr($str, $start, $end = null) {    
    return MB_STRING ? mb_substr($str, $start, ($end == null) ? str::length($str) : $end, 'UTF-8') : substr($str, $start, $end);
  }

  /** 
   * An UTF-8 safe version of strtolower()
   * 
   * @param  string  $str
   * @return string  
   */
  static public function lower($str) {
    return MB_STRING ? mb_strtolower($str, 'UTF-8') : strtolower($str);
  }

  /** 
   * An UTF-8 safe version of strotoupper()
   * 
   * @param  string  $str
   * @return string  
   */
  static public function upper($str) {
    return MB_STRING ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
  }

  /** 
   * An UTF-8 safe version of strlen()
   * 
   * @param  string  $str
   * @return string  
   */
  static public function length($str) {
    return MB_STRING ? mb_strlen($str, 'UTF-8') : strlen($str);
  }

  /** 
   * Checks if a str contains another string
   * 
   * @param  string  $str
   * @param  string  $needle
   * @param  boolean $i ignore upper/lowercase
   * @return string  
   */
  static public function contains($str, $needle, $i = true) {
    if($i) {
      $str    = static::lower($str);
      $needle = static::lower($needle);
    }
    return (strstr($str, $needle)) ? true : false;
  }

  /**
   * Find one or more needles in one or more haystacks
   * 
   * Also avoid the retarded counter-intuitive original
   * strpos syntax that makes you put haystack before needle
   * 
   * @param mixed    $needle  The needle(s) to search for
   * @param mixed    $haystack The haystack(s) to search in
   * @param boolean  $absolute If true all the needle(s) need to be found in all the haystack(s), otherwise one found is enough
   * @param boolean  $case_sensitive Wether the function is case sensitive or not
   * @return boolean  Found or not
   */
  static function find($needle, $haystack, $absolute = false, $case_sensitive = false) {
    if(is_array($needle)) {
      $found = 0;
      foreach($needle as $need) if(static::find($need, $haystack, $absolute, $case_sensitive)) $found++;
      return ($absolute) ? count($needle) == $found : $found > 0;
    } elseif(is_array($haystack)) {
      $found = 0;
      foreach($haystack as $hay) if(static::find($needle, $hay, $absolute, $case_sensitive)) $found++;
      return ($absolute) ? count($haystack) == $found : $found > 0;
    } else {
      if(!$case_sensitive) {
        $haystack = strtolower($haystack);
        $needle = strtolower($needle);
      }      
      // Simple strpos
      $pos = strpos($haystack, $needle);
      if($pos === false) return false;
      else return true;
    }
  }

  /** 
   * preg_match sucks! This tries to make it more convenient
   * 
   * @param  string  $string
   * @param  string  $preg Regular expression
   * @param  string  $get Which part should be returned from the result array
   * @param  string  $placeholder Default value if nothing will be found
   * @return mixed  
   */
  static public function match($string, $preg, $get = false, $placeholder = false) {
    $match = @preg_match($preg, $string, $array);
    if(!$match) return false;
    if($get === false) return $array;
    return a::get($array, $get, $placeholder);
  }

  /** 
   * Generates a random string
   * 
   * @param  int  $length The length of the random string
   * @return string  
   */
  static public function random($length = false, $type = 'alphaNum') {
    $length = ($length) ? $length : rand(5,10);
    $pool   = a::shuffle(static::pool($type));
    $pool   = ($length) ? array_slice($pool, 0, $length) : $pool;
    return implode('', $pool);
  }

  /**
   * Shuffles all characters in the string (utf8 safe)
   * 
   * @param string $string
   * @return string
   */
  static public function shuffle($string) {
    $length = str::length($string);
    $parts  = array(); 
    while($length-- > 0) $parts[] = str::substr($string, $length, 1);
    shuffle($parts);
    return join('', $parts);
  }

  /** 
   * Convert a string to a safe version to be used in a URL
   * 
   * @param  string  $text The unsafe string
   * @param  string  $separator To be used instead of space and other non-word characters.
   * @return string  The safe string
   */
  static public function slug($string, $separator = '-') {

    $string = trim($string);
    $string = static::lower($string);
    $string = static::ascii($string);

    // replace spaces with simple dashes
    $string = preg_replace('![^a-z0-9]!i','-', $string);
    // remove double dashes
    $string = preg_replace('![-]{2,}!','-', $string);
    // trim trailing and leading dashes
    $string = trim($string, '-');
    // replace slashes with dashes
    $string = str_replace('/', '-', $string);

    return $string;

  }

  /**
   * Alternative for str::slug($text)
   */
  static public function urlify($string) {
    return static::slug($string);
  }

  /** 
   * Better alternative for explode()
   * It takes care of removing empty values
   * and it has a built-in way to skip values
   * which are too short. 
   * 
   * @param  string  $string The string to split
   * @param  string  $separator The string to split by
   * @param  int     $length The min length of values. 
   * @return array   An array of found values
   */
  static public function split($string, $separator = ',', $length = 1) {

    if(is_array($string)) return $string;

    $string = trim($string, $separator);
    $parts  = explode($separator, $string);
    $out    = array();

    foreach($parts AS $p) {
      $p = trim($p);
      if(static::length($p) > 0 && static::length($p) >= $length) $out[] = $p;
    }

    return $out;

  }

  /** 
   * A more brutal way to trim. 
   * It removes double spaces. 
   * Can be useful in some cases but 
   * be careful as it might remove too much. 
   * 
   * @param  string  $string The string to trim
   * @return string  The trimmed string
   */
  static public function trim($string) {
    $string = preg_replace('/\s\s+/u', ' ', $string);
    return trim($string);
  }

  /** 
   * An UTF-8 safe version of ucwords()
   * 
   * @param  string  $string 
   * @return string 
   */
  static public function ucwords($string) {
    return MB_STRING ? mb_convert_case($string, MB_CASE_TITLE, 'UTF-8') : ucwords(strtolower($string));
  }

  /** 
   * An UTF-8 safe version of ucfirst()
   * 
   * @param  string $string
   * @return string 
   */
  static public function ucfirst($string) {
    return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
  }

  /**
   * Tries to detect the string encoding
   * 
   * @param string $string
   * @return string
   */
  static public function encoding($string) {

    if(MB_STRING) {
      return mb_detect_encoding($string,'UTF-8, ISO-8859-1, windows-1251');  
    } else {
      $list = array('utf-8', 'iso-8859-1', 'windows-1251');
      foreach($list as $item) {
        $sample = iconv($item, $item, $string);
        if(md5($sample) == md5($string)) return $item;
      }
      return false;
    }

  }

  /**
   * Converts a string to a different encoding
   * 
   * @param string $string
   * @param string $targetEncoding
   * @param string $sourceEncoding (optional)
   * @return string
   */
  static public function convert($string, $targetEncoding, $sourceEncoding = null) {
    // detect the source encoding if not passed as third argument
    if(is_null($sourceEncoding)) $sourceEncoding = static::encoding($string);
    return iconv($sourceEncoding, $targetEncoding, $string); 
  }

  /** 
   * Converts a string to UTF-8
   * 
   * @param  string  $string 
   * @return string 
   */
  static public function utf8($string) {
    return static::convert($string, 'utf-8');
  }

  /** 
   * A better way to strip slashes
   * 
   * @param  string  $string 
   * @return string 
   */
  static public function stripslashes($string) {
    if(is_array($string)) return $string;
    return (get_magic_quotes_gpc()) ? stripslashes(stripslashes($string)) : $string;
  }

  /**
   * A super simple string template engine, 
   * which replaces tags like {mytag} with any other string
   * 
   * @param  string $string
   * @param  array  $replace An associative array with keys, which should be replaced and values.
   * @return string
   */
  static public function template($string, $data = array()) {  
    $replace = array();
    foreach($data as $key => $value) $replace['{' . $key . '}'] = $value;    
    return str_replace(array_keys($replace), array_values($replace), $string);
  }

  /**
   * Convert a string to 7-bit ASCII.
   *
   * @param  string  $string
   * @return string
   */
  static public function ascii($string) {

    $foreign = c::get('str.ascii', array(
      '/Ä/' => 'Ae',
      '/æ|ǽ|ä/' => 'ae',
      '/œ|ö/' => 'oe',
      '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ|А/' => 'A',
      '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|а/' => 'a',
      '/Б/' => 'B',
      '/б/' => 'b',
      '/Ç|Ć|Ĉ|Ċ|Č|Ц/' => 'C',
      '/ç|ć|ĉ|ċ|č|ц/' => 'c',
      '/Ð|Ď|Đ|Д/' => 'Dj',
      '/ð|ď|đ|д/' => 'dj',
      '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|Е|Ё|Э/' => 'E',
      '/è|é|ê|ë|ē|ĕ|ė|ę|ě|е|ё|э/' => 'e',
      '/Ф/' => 'F',
      '/ƒ|ф/' => 'f',
      '/Ĝ|Ğ|Ġ|Ģ|Г/' => 'G',
      '/ĝ|ğ|ġ|ģ|г/' => 'g',
      '/Ĥ|Ħ|Х/' => 'H',
      '/ĥ|ħ|х/' => 'h',
      '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|И/' => 'I',
      '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|и/' => 'i',
      '/Ĵ|Й/' => 'J',
      '/ĵ|й/' => 'j',
      '/Ķ|К/' => 'K',
      '/ķ|к/' => 'k',
      '/Ĺ|Ļ|Ľ|Ŀ|Ł|Л/' => 'L',
      '/ĺ|ļ|ľ|ŀ|ł|л/' => 'l',
      '/М/' => 'M',
      '/м/' => 'm',
      '/Ñ|Ń|Ņ|Ň|Н/' => 'N',
      '/ñ|ń|ņ|ň|ŉ|н/' => 'n',
      '/Ö/' => 'Oe',
      '/ö/' => 'oe',
      '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|О/' => 'O',
      '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|о/' => 'o',
      '/П/' => 'P',
      '/п/' => 'p',
      '/Ŕ|Ŗ|Ř|Р/' => 'R',
      '/ŕ|ŗ|ř|р/' => 'r',
      '/Ś|Ŝ|Ş|Ș|Š|С/' => 'S',
      '/ś|ŝ|ş|ș|š|ſ|с/' => 's',
      '/Ţ|Ț|Ť|Ŧ|Т/' => 'T',
      '/ţ|ț|ť|ŧ|т/' => 't',
      '/Ü/' => 'Ue',
      '/ü/' => 'ue',
      '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|У/' => 'U',
      '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|у/' => 'u',
      '/В/' => 'V',
      '/в/' => 'v',
      '/Ý|Ÿ|Ŷ|Ы/' => 'Y',
      '/ý|ÿ|ŷ|ы/' => 'y',
      '/Ŵ/' => 'W',
      '/ŵ/' => 'w',
      '/Ź|Ż|Ž|З/' => 'Z',
      '/ź|ż|ž|з/' => 'z',
      '/Æ|Ǽ/' => 'AE',
      '/ß/'=> 'ss',
      '/Ĳ/' => 'IJ',
      '/ĳ/' => 'ij',
      '/Œ/' => 'OE',
      '/Ч/' => 'Ch',
      '/ч/' => 'ch',
      '/Ю/' => 'Ju',
      '/ю/' => 'ju',
      '/Я/' => 'Ja',
      '/я/' => 'ja',
      '/Ш/' => 'Sh',
      '/ш/' => 'sh',
      '/Щ/' => 'Shch',
      '/щ/' => 'shch',
      '/Ж/' => 'Zh',
      '/ж/' => 'zh',
    ));
  
    $string = preg_replace(array_keys($foreign), array_values($foreign), $string);
    return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $string);
    
  }

  /**
   * Get a character pool with various possible combinations
   *
   * @param  string  $type
   * @param  boolean $array 
   * @return string
   */
  static public function pool($type, $array = true) {

    $pool = array();
    
    if(is_array($type)) {
      foreach($type as $t) {
        $pool = array_merge($pool, static::pool($t));
      }
    } else {

      switch($type) {
        case 'alphaLower':
          $pool = range('a','z');
          break;
        case 'alphaUpper':
          $pool = range('A', 'Z');
          break;
        case 'alpha':
          $pool = static::pool(array('alphaLower', 'alphaUpper'));
          break;
        case 'num':
          $pool = range(0, 9);
          break;
        case 'alphaNum':
          $pool = static::pool(array('alpha', 'num'));
          break;
      }

    }

    return ($array) ? $pool : implode('', $pool);

  }

}