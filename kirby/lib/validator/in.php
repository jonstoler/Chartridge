<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * In Validator
 * 
 * Checks for a value contained in a list of values
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class In extends Validator {

  public $message = 'The {attribute} must be in: :in';

  public function vars() {
    return array(
      'in' => implode(', ', $this->options)
    );
  }

  public function validate() {    
    return in_array($this->value, $this->options);
  }

}