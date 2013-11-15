<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Validator
 * 
 * Base class for all validators
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Validator {

  // the used validation method
  protected $method;
  
  // the entire set of data
  protected $data;
  
  // the current value, which should be validated
  protected $value;
  
  // the name of the field in the data array, which should be validated
  protected $attribute;
  
  // a set of additional arguments/options for the validation
  protected $options;
  
  // the validation result (boolean)
  protected $result = false;
  
  // the default message
  protected $message = 'The {attribute} is invalid';
  
  // a set of variables, which should be replaced in the error message
  protected $vars = array();

  // on runtime installed validators
  static $installed = array();

  /**
   * Installs a new set of validators
   * Pass a single key and file to install a single additional validator
   * Pass an array of keys and values to install an entire set of validators
   * Pass a directory without second argument to install all validators within that directory
   * 
   * @param mixed $key
   * @param mixed $file
   * @return true
   */
  static public function install($key, $file = null) {
    
    // install an array of validators
    if(is_array($key)) {
      foreach($key as $k => $f) static::install($k, $f);
      return true;
    }  

    // install an entire directory of validators
    if(is_null($file) && is_dir($key)) {
      $dir   = realpath($key);
      $files = dir::read($dir);
      foreach($files as $file) {
        $key = f::name($file);
        static::install($key, $dir . DS . $file);
      }
      return true;
    }

    static::$installed[strtolower($key)] = $file;

  }

  /**
   * Initializes a new validator
   * 
   * @param string $method the used validation method (refers to the class- and file name of the validator)
   * @param array $data A set of data the value should be extracted from. Pass a single value for simple, data-independent validators. 
   * @param string $attribute The name of the field in the data array, which should be used to extract the value. Pass null in connection with a single data value. 
   * @param array $options An additional array of arguments for the validation
   * @return object Validator 
   */
  static public function create($method, $data, $attribute = null, $options = array()) {

    $method = strtolower($method);
    $file   = isset(static::$installed[$method]) ? static::$installed[$method] : dirname(__FILE__) . DS . 'validator' . DS . $method . '.php';
    $class  = 'Kirby\\Toolkit\\Validator\\' . $method; 

    // check for an existing validator
    if(!file_exists($file)) raise('invalid validator: ' . $method);

    require_once($file);

    return new $class($method, $data, $attribute, $options);

  }
  
  /**
   * Constructor
   * 
   * @param string $method the used validation method (refers to the class- and file name of the validator)
   * @param array $data A set of data the value should be extracted from. Pass a single value for simple, data-independent validators. 
   * @param string $attribute The name of the field in the data array, which should be used to extract the value. Pass null in connection with a single data value. 
   * @param array $options An additional array of arguments for the validation
   */
  public function __construct($method, $data, $attribute, $options = array()) {
    $this->method    = $method;
    $this->data      = $data;
    $this->attribute = $attribute;
    $this->options   = $options;
    
    // set the value, which should be validated
    if($attribute == null) {
      $this->attribute = 'value';
      $this->value     = $this->data;
    } else {
      $this->value = a::get($data, $attribute);
    }

    // run validation
    $this->result = $this->validate(); 
    
    // set vars for the final message
    $this->vars = $this->vars();
  }

  /**
   * Placeholder for the actual validation action in child classes
   * 
   * @return boolean
   */
  public function validate() {
    return false;
  }

  /**
   * Returns the used attribute name
   * 
   * @return string
   */
  public function attribute() {
    return $this->attribute;
  }

  /**
   * Returns the used validation method
   * 
   * @return string
   */
  public function method() {
    return $this->method;
  }

  /**
   * Returns the validated value
   * 
   * @return mixed
   */
  public function value() {
    return $this->value;
  }

  /**
   * Checks if the validation failed
   *
   * @return boolean
   */
  public function failed() {
    return !$this->result;
  }

  /**
   * Checks if the validation succeeded
   *
   * @return boolean
   */
  public function passed() {
    return !$this->failed();
  }

  /**
   * Use this in child classes to return a set
   * of vars, which should be replaced in the 
   * final error message
   * 
   * @return array
   */
  public function vars() {
    return array();
  }

  /**
   * Returns the error message
   * 
   * @param string $message Optional custom message to overwrite the default message
   * @param string $attribute Optional custom attribute name to overwrite the default attribute
   * @return string
   */
  public function message($message = null, $attribute = null) {

    if(!$this->failed()) return null;

    // if no custom message is set…
    if(is_null($message)) {
      // try to load the message from the language array and fallback to the set default message
      $message = l::get('validation.messages > ' . $this->method, $this->message);
    }

    // set some default vars to be replaced in the message
    $defaults = array(
      'attribute' => (is_null($attribute)) ? $this->attribute : $attribute,
      'value'     => $this->value
    );
    
    // get an array of all variables, which should be replaced in the message
    $vars = array_merge($defaults, $this->vars);

    // switch multiple error message templates
    if(is_array($message)) {

      if(is_numeric($this->value)) {
        $message = a::get($message, 'numeric');
      } else if(is_string($this->value)) {
        $message = (f::exists($this->value)) ? a::get($message, 'file') : a::get($message, 'string');
      } else if(is_array($this->value)) {
        $message = a::get($message, 'array');
      }

    }

    // replace all vars in the text with available keys
    $message = str::template($message, $vars);

    // return the full message
    return $message;

  }

  /**
   * Return the error message for this validator if available
   * 
   * @param string $message Optional custom message to overwrite the default message
   * @param string $attribute Optional custom attribute name to overwrite the default attribute
   * @return object Error object
   */
  public function error($message = null, $attribute = null) {
    return error::raise($this->message($message, $attribute), $this->attribute);
  }

}