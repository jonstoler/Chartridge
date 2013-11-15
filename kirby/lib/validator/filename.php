<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Filename Validator
 * 
 * Checks for a valid filename format
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Filename extends Validator {

  public $message = 'The {attribute} must be a valid filename';

  public function validate() {
    return v::match($this->value, '/^[a-z0-9@._-]+$/i') and v::min($this->value, 2);
  }

}