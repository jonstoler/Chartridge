<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Integer Validator
 * 
 * Checks for a valid integer
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Integer extends Validator {

  public $message = 'The {attribute} may only contain integers.';

  public function validate() {
    return filter_var($this->value, FILTER_VALIDATE_INT) !== false;    
  }

}