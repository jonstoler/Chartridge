<?php

namespace Kirby\Toolkit;

use Exception;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Email
 * 
 * A simple email handling class which supports
 * multiple email services. Check out the email subfolder
 * for all available services
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Email {

  // the used service driver
  public $service = null;
  public $options = array();

  // email details
  public $to      = null;
  public $from    = null;
  public $replyTo = null;
  public $subject = null;
  public $body    = null;

  // internal collection of errors
  protected $errors = null;

  // optional store for services responses
  protected $response = array();

  /**
   * Constructor
   * 
   * @param string $service The name of the service driver you want to use
   */
  public function __construct($params = null) {

    if(is_array($params)) {

      $defaults = array(
        'service' => 'mail'
      );    

      $options = array_merge($defaults, $params);

    } else {

      if(is_null($params)) {
        // get the default service which should be used
        $params = c::get('email.service', 'mail');
      }

      // try to load service options from the config
      $options = a::get(c::get('email.services', array()), $params);

      // last resort
      if(!$options) {
        $options = array(
          'service' => 'mail'
        );
      }

    }

    $this->service = $options['service'];
    $this->options = (array)$options;
    $this->errors  = new Errors;

  }

  /**
   * Sends the constructed email
   * 
   * @param array $params Optional way to set values for the email
   * @return boolean
   */
  public function send($params = null) {

    if(!is_null($params)) {

      $defaults = array(
        'service' => $this->service,
        'to'      => $this->to,
        'from'    => $this->from,
        'replyTo' => $this->replyTo,
        'subject' => $this->subject,
        'body'    => $this->body,
      );

      $options = array_merge($defaults, $params);

      // overwrite the values
      $this->service = $options['service'];
      $this->to      = $options['to'];
      $this->from    = $options['from'];
      $this->replyTo = $options['replyTo'];
      $this->subject = $options['subject'];
      $this->body    = $options['body'];

    }

    // if there's no dedicated reply to address, use the from address
    if(is_null($this->replyTo)) $this->replyTo = $this->from;

    // validate the email 
    $this->validate();

    $serviceFile  = dirname(__FILE__) . DS . 'email' . DS . 'service' . DS . strtolower($this->service) . '.php';
    $serviceClass = 'Kirby\\Toolkit\\Email\\Service\\' . $this->service;

    // check if the class file exists
    if(!file_exists($serviceFile)) $this->raise('The service is not available', 'missing-service');

    // load the class only for the first time
    require_once($serviceFile);

    // check if the service class is available 
    if(!class_exists($serviceClass)) $this->raise('The service is not available', 'missing-service');

    // initiate the service and send the email
    $service = new $serviceClass($this);
    
    try {

      // send the email over the chosen service
      $service->send();

      // store the optional response from the service
      $this->response = $service->response();

    } catch(Exception $e) {
      $this->raise($e->getMessage(), $e->getCode());
    }

    // return true on success and false on error
    return $this->passed();

  }

  /**
   * Validates the constructed email 
   * to make sure it can be sent at all
   */
  public function validate() {

    if(c::get('email.disabled')) $this->raise('Email has been disabled', 'disabled');
  
    $data = array(
      'to'      => $this->extractAddress($this->to),
      'from'    => $this->extractAddress($this->from),
      'replyTo' => $this->extractAddress($this->replyTo),
      'subject' => $this->subject,
      'body'    => $this->body
    );
  
    $validation = v($data, array(
      'to'      => array('required', 'email'),
      'from'    => array('required', 'email'),
      'replyTo' => array('email'), 
      'subject' => array('required'),
      'body'    => array('required')
    ), array(
      'from'    => 'sender address', 
      'to'      => 'recipient address',
      'replyTo' => 'reply address',
    ));

    if($validation->failed()) $this->raise($validation);

  }

  /**
   * Returns all errors
   * 
   * @return array
   */
  public function errors() {
    return $this->errors;
  }

  /**
   * Returns a specific error by code
   * 
   * @param string $code
   */
  public function error($coded = null) {
    return is_null($code) ? $this->errors->first() : $this->errors->get($code);
  }

  /**
   * Raises an internal error
   */
  protected function raise($message, $code = null) {
    return $this->errors->raise($message, $code);
  }
  
  /**
   * Returns the optional response from the service
   * 
   * @return mixed
   */
  public function response() {
    return $this->response;
  }

  /**
   * Checks if sending the email failed
   * 
   * @return boolean
   */
  public function failed() {
    return $this->errors->count() > 0;
  }

  /**
   * Checks if sending the email succeeded
   * 
   * @return boolean
   */
  public function passed() {
    return !$this->failed();
  }

  /**
   * Extracts the email address from an address string
   * 
   * @return string
   */
  protected function extractAddress($string) {
    if(v::email($string)) return $string;
    preg_match('/<(.*?)>/i', $string, $array);
    return (empty($array[1])) ? $string : $array[1];
  }

}