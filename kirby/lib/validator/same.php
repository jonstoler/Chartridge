<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Same Validator
 * 
 * Checks for a value which is the same than the other passed value
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Same extends Validator {

  public $message = 'The {attribute} and {other} must be the same';

  public function vars() {
    return array(
      'other' => $this->options
    );
  }

  public function validate() {
    return $this->value == $this->options;  
  }

}