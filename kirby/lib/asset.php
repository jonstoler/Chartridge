<?php 

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Asset
 * 
 * The Asset class can represent any file and help with common stuff 
 * like fetching the mime type, size or dimensions (for images)
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Asset {

  // the full public url to the file
  protected $url = null;

  // the full root/path of the file
  protected $root = null;

  // the filename without directory including extension
  protected $filename = null;

  // the filename without extension 
  protected $name = null;

  // the parent directory
  protected $dir = null;

  // the name of the parent directory
  protected $dirname = null;

  // the file extension without dot (jpg, gif, etc)
  protected $extension = null;

  // the detected file type  
  protected $type = null;

  // unix timestamp of the last modification date
  protected $modified = null;

  // the raw file size
  protected $size = null;

  // a human readable file size (i.e. 1 MB)
  protected $niceSize = null;

  // the mime type if detectable
  protected $mime = null;

  // cache for an exif data object
  protected $exif = null;

  // cache for the dimensions object
  protected $dimensions = null;

  // array with more details about the asset
  protected $details = null;

  /**
   * Constructor
   * 
   * <code>
   * 
   * $image = new Asset('/root/to/myfile.jpg', 'http://mydomain.com/myfile.jpg');
   * 
   * </code> 
   * 
   * @param string $root
   * @param string $url
   */
  public function __construct($root, $url = null) {
    $this->root = realpath($root);
    $this->url  = $url;
  }

  /**
   * Returns the full URL to the file
   * i.e. http://yourdomain.com/content/somefolder/somesubfolder/somefile.jpg
   *
   * @return string
   */
  public function url() {
    return $this->url;
  }

  /**
   * Returns the full root of the asset
   * 
   * @return string
   */
  public function root() {
    return $this->root;
  }

  /**
   * Returns a md5 hash of this file's root
   * 
   * @return string
   */
  public function hash() {
    return md5($this->root);
  }

  /**
   * Returns the filename of the file
   * i.e. somefile.jpg
   *
   * @return string
   */
  public function filename() {
    if(!is_null($this->filename)) return $this->filename;
    return $this->filename = basename($this->root);
  }

  /**
   * Returns the parent directory path
   *
   * @return string
   */
  public function dir() {
    if(!is_null($this->dir)) return $this->dir;
    return $this->dir = dirname($this->root());
  }

  /**
   * Returns the parent directory's name
   *
   * @return string
   */
  public function dirname() {
    if(!is_null($this->dirname)) return $this->dirname;
    return $this->dirname = basename($this->dir());
  }

  /**
   * Returns the name of the file without extension   
   *
   * @return string
   */
  public function name() {
    if(!is_null($this->name)) return $this->name;
    return $this->name = f::name($this->filename());
  }

  /**
   * Returns the extension of the file 
   * i.e. jpg
   *
   * @return string
   */
  public function extension() {
    if(!is_null($this->extension)) return $this->extension;
    return $this->extension = f::extension($this->filename());
  }

  /**
   * Returns the file type i.e. image
   * Is also being used as setter
   * 
   * Available file types by default are:
   * image, video, document, sound, content, meta, other
   * See the kirby/defaults.php for config options to 
   * refine type categorization
   *
   * @param string $type 
   * @return string
   */
  public function type() {
        
    // get the cached type if available
    if(!is_null($this->type)) return $this->type;

    // detect the file type
    $type = f::type($this->extension());

    // unkown file type
    return $this->type = (is_null($type)) ? 'unknown' : $type;

  }

  /**
   * Checks if a file is of a certain type
   * 
   * @param string $value An extension or mime type
   * @return boolean
   */
  public function is($value) {
    return f::is($this->root(), $value);
  }

  /**
   * Returns the last modified date of this file
   * as unix timestamp
   * 
   * @return int
   */
  public function modified() {
    if(!is_null($this->modified)) return $this->modified;
    return $this->modified = f::modified($this->root);
  }

  /**
   * Checks if the file actually exists
   * 
   * @return boolean
   */
  public function exists() {
    return file_exists($this->root());
  }

  /**
   * Checks if the file is readable
   * 
   * @return boolean
   */
  public function isReadable() {
    return is_readable($this->root());
  }

  /**
   * Checks if the file is writable
   * 
   * @return boolean
   */
  public function isWritable() {
    return is_writable($this->root());
  }

  /**
   * Returns the raw file size of this file
   * 
   * @return int
   */
  public function size() {
    if(!is_null($this->size)) return $this->size;
    return $this->size = f::size($this->root);
  }

  /**
   * Returns a human readble file size
   * i.e. 1.2 MB
   * 
   * @return string
   */
  public function niceSize() {
    if(!is_null($this->niceSize)) return $this->niceSize;
    return $this->niceSize = f::niceSize($this->size());
  }

  /**
   * Returns the mime type of this file
   * if detectable. i.e. image/jpeg
   * 
   * @return string
   */
  public function mime() {
    if(!is_null($this->mime)) return $this->mime;
    return $this->mime = f::mime($this->root());
  }

  /**
   * Returns the exif data object for this asset
   * 
   * @return object KirbyExif
   */
  public function exif() {
    if(!is_null($this->exif)) return $this->exif;
    return $this->exif = new Exif($this);
  }

  /**
   * Inspects the asset file with additional
   * methods to retreive more info about it
   * 
   * @return array
   */
  public function details() {

    if(!is_null($this->details)) return $this->details;

    // try to get the asset dimensions with getimagesize
    // this should work for all major image types and some videos
    return $this->details = getimagesize($this->root());

  }

  /**
   * Returns the dimensions object for this asset
   * 
   * @return object KirbyDimensions
   */
  public function dimensions() {

    if(!is_null($this->dimensions)) return $this->dimensions;

    // fetch details about the asset first
    $details = $this->details();

    // also set the mime type since this is more reliable
    $this->mime = a::get($details, 'mime');    

    // init and return the dimensions object with the detected width and height
    return $this->dimensions = new Dimensions(a::get($details, 0, 0), a::get($details, 1, 0));

  }

  /**
   * Returns the width of the asset
   * 
   * @return int
   */
  public function width() {
    return $this->dimensions()->width();
  }

  /**
   * Returns the height of the asset
   * 
   * @return int
   */
  public function height() {
    return $this->dimensions()->height();
  }

  /**
   * Returns the ratio of the asset
   * 
   * @return int
   */
  public function ratio() {
    return $this->dimensions()->ratio();
  }

  /**
   * Sends an appropriate header for the asset
   * 
   * @param boolean $send
   * @return mixed
   */
  public function header($send = true) {
    return header::type($this->mime(), false, $send);
  }

  /**
   * Reads the content of the asset and returns it
   * 
   * @return mixed
   */
  public function read() {
    return f::read($this->root());
  }

  /**
   * Returns a full link to this file
   * Perfect for debugging in connection with echo
   * 
   * @return string
   */
  public function __toString() {
    return '<a href="' . $this->url() . '">' . $this->url() . '</a>';  
  }

}