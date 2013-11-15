<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Size Validator
 * 
 * Checks the size of the value
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Size extends Validator {

  public $message = array(
    'numeric' => 'The {attribute} must be {size}.',
    'file'    => 'The {attribute} must be {size} kilobyte.',
    'string'  => 'The {attribute} must be {size} characters.',
    'array'   => 'The {attribute} must be {size} elements.',
  );

  public function vars() {
    return array(
      'size' => $this->options
    );
  }

  public function validate() {
    return size($this->value) == $this->options;
  }

}