<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * Content
 * 
 * This class handles output buffering,
 * content loading and setting content type headers. 
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Content {
  
  /**
   * Starts the output buffer
   * 
   * <code>
   * 
   * content::start();
   * echo 'some content';
   * echo content::stop();
   * 
   * </code>
   * 
   */
  static public function start() {
    ob_start();
  }

  /**
   * Stops the output buffer and returns its content
   * 
   * <code>
   * 
   * content::start();
   * echo 'some content';
   * echo content::stop();    
   * // echo the content immediatelly
   * 
   * 
   * content::start();
   * echo 'some content';
   * $content = content::stop(true);    
   * // return the content
   * 
   * </code>
   * 
   * @return string
   */
  static public function stop() {
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
  }

  /**
   * Stops the output buffer and echos its content
   */
  static public function flush() {
    ob_end_flush();
  }

  /**
   * Loads content from a passed file
   * 
   * @param  string  $file The path to the file
   * @param  array   $data Additional variables which should be available for the loaded content
   * @param  boolean $return True: return the content of the file, false: echo the content
   * @return mixed
   */
  static public function load($file, $data = array(), $return = true) {
    static::start();
    extract($data);
    require($file);
    $content = static::stop();
    if($return) return $content;
    echo $content;        
  }

}
