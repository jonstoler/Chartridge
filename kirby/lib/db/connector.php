<?php

namespace Kirby\Toolkit\DB;

use Exception;
use PDO;
use Kirby\Toolkit\A;
use Kirby\Toolkit\C;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * DB Connector
 * 
 * Used by the DB class to connect with different database types
 * Returns a PDO connection
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Connector {

  // the PDO connection
  protected $connection;
  
  // the DSN connection string 
  protected $dsn;
  
  // the optional prefix for all table names
  protected $prefix;
  
  // the database type. so far mysql and sqlite are supported
  protected $type;

  /**
   * Constructor
   * 
   * @param mixed $params Connection parameters
   */
  public function __construct($params = array()) {

    // connect to the default legacy mysql connection
    if(is_null($params)) {
      $params = array(
        'host'     => c::get('db.host'), 
        'user'     => c::get('db.user'),
        'password' => c::get('db.password'),
        'database' => c::get('db.name'),
        'prefix'   => c::get('db.prefix'),
        'charset'  => c::get('db.charset'),
        'type'     => 'mysql'
      );
    } else if(is_string($params)) {

      $params = c::get('db > ' . $params);

      // check for invalid connection params
      if(empty($params)) throw new Exception('Invalid connection details');

    }
    
    // get the connection method
    $type = a::get($params, 'type');

    // check for a valid connection method
    if(empty($type) or !method_exists($this, $type)) throw new Exception('The db type is not supported: ' . $type);

    // get the dsn
    $this->dsn  = $this->$type($params);

    // store the database type
    $this->type = $type; 

    // store the prefix for table names
    $this->prefix = a::get($params, 'prefix');

    // try to connect
    $this->connection = new PDO($this->dsn, a::get($params, 'user'), a::get($params, 'password'));
    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  }

  /**
   * Returns the established PDO connection 
   * 
   * @return object
   */
  public function connection() {
    return $this->connection;
  }

  /**
   * Returns the used database type
   * 
   * @return string
   */
  public function type() {
    return $this->type;
  }

  /**
   * Returns the optional prefix for table names
   * 
   * @return string
   */
  public function prefix() {
    return $this->prefix;
  }

  // connection methods

  /**
   * Returns an sqlite dsn string
   * 
   * @param array $params
   * @return string
   */
  protected function sqlite($params) {
    return 'sqlite:' . a::get($params, 'database');                    
  }

  /**
   * Returns a mysql dsn string
   * 
   * @param array $params
   * @return string
   */
  protected function mysql($params) {
    return 'mysql:host=' . a::get($params, 'host') . ';dbname=' . a::get($params, 'database') . ';charset=' . a::get($params, 'charset', 'utf8');
  }

}