<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * File
 * 
 * This class makes it easy to 
 * create/edit/delete files
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class F {

  /**
   * Checks if a file exists
   * 
   * @param string $file
   * @return boolean
   */
  static public function exists($file) {
    return file_exists($file);
  }

  /**
   * Safely requires a file if it exists
   */
  static public function load($file) {
    if(file_exists($file)) require_once($file);
  }

  /**
   * Creates a new file
   * 
   * <code>
   *
   * f::write('test.txt', 'hello');
   * // creates a new text file with hello as content
   * 
   * // create a new file
   * f::write('text.txt', array('test' => 'hello'));
   * // creates a new file and encodes the array as json   
   * 
   * </code>
   * 
   * @param  string  $file The path for the new file
   * @param  mixed   $content Either a string or an array. Arrays will be converted to JSON. 
   * @param  boolean $append true: append the content to an exisiting file if available. false: overwrite. 
   * @return boolean 
   */  
  static public function write($file, $content, $append = false) {
    if(is_array($content)) $content = a::json($content);
    $mode = ($append) ? FILE_APPEND | LOCK_EX : LOCK_EX;
    return (@file_put_contents($file, $content, $mode)) ? true : false;
  }

  /**
   * Appends new content to an existing file
   * 
   * @param  string  $file The path for the file
   * @param  mixed   $content Either a string or an array. Arrays will be converted to JSON. 
   * @return boolean 
   */  
  static public function append($file, $content) {
    return static::write($file,$content,true);
  }
  
  /**
   * Reads the content of a file
   * 
   * <code>
   *
   * $content = f::read('test.txt');
   * // i.e. content is hello
   * 
   * $content = f::read('text.txt', 'json');
   * // returns an array with the parsed content
   * 
   * </code>   
   * 
   * @param  string  $file The path for the file
   * @param  mixed   $parse if set to true, parse the result with the passed method. See: "str::parse()" for more info about available methods. 
   * @return mixed 
   */  
  static public function read($file, $parse = false) {
    $content = f::exists($file) ? file_get_contents($file) : null;
    return ($parse) ? str::parse($content, $parse) : $content;
  }

  /**
   * Returns the file content as base64 encoded string
   * 
   * @param string $file The path for the file
   * @return string
   */
  static public function base64($file) {
    return base64_encode(f::read($file));
  }

  /**
   * Returns the file as data uri
   * 
   * @param string $file The path for the file
   * @return string
   */
  static public function uri($file) {
    $mime = static::mime($file);
    return ($mime) ? 'data:' . $mime . ';base64,' . static::base64($file) : false;
  }

  /**
   * Moves a file to a new location
   * 
   * <code>
   *
   * $move = f::move('test.txt', 'super.txt');
   * 
   * if($move) echo 'The file has been moved';
   * 
   * </code>
   * 
   * @param  string  $old The current path for the file
   * @param  string  $new The path to the new location
   * @return boolean 
   */  
  static public function move($old, $new) {
    if(!f::exists($old)) return false;
    return @rename($old, $new);
  }

  /**
   * Copy a file to a new location.
   *
   * @param  string  $file
   * @param  string  $target
   * @return boolean
   */
  static public function copy($file, $target) {
    return @copy($file, $target);
  }

  /**
   * Deletes a file
   * 
   * <code>
   *
   * $remove = f::remove('test.txt');
   * if($remove) echo 'The file has been removed';
   * 
   * </code>   
   * 
   * @param  string  $file The path for the file
   * @return boolean 
   */  
  static public function remove($file) {
    return (file_exists($file) && is_file($file) && !empty($file)) ? @unlink($file) : false;
  }

  /**
   * Gets the extension of a file
   *
   * <code>
   *
   * $extension = f::extension('test.txt');
   * // extension is txt
   * 
   * </code>
   * 
   * @param  string  $file The filename or path
   * @param  string  $extension Set an optional extension to overwrite the current one
   * @return string 
   */  
  static public function extension($file, $extension = false) {

    // overwrite the current extension
    if($extension) return static::name($file) . '.' . $extension;

    // return the current extension
    return strtolower(pathinfo($file, PATHINFO_EXTENSION));
  
  }

  /**
   * Returns all extensions for a certain file type
   * 
   * @param string $type
   * @return array
   */
  static public function extensions($type = null) {
    if(is_null($type)) return array_keys(c::get('f.mimes', array()));
    return a::get(c::get('f.types'), $type, array());
  }

  /**
   * Extracts the filename from a file path
   * 
   * <code>
   *
   * $filename = f::filename('/var/www/test.txt');
   * // filename is test.txt
   * 
   * </code>
   * 
   * @param  string  $file The path
   * @return string 
   */  
  static public function filename($name) {
    $name = basename($name);
    $name = url::stripQuery($name);
    $name = preg_replace('!\:.*!i', '', $name);
    $name = preg_replace('!\#.*!i', '', $name);
    return $name;
  }

  /**
   * Extracts the name from a file path or filename without extension
   *
   * <code>
   *
   * $name = f::name('/var/www/test.txt');
   * 
   * // name is test
   * 
   * </code>  
   * 
   * @param  string  $file The path or filename
   * @return string 
   */  
  static public function name($name) {
    $name = static::filename($name);
    $dot  = strrpos($name, '.');
    return ($dot) ? substr($name, 0, $dot) : $name;
  }

  /**
   * Just an alternative for dirname() to stay consistent
   *
   * <code>
   *
   * $dirname = f::dirname('/var/www/test.txt');
   * // dirname is /var/www  
   * 
   * </code>
   * 
   * @param  string  $file The path
   * @return string 
   */  
  static public function dirname($file = __FILE__) {
    return dirname($file);
  }

  /**
   * Returns the size of a file.
   * 
   * <code>
   *
   * $size = f::size('/var/www/test.txt');
   * // size is ie: 1231939
   * 
   * $size = f::size('/var/www/test.txt', true);
   * // size is ie: 1,2 mb
   * 
   * </code>   
   * 
   * @param  string  $file The path
   * @param  boolean $nice True: return the size in a human readable format
   * @return mixed
   */    
  static public function size($file, $nice = false) {
    clearstatcache();
    $size = filesize($file);
    return ($nice) ? static::niceSize($size) : $size;
  }

  /**
   * Converts an integer size into a human readable format
   * 
   * <code>
   *
   * $niceSize = f::niceSize('/path/to/a/file.txt');
   * // nice size is i.e: 212 kb
   * 
   * $niceSize = f::niceSize(1231939);
   * // nice size is: 1,2 mb
   * 
   * </code>   
   * 
   * @param  int $size The file size or a file path
   * @return string
   */    
  static public function niceSize($size) {
    
    // file mode
    if(!is_int($size) && file_exists($size)) {
      $size = static::size($size);
    }

    // make sure it's an int
    $size = (int)$size;
    
    // avoid errors for invalid sizes
    if($size <= 0) return '0 kb';
    
    // available units
    $unit = array('b','kb','mb','gb','tb','pb', 'eb', 'zb', 'yb');
    
    // the math magic
    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . a::get($unit, $i, '?');
  
  }

  /**
   * Get the file's last modification time.
   *
   * @param string $file
   * @return int
   */
  static public function modified($file) {
    return filemtime($file);
  }

  /**
   * Returns the mime type of a file
   * 
   * @param string $file
   * @return string
   */
  static public function mime($file) {

    // stop for invalid files
    if(!file_exists($file)) return null;

    // Fileinfo is prefered if available
    if(function_exists('finfo_file')) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime  = finfo_file($finfo, $file);
      finfo_close($finfo);
      return $mime;
    } 

    // for older versions with mime_content_type go for that. 
    if(function_exists('mime_content_type') && $mime = @mime_content_type($file) !== false) {
      return $mime;
    } 

    // guess the matching mime type by extension
    $info = a::get(c::get('f.mimes'), f::extension($file), null);

    // if there are more than one applicable mimes for the extension, return the first
    if(is_array($info)) return a::first($info);

    // return what's left
    return $info;

  }

  /**
   * Categorize the file
   * 
   * @param string $file Either the file path or extension
   * @return string
   */
  static public function type($file) {

    if(v::between($file, 2,4)) {
      // use the file name as extension
      $extension = $file;
    } else {
      // get the extension from the filename
      $extension = f::extension($file);
    }

    if(empty($extension)) {
      // detect the mime type first to get the most reliable extension
      $mime      = f::mime($file);
      $extension = f::mimeToExtension($mime);
    }

    // get all categorized types
    $types = c::get('f.types', array());

    foreach($types as $type => $extensions) {
      if(in_array($extension, $extensions)) return $type;
    }

    return null;

  }

  /**
   * Returns an array of all available file types
   *
   * @return array
   */
  static public function types() {
    return array_keys(c::get('f.types'));
  }

  /**
   * Checks if a file is of a certain type
   * 
   * @param string $file Full path to the file
   * @param string $value An extension or mime type
   * @return boolean
   */
  static public function is($file, $value) {
    
    if(in_array($value, static::extensions())) {
      // check for the extension
      return f::extension($file) == $value;
    } else if(str::contains($value, '/')) {
      // check for the mime type
      return f::mime($file) == $value;
    }

    return false;
    
  }

  /**
   * Converts a mime type to a file extension
   * 
   * @param string $mime
   * @return string
   */
  static public function mimeToExtension($mime) {
    foreach(c::get('f.mimes') as $key => $value) {
      if(is_array($value) && in_array($mime, $value)) return $key; 
      if($value == $mime) return $key;
    }
    return null;
  }
    
  /**
   * Converts a file extension to a mime type
   * 
   * @param string $extension
   * @return string
   */
  static public function extensionToMime($extension) {
    $mime = a::get(c::get('f.mimes'), $extension);
    return (is_array($mime)) ? a::first($mime) : $mime;
  }

  /**
   * Sanitize a filename to strip unwanted special characters
   * 
   * <code>
   *
   * $safe = f::safeName('über genious.txt');
   * // safe will be ueber-genious.txt
   * 
   * </code>   
   * 
   * @param  string $string The file name
   * @return string
   */    
  static public function safeName($string) {
    $name      = f::name($string);
    $extension = f::extension($string);
    $end       = (!empty($extension)) ? '.' . str::slug($extension) : '';
    return str::slug($name) . $end;
  }

  /**
   * Checks if the file is writable
   * 
   * @param string $file
   * @return boolean
   */
  static public function writable($file) {
    return is_writable($file);
  }

  /**
   * Checks if the file is readable
   * 
   * @param string $file
   * @return boolean
   */
  static public function readable($file) {
    return is_readable($file);
  }

}
