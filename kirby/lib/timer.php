<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * Timer
 * 
 * A handy little timer class to do some
 * code profiling
 *
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Timer {

	// global timer store
	protected static $timer  = null;
	
	// sub timers
	protected static $timers = array();

	/**
	 * Starts a timer
	 * 
	 * @param string $key
	 * @return double
	 */
	static public function start($key = null) {

		// universal timer killer
		if(c::get('timer') === false) return false;

		$time = explode(' ', microtime());
		$time = (double)$time[1] + (double)$time[0];
		
		if(is_null($key)) {
			// global timer
			static::$timer = $time;
		} else {
			// sub timer
			static::$timers[$key] = $time;	
		}
		
		// return the start time
		return $time;

	}

	/**
	 * Stops and retrieves a timer
	 * 
	 * @param string $key
	 * @return double
	 */
	static public function stop($key = null) {
		
		// universal timer killer
		if(c::get('timer') === false) return false;
		
		$time  = explode(' ', microtime());
		$time  = (double)$time[1] + (double)$time[0];
		$timer = (is_null($key)) ? static::$timer : a::get(static::$timers, $key);
	
		return round(($time-$timer), 5);
	
	}

}