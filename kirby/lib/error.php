<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Error
 * 
 * Creates a simple, reusable error object
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Error {

  public $message;
  public $code;
  public $data;

  /**
   * Static handler to create a new error object
   * 
   * @param string $message
   * @param mixed $code
   * @param mixed $data
   * @return object
   */
  static public function raise($message, $code = 0, $data = null) {
    return new static($message, $code, $data);
  }

  /**
   * Constructor
   * 
   * @param string $message
   * @param mixed $code
   * @param mixed $data
   */
  public function __construct($message, $code = 0, $data = null) {
    $this->message = $message;
    $this->code    = $code;
    $this->data    = $data;
  }

  /**
   * Returns the error message
   * 
   * @return string
   */
  public function message() {
    return $this->message;
  }

  /**
   * Returns the error code
   * 
   * @return mixed
   */
  public function code() {
    return $this->code;
  }

  /**
   * Returns optional data
   * 
   * @return array
   */
  public function data() {
    return $this->data;
  }

  /**
   * Makes it possible to echo the entire error object
   * 
   * @return string
   */
  public function __toString() {
    return $this->message; 
  }

  /**
   * Returns the error object as clean array
   * 
   * @return array
   */
  public function toArray() {
    return array(
      'message' => $this->message,
      'code'    => $this->code,
      'data'    => $this->data,
    );    
  }

  /**
   * Returns the error object as json string
   * 
   * @return string
   */
  public function toJSON() {
    return json_encode($this->toArray());
  }

  /**
   * Alternate version of toJSON
   */
  public function json() {
    return $this->toJSON();
  }

  /**
   * HTTP Error header defined by the error code
   * 
   * @param boolean $send if false, the header will be returned
   * @return mixed
   */
  public function header($send = true) {

    $codes = array_merge(array(
      400 => 'Bad Request',
      401 => 'Unauthorized',
      402 => 'Payment required',
      403 => 'Forbidden',
      404 => 'Not found',
      405 => 'Method not allowed',
      //...
      500 => 'Internal Server Error',
      501 => 'Not implemented',
      502 => 'Bad Gateway',
      503 => 'Service Unavailable'
    ), (array)c::get('error.codes'));

    $code = (!$this->code or !array_key_exists($this->code, $codes)) ? 400 : $this->code;

    $message  = a::get($codes, $code, 'Something went wrong');
    $protocol = server::get('SERVER_PROTOCOL', 'HTTP/1.0');
    $header   = $protocol . ' ' . $code . ' ' . $message;

    if(!$send) return $header;

    header($header);

  }
  
  /**
   * Converts the error into an Exception, which can be thrown easily. 
   * 
   * @return object
   */
  public function toException() {
    return new Exception($this);
  }

}