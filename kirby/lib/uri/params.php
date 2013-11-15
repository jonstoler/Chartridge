<?php

namespace Kirby\Toolkit\URI;

use Kirby\Toolkit\Collection;
use Kirby\Toolkit\URL;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Params
 *
 * The params object is a child object of the URI object. 
 * It contains all named parameters from a URL: 
 * param1:value1/param2:value2/param3:value3
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Params extends Collection {

  /**
   * Returns all params in a single string
   * 
   * @return string
   */
  public function toString() {
    return url::buildParams($this->toArray());
  }

  /**
   * Converts the object to a string
   * 
   * @return string
   */
  public function __toString() {
    return $this->toString();
  }

}