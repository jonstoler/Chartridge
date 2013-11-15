<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Array Validator
 * 
 * Checks for arrays
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Array extends Validator {

  public $message = 'The {attribute} must be an array';

  public function validate() {
    return is_array($this->value);
  }

}