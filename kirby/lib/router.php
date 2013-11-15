<?php

namespace Kirby\Toolkit;

use Kirby\Toolkit\Router\Route;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Router
 * 
 * The router makes it possible to react 
 * on any incoming URL scheme
 * 
 * Partly inspired by Laravel's router
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Router {

  // the matched route if found
  static protected $route = null;

  // all registered routes
  static protected $routes = array(
    'GET'    => array(),
    'POST'   => array(),
    'PUT'    => array(),
    'DELETE' => array()
  );

  // The wildcard patterns supported by the router.
  static protected $patterns = array(
    '(:num)'     => '([0-9]+)',
    '(:alpha)'   => '([a-zA-Z]+)',
    '(:any)'     => '([a-zA-Z0-9\.\-_%=]+)',
    '(:all)'     => '(.*)',
  );

  // The optional wildcard patterns supported by the router.
  static protected $optional = array(
    '/(:num?)'   => '(?:/([0-9]+)',
    '/(:alpha?)' => '(?:/([a-zA-Z]+)',
    '/(:any?)'   => '(?:/([a-zA-Z0-9\.\-_%=]+)',
    '/(:all?)'   => '(?:/(.*)',
  );

  // additional events, which can be triggered by routes
  static protected $filters = array();

  /**
   * Resets all registered routes
   */
  static public function reset() {
    static::$route  = null;
    static::$routes = array(
      'GET'    => array(),
      'POST'   => array(),
      'PUT'    => array(),
      'DELETE' => array()
    );    
    static::$filters = array();
  }  

  /**
   * Returns the found route
   * 
   * @return mixed
   */
  static public function route() {
    return static::$route;
  }

  /**
   * Returns the arguments array from the current route
   * 
   * @return array
   */
  static public function arguments() {
    if($route = static::route()) return $route->arguments();
  }

  /**
   * Adds a new route
   * 
   * @param object $route
   * @return object
   */
  static public function register(Route $route) {

    // convert single methods or methods separated by | to arrays    
    if(is_string($route->method)) {

      if(str::contains($route->method, '|')) {
        $route->method = str::split($route->method, '|');
      } else {
        $route->method = array($route->method);
      }

    }

    foreach($route->method as $method) {
      static::$routes[strtoupper($method)][$route->pattern] = $route;
    }
    return $route;
  }

  /**
   * Add a new router filter
   * 
   * @param string $name A simple name for the filter, which can be used by routes later
   * @param closure $function A filter function, which should be called before routes 
   */
  static public function filter($name, $function) {
    static::$filters[$name] = $function;
  }

  /**
   * Return all registered filters
   * 
   * @return array
   */
  static public function filters() {
    return static::$filters;
  }

  /**
   * Call all matching filters
   * 
   * @param mixed $filters
   */
  static protected function filterer($filters) {
    foreach((array)$filters as $filter) {
      if(array_key_exists($filter, static::$filters) and is_callable(static::$filters[$filter])) {
        call_user_func(static::$filters[$filter]);
      }
    }    
  }

  /**
   * Returns all added routes
   * 
   * @param string $method
   * @return array
   */
  static public function routes($method = null) {
    return is_null($method) ? static::$routes : static::$routes[strtoupper($method)];
  }

  /**
   * Iterate through every route to find a matching route.
   *
   * @param  string $url Optional url to match against
   * @return Route
   */
  static public function run($url = null) {

    $uri    = is_null($url) ? uri::current() : new URI($url);
    $path   = $uri->path()->toString();
    $method = r::method();
    $ajax   = r::ajax();
    $https  = r::ssl();
    $routes = static::$routes[$method];

    // empty urls should never happen
    if(empty($path)) $path = '/';

    foreach($routes as $route) {
      
      if($route->https and !$https) continue;
      if($route->ajax  and !$ajax)  continue;

      // handle exact matches
      if($route->pattern == $path) {       
        static::$route = $route;
        break;
      }

      // We only need to check routes with regular expression since all others
      // would have been able to be matched by the search for literal matches
      // we just did before we started searching.
      if(!str::contains($route->pattern, '(')) continue;
      
      $preg = '#^'. static::wildcards($route->pattern) . '$#u';

      // If we get a match we'll return the route and slice off the first
      // parameter match, as preg_match sets the first array item to the
      // full-text match of the pattern.
      if(preg_match($preg, $path, $parameters)) {
        static::$route = $route;
        static::$route->arguments = array_slice($parameters, 1);
        break;
      }    
    
    }

    if(static::$route) {
      static::filterer(static::$route->filter);
      return static::$route;
    } else {
      return null;
    }

  }

  /**
   * Translate route URI wildcards into regular expressions.
   *
   * @param  string  $uri
   * @return string
   */
  static protected function wildcards($pattern) {
      
    $search  = array_keys(static::$optional);
    $replace = array_values(static::$optional);

    // For optional parameters, first translate the wildcards to their
    // regex equivalent, sans the ")?" ending. We'll add the endings
    // back on when we know the replacement count.
    $pattern = str_replace($search, $replace, $pattern, $count);

    if($count > 0) $pattern .= str_repeat(')?', $count);
    
    return strtr($pattern, static::$patterns);
  
  }

}