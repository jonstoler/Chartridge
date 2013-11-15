<?php 

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Dimensions
 * 
 * The dimension object is used to provide additional
 * methods for KirbyImage objects and possibly other 
 * objects with width and height to recalculate the size, 
 * get the ratio or just the width and height. 
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Dimensions {

  // the width of the parent object
  protected $width  = 0;

  // the height of the parent object
  protected $height = 0;

  /**
   * Constructor
   *
   * @param int $width
   * @param int $height
   */
  public function __construct($width, $height) {
    $this->width  = $width;
    $this->height = $height;
  }

  /**
   * Returns the width
   * 
   * @return int
   */
  public function width() {
    return $this->width;
  }

  /**
   * Returns the height
   * 
   * @return int
   */
  public function height() {
    return $this->height;
  }

  /**
   * Calculates and returns the ratio
   * 
   * <code>
   * 
   * $dimensions = new Dimensions(1200, 768);
   * echo $dimensions->ratio();
   * // output: 1.5625
   *
   * </code>
   * 
   * @return float
   */
  public function ratio() {
    return ($this->width() / $this->height());
  }

  /**
   * Recalculates the width and height 
   * to fit into the given box. 
   * 
   * <code>
   * 
   * $dimensions = new Dimensions(1200, 768);
   * $dimensions->fit(500);
   * 
   * echo $dimensions->width();
   * // output: 500
   * 
   * echo $dimensions->height();
   * // output: 320
   *
   * </code>
   * 
   * @param int $box the max width and/or height
   * @param boolean $force If true, the dimensions will be upscaled to fit the box if smaller
   * @return object returns this object with recalculated dimensions
   */
  public function fit($box, $force = false) {

    if($this->width == 0 || $this->height == 0) {
      $this->width  = $box;
      $this->height = $box;
      return $this;
    }

    $ratio = $this->ratio();

    if($this->width > $this->height) {
      if($this->width > $box || $force == true) $this->width = $box;
      $this->height = floor($this->width / $ratio);
    } elseif($this->height > $this->width) {
      if($this->height > $box || $force == true) $this->height = $box;
      $this->width = floor($this->height * $ratio);
    } elseif($this->width > $box) {
      $this->width  = $box;
      $this->height = $box;
    }

    return $this;

  }

  /**
   * Recalculates the width and height
   * to fit the given width
   * 
   * <code>
   * 
   * $dimensions = new Dimensions(1200, 768);
   * $dimensions->fitWidth(500);
   * 
   * echo $dimensions->width();
   * // output: 500
   * 
   * echo $dimensions->height();
   * // output: 320
   *
   * </code>
   * 
   * @param int $width the max width
   * @param boolean $force If true, the dimensions will be upscaled to fit the width if smaller
   * @return object returns this object with recalculated dimensions
   */
  public function fitWidth($fit, $force = false) {

    if($this->width <= $fit && !$force) return $this;

    $ratio = $this->ratio();

    $this->width  = $fit;
    $this->height = floor($fit / $ratio);
    
    return $this;      

  }

  /**
   * Recalculates the width and height
   * to fit the given height
   * 
   * <code>
   * 
   * $dimensions = new Dimensions(1200, 768);
   * $dimensions->fitHeight(500);
   * 
   * echo $dimensions->width();
   * // output: 781
   * 
   * echo $dimensions->height();
   * // output: 500
   *
   * </code>
   *    
   * @param int $height the max height
   * @param boolean $force If true, the dimensions will be upscaled to fit the height if smaller
   * @return object returns this object with recalculated dimensions
   */
  public function fitHeight($fit, $force = false) {

    if($this->height <= $fit && !$force) return $this;

    $ratio = $this->ratio();
    
    $this->width  = floor($fit * $ratio);
    $this->height = $fit; 

    return $this;

  }

  /**
   * Recalculates the dimensions by the width and height
   * 
   * @param int $width the max height
   * @param int $height the max width
   * @return object
   */
  public function fitWidthAndHeight($width, $height, $force = false) {

    if($this->width > $this->height) {

      $this->fitWidth($width, $force);
      
      // do another check for the max height
      if($this->height > $height) $this->fitHeight($height);

    } else {

      $this->fitHeight($height, $force);
      
      // do another check for the max width
      if($this->width > $width) $this->fitWidth($width);

    }

    return $this;

  }

  /**
   * Echos the dimensions as width x height
   * 
   * @return string
   */
  public function __toString() {
    return $this->width . ' x ' . $this->height;
  }

}
