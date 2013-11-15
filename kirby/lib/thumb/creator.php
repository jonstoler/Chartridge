<?php

namespace Kirby\Toolkit\Thumb;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Thumb;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Thumb Creator
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class Creator {

  // the parent thumb object
  protected $thumb = null;

  /**
   * Constructor
   * 
   * @param object $thumb The parent Thumb object
   */  
  public function __construct(Thumb $thumb) {
  
    // store the parent thumbnail object    
    $this->thumb = $thumb;

    // check for an existing dir and create it if it is missing yet
    if(!is_dir($this->thumb->dir())) {
      if(!dir::make($this->thumb->dir())) raise('The directory could not be created');      
    }

    // check if the image is writable at all
    if(!$this->thumb->isWritable()) raise('The image file is not writable: ' . $this->thumb->root());
    
    // check if everything else is fine
    $this->check();

  }

  /**
   * Checks if all requirements for the creator are met
   */  
  abstract protected function check();  

  /**
   * Runs the thumbnail creation code
   */  
  abstract public function run();

}