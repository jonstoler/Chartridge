<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Errors
 * 
 * A collection of errors for all objects/classes
 * which can have multiple errors
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Errors extends Collection {

  /**
   * Adds a new error to the collection
   * 
   * @param string $message Either an error message or an existing error object
   * @param mixed $code 
   * @param mixed $data
   */
  public function raise($message = null, $code = 0, $data = null) {

    // pass a single error
    if(is_a($message, 'Kirby\\Toolkit\\Error')) {
      $this->set($message->code(), $message);
    
    // pass an entire errors object
    } else if(is_a($message, 'Kirby\\Toolkit\\Errors')) {
      $this->data = array_merge($this->data, $message->get());

    // pass an entire set of  errors
    } else if(is_object($message) and method_exists($message, 'errors') and is_a($message->errors(), 'Kirby\\Toolkit\\Errors')) {
      $this->data = array_merge($this->data, $message->errors()->get());

    // raise multiple errors at once    
    } else if(is_array($message)) {
      foreach($message as $c => $m) $this->set($c, error::raise($m, $c));

    // create a new error and add it
    } else if(!is_null($message)) {
      $this->set($code, error::raise($message, $code, $data));
    }

  }  

  /**
   * Returns an array with all error messages
   * 
   * @return array
   */
  public function messages() {
    $result = array();
    foreach($this->data as $error) {
      $result[$error->code()] = $error->message();
    }
    return $result;
  }

  /**
   * Returns a nested array with all errors
   * 
   * @return array
   */
  public function toArray() {
    $result = array();
    foreach(parent::toArray() as $code => $error) {
      $result[$code] = $error->toArray();
    }
    return $result;
  }

  /**
   * Returns a json string with all errors
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

}