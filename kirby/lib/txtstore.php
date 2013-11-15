<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * TxtStore
 * 
 * Simple data structure for text files
 * This is used by the Kirby CMS to structure text files
 * with different fields and can be used in any other app
 * to create simple structured text storage
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class TxtStore {

  /**
   * Reads the structure from a file or string
   * 
   * @param string $input
   * @param boolean $file Specify if you want to pass a file or string
   * @return array
   */
  static public function read($input, $file = true) {

    $content  = ($file) ? f::read($input) : $input;
    $content  = str_replace("\xEF\xBB\xBF", '', $content);
    $sections = preg_split('![\r\n]+[-]{4,}[\r\n]+!i', $content);
    $data     = array();
    
    foreach($sections AS $s) {

      $parts = explode(':', $s);  
      $key   = str::lower(preg_replace('![^a-z0-9]+!i', '_', trim($parts[0])));

      if(empty($key)) continue;
      
      $value = trim(implode(':', array_slice($parts, 1)));

      // store the key and value in the data array
      $data[$key] = $value;
    
    }

    return $data;

  }

  /**
   * Creates the structure from an array
   * 
   * @param array $data
   * @return string
   */
  static public function structure($data = array()) {

    $divider = PHP_EOL . '----' . PHP_EOL;
    $result  = null;
    $break   = null;
    $keys    = array();
    foreach($data AS $key => $value) {
      $key = str::slug($key);
      $key = str::ucfirst(str_replace('-', '_', $key));
      if(in_array($key, $keys) || empty($key)) continue;
      $keys[]  = $key;     
      $result .= $break . $key . ': ' . trim($value);
      $break   = $divider;    
    }
    return $result;

  }

  /**
   * Writes a data structure to the given file
   * 
   * @param string $file path to the file
   * @param array The data array
   * @return boolean
   */
  static public function write($file, $data = array()) {
    // write the structure to the destination file and prepend a BOM to make sure it's UTF-8
    return f::write($file, "\xEF\xBB\xBF" . static::structure($data));      
  }

}