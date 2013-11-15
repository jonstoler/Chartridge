<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Upload
 * 
 * File Upload class
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Upload {

  // stores all options for the upload
  protected $options = array();
  
  // the key for the $_FILES array
  protected $key = null;
  
  // the source file info from the $_FILES array
  protected $source = null;
  
  // the destination for the upload
  protected $file = null;
  
  // the detected mime type of the uploaded file
  protected $mime = null;
  
  // the detected extension of the uploaded file
  protected $extension = null;

  // the filename without extension
  protected $name = null;
  
  // the converted safe filename 
  protected $safeName = null;
  
  // possible error messages
  protected $error = null;

  /**
   * Constructor
   * 
   * @param string $key
   * @param string $file
   * @param array $params Check defaults for possible options
   */
  public function __construct($key, $file, $params = array()) {

    $defaults = array(
      'overwrite' => c::get('upload.overwrite', true),
      'allowed'   => c::get('upload.allowed'),
      'validate'  => null, 
    );

    $this->options     = array_merge($defaults, $params);
    $this->key         = $key;
    $this->source      = $this->source();
    $this->mime        = $this->mime();
    $this->name        = $this->name();
    $this->safeName    = $this->safeName();
    $this->extension   = $this->extension();
    $this->file        = $this->file($file);

    // validate the uploaded file
    $this->validate();

    // move the uploaded file if everything went well so far
    $this->move();

  }

  /**
   * Returns the source file array from $_FILES[$this->key]
   * 
   * @return array
   */
  public function source() {

    if(!is_null($this->source)) return $this->source;

    $default = array(
      'name'     => null,
      'type'     => null,
      'tmp_name' => null,
      'error'    => null,
      'size'     => null
    );

    return $this->source = array_merge($default, a::get($_FILES, $this->key, array()));

  }

  /**
   * Returns the detected mime type of 
   * the uploaded file
   * 
   * @return string
   */
  public function mime() {
    if(!is_null($this->mime)) return $this->mime;
    return $this->mime = f::mime($this->source['tmp_name']);
  }

  /**
   * Returns the raw filename of the 
   * uploaded file including extension
   * 
   * @return string
   */
  public function filename() {
    return $this->source['name']; 
  }

  /**
   * Returns the raw name of the uploaded
   * file without extension
   * 
   * @return string
   */
  public function name() {
    if(!is_null($this->name)) return $this->name;
    return $this->name = f::name($this->filename());
  }

  /**
   * Returns a safe version of the uploaded file name
   * without any special characters or other unwanted stuff
   * 
   * @return string
   */
  public function safeName() {
    if(!is_null($this->safeName)) return $this->safeName;
    return $this->safeName = f::safeName($this->name());
  }

  /**
   * Returns the extension of the uploaded file
   * 
   * @return string
   */
  public function extension() {
    if(!is_null($this->extension)) return $this->extension;
    
    // try to detect the extension from the uploaded file name
    $extension = f::extension($this->source['name']);
    
    // if no extension is set, try to guess it
    if(empty($extension)) $extension = f::mimeToExtension($this->mime);
    
    // return the final extension
    return $this->extension = $extension;
  
  }

  /**
   * Returns the raw file size of the upload
   * 
   * @return int
   */
  public function size() {
    return $this->source['size'];
  }

  /**
   * Returns a human-readable version of the upload size
   * 
   * @return string
   */
  public function niceSize() {
    return f::niceSize($this->size());
  }

  /**
   * Returns the destination directory
   * 
   * @return string
   */
  public function dir() {
    return dirname($this->file());
  }

  /**
   * Returns the full path to the destination file
   * This will replace all placeholders in the file name
   * Check replace for available placeholders
   * 
   * @param string $file Filename template. Leave empty to use this as getter
   * @return string
   */
  public function file($file = null) {
  
    if(is_null($file)) return $this->file;

    return str::template($file, array(
      'name'         => $this->name(),
      'filename'     => $this->filename(),
      'safeName'     => $this->safeName(),
      'safeFilename' => $this->safeName() . '.' . $this->extension(),
      'extension'    => $this->extension(),
    ));

  }

  /**
   * Raises a new error
   * 
   * @param string $message
   * @param string $code The internal error code
   * @return false
   */
  public function raise($message, $code) {
    return $this->error = error::raise($message, $code);
  }

  /**
   * Returns the error message if available
   * 
   * @return string
   */
  public function error() {
    return $this->error;
  }

  /**
   * Returns the error message if available
   * 
   * @return string
   */
  public function message() {
    return $this->error;
  }

  /**
   * Checks if the upload failed
   * 
   * @return boolean
   */
  public function failed() {
    return !is_null($this->error);
  }

  /**
   * Validates the uploaded file
   * 
   * @return boolean
   */
  protected function validate() {

    if(is_null($this->source['name']) || is_null($this->source['tmp_name'])) {
      return $this->raise('The file has not been found', 'missing-file');
    }

    if($this->source['error'] != 0) {
      return $this->raise('The upload failed', 'invalid-upload');
    }

    if(file_exists($this->file()) && $this->options['overwrite'] === false) {
      return $this->raise('The file exists and cannot be overwritten', 'file-exists');
    }

    if($this->size() > $this->maxSize()) {
      return $this->raise('The file is too big', 'too-big');
    }

    if(is_array($this->allowed()) && !in_array($this->mime(), $this->allowed())) {
      return $this->raise('The file type is not allowed', 'invalid-file');
    }

    if(is_callable($this->options['validate'])) {
      return $this->options['validate']($this);
    }

    return true;

  }

  /**
   * Moves the uploaded file to the destination
   *
   * @return boolean
   */
  protected function move() {

    // don't move the upload if the validation failed
    if($this->failed()) return false;

    // try to move and raise an error if something goes wrong
    if(!f::copy($this->source['tmp_name'], $this->file())) {
      return $this->raise('The file could not be moved to the server', 'move-error');
    }

    return true;

  }

  /**
   * Returns an array of allowed mime types or null if everything is allowed
   * 
   * @return mixed
   */
  protected function allowed() {
    return $this->options['allowed'];
  }

  /**
   * Returns the max allowed upload size
   *
   * @return int
   */
  static public function maxSize() {

    if($size = c::get('upload.maxsize')) return $size;

    $size = ini_get('post_max_size');
    $size = trim($size);
    $last = strtolower($size[strlen($size)-1]);
    switch($last) {
      case 'g':
        $size *= 1024;
      case 'm':
        $size *= 1024;
      case 'k':
        $size *= 1024;
    }
    return $size;    

  }

}
