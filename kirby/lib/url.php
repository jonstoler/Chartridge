<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * URL
 * 
 * A bunch of handy methods to work with URLs
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class URL {
    
  /** 
   * Returns the current URL
   * 
   * @return string
   */
  static public function current() {    
    if(r::cli()) return null;
    return r::scheme() . '://' . server::get('http_host') . server::get('request_uri');
  }

  /**
   * Parses the url and returns a bunch of useful data
   * 
   * @param string $url
   * @return array
   */
  static public function parse($url) {

    $defaults = array(
      'scheme'   => null,
      'host'     => null,
      'port'     => null,
      'user'     => null,
      'pass'     => null,
      'path'     => null,
      'query'    => null,
      'fragment' => null
    );

    $data = parse_url($url);
    
    if(!$data) return false;

    // make sure all elements of the array are available as keys
    $data = array_merge($defaults, $data);

    return $data;

  }

  /**
   * Returns the scheme
   * 
   * @param string $url
   * @param string $default
   * @return string
   */
  static public function scheme($url, $default = null) {
    return a::get(static::parse($url), 'scheme', $default);
  }

  /**
   * Returns the host
   * 
   * @param string $url
   * @param string $default
   * @return string
   */
  static public function host($url, $default = null) {
    return a::get(static::parse($url), 'host', $default);
  }

  /**
   * Returns the port
   * 
   * @param string $url
   * @param string $default
   * @return string
   */
  static public function port($url, $default = null) {
    return a::get(static::parse($url), 'port', $default);
  }

  /**
   * Returns the query of the url
   * 
   * @param string $url
   * @param string $default
   * @return string
   */
  static public function query($url, $default = null) {
    return a::get(static::parse($url), 'query', $default);
  }

  /**
   * Builds a full query string out of an array
   * 
   * @param array $data
   * @param string $prepend
   * @return string
   */
  static public function buildQuery($data, $prepend = '?') {
    
    // convert the data array to a string
    $query = http_build_query($data);

    // prepend the question mark or any other character
    if(!empty($query)) $query = $prepend . $query;

    return $query;

  }

  /**
   * Returns the path of the url
   * 
   * @param string $url
   * @param string $default
   * @return string
   */
  static public function path($url, $default = null) {
    return a::get(static::parse($url), 'path', $default); 
  }

  /**
   * Builds a path
   * 
   * @param array $data
   * @return string
   */
  static public function buildPath($data) {
    return implode('/', $data);
  }

  /**
   * Returns an array of named params from the url
   * 
   * @param string $url
   * @return array
   */
  static public function params($url) {
    
    $path   = static::path($url);
    $parts  = str::split($path, '/');
    $params = array();
    
    // use ; as parameter separator on win
    $sep = DS == '/' ? ':' : ';';

    foreach($parts as $part) {
      $parts = str::split($part, $sep);
      if(count($parts) < 2) continue;
      $params[$parts[0]] = urldecode($parts[1]);
    }    

    return $params;

  }

  /**
   * Build params
   * 
   * @param array $data
   * @return string
   */
  static public function buildParams($data) {
    $params = array();
    $sep    = (DS == '/') ? ':' : ';';
    foreach($data as $key => $value) $params[] = $key . $sep . urlencode($value);
    return implode('/', $params);
  }

  /**
   * Returns the base url
   * 
   * @param string $url
   * @return string
   */
  static public function base($url) {
    $parsed = static::parse($url);
    if(!$parsed || empty($parsed['host'])) return false;
    return a::get($parsed, 'scheme', 'http') . '://' . $parsed['host'];
  }

  /**
   * Shortens a URL
   * It removes http:// or https:// and uses str::short afterwards
   *
   * <code>
   * 
   * echo url::short('http://veryveryverylongurl.com', 30);
   * // output: veryveryverylongurl.com
   *
   * </code>
   * 
   * @param  string  $url The URL to be shortened
   * @param  int     $chars The final number of characters the URL should have
   * @param  boolean $base True: only take the base of the URL. 
   * @param  string  $rep The element, which should be added if the string is too long. Ellipsis is the default.
   * @return string  The shortened URL  
   */  
  static public function short($url, $length = false, $base = false, $rep = '…') {
    
    if($base) $url = static::base($url);

    // replace all the nasty stuff from the url
    $url = str_replace(array('http://', 'https://', 'ftp://', 'www.'), '', $url);
    
    // try to remove the last / after the url
    $url = preg_replace('!(\/)$!', '', $url);

    return ($length) ? str::short($url, $length, $rep) : $url;
  
  }

  /** 
   * Checks if the URL has a query string attached
   * 
   * <code>
   * 
   * if(url::hasQuery('http://www.youtube.com/watch?v=9q_aXttJduk')) {
   *   echo 'Yes, there is a query attached';
   * }
   * 
   * </code>
   * 
   * @param  string  $url
   * @return boolean
   */
  static public function hasQuery($url) {
    return (str::contains($url, '?')) ? true : false;
  }

  /** 
   * Strips the query from the URL
   * 
   * <code>
   * 
   * echo url::stripQuery('http://www.youtube.com/watch?v=9q_aXttJduk');
   * // output: http://www.youtube.com/watch
   * 
   * </code>
   * 
   * @param  string  $url
   * @return string
   */
  static public function stripQuery($url) {
    return preg_replace('/\?.*$/is', '', $url);
  }

  /** 
   * Strips a hash value from the URL
   * 
   * <code>
   * 
   * echo url::stripHash('http://testurl.com/#somehash');
   * // output: http://testurl.com/
   * 
   * </code>
   * 
   * @param  string  $url
   * @return string
   */
  static public function stripHash($url) {
    return preg_replace('/#.*$/is', '', $url);
  }

  /** 
   * Checks for a valid URL
   * 
   * @param  string  $url
   * @return boolean
   */
  static public function valid($url) {
    return v::url($url);
  }

  /**
   * Tries to fix a broken url without protocol
   * 
   * @param string $url
   * @return string
   */
  static public function fix($url) {
    // make sure to not touch absolute urls
    return (!preg_match('!^(https|http|ftp)\:\/\/!i', $url)) ? 'http://' . $url : $url;
  }

  /**
   * Returns the home url if set
   * use event::on('kirby.toolkit.url.home', function() {...})
   * to overwrite the default home url
   * 
   * @return string
   */
  static public function home() {    

    $url = uri::current()->baseurl();

    if(event::exists('kirby.toolkit.url.home')) {
      event::trigger('kirby.toolkit.url.home', array(&$url, func_get_args()));
    } 
    
    return $url;

  }

  /**
   * A smart way to build urls
   * use event::on('kirby.toolkit.url.to', function() {...})
   * to customize url handling
   * 
   * @param $uri
   * @return string
   */
  static public function to($url = '/') {

    // make sure to not touch absolute urls
    if(preg_match('!^(https|http|ftp)\:\/\/!i', $url) or $url == '#') return $url;

    // use the custom handler if available
    if(event::exists('kirby.toolkit.url.to')) {  
      event::trigger('kirby.toolkit.url.to', array(&$url, func_get_args()));
      return $url;
    }

    // clean the uri
    $url  = ltrim($url, '/');
    $home = static::home();

    // don't do anything if the home url is blank
    if(empty($home)) return $url;

    // return the absolute url for the given uri by prepending the home url
    return empty($url) ? static::home() : static::home() . '/' . $url;

  }

  /**
   * Return the last url the user has been on if detectable
   * 
   * @return string
   */
  static public function last() {
    return visitor::referer();
  }

}
