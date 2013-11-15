<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Different Validator
 * 
 * Checks for two different values
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Different extends Validator {

  public $message = 'The {attribute} and {other} must be different';

  public function vars() {
    return array(
      'other' => $this->options
    );
  }

  public function validate() {
    return $this->value != $this->options;  
  }

}