<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Max Validator
 * 
 * Checks for a value with a size less than the max value
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Max extends Validator {

  public $message = array(
    'numeric' => 'The {attribute} must be less than {max}.',
    'string'  => 'The {attribute} must be less than {max} characters.',
    'file'    => 'The {attribute} must be less than {max} kilobytes',
    'array'   => 'The {attribute} must be less than {max} elements'
  );

  public function vars() {
    return array(
      'max' => $this->options
    );
  }

  public function validate() {    
    return size($this->value) <= $this->options;
  }

}