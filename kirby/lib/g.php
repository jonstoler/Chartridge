<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * Globals
 * 
 * The Kirby Globals Class
 * Easy setting/getting of globals
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class G {

  /**
   * Gets an global value by key
   * 
   * <code>
   * 
   * g::get('var1', 'some other value');
   * // returns 'some value' if var1 has been set earlier, otherwise returns 'some other value'
   * 
   * </code>
   *
   * @param  mixed    $key The key to look for. Pass false or null to return the entire globals array. 
   * @param  mixed    $default Optional default value, which should be returned if no element has been found
   * @return mixed
   */
  static public function get($key = null, $default = null) {
    if(empty($key)) return $GLOBALS;
    return a::get($GLOBALS, $key, $default);
  }

  /** 
   * Sets a global by key
   * 
   * <code>
   * 
   * g::set('var1', 'some value');
   * 
   * // later
   * echo g::get('var1');
   * // output: 'some value'
   * 
   * </code>
   * 
   * @param  string  $key The key to define
   * @param  mixed   $value The value for the passed key
   */  
  static public function set($key, $value = null) {
    if(is_array($key)) {
      // set all new values
      $GLOBALS = array_merge($GLOBALS, $key);
    } else {
      $GLOBALS[$key] = $value;
    }
  }

  /**
   * Removes a variable from the GLOBALS array
   * 
   * @param string $key
   */
  static public function remove($key) {
    unset($GLOBALS[$key]);
  }

}
