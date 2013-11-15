<?php

namespace Kirby\Toolkit\Model;

use Kirby\Toolkit\A;
use Kirby\Toolkit\DB;
use Kirby\Toolkit\Model;
use Kirby\Toolkit\Str;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Database Model
 * 
 * Base class for building all kinds of database driven models
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Database extends Model {
  
  static protected $table = null;

  /**
   * Returns the DbQuery object with the table prefilled
   * 
   * @return object DbQuery
   */
  static public function table() {  

    $class = get_called_class();

    if(is_null(static::$table)) {

      // auto-guess the table name
      $table = strtolower($class) . 's';

      // strip the namespace
      if(str::contains($table, '\\')) {
        $table = str::split($table, '\\');
        $table = a::last($table);
      }

    } else {
      $table = static::$table;
    }

    return db::table($table)->fetch($class);    

  }

  /**
   * Inserts a new row to the table
   * 
   * @return boolean
   */
  protected function insert() {
    $insert = static::table()->insert($this->get());
    
    if($insert) {
      $this->id = $insert;
      return true;
    } else {
      return false;
    }

  }

  /**
   * Define this function in your model 
   * to update the model
   * 
   * @return boolean
   */
  protected function update() {
    return static::table()
                ->where(array($this->primaryKeyName() => $this->primaryKey()))
                ->update($this->get());  
  }

  /**
   * Define this function in your model 
   * to delete the model
   * 
   * @return boolean
   */
  public function delete() {
    return static::table()
                ->where(array($this->primaryKeyName() => $this->primaryKey()))
                ->delete();  
  }
  
  /**
   * Find a model by its primary key
   * 
   * @param mixed $primaryKey
   * @return object
   */
  static public function find($primaryKey) {
    return static::table()
                ->where(array(static::primaryKeyName() => $primaryKey))
                ->first();
  }

  /**
   * Makes it possible to call all DB/Query methods statically
   * for the model and thus get access to the full DB/Query functionality
   * within the model class. 
   * 
   * @param string $method
   * @param array $arguments
   * @return mixed
   */
  static public function __callStatic($method, $arguments = array()) {

    $table = static::table();

    if(method_exists($table, $method)) {
      return call_user_func_array(array(static::table(), $method), $arguments);
    } else {
      raise('Invalid model method: ' . $method);
    }

  }

}