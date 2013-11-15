<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Between Validator
 * 
 * Checks for the size of the value being between the first and second argument
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Between extends Validator {

  public $message = array(
    'numeric' => 'The {attribute} must be at least {min} and less than {max}.',
    'string'  => 'The {attribute} must be at least {min} and less than {max} characters.',
    'file'    => 'The {attribute} must be at least {min} and less than {max} kilobytes',
    'array'   => 'The {attribute} must be at least {min} and less than {max} elements'
  );

  public function vars() {
    return array(
      'min' => $this->options[0],
      'max' => $this->options[1]
    );
  }

  public function validate() {    
    return v::min($this->value, $this->options[0]) and v::max($this->value, $this->options[1]);
  }

}