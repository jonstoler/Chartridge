<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Prowl 
 *
 * This class makes it easy for you to send Prowl
 * alerts in your applications (http://prowlapp.com/) 
 * 
 * @package   Kirby Toolkit 
 * @author    Jonathan Stoler <jon@stoler.com>
 * @link      http://jonathan.stoler.com
 * @copyright Jonathan Stoler
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Prowl {

  private $endpoint = 'https://api.prowlapp.com/publicapi/';

  protected $apiKey    = null;
  protected $remaining = null;

  static protected $application = null;


  static public function setApplication($name) {
    static::$application = $name;
  }

  public function application() {
    return static::$application;
  }

  /**
   * Constructor
   * 
   * <code>
   * 
   * $prowl = new Prowl('abcdef');
   * 
   * </code> 
   * 
   * @param string $apiKey
   */
  public function __construct($apiKey = null) {
    $this->apiKey = $apiKey;
  }

  /**
   * Magic getter
   * 
   * Can be used like this: 
   * `$prowl->apiKey`
   * 
   * @param string $property
   * @return mixed Whatever is stored for that property
   */
  public function __get($property) {
    return $this->$property;
  }

  public function remaining() {
    
  }

  /**
   * Magic setter
   * 
   * Can be used to simply set values like this
   * `$prowl->apiKey = 'abcdef'`;
   * 
   * @param string $property the name of the property
   * @param mixed $value an array or object or whatever you want to store
   */ 
  public function __set($property, $value) {
    $this->$property = $value;
  }

  /**
   * Add
   *
   * Sends a prowl event.
   *
   * @param string $event the name of the event or subject of the notification
   * @param string $description a description of the event, generally terse
   * @param string $url the URL which should e attached to the notification
   * @param mixed $priority 
   *
   * @return boolean
   */
  public function add($event, $description, $url = null, $priority = 0, $app = null) {
    if($this->apiKey){
      if($app === NULL){ $app = static::$application; }
      if(is_string($priority)){
        $priority = $this->convertPriority($priority);
      }

      $args = array();
      $args['apikey'] = $this->apiKey;
      $args['event'] = $event;
      $args['description'] = $description;
      if($url !== NULL){ $args['url'] = $url; }
      $args['priority'] = $priority;
      $args['application'] = $app;

      $result = remote::post($this->endpoint . 'add', ['data' => $args]);
      $response = xml::parse($result->content());

      if(a::get($response, 'success', false)){
        $this->remaining = intval($response['success']['@attributes']['remaining']);
      }

      return $response;
    }
  }

  /**
   * Notify
   *
   * Alias for add()
   *
   * @see add()
   */
  public function notify($event, $description, $url = null, $priority = 0, $app = null) {
    $this->add($event, $description, $url, $priority, $app);
  }

  public function verify($apiKey) {
    $result = remote::get($this->endpoint . 'verify', ['data' => ['apikey' => $apiKey]]);
    $response = xml::parse($result->content());

    if(a::get($response, 'success', false)){
      $this->remaining = intval($response['success']['@attributes']['remaining']);
      return true;
    }

    return false;
  }

  private function convertPriority($priority = 'normal') {
    $stringRepresentation = ['very low', 'moderate', 'normal', 'high', 'emergency'];
    $intRepresentation    = [-2, -1, 0, 1, 2];
    $int = -2;
    foreach($stringRepresentation as $rep) {
      if($priority == str::lower($rep)){ return $int; }
      $int++;
    }
    return null;
  }

}