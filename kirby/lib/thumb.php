<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Thumb
 * 
 * A simple thumbnail generator using the
 * Asset and Dimensions classes.
 *
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Thumb {

  // the original image Asset object
  public $image = null;
          
  // the internal source object
  public $source = null;

  // the internal tmp object
  public $tmp = null;
          
  // the internal result object
  public $result = null;

  // the options array
  public $options = array();

  // stores the internal error message
  public $error = null;

  /**
   * Constructor
   * 
   * @param mixed $image The image (Asset) object
   * @param array $options Options for the final thumbnail
   */
  public function __construct($image, $options = array()) {

    if(!is_object($image)) {
      if(!f::exists($image)) raise('The given image does not exist');
      $image = new Asset($image);
    } 

    $this->image   = $image;
    $this->options = array_merge($this->defaults(), $options); 
    $this->source  = new Dimensions($image->width(), $image->height());
    $this->tmp     = clone $this->source;
    $this->result  = clone $this->source;

    // calculate the new size
    $this->resize();
        
    // create the thumbnail    
    $this->create();
                          
  }

  /**
   * Returns all default values for the thumb object
   * 
   * @return array
   */
  public function defaults() {

    return array(
      // the width of the final image
      'width' => null, 
      // the height of the final image
      'height' => null,
      // activate cropping (inactive by default)
      'crop' => c::get('thumb.crop', false),
      // set the quality for jpg images
      'quality' => c::get('thumb.quality', 100),
      // the image will be upscaled if smaller than wanted
      'upscale' => c::get('thumb.upscale', false),
      // convert images to grayscale
      'grayscale' => false,
      // set the alt text for the optional image tag
      'alt' => $this->image->name(), 
      // set the class selector for the optional image tag
      'class' => null,
      // set the available memory for GD lib
      'memory' => c::get('thumb.memory', '128M'),
      // convert images to a different format (allowed values: jpg, png, gif)
      'to' => false,
      // set the creator library, which will be used to create the thumbnail
      'creator' => c::get('thumb.creator', 'gdlib'),
      // setup the location and url of the final thumbnail
      'location' => array(
        // the base root of the thumbs folder
        'root' => c::get('thumb.location.root'),
        // the base url of the thumbs folder
        'url' => c::get('thumb.location.url'),
        // the path including the filename for the thumb relative to the base root/url
        'path' => c::get('thumb.location.path', '{safeName}-{hash}-{settings}.{extension}'),
      )
    );

  }

  /**
   * Returns all final options which are applied
   * 
   * @return array
   */
  public function options() {
    return $this->options;
  }

  /**
   * Returns the original image/Asset object
   * 
   * @return object Asset object
   */
  public function image() {
    return $this->image;
  }

  /**
   * Returns the dimensions object of the result
   * the source or the tmp image
   * 
   * @param string $return 'result', 'source' or 'tmp'
   * @return object Dimensions
   */
  public function dimensions($return = 'result') {
    return $this->$return;
  }

  /**
   * Returns the width of the thumbnail
   * 
   * @return int
   */
  public function width() {
    return $this->result->width();  
  }

  /**
   * Returns the height of the thumbnail
   * 
   * @return int
   */
  public function height() {
    return $this->result->height();  
  }

  /**
   * Returns the ratio of the thumbnail
   * 
   * @return float
   */
  public function ratio() {
    return $this->result->ratio();  
  }

  /**
   * Returns the mime type of the thumbnail
   * 
   * @return string
   */
  public function mime() {
    return f::mime($this->root());
  }

  /**
   * Returns the extension of the thumbnail
   * 
   * @return string
   */
  public function extension() {
    // if convertion is activated, also convert the extension
    return ($this->options['to']) ? $this->options['to'] : $this->image->extension(); 
  }

  /**
   * Generates and returns the full html tag for the thumbnail
   * 
   * @param array $attr An optional array of attributes, which should be added to the image tag
   * @return string
   */
  public function tag($attr = array()) {

    // don't return the tag if the url is not available
    if(!$this->url()) return false;
  
    $attr = array_merge(array(
      'width'  => $this->result->width(),
      'height' => $this->result->height(),
      'alt'    => $this->options['alt'],
      'class'  => $this->options['class'],
    ), $attr);

    return html::img($this->url(), $attr);
    
  }
  
  /**
   * Builds and returns the path to the file 
   * relative to the location root/url
   * 
   * Available placeholders: 
   * 
   * {settings}       A generic unique string of thumb settings (i.e 100-200-0-0-1)
   * {extension}      The extension of the thumb image 
   * {name}           The raw file name without the extension
   * {safeName}       A safe version of the name without the extension
   * {filename}       The raw filename with extension
   * {safeFilename}   A safe version of the filename with extension
   * {width}          The width of the final image
   * {height}         The height of the final image
   * {hash}           A md5 hashed version of the source's root
   * 
   * @return string
   */
  public function path() {

    // get the path from the options array
    $path = $this->options['location']['path'];

    // build the settings string
    $settings = array(
      ($this->options['width'])   ? $this->options['width']   : 0,
      ($this->options['height'])  ? $this->options['height']  : 0,
      ($this->options['upscale']) ? $this->options['upscale'] : 0,
      ($this->options['crop'])    ? $this->options['crop']    : 0,
      $this->options['quality']
    );

    // replace all available template vars
    return str::template($path, array(
      'settings'     => implode('-', $settings),
      'extension'    => $this->extension(),
      'name'         => $this->image->name(),
      'filename'     => $this->image->filename(),
      'safeName'     => f::safeName($this->image->name()),
      'safeFilename' => f::safeName($this->image->name()) . '.' . $this->extension(),
      'width'        => $this->width(),
      'height'       => $this->height(),
      'hash'         => md5($this->image->root()),
    ));

  }

  /**
   * Returns the full root to the 
   */
  public function root() {
    return $this->options['location']['root'] . DS . $this->path();
  }

  /**
   * Returns the absolute url for the thumbnail
   * 
   * @return string
   */
  public function url() {
    return $this->exists() ? @$this->options['location']['url'] . '/' . $this->path() : $this->image->url();
  }

  /**
   * Returns the data uri for the thumbnail
   * 
   * @return string
   */
  public function uri() {
    return f::uri($this->root());
  }
  
  /**
   * Returns the directory where the thumb is located
   * 
   * @return string
   */
  public function dir() {
    return dirname($this->root());
  }

  /**
   * Returns the name of the directory where the thumb is located
   * 
   * @return string
   */
  public function dirname() {
    return basename($this->dir());
  }

  /**
   * Returns the filename of the thumb
   * 
   * @return string
   */
  public function filename() {
    return f::filename($this->path());
  }
   
  /**
   * Returns the name of the file without extension   
   *
   * @return string
   */
  public function name() {
    return f::name($this->filename());
  }

  /**
   * Checks if the thumbnail exists
   * 
   * @return boolean
   */
  public function exists() {
    return file_exists($this->root());    
  }

  /**
   * Returns the unix timestamp of the last modification 
   * 
   * @return int
   */
  public function modified() {
    return f::modified($this->root());  
  }

  /**
   * Returns the raw file size of the generated thumb
   * 
   * @return int
   */
  public function size() {
    return f::size($this->root());    
  }

  /**
   * Returns the human readable file size of the generated thumb
   * 
   * @return string
   */
  public function niceSize() {
    return f::niceSize($this->root());    
  }

  /**
   * Checks if the thumbnail is writable
   * 
   * @return boolean
   */
  public function isWritable() {  
    if($this->exists()) return f::writable($this->root());
    return f::writable(dirname($this->root()));
  }

  /**
   * Checks if the thumbnail is readable
   * 
   * @return boolean
   */
  public function isReadable() {
    return f::readable($this->root());  
  }

  /**
   * Checks if the thumbnail is grayscale
   * 
   * @return boolean
   */
  public function isGrayscale() {
    return $this->options['grayscale'];  
  }

  /**
   * Checks if the source has been modified since the last update
   * 
   * @return boolean
   */
  public function isModified() {
    return ($this->image()->modified() > $this->modified()) ? true : false;
  }

  /**
   * Resizes the dimensions of the thumb object
   * 
   * @return array
   */
  protected function resize() {
        
    if($this->options['crop']) {

      if(!$this->options['width'])  $this->options['width']  = $this->options['height'];      
      if(!$this->options['height']) $this->options['height'] = $this->options['width'];      

      // overwrite the result 
      $this->result = new Dimensions($this->options['width'], $this->options['height']);

      // compare rations between the original image and the result                      
      if($this->tmp->ratio() > $this->result->ratio()) {
        // fit the height of the source
        $this->tmp->fitHeight($this->options['height'], $upscale = true);
      } else {
        // fit the width of the source
        $this->tmp->fitWidth($this->options['width'], $upscale = true);
      }
      
    } else {
        
      // if there's a width and a height fit both
      if($this->options['width'] && $this->options['height']) {
        $this->result->fitWidthAndHeight($this->options['width'], $this->options['height'], $this->options['upscale']);
      // fit only the width
      } elseif($this->options['width']) {
        $this->result->fitWidth($this->options['width'], $this->options['upscale']);
      // fit the height
      } elseif($this->options['height']) {
        $this->result->fitHeight($this->options['height'], $this->options['upscale']);
      } 
        
    }
            
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
   * Creates the thumbnail and tries to save it
   * 
   * @return array
   */
  protected function create() {
    
    // if the thumb already exists and the source hasn't been updated 
    // we don't need to generate a new thumbnail
    if($this->exists() && !$this->isModified()) return true;

    // reset a possible previous error 
    $this->error = null;
    
    // run the creator
    try {
      $class  = '\\Kirby\\Toolkit\\Thumb\\Creator\\' . $this->options['creator'];
      $object = new $class($this); 

      $object->run();
    } catch(\Exception $e) {
      $this->error = new Error($e->getMessage(), 'create');
    }

  }

  /**
   * Makes it possible to simply echo the object
   * to get a full tag for the thumb
   */
  public function __toString() {
    return $this->tag();
  }

}