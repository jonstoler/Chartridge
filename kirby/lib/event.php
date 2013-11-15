<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Event
 * 
 * Attach and trigger events throghout the system
 * There can only be one callback per event. This is not a full blown event handler, 
 * which makes it more simple to use but less powerful in some situations. 
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Event {

  // array with all collected events
  static public $events = array();

  /**
   * Registers a new event. 
   * 
   * @param string $event The name of the event
   * @param func $callback The callback function
   */
  static public function on($event, $callback, $overwrite = false) {
    if(!isset(static::$events[$event]) or $overwrite) static::$events[$event] = array();
    if(is_callable($callback)) static::$events[$event][] = $callback;
    return static::$events;
  }

  /**
   * Remove an event
   * 
   * @param string $event If no name is given, all events will be removed
   */
  static public function off($event = null) {
    if(is_null($event)) return static::$events = array();
    static::$events[$event] = array();
    return static::$events;
  }

  /**
   * Checks if an event is registered
   * 
   * @param string $event The name of the event
   * @return boolean
   */
  static public function exists($event) {
    return !empty(static::$events[$event]);
  }

  /**
   * Returns all events
   * 
   * @param string $event Pass a name of an event to get all callbacks
   * @return array
   */
  static public function events($event = null) {
    if(is_null($event)) return static::$events;
    return a::get(static::$events, $event, array());
  }

  /**
   * Triggers all available events for a given key
   * 
   * @param string $event The name of the event, that should be triggered
   * @param array $arguments An optional array of arguments, which should be passed to the event
   */
  static public function trigger($event, $arguments = array()) {
    
    if(!static::exists($event)) return false;

    // always pass the arguments as array, even if it's just one    
    if(!is_array($arguments)) $arguments = array($arguments);

    // call the events
    foreach(static::$events[$event] as $e) {
      $result = call_user_func_array($e, $arguments);
      // stop all other events from being executed if the result is false 
      if($result === false) break;
    }

  }

}