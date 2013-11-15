<?php

namespace Kirby\Toolkit\Validator;

use Kirby\Toolkit\V;
use Kirby\Toolkit\Validator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Date Validator
 * 
 * Checks for a plausible date
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Date extends Validator {

  public $message = 'The {attribute} must be a valid date';

  public function validate() {

    $time = strtotime($this->value);
    if(!$time) return false;

    $year  = date('Y', $time);
    $month = date('m', $time);
    $day   = date('d', $time);

    return checkdate($month, $day, $year);

  }

}