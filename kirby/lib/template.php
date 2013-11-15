<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Template
 *
 * This is Kirby's super minimalistic 
 * template engine. It loads and fills 
 * templates. Who would have thought that
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Template {

  // a global array of data which is shared between all templates
  static public $globals = array();

  // registered filters for views
  static public $filters = array();

  // optional file callback
  static public $root = null;  

  // the template file path
  public $path;  
  
  // all template variables
  public $data = array();

  // all template options
  public $options = array();
  
  // an optional format, which will be appended to the filename
  public $format = null;

  /**
   * Static instance creator
   * 
   * @param mixed $path The path to the template file
   * @param array $data Predefined data variables for the template
   * @param array $params Additional params for the template
   * @return object
   */
  static public function create($path, $data = array(), $params = array()) {
    return new static($path, $data, $params);
  }

  /**
   * Constructor
   * 
   * @param mixed $path The path to the template file
   * @param array $data Predefined data variables for the template
   * @param array $params Additional params for the template
   */
  public function __construct($path, $data = array(), $params = array()) {
    
    $this->options = array_merge(array('format' => null), (array)$params);
    $this->path    = $path;
    $this->data    = $this->set($data);
    $this->format  = $this->options['format'];

    // try to resolve objects in the path first
    if(is_object($this->path)) $this->file();

    // call a view filter for this view, if available
    if(!is_object($this->path) and array_key_exists($this->path, static::$filters) and is_callable(static::$filters[$this->path])) {
      call_user_func_array(static::$filters[$this->path], array($this));
    }

  }

  /**
   * Smart detector of the template file
   * Can be extended by adding a callback to template::$root
   * 
   * @return string
   */
  public function file() {

    if(is_callable(static::$root)) {
      $root = call_user_func_array(static::$root, array($this));
    } else if(is_string(static::$root)) {
      $root = static::$root;
    } else {
      $root = null;
    }

    // check if the root is already a valid template file
    if($root and is_file($root)) return $root;
    
    // add the template format if available
    $path = ($this->format) ? $this->path . '.' . $this->format : $this->path;

    // attach the php extension if not attached yet
    if(!preg_match('!\.php$!i', $path)) {
      $path .= '.php';
    }

    // build the full root
    return ($root) ? $root . DS . $path : $path;

  }

  /**
   * Returns the entire data array 
   * 
   * @return array
   */
  public function data() {
    return array_merge((array)static::$globals, (array)$this->data);
  }

  /**
   * Returns the options array
   * 
   * @return array
   */
  public function options() {
    return $this->options;
  }

  /**
   * Returns the optional template format
   * 
   * @return string
   */
  public function format() {
    return $this->format;
  }

  /**
   * Sets a new template variable
   * 
   * @param mixed $key
   * @param mixed $value
   */
  public function set($key, $value = null) {
    if(is_array($key)) {
      foreach($key as $k => $v) {
        $this->set($k, $v);
      }
      return $this->data;
    }
    $this->data[$key] = $value;
  }

  /**
   * Magic setter
   * 
   * @param string $key
   * @param mixed $value
   */
  public function __set($key, $value) {
    return $this->set($key, $value);
  }

  /**
   * Magic getter for template variables
   * 
   * @param string $key
   * @return mixed
   */
  public function __get($key) {
    return a::get($this->data, $key);
  }

  /**
   * Checks if the template file exists
   *
   * @return boolean
   */
  public function exists() {
    return is_file($this->file());
  }

  /**
   * Renders the template 
   * 
   * @return string
   */
  public function render() {
    $file = $this->file();
    return is_file($file) ? content::load($file, $this->data()) : '';
  }

  /**
   * Register a view filter
   * 
   * @param string $path
   * @param closure $callback
   */
  static public function filter($file, $callback) {
    static::$filters[$file] = $callback;
  }

  /**
   * Getter and setter for global template variables
   * 
   * @param mixed $key
   * @param mixed $value
   * @return array
   */
  static public function globals($key = null, $value = null) {
    if(is_null($key)) {
      return static::$globals;
    } else if(is_array($key)) {
      return static::$globals = array_merge(static::$globals, $key);
    } else if(is_null($value)) {
      return a::get(static::$globals, $key);
    } else {
      static::$globals[$key] = $value;
      return static::$globals;
    }
  }

  /**
   * Makes it possible to echo the template object
   * 
   * @return string
   */
  public function __toString() {
    return $this->render();
  }

}
