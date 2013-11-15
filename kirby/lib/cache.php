<?php

namespace Kirby\Toolkit;

use Exception;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * Cache 
 * 
 * The ultimate cache wrapper for 
 * all available drivers
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Cache {

  // the current driver
  static protected $driver;

  /**
   * Connect a cache driver
   * Check out the driver for more details
   * on how to setup individual connections
   * 
   * @param string $driver The name of the driver. ie. 'file'
   * @param array $params Additional params for the driver connection
   * @return object The cache driver object
   */
  static public function connect($driver, $params = array()) {

    // driver class file
    $file  = dirname(__FILE__) . DS . 'cache' . DS . 'driver' . DS . $driver . '.php';
    $class = 'Kirby\\Toolkit\\Cache\\Driver\\' . $driver;

    if(!file_exists($file)) throw new Exception('The cache driver does not exist: ' . $driver);

    // load the driver class
    require_once($file);

    return static::$driver = new $class($params);

  }

  /**
   * Returns the currently connected driver
   * 
   * @return object
   */
  static public function driver() {
    if(is_null(static::$driver)) throw new Exception('No cache driver connected yet');
    return static::$driver;
  }

  /**
   * Write an item to the cache for a given number of minutes.
   *
   * <code>
   *    // Put an item in the cache for 15 minutes
   *    Cache::set('value', 'my value', 15);
   * </code>
   *
   * @param  string  $key
   * @param  mixed   $value
   * @param  int     $minutes
   * @return void
   */
  static public function set($key, $value, $minutes = null) {
    return static::driver()->set($key, $value, $minutes);
  }

  /**
   * Get an item from the cache.
   *
   * <code>
   *    // Get an item from the cache driver
   *    $value = Cache::get('value');
   *
   *    // Return a default value if the requested item isn't cached
   *    $value = Cache::get('value', 'default value');
   * </code>
   *
   * @param  string  $key
   * @param  mixed   $default
   * @return mixed
   */
  static public function get($key, $default = null) {
    return static::driver()->get($key, $default);
  }

  /**
   * Checks when an item in the cache expires
   * 
   * @param string $key
   * @return int
   */
  static public function expires($key) {
    return static::driver()->expires($key);
  }

  /**
   * Checks if an item in the cache is expired
   * 
   * @param string $key
   * @return int
   */
  static public function expired($key) {
    return static::driver()->expired($key);
  }

  /**
   * Checks when the cache value has been created
   * 
   * @param string $key
   * @return int UNIX timestamp
   */
  static public function created($key) {
    return static::driver()->created($key);
  }  

  /**
   * Alternate version for cache::created($key);
   * 
   * @param string $key
   * @return int UNIX timestamp
   */
  static public function modified($key) {
    return static::driver()->modified($key);
  }  

  /**
   * Determine if an item exists in the cache.
   *
   * @param  string  $key
   * @return boolean
   */
  static public function exists($key) {
    return static::driver()->exists($key);
  }

  /**
   * Remove an item from the cache
   * 
   * @param string $key
   * @return boolean
   */
  static public function remove($key) {
    return static::driver()->remove($key);
  }

  /**
   * Flush the entire cache
   * 
   * @return boolean
   */
  static public function flush() {
    return static::driver()->flush();
  }

}