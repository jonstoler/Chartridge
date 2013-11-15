<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Match Validator
 * 
 * Checks the value against a regular expression
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Match extends Validator {

  public $message = 'The {attribute} must match the following format: {format}';

  public function vars() {
    return array(
      'format' => $this->options
    );
  }

  public function validate() {
    return preg_match($this->options, $this->value());
  }

}