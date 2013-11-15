<?php 

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Exif
 * 
 * Reads exif data from a given image asset
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Exif {

  // the parent asset object
  protected $asset = null;
  
  // the raw exif array
  protected $data = null;

  // the camera object with model and make
  protected $camera = null;
  
  // the location object
  protected $location = null;

  // the timestamp
  protected $timestamp = null;
    
  // the exposure value
  protected $exposure = null;
  
  // the aperture value 
  protected $aperture = null;
  
  // iso value
  protected $iso = null;

  // focal length
  protected $focalLength = null;

  // color or black/white
  protected $isColor = null;

  /**
   * Constructor
   * 
   * @param object $asset Asset 
   */
  public function __construct(Asset $asset) {
    $this->asset = $asset;
    $this->parse();
  }

  /**
   * Returns the raw data array from the parser
   * 
   * @return array
   */
  public function data() {
    return $this->data;
  }

  /**
   * Returns the Camera object
   *
   * @return object KirbyExifCamera
   */
  public function camera() {

    if(!is_null($this->camera)) return $this->camera;

    // check for valid exif data
    if(!is_array($this->data)) return null;

    // initialize and return it
    return $this->camera = new ExifCamera($this->data);

  }

  /**
   * Returns the location object
   *
   * @return object ExifLocation
   */
  public function location() {

    if(!is_null($this->location)) return $this->location;

    // check for valid exif data
    if(!is_array($this->data)) return null;

    // initialize and return it
    return $this->location = new ExifLocation($this->data);
  
  }

  /**
   * Returns the timestamp
   *
   * @return string
   */
  public function timestamp() {
    return $this->timestamp;
  }

  /**
   * Returns the exposure
   *
   * @return string
   */
  public function exposure() {
    return $this->exposure;
  }

  /**
   * Returns the aperture
   *
   * @return string
   */
  public function aperture() {
    return $this->aperture;
  }

  /**
   * Checks if this is a color picture
   * 
   * @return boolean
   */
  public function isColor() {
    return $this->isColor;
  }

  /**
   * Checks if this is a bw picture
   * 
   * @return boolean
   */
  public function isBW() {
    return !$this->isColor;
  }

  /**
   * Returns the focal length
   * 
   * @return string
   */
  public function focalLength() {
    return $this->focalLength;
  }

  /**
   * Pareses and stores all relevant exif data
   */
  protected function parse() {
    
    // read the exif data of the asset if possible
    $this->data = @read_exif_data($this->asset->root());
    
    // stop on invalid exif data
    if(!is_array($this->data)) return false;

    // store the timestamp when the picture has been taken
    if(isset($this->data['DateTime'])) {
      $this->timestamp = strtotime($this->data['DateTime']);
    } else {
      $this->timestamp = a::get($this->data, 'FileDateTime', $this->asset->modified());
    }

    // exposure
    $this->exposure = a::get($this->data, 'ExposureTime');

    // iso 
    $this->iso = a::get($this->data, 'ISOSpeedRatings');

    // focal length
    $this->focalLength = a::get($this->data, 'FocalLengthIn35mmFilm');

    // aperture
    $this->aperture = @$this->data['COMPUTED']['ApertureFNumber'];

    // color or bw
    $this->isColor = @$this->data['COMPUTED']['IsColor'];

  }

}