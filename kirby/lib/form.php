<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Form
 * 
 * Simple form builder
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Form {

  /**
   * Generates the form start tag
   * 
   * @param string $action The url to send the form to
   * @param string $method post, get
   * @param boolean $upload if true the enctype attribute will be added
   * @param array $attr Additional attributes for the tag
   * @return The generated html
   */
  static public function start($action = '', $method = 'post', $upload = false, $attr = array()) {

    $attr = array_merge(array(
      'action'  => $action,
      'method'  => $method, 
      'enctype' => r($upload, 'multipart/form-data'), 
    ), $attr);

    return '<form ' . Html::attr($attr) . '>';

  }

  /**
   * Generates the form end tag
   * 
   * @return The generated html
   */
  static public function end() {
    return '</form>';
  }

  /**
   * Global handler for input elements
   * 
   * @param string $type The type of the input field
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function input($type, $name, $value = null, $attr = array()) {
    
    $attr = array_merge(array(
      'type'  => $type,
      'name'  => $name, 
      'value' => $value, 
    ), $attr);

    return Html::tag('input', null, $attr);

  }

  /**
   * Generates a label tag
   * 
   * @param string $text The text for the label
   * @param string $for Optional for attribute
   * @param array $attr Additional attributes for the label tag
   * @return The generated html
   */
  static public function label($text, $for = false, $attr = array()) {

    $attr = array_merge(array(
      'for' => $for,
    ), $attr);

    return Html::tag('label', $text, $attr);   

  }

  /**
   * Generates a text input field
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function text($name, $value = null, $attr = array()) {
    return static::input('text', $name, $value, $attr);
  }

  /**
   * Generates a password input field
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function password($name, $value = null, $attr = array()) {
    return static::input('password', $name, $value, $attr);
  }

  /**
   * Generates an URL input field
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function url($name, $value = null, $attr = array()) {
    return static::input('url', $name, $value, $attr);
  }

  /**
   * Generates an email input field
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function email($name, $value = null, $attr = array()) {
    return static::input('email', $name, $value, $attr);
  }

  /**
   * Generates a search input field
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function search($name, $value = null, $attr = array()) {
    return static::input('search', $name, $value, $attr);
  }

  /**
   * Generates a tel input field
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function tel($name, $value = null, $attr = array()) {
    return static::input('tel', $name, $value, $attr);
  }

  /**
   * Generates a file input field
   * 
   * @param string $name The name attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function file($name, $attr = array()) {
    return static::input('file', $name, null, $attr);
  }

  /**
   * Generates a radio button
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param boolean $checked false: the radio button will not be checked, true: it will be checked
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function radio($name, $value, $checked = false, $attr = array()) {

    $attr = array_merge(array(
      'checked' => r($checked, 'checked')
    ), $attr);

    return static::input('radio', $name, $value, $attr);
  }

  /**
   * Generates a checkbox
   * 
   * @param string $name The name attribute for the field
   * @param boolean $checked false: the checkbox will not be checked, true: it will be checked
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function checkbox($name, $checked = false, $attr = array()) {

    $attr = array_merge(array(
      'checked' => r($checked, 'checked')
    ), $attr);
    
    return static::input('checkbox', $name, false, $attr);
  }

  /**
   * Generates a button
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function button($name, $value, $attr = array()) {
    $attr = array_merge(array(
      'name' => $name
    ), $attr);
    return html::tag('button', $value, $attr);
  }

  /**
   * Generates a submit button
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function submit($name, $value, $attr = array()) {
    return static::input('submit', $name, $value, $attr);
  }

  /**
   * Generates a reset button
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function reset($name, $value, $attr = array()) {
    return static::input('reset', $name, $value, $attr);
  }

  /**
   * Generates a hidden field
   * 
   * @param string $name The name attribute for the field
   * @param string $value The value attribute for the field
   * @param array $attr Additional attributes for the input tag
   * @return The generated html
   */
  static public function hidden($name, $value, $attr = array()) {
    return static::input('hidden', $name, $value, $attr);    
  }

  /**
   * Builds a hidden field with a csfr token
   */
  static public function csfr($name = 'csfr') {
    return static::hidden($name, csfr());    
  }

  /**
   * Generates a textarea
   * 
   * @param string $name The name attribute for the field
   * @param string $value The content of the textarea
   * @param array $attr Additional attributes for the textarea
   * @return The generated html
   */
  static public function textarea($name, $value = null, $attr = array()) {
 
    $attr = array_merge(array(
      'name'  => $name,
    ), $attr);

    return Html::tag('textarea', $value, $attr);

  }

  /**
   * Generates a select box
   * 
   * @param string $name The name attribute for the field
   * @param array $options An associative array with options for the select box
   * @param string $selected The key of the selected option
   * @param array $attr Additional attributes for the select tag
   * @return The generated html
   */
  static public function select($name, $options = array(), $selected = false, $attr = array()) {

    $attr = array_merge(array(
      'name' => $name,
    ), $attr);

    $content   = array();
    $content[] = false;
    foreach($options as $key => $value) $content[] = static::option($key, $value, r($selected == $key, true));
    $content[] = false;

    return Html::tag('select', implode(PHP_EOL, $content), $attr);

  }

  /**
   * Generates an option tag used in select boxes
   * 
   * @param string $key The key of the option
   * @param array $value The displayed text value
   * @param boolean $selected true: the option will be selected, false: the option will not be selected
   * @param array $attr Additional attributes for the option tag
   * @return The generated html
   */
  static public function option($key, $value, $selected = false, $attr = array()) {

    $attr = array_merge(array(
      'value'    => $key,
      'selected' => r($selected, 'selected')
    ), $attr);

    return Html::tag('option', $value, $attr);

  }

}