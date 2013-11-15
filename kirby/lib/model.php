<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Model
 * 
 * Base class for building all kinds of models
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Model extends Object {

  // activate/deactivate automatic created and modified timestamps
  static protected $timestamps = false;
    
  // the key name for the primary key
  static protected $primaryKeyName = false;

  // an array of errors after validation
  public $errors = null;
  
  /**
   * Constructor
   * 
   * @param array $data
   */
  public function __construct($data = array()) {

    // make sure the primary key is in the array of allowed keys    
    if(is_array($this->allowedKeys)) {
      $this->allowedKeys[] = $this->primaryKeyName();    
    }

    // add the internal errors collection
    $this->errors = new Errors;

    parent::__construct($data);
  
  }
  
  /**
   * Compares this model to another given model by type and primary key
   * 
   * @param $model
   * @return boolean
   */
  public function is($model) {
    return $model and get_class($this) == get_class($model) and $this->primaryKey() == $model->primaryKey();
  }

  /**
   * Saves the model
   *
   * @return boolean
   */
  public function save() {

    // reset errors
    $this->errors = new Errors;

    // set timestamps
    $this->timestamps();

    // validation
    $this->validate();
                       
    // stop saving process when errors occurred
    if($this->invalid()) return false;

    return ($this->isNew()) ? $this->insert() : $this->update();

  }

  /**
   * Checks if the model is new
   * 
   * @return boolean
   */
  public function isNew() {
    return !$this->primaryKey() ? true : false;
  }

  /**
   * Put your validation code for the model here
   * Use the v method to run the auto validation 
   * 
   * @return boolean
   */
  protected function validate() {    
    return true;
  }

  /**
   * Define this function in your model 
   * to insert the model for the first time
   * 
   * @return boolean
   */
  protected function insert() {
    return true;
  }

  /**
   * Define this function in your model 
   * to update the model
   * 
   * @return boolean
   */
  protected function update() {
    return true;
  }

  /**
   * Define this function in your model 
   * to delete the model
   * 
   * @return boolean
   */
  public function delete() {
    return true;
  }

  /**
   * Returns the name of the primary key column
   * 
   * @return string
   */
  static public function primaryKeyName() {
    return static::$primaryKeyName ? static::$primaryKeyName : c::get('model.primaryKeyName', 'id');  
  }

  /**
   * Returns/sets the value of the primary key
   *
   * @param mixed $value Optional argument to use this as setter 
   * @return mixed
   */
  public function primaryKey($value = null) {
    if(!is_null($value)) {
      // overwrite/set the primary key
      $this->set($this->primaryKeyName(), $value);
      // return the new primary key
      return $this->primaryKey();
    }
    // return the primary key
    return $this->get($this->primaryKeyName());  
  }

  /**
   * Adds correct timestamps to the model if activated 
   * 
   * @return void
   */
  protected function timestamps() {
     
    // check if timestamping is wanted at all
    if(!$this->timestamps) return false;

    // timestamps setup
    $options = array(
      'created'  => c::get('model.timestamps.created',  'created'),
      'modified' => c::get('model.timestamps.modified', 'modified'),
      'format'   => c::get('model.timestamps.format', null),
    );

    // merge custom options for the child model
    if(is_array($this->timestamps)) {
      $options = array_merge($options, $this->timestamps);
    }

    // make sure timestamps are in the allowedKeys array
    if(is_array($this->allowedKeys)) {
      if(!in_array($options['created'],  $this->allowedKeys)) $this->allowedKeys[] = $options['created'];
      if(!in_array($options['modified'], $this->allowedKeys)) $this->allowedKeys[] = $options['modified'];
    }

    // get the current UNIX timestamp
    $time = time();        
    
    // format the timestring if wanted    
    if(!is_null($options['format'])) $time = date($options['format'], $time);
    
    // set the updated time on each update    
    $this->set($options['modified'], $time);
    
    // set the created time only on inserts    
    if($this->isNew() && !isset($this->{$options['created']})) $this->set($options['created'], $time);    

  }

  /**
   * Adds an error message for the given key to the list of validation errors
   * 
   * @param mixed $message Pass a string, array or an entire Validation object
   * @param mixed $code Pass a unique error code
   */
  public function raise($message, $code = null) {  
    $this->errors->raise($message, $code);
  }

  /**
   * Returns the entire array of errors
   * 
   * @return array
   */
  public function errors() {
    return $this->errors;
  }

  /**
   * Returns a specific error for a given code
   * 
   * @param string $code if not specified the first error will be returned
   * @return string
   */
  public function error($code = null) {
    return is_null($code) ? $this->errors->first() : $this->errors->get($code);
  }
  
  /**
   * Checks if the model is invalid
   * 
   * @return boolean
   */        
  public function invalid() {
    return $this->errors->count() > 0;    
  }

  /**
   * Checks if the model is valid
   * 
   * @return boolean
   */
  public function valid() {
    return !$this->invalid();
  }

}