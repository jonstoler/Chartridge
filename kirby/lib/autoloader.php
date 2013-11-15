<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Autoloader
 *
 * Simple helper to auto-load classes and create aliases for them if wanted
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Autoloader {

  public $aliases     = array();
  public $root        = null;
  public $namespace   = null;
  public $classfolder = false;

  /**
   * Registers the autoloader function 
   * and starts autoloading all available classes within $this->root
   * It also makes sure to create aliases for classes if wanted and autoload those as well.
   */
  public function start() {
    
    if(is_null($this->root))      raise('Please specify a root directory for the autoloader');
    if(!is_array($this->aliases)) raise('Aliases for the autloader must be defined as associative array');

    $autoloader = $this;

    spl_autoload_register(function($class) use ($autoloader) {

      // check for existing aliases
      if(array_key_exists(strtolower($class), $autoloader->aliases)) {
        // create an alias for that class      
        class_alias($autoloader->aliases[strtolower($class)], $class);
        $class = $autoloader->aliases[strtolower($class)];
        
        // check if the class has already been loaded
        if(class_exists($class)) return true;

      } 

      // prepare the path to the class file
      $replace = array($autoloader->namespace . '\\', '\\');
      $with    = array('', DS);

      // create the path to the class file. 
      $path = strtolower(str_replace($replace, $with, $class));
      
      foreach((array)$autoloader->root as $root) {
        $file = ($autoloader->classfolder) ? $root . DS . $path . DS . basename($path) . '.php' : $root . DS . $path . '.php';
        
        if(file_exists($file)) {
          require_once($file);
          break;
        }
      }

    });

  }

}