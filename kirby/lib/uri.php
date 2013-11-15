<?php

namespace Kirby\Toolkit;

use Kirby\Toolkit\URI\Params;
use Kirby\Toolkit\URI\Path;
use Kirby\Toolkit\URI\Query;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * URI
 *
 * The Kirby URI object makes it possible
 * to inspect the current URL and modify it
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Uri {

  // store for the current URI singleton
  static protected $current = null;

  // all options 
  protected $options = array();
  
  // the file in the URL (i.e. index.php)
  protected $file;
    
  // the subfolder if defined in options
  protected $subfolder;
  
  // the path object (UriPath)
  protected $path;
  
  // the params object (UriParams)
  protected $params;
  
  // the query object (UriQuery)
  protected $query;

  // the file extension (i.e. html, js, php, etc)
  protected $extension;

  // the original URL
  protected $original;

  // the scheme (http or https)
  protected $scheme;
  
  // the hostname
  protected $host;
  
  // the base url http://yourdomain.com
  protected $baseurl;
  
  // the full url
  protected $url;
  
  // the parsed results
  protected $parsed;

  /**
   * Singleton for the current URI
   *
   * @param string $url Set a URL to overwrite the current URI
   * @param array $options Set additional options for the URI object
   * @return object
   */
  static public function current($url = null, $options = null) {

    if(!is_null(static::$current)) {
      if(is_null($url) && is_null($options)) {
        return static::$current;
      } else {
        // overwrite the url and options in the current URI
        return static::$current->set($url, $options);
      }
    } 

    // create a new current URI
    return static::$current = new URI($url, $options);

  }

  /**
   * Constructor
   * 
   * @param string $url
   * @param array $options
   */
  public function __construct($url = null, $options = array()) {

    if(is_array($url)) {
      $options = $url;
      $url     = a::get($options, 'url', null);
    }

    $this->set($url, $options);

  }

  /**
   * Sets the inspectable url and additional options
   * 
   * @param string $url
   * @param array $options
   */
  public function set($url = null, $options = array()) {

    $defaults = array(
      'file'      => null,
      'subfolder' => null,
      'strip'     => null
    );

    // reset all attributes
    $this->options   = array_merge($defaults, (array)$options);
    $this->file      = null;
    $this->subfolder = null;
    $this->path      = null;
    $this->params    = null;
    $this->query     = null;
    $this->extension = null;
    $this->original  = null;
    $this->scheme    = null;
    $this->host      = null;
    $this->baseurl   = null;
    $this->url       = null;
    $this->parsed    = null;

    // parse the given url;
    $this->parse($url);

    return $this;
        
  }

  /**
   * Private parsing method which will take the URL 
   * apart and analyze all its components
   * 
   * @param string $url
   */
  private function parse($url) {
    
    $defaults = array(
      'scheme' => (server::get('https') && str::lower(server::get('https')) != 'off') ? 'https' : 'http',
      'host'   => server::get('http_host'),
      'path'   => '',
      'port'   => '',
      'query'  => false,
    );
    
    // build the full url if not given
    if(!$url or $url == 'this') {
      $url = $defaults['scheme'] . '://' . $defaults['host'] . server::get('request_uri');
    }

    $this->original = $url;                    
    $this->parsed   = (object)array_merge($defaults, (array)@parse_url($url));
            
    // take some values from the parsed object and store it in URI attributes
    $this->scheme = $this->parsed->scheme;
    $this->host   = $this->parsed->host;
            
    // parse the query string into an array
    @parse_str($this->parsed->query, $this->parsed->query);

    // attach the subfolder object
    $this->subfolder = $this->options['subfolder'];    

    // get the path    
    $path = trim($this->parsed->path, '/\\');

    // auto-detect the subfolder
    if($this->subfolder == '@auto') {
      $this->subfolder = trim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }

    // make sure to clean the passed subfolder to avoid errors
    if($this->subfolder) {
      $path = trim(preg_replace('!^' . preg_quote($this->subfolder) . '!i', '/', $path), '/');
    }

    // strip additional stuff off the uri
    if($this->options['strip']) {
      $path = trim(preg_replace('!^' . preg_quote($this->options['strip']) . '!i', '/', $path), '/');
    }

    // split the path
    $parts = (array)str::split($path, '/');

    // use ; as parameter separator on win
    $sep = DS == '/' ? ':' : ';';

    // start a new params array
    $params = array();
    $path   = array();
    $pindex = 1;

    // parse params
    foreach($parts AS $p) {
      if(preg_match('/\\'. $sep . '/', $p)) {
        $parts = explode($sep, $p);
        if(count($parts) < 2) continue;
        $params[$parts[0]] = urldecode($parts[1]);
      } else {
        $path[$pindex] = urldecode($p);
        $pindex++;
      }
    }

    // get the extension from the last part of the path    
    $this->extension = f::extension(a::last($path));

    // handle the extension on the last element    
    if($this->extension) {
      $this->file = a::last($path);
    } else {
      $this->extension = f::extension($this->file);
    } 

    // create a new params and path array    
    $this->params = new Params($params);
    $this->path   = new Path($path);
    $this->query  = new Query($this->parsed->query);
                
    return $this->path;
                
  }

  /**
   * Returns the parsed information
   * 
   * @return array
   */
  public function parsed() {
    return $this->parsed;
  }

  /**
   * Returns the scheme of the URL (http or https)
   * 
   * @return string
   */
  public function scheme() {
    return $this->scheme;
  }

  /**
   * Returns the host name 
   * 
   * @return string
   */
  public function host() {
    return $this->host;
  }

  /**
   * Returns the original url
   * 
   * @return string
   */
  public function original() {
    return $this->original;
  }

  /**
   * Returns the base url (ie. http://yourdomain.com)
   * 
   * @return string
   */
  public function baseurl() {

    if(empty($this->host)) return false;

    if(!is_null($this->baseurl)) return $this->baseurl;

    // check for a port
    $port = ($this->parsed->port) ? ':' . $this->parsed->port : '';

    // create a full url out of this
    $parts   = array();
    $parts[] = $this->scheme . '://' . $this->host . $port;
    $parts[] = $this->subfolder;

    return $this->baseurl = rtrim(implode('/', $parts), '/');

  }

  /**
   * Returns the subfolder if defined in $this->options
   * 
   * @return string
   */
  public function subfolder() {
    return $this->subfolder;
  }

  /**
   * Returns the file (i.e. test.php)
   * 
   * @return string
   */
  public function file() {
    return $this->file;
  }

  /**
   * Returns the file extension (i.e. html, js, php, etc)
   * 
   * @return string
   */
  public function extension() {
    return $this->extension;
  }

  /**
   * Returns the full url
   * 
   * @return string
   */
  public function url() {
    return $this->toURL();
  }
  
  /**
   * Smart getter for the Path object
   * 
   * @param mixed $key null: get the entire path object, int: get a part of the path 
   * @param mixed $default null: no default. string: a default value if nothing is found for this key
   * @return string
   */
  public function path($key=null, $default=null) {
    if(is_null($key)) return $this->path;
    return $this->path->get($key, $default);
  }

  /**
   * Smart getter for the Params object
   * 
   * @param mixed $key null: get the entire params object, string: get a part of the params object by key 
   * @param mixed $default null: no default. string: a default value if nothing is found for this key
   * @return string
   */
  public function param($key=null, $default=null) {
    if(is_null($key)) return $this->params;
    return $this->params->get($key, $default);
  }

  /**
   * Alternative for $this->param()
   */
  public function params($key=null, $default=null) {
    return $this->param($key, $default);
  }
    
  /**
   * Smart getter for the Query object
   * 
   * @param mixed $key null: get the entire query object, string: get a part of the query object by key 
   * @param mixed $default null: no default. string: a default value if nothing is found for this key
   * @return string
   */
  public function query($key=null, $default=null) {
    if(is_null($key)) return $this->query;
    return $this->query->get($key, $default);
  }

  /**
   * Takes care of properly cloning child objects 
   * 
   * @return object $this
   */
  public function __clone() {
    $this->path   = clone $this->path;
    $this->params = clone $this->params;
    $this->query  = clone $this->query;
    return $this;
  }

  /**
   * Makes it possible to echo the object
   * and get a string represantation of the URI
   * 
   * @return string
   */
  public function __toString() {
    return $this->toString();
  }

  /**
   * Converts the URI object to a string
   * This returns the path and optionally the query
   *
   * @param boolean $includeQuery true: the query will be appended, false: this will only return the path 
   * @return string
   */
  public function toString($includeQuery = true) {
    
    $parts  = array();
    $path   = $this->path();
    $params = $this->params();
    $query  = $this->query();
        
    if($path->get())   $parts[] = (string)$path;
    if($params->get()) $parts[] = (string)$params;

    if($includeQuery && $query->get()) $parts[] = '?' . $query;
        
    return implode('/', $parts);  
  
  }

  /**
   * Converts the URI object to a proper URL
   * 
   * @param boolean $includeQuery true: the query will be appended, false: this will only return the path 
   * @return string
   */
  public function toUrl($includeQuery = true) {
    
    $parts = array();
    $parts[] = $this->baseurl();
    $parts[] = $this->toString($includeQuery);

    return rtrim(implode('/', $parts), '/');
  
  }
  
  /**
   * Converts the URI object to a unique md5 hash
   * 
   * @return string
   */
  public function toHash() {
    $url = $this->toURL();
    return md5($url);    
  }

  /**
   * Removes the entire path 
   * 
   * @return $this
   */
  public function stripPath() {
    $this->path = new Path();
    return $this;
  }

  /**
   * Replaces a parameter with a new value
   * 
   * @param string $key
   * @param mixed $value
   * @return object $this
   */
  public function replaceParam($key, $value) {
    $this->params->replace($key, $value);
    return $this;
  }

  /**
   * Removes a parameter
   * 
   * @param string $key
   * @return object $this
   */
  public function removeParam($key) {
    $this->params->remove($key);
    return $this;
  }

  /**
   * Removes all parameters 
   * 
   * @return $this
   */
  public function stripParams() {
    $this->params = new Params();
    return $this;
  }

  /**
   * Removes a parameter and returns the URL
   * 
   * @param string $param
   * @return string 
   */
  public function urlWithoutParam($param) {
    $this->removeParam($param);
    return $this->toUrl();    
  }

  /**
   * Replaces a query key with a new value
   * 
   * @param string $key
   * @param mixed $value
   * @return object $this
   */
  public function replaceQueryKey($key, $value) {
    $this->query->replace($key, $value);
    return $this;
  }

  /**
   * Removes a query key
   * 
   * @param string $key
   * @return object $this
   */
  public function removeQueryKey($key) {
    $this->query->remove($key);
    return $this;
  }

  /**
   * Removes the entire query
   * 
   * @return object $this
   */
  public function stripQuery() {
    $this->query = new Query();
    return $this;
  }

  /**
   * Removes a query key first and then returns the url
   * 
   * @return object $this
   */
  public function urlWithoutQueryKey($key) {
    $this->removeQueryKey($key);
    return $this->toUrl();    
  }

  /**
   * Returns a more readable dump array for the dump() helper
   * 
   * @return array
   */
  public function __toDump() {

    return array(
      'scheme'    => $this->scheme(),
      'host'      => $this->host(),
      'baseurl'   => $this->baseurl(),
      'file'      => $this->file(),
      'extension' => $this->extension(),
      'subfolder' => $this->subfolder(),
      'path'      => (string)$this->path(),
      'params'    => (string)$this->params(),
      'query'     => (string)$this->query(),
    );

  }

} 