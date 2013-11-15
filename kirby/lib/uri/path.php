<?php

namespace Kirby\Toolkit\URI;

use Kirby\Toolkit\Collection;
use Kirby\Toolkit\URL;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Path
 *
 * The path object is a child object of the URI object. 
 * It contains all parts of the path from a URL: 
 * this/is/a/beautiful/path
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Path extends Collection {
  
  /**
   * Returns the entire path in a single string
   * 
   * @return string
   */
  public function __toString() {
    return $this->toString();
  }
 
  /**
   * Converts the object to a string
   * 
   * @return string
   */
  public function toString() {
    return url::buildPath($this->toArray());
  }
    
}
