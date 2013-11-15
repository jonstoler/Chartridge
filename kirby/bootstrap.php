<?php

/**
 * Kirby Toolkit Bootstrapper
 * 
 * Include this file to load all toolkit 
 * classes and helpers on demand
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

// helper constants
if(!defined('KIRBY'))     define('KIRBY',     true);
if(!defined('DS'))        define('DS',        DIRECTORY_SEPARATOR);
if(!defined('MB_STRING')) define('MB_STRING', (int)function_exists('mb_get_info'));

// stop loading the toolkit if another toolkit has already been loaded
if(defined('KIRBY_TOOLKIT_ROOT')) return;

// define toolkit roots
define('KIRBY_TOOLKIT_ROOT',     __DIR__);
define('KIRBY_TOOLKIT_ROOT_LIB', KIRBY_TOOLKIT_ROOT . DS . 'lib');

// load the autoloader
require_once(KIRBY_TOOLKIT_ROOT_LIB . DS . 'autoloader.php');

// initialize the autoloader
$autoloader = new Kirby\Toolkit\Autoloader();

// set the base root where all classes are located
$autoloader->root = KIRBY_TOOLKIT_ROOT_LIB;

// set the global namespace for all classes
$autoloader->namespace = 'Kirby\\Toolkit';

// add all needed aliases
$autoloader->aliases = array(
  'a'          => 'Kirby\\Toolkit\\A',
  'asset'      => 'Kirby\\Toolkit\\Asset',
  'autoloader' => 'Kirby\\Toolkit\\Autoloader',
  'c'          => 'Kirby\\Toolkit\\C',
  'cache'      => 'Kirby\\Toolkit\\Cache',
  'collection' => 'Kirby\\Toolkit\\Collection',
  'content'    => 'Kirby\\Toolkit\\Content',
  'cookie'     => 'Kirby\\Toolkit\\Cookie',
  'db'         => 'Kirby\\Toolkit\\DB',
  'dimensions' => 'Kirby\\Toolkit\\Dimensions',
  'dir'        => 'Kirby\\Toolkit\\Dir',
  'email'      => 'Kirby\\Toolkit\\Email',
  'error'      => 'Kirby\\Toolkit\\Error',
  'errors'     => 'Kirby\\Toolkit\\Errors',
  'event'      => 'Kirby\\Toolkit\\Event',
  'embed'      => 'Kirby\\Toolkit\\Embed',
  'event'      => 'Kirby\\Toolkit\\Event',
  'exif'       => 'Kirby\\Toolkit\\Exif',
  'f'          => 'Kirby\\Toolkit\\F',
  'form'       => 'Kirby\\Toolkit\\Form',
  'g'          => 'Kirby\\Toolkit\\G',
  'header'     => 'Kirby\\Toolkit\\Header',
  'html'       => 'Kirby\\Toolkit\\Html',
  'l'          => 'Kirby\\Toolkit\\L',
  'model'      => 'Kirby\\Toolkit\\Model',
  'obj'        => 'Kirby\\Toolkit\\Object',
  'object'     => 'Kirby\\Toolkit\\Object',
  'pagination' => 'Kirby\\Toolkit\\Pagination',
  'password'   => 'Kirby\\Toolkit\\Password',
  'r'          => 'Kirby\\Toolkit\\R',
  'redirect'   => 'Kirby\\Toolkit\\Redirect',
  'remote'     => 'Kirby\\Toolkit\\Remote',
  'redirect'   => 'Kirby\\Toolkit\\Redirect',
  'response'   => 'Kirby\\Toolkit\\Response',
  'router'     => 'Kirby\\Toolkit\\Router',
  'route'      => 'Kirby\\Toolkit\\Router\\Route',
  's'          => 'Kirby\\Toolkit\\S',
  'search'     => 'Kirby\\Toolkit\\Search',
  'server'     => 'Kirby\\Toolkit\\Server',
  'sql'        => 'Kirby\\Toolkit\\SQL',
  'str'        => 'Kirby\\Toolkit\\Str',
  'thumb'      => 'Kirby\\Toolkit\\Thumb',
  'timer'      => 'Kirby\\Toolkit\\Timer',
  'template'   => 'Kirby\\Toolkit\\Template',
  'txtstore'   => 'Kirby\\Toolkit\\Txtstore',
  'upload'     => 'Kirby\\Toolkit\\Upload',
  'uri'        => 'Kirby\\Toolkit\\URI',
  'url'        => 'Kirby\\Toolkit\\URL',
  'v'          => 'Kirby\\Toolkit\\V',
  'validator'  => 'Kirby\\Toolkit\\Validator',
  'validation' => 'Kirby\\Toolkit\\Validation',
  'visitor'    => 'Kirby\\Toolkit\\Visitor',
  'xml'        => 'Kirby\\Toolkit\\XML',
);

// start autoloading
$autoloader->start();

// load addons
require_once(KIRBY_TOOLKIT_ROOT . DS . 'addons' . DS . 'bootstrap.php');

// load the default config values
require_once(KIRBY_TOOLKIT_ROOT . DS . 'defaults.php');

// set the default timezone
date_default_timezone_set(c::get('timezone', 'UTC'));

// load the helper functions
require_once(KIRBY_TOOLKIT_ROOT . DS . 'helpers.php');
