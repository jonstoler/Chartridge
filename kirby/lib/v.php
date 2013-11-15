<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * V
 * 
 * Validation shortcuts
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class V {

  /**
   * Runs a full validation for an entire set of data and rules
   * 
   * @param array $data
   * @param array $rules specify a set of rules for validation
   * @param array $attributes Overwrite default attribute names
   * @param array $messages Overwrite default validation messages for each method here
   * @return object Validation
   */
  static public function all($data, $rules = array(), $attributes = array(), $messages = array()) {
    return new Validation($data, $rules, $attributes, $messages);
  }

  /** 
   * Checks if the value matches a regular expression
   * 
   * @param  string   $value
   * @param  string   $format
   * @return boolean
   */
  static public function match($value, $format) {
    return Validator::create('match', $value, null, $format)->passed();
  }

  /** 
   * Checks for two valid, matching values
   * 
   * @param  string  $value
   * @param  string  $other
   * @return boolean
   */
  static public function same($value, $other) {
    return Validator::create('same', $value, null, $other)->passed();
  }

  /** 
   * Checks for two different values
   * 
   * @param  string  $value
   * @param  string  $other
   * @return boolean
   */
  static public function different($value, $other) {
    return Validator::create('different', $value, null, $other)->passed();
  }

  /** 
   * Checks for valid date
   * 
   * <code>
   * 
   * if(v::date('2013-05-23')) {
   *   echo 'That is a valid email';
   * }
   *
   * </code>
   * 
   * @param  string  $value
   * @return boolean
   */
  static public function date($value) {
    return Validator::create('date', $value, null)->passed();
  }

  /** 
   * Checks for valid email address
   * 
   * <code>
   * 
   * if(v::email('mail@bastian-allgeier.de')) {
   *   echo 'That is a valid email';
   * }
   *
   * </code>
   * 
   * @param  string  $value
   * @return boolean
   */
  static public function email($value) {
    return Validator::create('email', $value, null)->passed();
  }

  /** 
   * Checks for valid URL
   * 
   * <code>
   * 
   * if(v::url('http://getkirby.com')) {
   *   echo 'That is a valid URL';
   * }
   *
   * </code>
   * 
   * @param  string  $value
   * @return boolean
   */
  static public function url($value) {
    return Validator::create('url', $value, null)->passed();
  }

  /** 
   * Checks for valid filename
   * 
   * <code>
   * 
   * if(v::filename('filename.jpg')) {
   *   echo 'That is a valid filename';
   * }
   *
   * </code>
   * 
   * @param  string  $value
   * @return boolean
   */
  static public function filename($value) {
    return Validator::create('filename', $value, null)->passed();
  }

  /**
   * Checks for an activated checkbox value
   * 
   * <code>
   * 
   * if(v::accepted(get('tos'))) {
   *   echo 'The terms of service have been accepted';
   * }
   *
   * </code>
   * 
   * @param string $value
   * @return boolean
   */
  static function accepted($value) {
    return Validator::create('accepted', $value, null)->passed();
  }

  /**
   * Checks for a min size/count/length of a given value
   * The value may be a string, array or file
   * 
   * <code>
   * 
   * if(v::min('My username', 6)) {
   *   echo 'Your username is long enough';
   * }
   *
   * </code>
   * 
   * @param mixed $value 
   * @param int $min
   * @return boolean
   */
  static public function min($value, $min) {
    return Validator::create('min', $value, null, $min)->passed();
  }

  /**
   * Checks for a max size/count/length of a given value
   * The value may be a string, array or file
   * 
   * <code>
   * 
   * if(v::max('My tweet text', 140)) {
   *   echo 'Your tweet has the correct length';
   * }
   *
   * </code>
   * 
   * @param mixed $value 
   * @param int $max
   * @return boolean
   */
  static public function max($value, $max) {
    return Validator::create('max', $value, null, $max)->passed();
  }

  /**
   * Checks if the size/count/length of a given value
   * is between a minimum and maximumn value. 
   * The value may be a string, array or file
   * 
   * <code>
   * 
   * if(v::between('My random text', 10, 140)) {
   *   echo 'Your text has the correct length';
   * }
   *
   * </code>
   * 
   * @param mixed $value 
   * @param int $min
   * @param int $max
   * @return boolean
   */
  static public function between($value, $min, $max) {
    return Validator::create('between', $value, null, array($min, $max))->passed();
  }

  /**
   * Checks if the value is included in an array of values
   * 
   * <code>
   * 
   * if(v::in('apple', array('apple', 'pear', 'melon'))) {
   *   echo 'That is a valid fruit!';
   * }
   *
   * </code>
   * 
   * @param mixed $value
   * @param array $values
   * @return boolean
   */
  static public function in($value, $values = array()) {
    return Validator::create('in', $value, null, $values)->passed();
  }

  /**
   * Checks if the value is not included in an array of values
   * 
   * <code>
   * 
   * if(v::notIn('pineapple', array('apple', 'pear', 'melon'))) {
   *   echo 'Yes, that is a valid fruit!';
   * }
   *
   * </code>
   * 
   * @param mixed $value
   * @param array $values
   * @return boolean
   */
  static public function notIn($value, $values = array()) {
    return Validator::create('notIn', $value, null, $values)->passed();
  }

  /**
   * Checks if the value is a valid ip
   * 
   * <code>
   * 
   * if(v::ip('127.0.0.1')) {
   *   echo 'Valid IP';
   * }
   *
   * </code>
   * 
   * @param mixed $value
   * @return boolean
   */
  static public function ip($value) {
    return Validator::create('ip', $value, null)->passed();
  }

  /**
   * Checks if the value contains only alpha chars
   * 
   * <code>
   * 
   * if(v::alpha('abc')) {
   *   echo 'Valid text';
   * }
   *
   * </code>
   * 
   * @param mixed $value
   * @return boolean
   */
  static public function alpha($value) {
    return Validator::create('alpha', $value, null)->passed();
  }

  /**
   * Checks if the value contains only numbers
   * 
   * <code>
   * 
   * if(v::numeric(1234)) {
   *   echo 'Valid number';
   * }
   *
   * </code>
   *
   * @param mixed $value
   * @return boolean
   */
  static public function numeric($value) {
    return Validator::create('numeric', $value, null)->passed();
  }

  /**
   * Checks if the value contains only numbers and chars from a-z
   * 
   * <code>
   * 
   * if(v::alphaNumeric('abc1234')) {
   *   echo 'Valid alpha numeric string';
   * }
   *
   * </code>
   * 
   * @param mixed $value
   * @return boolean
   */
  static public function alphaNumeric($value) {
    return Validator::create('alphaNumeric', $value, null)->passed();
  }

  /**
   * Checks for a valid integer value
   * 
   * <code>
   * 
   * if(v::integer(2)) {
   *   echo 'Valid integer';
   * }
   *
   * </code>
   * 
   * @param int $value
   * @return boolean
   */
  static public function integer($value) {
    return Validator::create('integer', $value, null)->passed();
  }

  /**
   * Compares the size of the value with a given size
   * 
   * @param mixed $value
   * @param int $size
   * @return boolean
   */
  static public function size($value, $size) {
    return Validator::create('size', $value, null, $size)->passed();
  }

}