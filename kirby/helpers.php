<?php

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Redirects the user to a new URL
 * This uses the URL::to() method and can be super
 * smart with the custom url::to() handler. Check out
 * the URL class for more information
 */
function go() {
  call_user_func_array(array('redirect', 'to'), func_get_args());
}

/**
  * Shortcut for r::get()
  *
  * @param   mixed    $key The key to look for. Pass false or null to return the entire request array. 
  * @param   mixed    $default Optional default value, which should be returned if no element has been found
  * @return  mixed
  */  
function get($key = null, $default = null) {
  return r::data($key, $default);
}

/**
 * Get all params in a single array
 * 
 * @return array
 */
function params() {
  return uri::current()->params()->toArray();
}

/**
 * Get a parameter from the current URI object
 * 
  * @param   mixed    $key The key to look for. Pass false or null to return the entire params array. 
  * @param   mixed    $default Optional default value, which should be returned if no element has been found
  * @return  mixed
 */
function param($key = null, $default = null) {
  return (is_null($key))? uri::current()->params()->toArray() : uri::current()->param($key, $default);
}

/**
 * Smart version of return with an if condition as first argument
 * 
 * @param boolean $condition
 * @param string $value The string to be returned if the condition is true
 * @param string $alternative An alternative string which should be returned when the condition is false
 */
function r($condition, $value, $alternative = null) {
  return ($condition) ? $value : $alternative;
}

/**
 * Smart version of echo with an if condition as first argument
 * 
 * @param boolean $condition
 * @param string $value The string to be echoed if the condition is true
 * @param string $alternative An alternative string which should be echoed when the condition is false
 */
function e($condition, $value, $alternative = null) {
  echo r($condition, $value, $alternative);
}

/**
 * Alternative for e()
 * 
 * @see e()
 */
function ecco($condition, $value, $alternative = null) {
  e($condition, $value, $alternative);
}

/**
 * Returns a language string
 * 
 * @param mixed $key
 * @param mixed $default
 * @return string
 */
function l($key = null, $default = null) {
  return l::get($key, $default);
}

/**
 * Shortcut for a::show()
 * 
 * @see a::show()
 * @param mixed $variable Whatever you like to inspect
 */ 
function dump($variable) {

  if(is_object($variable) and method_exists($variable, '__toDump')) {
    a::show($variable->__toDump());
  } else {
    a::show($variable);    
  }

}

/**
 * Generates a single attribute or a list of attributes
 * 
 * @see html::attr();
 * @param string $name mixed string: a single attribute with that name will be generated. array: a list of attributes will be generated. Don't pass a second argument in that case. 
 * @param string $value if used for a single attribute, pass the content for the attribute here
 * @return string the generated html
 */
function attr($name, $value = null) {
  return html::attr($name, $value);
}  

/**
 * Creates safe html by encoding special characters
 * 
 * @param string $text unencoded text
 * @return string
 */
function html($text, $keepTags = true) {
  return html::encode($text, $keepTags);
}

/**
 * Shortcut for html()
 * 
 * @see html()
 */
function h($text, $keepTags = true) {
  return html::encode($text, $keepTags);
}

/**
 * Creates safe xml by encoding special characters
 * 
 * @param string $text unencoded text
 * @return string
 */
function xml($text) {
  return xml::encode($text);
}

/**
 * Converts new lines to html breaks
 * 
 * @param string $text with new lines
 * @return string
 */
function multiline($text) {
  return html::breaks(html::encode($text));
}

/**
 * The widont function makes sure that there are no 
 * typographical widows at the end of a paragraph –
 * that's a single word in the last line
 * 
 * @param string $string
 * @return string
 */
function widont($string = '') {
  return str::widont($string);
}

/**
 * Returns the memory usage in a readable format
 * 
 * @return string
 */
function memory() {
  return f::niceSize(memory_get_usage());
}

/**
 * Determines the size/length of numbers, strings, arrays and files 
 *
 * @param mixed $value 
 * @return int
 */
function size($value) {
  if(is_numeric($value)) return $value; 
  if(is_string($value))  return str::length(trim($value));
  if(is_array($value))   return count($value);
  if(f::exists($value))  return f::size($value) / 1024;
}

/**
 * Generates a gravatar image link
 * 
 * @param string $email
 * @param int $size
 * @param string $default 
 * @return string
 */
function gravatar($email, $size = 256, $default = 'mm') {
  return 'https://gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=' . urlencode($default) . '&s=' . $size;  
}

/**
 * Raises an Exception
 * 
 * @see Exception
 * @param string $message An error message for the exception
 * @param string $exception Exception class 
 */
function raise($message, $params = null) {

  // default values for raising exceptions
  $defaults = array(
    'code'     => 0,
    'class'    => 'Kirby\\Toolkit\\Exception',
    'data'     => null, 
    'previous' => null
  );

  if(!is_array($params)) {
    $params = array(
      'code' => $params
    );    
  }

  $options = array_merge($defaults, $params);
  $class   = $options['class'];

  // create a new exception then throw it. 
  throw new $class($message, $options['code'], $options['previous']. $options['data']);

}

/**
 * Merges default options with passed parameters and
 * returns a Collection of those options. 
 * This is perfect to be used with classes, which 
 * need fancy option handling
 * 
 * @param array $defaults An array of default options
 * @param array $params An array of params, which should overwrite the defaults
 * @return object Collection
 */
function options($defaults = array(), $params = array()) {
  return new Collection(array_merge($defaults, $params));
}


/**
 * Checks / returns a csfr token
 * 
 * @param string $check Pass a token here to compare it to the one in the session
 * @return mixed Either the token or a boolean check result
 */
function csfr($check = null) {

  // make sure a session is started
  s::start();

  if(is_null($check)) {
    $token = str::random(64);
    s::set('csfr', $token);
    return $token;
  }

  return ($check === s::get('csfr')) ? true : false;

}

/**
 * Shortcut to create a new thumb object
 * 
 * @param mixed Either a file path or an Asset object
 * @param array An array of additional params for the thumb
 * @return object Thumb
 */
function thumb($image, $params = array()) {
  return new Thumb($image, $params);
}


/**
 * The most simple way to send emails 
 * This helper is using the Kirby email class
 * 
 * <code>
 * 
 * $email = email(array(
 *   'to'      => 'bastian@getkirby.com',
 *   'from'    => 'john@doe.com',
 *   'subject' => 'Hello',
 *   'body'    => 'Hello world!'
 * ));
 * 
 * if($email->failed()) die('The email could not be sent');
 * 
 * <code>
 * 
 * @param array $params
 * @return 
 */
function email($params = array()) {
  $email = new Email();
  $email->send($params);
  return $email;
}

/**
 * Runs a full validation for an entire set of data and rules
 * 
 * @param array $data
 * @param array $rules specify a set of rules for validation
 * @param array $attributes Overwrite default attribute names
 * @param array $messages Overwrite default validation messages for each method here
 * @return object Validation
 */
function v($data, $rules = array(), $attributes = array(), $messages = array()) {
  return v::all($data, $rules, $attributes, $messages);
}

/**
 * Shorter version of url::to()
 * 
 * @return string
 */
function url() {
  return call_user_func_array(array('url', 'to'), func_get_args());  
}

/**
 * Another shorter version of url::to()
 * 
 * @return string
 */
function u() {
  return call_user_func_array(array('url', 'to'), func_get_args());  
}

/**
  * Include commonly used code in your templates
  *
  * @param string $name
  * @param array $data
  * @param array $params
  * @return string
  */
function snippet($name, $data = array(), $params = array()) {
  $root = template::$root;
  template::$root = null;
  $tpl = template::create(g::get('snippetRoot', '') . $name, $data, $params);
  echo $tpl;
  template::$root = $root;
}

/**
  * Shortcut for snippet()
  *
  * @see snippet()
  */
function s($name, $data = array(), $params = array()) {
  snippet($name, $data, $params);
}

/**
  * Take a chance!
  *
  * @param int odds
  * @return boolean
  */
function chance($odds = 2){
  return (rand() <= (1 / $odds));
}