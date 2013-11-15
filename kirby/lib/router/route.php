<?php

namespace Kirby\Toolkit\Router;

use ReflectionClass;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Router;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Route
 * 
 * A route can be added to the Router
 * which wil then try to match it agains the current URL
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Route {

  public $pattern   = null;
  public $name      = null;
  public $method    = array('GET');
  public $filter    = array();
  public $action    = null;
  public $https     = false;
  public $ajax      = false;
  public $arguments = array();

  /**
   * Constructor
   * 
   * @param string $pattern The url pattern 
   * @param mixed $params
   * @param mixed $optional
   */
  public function __construct($pattern, $params = array(), $optional = array()) {

    // you can pass a closure or an action string as second argument
    // in this case the third argument will be used as optional params array
    if(is_callable($params) or is_string($params)) {
      $action = $params;
      $params = $optional;
      $params['action'] = $action;
    }

    $options = array_merge(array(
      'name'      => null,
      'method'    => array('GET'), 
      'filter'    => array(),
      'action'    => null, 
      'https'     => false,
      'ajax'      => false,
      'arguments' => array(),
    ), $params);

    $this->pattern   = ltrim($pattern, '/');
    $this->name      = $options['name'];
    $this->method    = $options['method'];
    $this->filter    = $options['filter'];
    $this->action    = $options['action'];
    $this->ajax      = $options['ajax'];
    $this->https     = $options['https'];
    $this->arguments = $options['arguments'];

    // sanitize the pattern
    if(empty($this->pattern)) $this->pattern = '/';

  }

  /**
   * Call the action of a route if callable
   * 
   * @return mixed
   */
  public function call() {
    if($this->isCallable()) {
      return call_user_func_array($this->action, $this->arguments());
    }
  }

  /**
   * Checks if the route has a callable closure
   * 
   * @return boolean
   */
  public function isCallable() {
    return (is_callable($this->action));
  }

  /**
   * Initiates a new route and registers it 
   * 
   * @return object
   */
  static public function register() {

    // get all passed arguments
    $args    = func_get_args();
    $pattern = $args[0];

    if(is_array($pattern)) {
      foreach($pattern as $p => $params) {
        self::register($p, $params);
      }
      return;
    }

    // create a route instance and pass all arguments    
    $class   = get_called_class();
    $reflect = new ReflectionClass($class);
    $route   = $reflect->newInstanceArgs($args);

    // register and return the new route
    return router::register($route);

  }

  /**
   * Alternate version of route::register()
   */
  static public function add() {
    return call_user_func_array(array(get_called_class(), 'register'), func_get_args());
  }

  /**
   * Find a registered route by a field and value
   * 
   * @param string $field
   * @param string $value
   * @return object
   */
  static public function findBy($field, $value) {
    foreach(router::routes() as $method => $routes) {
      foreach($routes as $route) {
        if($route->$field() == $value) return $route;
      }
    }
  }

  /**
   * Makes it possible to access all attributes with its own getter method as well
   * 
   * @param string $method
   * @param mixed $arguments
   * @return mixed
   */
  public function __call($method, $arguments) {

    if(in_array($method, array(
      'pattern', 
      'name', 
      'method', 
      'filter', 
      'action', 
      'https', 
      'ajax', 
      'arguments'
    ))) {
      return $this->$method;
    }

    raise('invalid route method: ' . $method);

  }

  /**
   * Makes it possible to call the following shortcuts:
   * 
   * route::get(),
   * route::post(),
   * route::put(),
   * route::delete(),
   *
   * route::ajax(),
   * route::https(),
   * 
   * route::findByName(),
   * route::findByAction(),
   * route::findByPattern(),
   * 
   * @param string $method
   * @param array $arguments
   * @return object
   */
  static public function __callStatic($method, $arguments = array()) {
    if(in_array($method, array('get', 'post', 'put', 'delete', 'ajax', 'https'))) {

      // create a route instance and pass all arguments
      $class   = get_called_class();
      $reflect = new ReflectionClass($class);
      $route   = $reflect->newInstanceArgs($arguments);

      switch($method) {
        case 'ajax':
          $route->ajax = true;
          break;
        case 'https':
          $route->https = true;
          break;
        default:
          $route->method = strtoupper($method);          
          break;
      }

      // register and return the new route
      return router::register($route);

    }  

    if(in_array($method, array('findByName', 'findByAction', 'findByPattern'))) {

      // get the field name from the method
      $field = strtolower(str_replace('findBy', '', $method));

      // call the findBy method with the right field and value
      return static::findBy($field, a::first($arguments));

    }

  }

}