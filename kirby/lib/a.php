<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Array 
 *
 * This class is supposed to simplify array handling
 * and make it more consistent. 
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class A {

  /**
   * Sets a value for any key or sub key in the array
   * 
   * @param  array   $array
   * @param  string  $key Use > notation to acccess deeper nested array keys
   * @param  mixed   $value
   */
  static public function set(&$array, $key, $value) {

    if(is_null($key)) return $array = $value;

    $keys = str::split($key, '>');

    while(count($keys) > 1) {
      $key = array_shift($keys);

      // If the key doesn't exist at this depth, we will just create an
      // empty array to hold the next value, allowing us to create the
      // arrays to hold the final value.
      if(!isset($array[$key]) or ! is_array($array[$key])) {
        $array[$key] = array();
      }

      $array =& $array[$key];
    
    }
    
    $array[array_shift($keys)] = $value;
  
  }

  /**
   * Gets an element of an array by key
   * 
   * <code>
   * 
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );    
   * 
   * echo a::get($array, 'cat');
   * // output: 'miao'
   * 
   * echo a::get($array, 'elephant', 'shut up');
   * // output: 'shut up'   
   * 
   * $catAndDog = a::get(array('cat', 'dog'));
   * // result: array(
   * //   'cat' => 'miao',
   * //   'dog' => 'wuff'
   * // );
   * 
   * </code>
   *
   * @param   array  $array The source array
   * @param   mixed  $key The key to look for
   * @param   mixed  $default Optional default value, which should be returned if no element has been found
   * @return  mixed
   */
  static public function get($array, $key, $default = null) {

    // get an array of keys
    if(is_array($key)) {
      $result = array();
      foreach($key as $k) $result[$k] = static::get($array, $k);
      return $result;

    // get a single 
    } else if(isset($array[$key])) {
      return $array[$key];    

    // return the entire array if the key is null
    } else if(is_null($key)) {
      return $array;

    // dive deep into the array and get a key in a nested array
    } else if(str::contains($key, '>')) {
      foreach(str::split($key, '>') as $segment) {
        if(!is_array($array) or !array_key_exists($segment, $array)) return $default;
        $array = $array[$segment];
      }      
      return $array;

    // get the default value if nothing else worked out
    } else {
      return $default;
    }

  }
  
  /**
   * Removes an element from an array
   * 
   * <code>
   * 
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );    
   * 
   * $array = a::remove($array, 'cat');
   * // array is now: array(
   * //     'dog' => 'wuff',
   * //     'bird' => 'tweet'
   * // );
   * 
   * </code>
   * 
   * @param   array    $array The source array
   * @param   mixed    $search The value or key to look for
   * @param   boolean  $key Pass true to search for an key, pass false to search for an value.   
   * @return  array    The result array without the removed element
   */
  static public function remove($array, $search, $key = true) {
    if($key) {
      unset($array[$search]);
    } else {
      $found = false;
      while(!$found) {
        $index = array_search($search, $array);
        if($index !== false) {
          unset($array[$index]);
        } else {
          $found = true;
        }
      }
    }
    return $array;
  }

  /**
   * Alternative for a::remove with $key set to true
   * 
   * @see a::remove() 
   */
  static public function removeKey($array, $search) {
    static::remove($array, $search, true);
  }

  /**
   * Alternative for a::remove with $key set to false
   * 
   * @see a::remove() 
   */
  static public function removeValue($array, $search) {
    static::remove($array, $search, false);
  }

  /**
   * Injects an element into an array
   * 
   * @param   array  $array The source array
   * @param   int    $position The position, where to inject the element
   * @param   mixed  $element The element, which should be injected
   * @return  array  The result array including the new element
   */
  static public function inject($array, $position, $element = 'placeholder') {
    $start = array_slice($array, 0, $position);
    $end = array_slice($array, $position);
    return array_merge($start, (array)$element, $end);
  }

  /**
   * Shows an entire array or object in a human readable way
   * This is perfect for debugging
   * 
   * <code>
   * 
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );    
   * 
   * a::show($array);
   * 
   * // output: 
   * // Array
   * // (
   * //     [cat] => miao
   * //     [dog] => wuff
   * //     [bird] => tweet
   * // )
   * 
   * </code>
   * 
   * @param   array    $array The source array
   * @param   boolean  $echo By default the result will be echoed instantly. You can switch that off here. 
   * @return  mixed    If echo is false, this will return the generated array output.
   */
  static public function show($array, $echo = true) {
    if(r::cli()) {
      $output = print_r($array, true) . PHP_EOL;
    } else {
      $output  = '<pre>';
      $output .=  htmlspecialchars(print_r($array, true));
      $output .= '</pre>';
    }
    if($echo == true) echo $output;
    return $output;
  }

  /**
   * Converts an array to a JSON string
   * It's basically a shortcut for json_encode()
   * 
   * <code>
   *
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );    
   * 
   * echo a::json($array);
   * // output: {"cat":"miao","dog":"wuff","bird":"tweet"}
   * 
   * </code>
   * 
   * @param   array   $array The source array
   * @return  string  The JSON string
   */
  static public function json($array) {
    return json_encode((array)$array);
  }

  /**
   * Converts an array to a XML string
   * 
   * <code>
   * 
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );    
   * 
   * echo a::xml($array, 'animals');
   * // output: 
   * // <animals>
   * //   <cat>miao</cat>
   * //   <dog>wuff</dog>
   * //   <bird>tweet</bird>
   * // </animals>
   * 
   * </code>
   * 
   * @param   array    $array The source array
   * @param   string   $tag The name of the root element
   * @param   boolean  $head Include the xml declaration head or not
   * @param   string   $charset The charset, which should be used for the header
   * @param   int      $level The indendation level
   * @return  string   The XML string
   */
  static public function xml($array, $tag = 'root', $head = true, $charset = 'utf-8', $tab = '  ', $level = 0) {
    return xml::create($array, $tag, $head, $charset, $tab, $level);
  }

  /**
   * Extracts a single column from an array
   * 
   * <code>
   * 
   * $array[0] = array(
   *   'id' => 1,
   *   'username' => 'bastian',
   * );    
   * 
   * $array[1] = array(
   *   'id' => 2,
   *   'username' => 'peter',
   * );    
   * 
   * $array[3] = array(
   *   'id' => 3,
   *   'username' => 'john',
   * );    
   * 
   * $extract = a::extract($array, 'username');
   * 
   * // result: array(
   * //   'bastian',
   * //   'peter',
   * //   'john'
   * // );
   * 
   * </code>
   * 
   * @param   array   $array The source array
   * @param   string  $key The key name of the column to extract
   * @return  array   The result array with all values from that column. 
   */
  static public function extract($array, $key) {
    $output = array();
    foreach($array AS $a) if(isset($a[$key])) $output[] = $a[ $key ];
    return $output;
  }

  /**
   * Shuffles an array and keeps the keys
   * 
   * <code>
   * 
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );    
   * 
   * $shuffled = a::shuffle($array);
   * // output: array(
   * //    'dog' => 'wuff',
   * //    'cat' => 'miao',
   * //    'bird' => 'tweet'
   * // );
   *
   * </code>
   * 
   * @param   array  $array The source array
   * @return  array  The shuffled result array
   */
  static public function shuffle($array) {

    $keys = array_keys($array); 
    $new  = array();

    shuffle($keys);

    // resort the array
    foreach($keys as $key) $new[$key] = $array[$key];
    return $new;

  } 

  /**
   * Returns the first element of an array
   *
   * I always have to lookup the names of that function
   * so I decided to make this shortcut which is 
   * easier to remember.
   * 
   * <code>
   *
   * $array = array(
   *   'cat',
   *   'dog',
   *   'bird',
   * );    
   * 
   * $first = a::first($array);
   * // first: 'cat'
   * 
   * </code>
   *
   * @param   array  $array The source array
   * @return  mixed  The first element
   */
  static public function first($array) {
    return array_shift($array);
  }

  /**
   * Returns the last element of an array
   *
   * I always have to lookup the names of that function
   * so I decided to make this shortcut which is 
   * easier to remember.
   * 
   * <code>
   * 
   * $array = array(
   *   'cat',
   *   'dog',
   *   'bird',
   * );    
   * 
   * $last = a::last($array);
   * // first: 'bird'
   * 
   * </code>
   * 
   * @param   array  $array The source array
   * @return  mixed  The last element
   */
  static public function last($array) {
    return array_pop($array);
  }

  /**
   * Fills an array up with additional elements to certain amount. 
   * 
   * <code>
   * 
   * $array = array(
   *   'cat',
   *   'dog',
   *   'bird',
   * );    
   * 
   * $result = a::fill($array, 5, 'elephant');
   * 
   * // result: array(
   * //   'cat',
   * //   'dog',
   * //   'bird',
   * //   'elephant',
   * //   'elephant',
   * // );
   * 
   * </code>
   *
   * @param   array  $array The source array
   * @param   int    $limit The number of elements the array should contain after filling it up. 
   * @param   mixed  $fill The element, which should be used to fill the array
   * @return  array  The filled-up result array
   */
  static public function fill($array, $limit, $fill='placeholder') {
    if(count($array) < $limit) {
      $diff = $limit-count($array);
      for($x=0; $x<$diff; $x++) $array[] = $fill;
    }
    return $array;
  }

  /**
   * Checks for missing elements in an array
   *
   * This is very handy to check for missing 
   * user values in a request for example. 
   * 
   * <code>
   *
   * $array = array(
   *   'cat' => 'miao',
   *   'dog' => 'wuff',
   *   'bird' => 'tweet'
   * );    
   * 
   * $required = array('cat', 'elephant');
   * 
   * $missng = a::missing($array, $required);
   * // missing: array(
   * //    'elephant'
   * // );
   * 
   * </code>
   * 
   * @param   array  $array The source array
   * @param   array  $required An array of required keys
   * @return  array  An array of missing fields. If this is empty, nothing is missing. 
   */
  static public function missing($array, $required=array()) {
    $missing = array();
    foreach($required AS $r) {
      if(empty($array[$r])) $missing[] = $r;
    }
    return $missing;
  }

  /**
   * Sorts a multi-dimensional array by a certain column
   * 
   * <code>
   * 
   * $array[0] = array(
   *   'id' => 1,
   *   'username' => 'bastian',
   * );    
   * 
   * $array[1] = array(
   *   'id' => 2,
   *   'username' => 'peter',
   * );    
   * 
   * $array[3] = array(
   *   'id' => 3,
   *   'username' => 'john',
   * );    
   * 
   * $sorted = a::sort($array, 'username ASC');
   * // Array
   * // (
   * //      [0] => Array
   * //          (
   * //              [id] => 1
   * //              [username] => bastian
   * //          )
   * //      [1] => Array
   * //          (
   * //              [id] => 3
   * //              [username] => john
   * //          )
   * //      [2] => Array
   * //          (
   * //              [id] => 2
   * //              [username] => peter
   * //          )
   * // )
   * 
   * </code>
   *
   * @param   array   $array The source array
   * @param   string  $field The name of the column
   * @param   string  $direction desc (descending) or asc (ascending)
   * @param   const   $method A PHP sort method flag or 'natural' for natural sorting, which is not supported in PHP by sort flags 
   * @return  array   The sorted array
   */
  static public function sort($array, $field, $direction = 'desc', $method = SORT_REGULAR) {

    $direction = (strtolower($direction) == 'desc') ? SORT_DESC : SORT_ASC;
    $helper    = array();

    foreach($array as $key => $row) {      
      $helper[$key] = (is_object($row)) ? (method_exists($row, $field)) ? str::lower($row->$field()) : str::lower($row->$field) : str::lower($row[$field]);
    }

    // natural sorting    
    if($method === 'natural') {
      natsort($helper);
      if($direction === SORT_DESC) $helper = array_reverse($helper);
    } else if($direction === SORT_DESC) {
      arsort($helper, $method);
    } else {
      asort($helper, $method);
    }

    $result = array();
    
    foreach($helper as $key => $val) {
      $result[$key] = $array[$key];
    }
    
    return $result;
    
  }
  
  /**
   * Checks wether an array is associative or not (experimental)
   * 
   * @param   array    $array The array to analyze
   * @return  boolean  true: The array is associative false: It's not
   */
  static function isAssociative($array) {
    return !ctype_digit(implode(NULL, array_keys($array)));
  }
  
  /**
   * Returns the average value of an array
   * 
   * @param   array  $array The source array
   * @param   int    $decimals The number of decimals to return
   * @return  int    The average value
   */
  static function average($array, $decimals = 0) {
    return round(array_sum($array), $decimals) / sizeof($array); 
  }  

}