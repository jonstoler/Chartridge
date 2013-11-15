<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Object
 * 
 * A core object with magic getters and setters 
 * and some helpful helper methods
 *
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Object {
    
  // internal store for all object data
  protected $data = array();
  
  // store for old, overwritten data
  protected $old = array();

  // optional list of allowed keys
  protected $allowedKeys = null;

  /** 
   * Initializes a new object
   * 
   * @param array $data an optional array of data for the object
   * @return void
   */
  public function __construct($data = array()) {
    // make sure that objects will be properly converted to arrays
    if(is_array($data) or is_object($data)) {
      if($data instanceof Object) $data = $data->toArray();
      if(is_array($data)) $this->set($data);
    }
    $this->init();
  }

  /** 
   * Alternative to safely construct stuff 
   * without messing up the object construction
   */
  public function init() {
    
  }
    
  /** 
   * Magic call function
   * 
   * This adds additional method getters to the
   * object. Stuff like `$myobject->myvalue()`
   * 
   * @param string $method The name of the called method
   * @param mixed  $arguments Those do not need to be specified, but the argument is required by PHP
   * @return mixed If the getter finds something, that value will be returned. Otherwise it will return null or void
   */
  public function __call($method, $arguments = null) {    
    array_unshift($arguments, $method);
    return call_user_func_array(array($this, 'get'), $arguments);
  }

  /** 
   * Magic setter
   * 
   * With the magic setter you can basically set any
   * value for the object just like: 
   * `$myobject->myvalue = 'My value'`
   * 
   * @param mixed $key The setter key (auto-prefilled by PHP)
   * @param mixed $value The value, which should be set for the key
   */
  public function __set($key, $value) {
    $this->set($key, $value);
  }

  /** 
   * The core setter
   * 
   * This will be invoked by the magic setter 
   * and can be used as setter itself:
   * `$myobject->set('myvalue', 'My value')`
   * 
   * @param mixed $key The setter key. If you pass an array you can set multiple values at once
   * @param mixed $value The optional value for the given key. 
   * @return object Returns the same object so set methods are chainable
   */
  public function set($key, $value=null) {

    if(is_array($key)) {
      foreach($key as $key => $value) {
        $this->set($key, $value);
      }
      return $this;
    }

    // custom setter name
    $method = 'set' . $key;
    
    // check for custom setters
    if(method_exists($this, $method)) {
      // call the custom setter      
      $this->{$method}($value);
    } else {
      $this->write($key, $value);  
    }

    return $this;

  }

  /**
   *
   * Writes the value to the internal data array
   * 
   * This can be used in custom setters to 
   * directly write a value to the data array 
   * instead of using set, which would cause a 
   * endless loop in a custom setter.   
   * 
   * @param string $key The name for the key in the $data array
   * @param mixed $value Can be anything
   */
  public function write($key, $value) {    
    // check for allowed keys
    if(is_array($this->allowedKeys) && !in_array($key, $this->allowedKeys)) raise('The following key is not allowed in the object: ' . $key);
    
    // store the old value
    if(isset($this->data[$key])) {
      $this->old[$key] = $this->data[$key];
    }

    // before overwriting it
    $this->data[$key] = $value;    
  }

  /**
   *
   * The magic getter
   * 
   * With this it is possible to get values from 
   * the object just like this:   
   * `echo $myobject->somevalue;`
   * 
   * @param string $key The key name (auto-filled by PHP)
   * @return mixed Null if nothing can be found for this key or otherwise the stored data for this key
   */  
  public function __get($key) {
    return $this->get($key);
  }

  /**
   *
   * The core getter
   * 
   * This is invoked by the magic getter and 
   * can be used as well to get values from the object
   * `echo $myobject->get('somevalue');`
   * 
   * This has the nice sideeffect of offering a default
   * fallback if the value can't be found:
   * `echo $myobject->get('somevalue', 'my default value');
   * 
   * It can even be used as a full getter for the entire array of values
   * `print_r($myobject->get())`
   * 
   *   
   * @param mixed $key The optional key name to search for
   * @param mixed $default The fallback value if the key cannot be found
   * @return mixed whatever you stored for that key
   */  
  public function get($key = null, $default = null) {
    
    if(is_null($key)) return $this->toArray();
        
    $method = 'get' . $key;
    // check for custom getters
    if(method_exists($this, $method)) {
      // call the custom getter
      return $this->$method($default);
    } else {
      return $this->read($key, $default);
    }
  }

  /**
   *
   * Reads the value from the internal data array
   * 
   * This can be used in custom getters to 
   * directly read a value from the data array 
   * instead of using get or a magic getter, which would 
   * cause a endless loop in a custom setter.   
   * 
   * @param string $key The name for the key in the $data array
   * @param mixed $default An optional fallback value if nothing can be found for the key
   * @return mixed Whatever is stored for the key
   */
  public function read($key = null, $default = null) {
    if(is_null($key)) return $this->data;
    return (isset($this->data[$key])) ? $this->data[$key] : $default;  
  }

  /**
   *
   * Makes it possible to unset object values
   * 
   * i.e.: unset($myobject->myvalue);
   * 
   * @param string $key The name for the key in the $data array (is auto-filled by PHP)
   */
  public function __unset($key) {
    unset($this->data[$key]);
  }

  /**
   *
   * Checks if a value is available for the given key
   * 
   * i.e.: isset($myobject->myvalue);
   * 
   * @param string $key The name for the key in the $data array (is auto-filled by PHP)
   */
  public function __isset($key) {
    return isset($this->data[$key]);
  }

  /**
   * Removes all data from the $data array
   * Can also be used to set a fresh set of 
   * data at once. 
   * 
   * @param array $data a new set of data for the $data array
   */
  public function reset($data = array()) {
    // remove the old data
    $this->data = array();
    // set the new data    
    if(!empty($data)) $this->set($data);
  }

  /** 
   * Adds a new value to the object
   * 
   * It's basically an alternative for set()
   * 
   * @param string $key
   * @param mixed $value
   * @return object returns the current object to make it chainable
   */  
  public function add($key, $value) {
    return $this->set($key, $value);
  }
  
  /** 
   * Replaces a value of the object
   * 
   * It's yet another alternative for set()
   * 
   * @param string $key
   * @param mixed $value
   * @return object returns the current object to make it chainable
   */  
  public function replace($key, $value) {
    return $this->set($key, $value);
  }

  /** 
   * Removes a value from the object
   * 
   * It's an alternative for unset($object->key)
   * 
   * @param string $key
   * @return object returns the current object to make it chainable
   */  
  public function remove($key = null) {

    if(is_null($key)) { 
      $this->reset();
      return $this;
    }

    unset($this->$key);
    return $this;
  }

  /**
   * Get stuff from the old array
   * 
   * @param string $key Optional key. If not given, the entire array will be returned
   * @param mixed $default Optional default value if the key does not exist
   * @return mixed
   */
  public function old($key = null, $default = null) {
    if(is_null($key)) return $this->old;
    return a::get($this->old, $key, $default);
  }

  /**
   * Gets all keys from the $data array
   * 
   * @return array An array of key names
   */
  public function keys() {
    return array_keys($this->data);
  }

  /**
   * Converts the object to an array
   * Automatically takes care of removing 
   * the internal key format (_keyname)
   * and returns a clean array of keys and values
   * 
   * @return array 
   */
  public function toArray() {
    return $this->data;
  }

  /**
   * Converts the object to an array first
   * and afterwards to a json string
   * 
   * @return string
   */
  public function toJSON() {
    return json_encode($this->toArray());
  }

  /**
   * Alternate version of toJSON()
   * 
   * @return string
   */
  public function json() {
    return $this->toJSON();
  }

  /**
   * Converts the object to a simple 
   * string with all keys and values from the 
   * internal array to make it easier
   * to debug the current object and its content
   * 
   * @return string
   */
  public function __toString() {      
    return a::show($this->toArray(), false);
  }

}
