<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * Request
 * 
 * Handles all incoming requests
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class R {

  // Stores all sanitized request data
  static protected $data = null;

  // the used request method
  static protected $method = null;  
  
  // the request body
  static protected $body = null;

  /**
   * Returns either the entire data array or parts of it
   * 
   * <code>
   * 
   * echo r::data('username');
   * // sample output 'bastian'
   * 
   * echo r::data('username', 'peter');
   * // if no username is found in the request peter will be echoed
   * 
   * </code>
   * 
   * @param string $key An optional key to receive only parts of the data array
   * @param mixed $default A default value, which will be returned if nothing can be found for a given key
   * @param mixed
   */
  static public function data($key = null, $default = null) {
    
    if(!is_null(static::$data)) {
      $data = static::$data;
    } else {
      $_REQUEST = array_merge($_GET, $_POST);
      $data = static::$data = (static::is('GET')) ? static::sanitize($_REQUEST) : array_merge(static::body(), static::sanitize($_REQUEST));
    }
    
    return a::get($data, $key, $default);
    
  }

  /**
   * Checks whether the request contains the given key(s)
   * 
   * @param string $key the key to check for
   * @return boolean
   */
  static public function has() {
    $keys = func_get_args();
    foreach($keys as $key){
      if(static::get($key, false) === false){ return false; }
    }
    return true;
  }

  /**
   * Private method to sanitize incoming request data
   * 
   * @param array $data
   * @return array 
   */
  static protected function sanitize($data) {

    if(!is_array($data)) {
      return trim(str::stripslashes($data));      
    }

    foreach($data as $key => $value) {
      $value = static::sanitize($value);
      $data[$key] = $value;    
    }      

    return $data;  

  }

  /**
   * Sets or overwrites a variable in the data array
   * 
   * <code>
   * 
   * r::set('username', 'bastian');
   * 
   * a::show($_REQUEST);
   * 
   * // sample output: array(
   * //    'username' => 'bastian'
   * //    ... other stuff from the request
   * // );
   * 
   * </code>
   * 
   * @param mixed $key The key to set/replace. Use an array to set multiple values at once
   * @param mixed $value The value
   * @return array
   */
  static public function set($key, $value = null) {
    
    // set multiple values at once
    if(is_array($key)) {
      foreach($key as $k => $v) static::set($k, $v);
      return static::$data;
    }

    // make sure the data array is actually an array
    if(is_null(static::$data)) static::$data = array();

    static::$data[$key] = $_REQUEST[$key] = static::sanitize($value);
    return static::$data;

  }

  /**
   * Alternative to static::data($key, $default)
   * 
   * <code>
   * 
   * echo r::get('username');
   * // sample output 'bastian'
   * 
   * echo r::get('username', 'peter');
   * // if no username is found in the request peter will be echoed
   * 
   * </code>
   * 
   * @param string $key An optional key to receive only parts of the data array
   * @param mixed $default A default value, which will be returned if nothing can be found for a given key
   * @param mixed
   */
  static public function get($key = null, $default = null) {
    return static::data($key, $default);  
  }

  /**
   * Removes a variable from the request array
   * 
   * @param string $key
   */
  static public function remove($key) {
    unset($_REQUEST[$key]);
    unset(static::$data[$key]);
  }

  /**
   * Returns the current request method
   *
   * @return string POST, GET, DELETE, PUT
   */  
  static public function method() {
    $method = strtoupper(server::get('request_method', 'GET'));
    return ($method == 'HEAD') ? 'GET' : $method;
  }

  /**
   * Returns the request body from POST requests for example
   *
   * @return array
   */    
  static public function body() {
    if(!is_null(static::$body)) return static::$body; 
    @parse_str(@file_get_contents('php://input'), static::$body); 
    return static::$body = static::sanitize((array)static::$body);
  }

  /**
   * Checks if the request is of a specific type: 
   * 
   * - GET
   * - POST
   * - PUT
   * - DELETE
   * - AJAX
   * 
   * @return boolean
   */
  static public function is($method) {
    if($method == 'ajax') {
      return static::ajax();
    } else {
      return (strtoupper($method) == static::method()) ? true : false;
    }
  }

  /**
   * Returns the referer if available
   * 
   * <code>
   * 
   * echo r::referer();
   * // sample result: http://someurl.com
   * 
   * </code>
   * 
   * @param string $default Pass an optional URL to use as default referer if no referer is being found
   * @return string
   */
  static public function referer($default = null) {
    return server::get('http_referer', $default);
  }

  /**
   * Nobody remembers how to spell it
   * so this is a shortcut
   * 
   * <code>
   * 
   * echo r::referrer();
   * // sample result: http://someurl.com
   * 
   * </code>
   * 
   * @param string $default Pass an optional URL to use as default referer if no referer is being found
   * @return string
   */
  static public function referrer($default = null) {
    return static::referer($default);    
  }

  /**
   * Returns the IP address from the 
   * request user if available
   * 
   * @param mixed
   */
  static public function ip() {
    return server::get('remote_addr', '0.0.0.0');
  }

  /**
   * Checks if the request has been made from the command line
   * 
   * @return boolean
   */
  static public function cli() {
    return defined('STDIN') || (substr(PHP_SAPI, 0, 3) == 'cgi' && getenv('TERM'));
  }

  /**
   * Checks if the request is an AJAX request
   * 
   * <code>
   * 
   * if(r::ajax()) echo 'ajax rulez';
   * 
   * </code>
   * 
   * @return boolean
   */
  static public function ajax() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;        
  }

  /**
   * Returns the request scheme
   * 
   * @return string
   */
  static public function scheme() {
    return (server::get('https') && str::lower(server::get('https')) != 'off') ? 'https' : 'http';
  }

  /**
   * Checks if the request is encrypted
   * 
   * @return boolean
   */
  static public function ssl() {
    return (static::scheme() == 'https') ? true : false;
  }

  /**
   * Alternative for static::ssl()
   * 
   * @return boolean
   */
  static public function secure() {
    return static::ssl();
  }

}
