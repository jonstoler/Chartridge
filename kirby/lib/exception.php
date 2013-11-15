<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Exception
 * 
 * The extended Kirby exception can handle additional 
 * data, passed to the exception and has a couple shortcut methods.
 * It's been used by the raise() helper by default.
 *
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Exception extends \Exception {

  // stores additional data for the exception
  protected $data;

  /**
   * Constructor
   * 
   * @param mixed $message Either a error message string or a Kirby\Toolkit\Error or Errors object
   * @param int $code The error code
   * @param object An optional previous Exception
   * @param mixed $data Optional data, which should be stored with the exception
   */
  public function __construct($message = '', $code = 0, $previous = null, $data = array()) {

    // throw the passed error object
    if(is_a($message, 'Kirby\\Toolkit\\Error')) {
        
      $this->message = $message->message();
      $this->code    = $message->code();
      $this->data    = $message->data();

    // throw the first error in the error collection
    } else if(is_a($message, 'Kirby\\Toolkit\\Errors')) {
      
      $this->message = $message->first()->message();
      $this->code    = $message->first()->code();
      $this->data    = $message->first()->data();

    } else {

      $this->message  = $message;
      $this->code     = $code;
      $this->data     = $data;
      $this->previous = $previous;

    }

  }

  /**
   * Returns the optional data stored with the exception
   * 
   * @return mixed
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Shortcut for static::getMessage()
   * 
   * @return string
   */
  public function message() {
    return $this->message;
  }

  /**
   * Shortcut for $this->getCode()
   * 
   * @return int
   */
  public function code() {
    return $this->code;
  }

  /**
   * Shortcut for $this->getData()
   * 
   * @return mixed
   */
  public function data() {
    return $this->data;
  }

  /**
   * Shortcut for $this->getPrevious()
   * 
   * @return object
   */
  public function previous() {
    return $this->previous;
  }

  /**
   * Shortcut for $this->getFile()
   * 
   * @return string
   */
  public function file() {
    return $this->file;
  }

  /**
   * Shortcut for $this->getLine()
   * 
   * @return string
   */
  public function line() {
    return $this->line;
  }

  /**
   * Shortcut for $this->getTrace()
   * 
   * @return string
   */
  public function trace() {
    return $this->getTrace();
  }

}