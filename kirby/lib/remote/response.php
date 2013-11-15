<?php

namespace Kirby\Toolkit\Remote;

use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\XML;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Remote Response
 * 
 * Contains all info about returned data
 * from a sent request
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Response {

  // the entire returned content 
  public $content = null;
  
  // an array of received headers
  public $headers = null;
  
  // an error if available
  public $error = null;
  
  // an error message if available
  public $message = null;
  
  // an array with all the info returned by curl 
  public $info = null;

  /**
   * Returns the curl error if available
   * 
   * @return string
   */
  public function error() {
    return $this->error;
  }

  /**
   * Returns the curl message if available
   * 
   * @return string
   */
  public function message() {
    return $this->message;
  }

  /**
   * Returns the content received from the request
   * 
   * @return string
   */
  public function content() {
    return $this->content;
  }

  /**
   * Checks if the request failed
   * 
   * @return boolean
   */
  public function failed() {
    return !empty($this->error);
  }

  /**
   * Returns all the info from curl 
   *
   * @param string $key an optional key to get only a specific var from the info array
   * @param mixed $default an optional default value to return if the var does not exist. 
   * @return array
   */
  public function info($key = null, $default = null) {
    if(is_null($key)) return $this->info;
    return a::get($this->info, $key, $default);
  }

  /**
   * Returns an array with all received headers
   * 
   * @return array
   */
  public function headers() {
    return $this->headers;
  }

  /**
   * Returns a specific header by array
   * 
   * @param string $key
   * @param mixed $default Optional default value to return if the header could not be found
   * @return string
   */
  public function header($key, $default = null) {
    return a::get($this->headers, $key, $default);
  }

  /**
   * Parses the request content as xml
   * 
   * @return array
   */
  public function xml() {
    return xml::parse($this->content);
  }

  /**
   * Unserializes the request content
   * 
   * @return array or object
   */
  public function unserialize() {
    return unserialize($this->content);
  }

  /**
   * Parses the request content as json
   * 
   * @return object
   */
  public function json() {
    return json_decode($this->content);
  }

  /**
   * Returns the size of the request response
   * 
   * @return int
   */
  public function size() {
    return $this->info('size_download', 0);
  }

  /**
   * Returns the size of the request response as humane readable string
   * 
   * @return string
   */
  public function niceSize() {
    return f::niceSize($this->size());
  }

  /**
   * Returns the mime type of the response
   * 
   * @return string
   */
  public function mime() {
    return $this->info('content_type');
  }

  /**
   * Returns the response code
   * 
   * @return int
   */
  public function code() {
    return $this->info('http_code');
  }

  /**
   * Echos the request content
   */
  public function __toString() {
    return $this->content;
  }

}