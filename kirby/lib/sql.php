<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * SQL
 * 
 * SQL Query builder 
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Sql {

  // list of literals which should not be escaped in queries
  static protected $literals = array('NOW()'); 

  /**
   * Builds a select clause 
   * 
   * @param array $params List of parameters for the select clause. Check out the defaults for more info. 
   * @return string
   */
  static public function select($params = array()) {

    $defaults = array(
      'table'    => '', 
      'columns'  => '*',
      'join'     => false,
      'distinct' => false,
      'where'    => false,
      'group'    => false,
      'having'   => false,
      'order'    => false,
      'offset'   => 0,
      'limit'    => false,
    );

    $options = array_merge($defaults, $params);
    $query   = array();

    $query[] = 'SELECT';

    // select distinct values
    if($options['distinct']) $query[] = 'DISTINCT';
    
    $query[] = (empty($options['columns'])) ? '*' : implode(', ', (array)$options['columns']);
    $query[] = 'FROM ' . $options['table'];

    if(!empty($options['join'])) {
      foreach($options['join'] as $join) {
        $query[] = ltrim(strtoupper(a::get($join, 'type', '')) . ' JOIN ') . $join['table'] . ' ON ' . $join['on'];
      }
    }

    if(!empty($options['where'])) {
      $query[] = 'WHERE ' . $options['where'];
    }

    if(!empty($options['group'])) {
      $query[] = 'GROUP BY ' . $options['group'];
    }
  
    if(!empty($options['having'])) {
      $query[] = 'HAVING ' . $options['having'];
    }
  
    if(!empty($options['order'])) {
      $query[] = 'ORDER BY ' . $options['order'];
    }

    if($options['offset'] > 0 || $options['limit']) {
      if(!$options['limit']) $options['limit'] = '18446744073709551615';
      $query[] = 'LIMIT ' . $options['offset'] . ', ' . $options['limit'];
    }

    return implode(' ', $query);

  }

  /**
   * Builds an insert clause
   * 
   * @param array $params List of parameters for the insert clause. See defaults for more info
   * @return string
   */
  static public function insert($params = array()) {

    $defaults = array(
      'table'  => '', 
      'values' => false,
    );

    $options = array_merge($defaults, $params);
    $query   = array();

    $query[] = 'INSERT INTO ' . $options['table'];
    $query[] = static::values($options['values'], ', ', false);

    return implode(' ', $query);

  }

  /**
   * Builds an update clause
   * 
   * @param array $params List of parameters for the update clause. See defaults for more info
   * @return string
   */
  static public function update($params = array()) {

    $defaults = array(
      'table'  => '', 
      'values' => false,
      'where'  => false,
    );

    $options = array_merge($defaults, $params);
    $query   = array();

    $query[] = 'UPDATE ' . $options['table'] . ' SET';
    $query[] = static::values($options['values']);

    if(!empty($options['where'])) {
      $query[] = 'WHERE ' . $options['where'];
    }

    return implode(' ', $query);

  }

  /**
   * Builds a delete clause
   * 
   * @param array $params List of parameters for the delete clause. See defaults for more info
   * @return string
   */
  static public function delete($params = array()) {

    $defaults = array(
      'table'  => '', 
      'where'  => false,
    );

    $options = array_merge($defaults, $params);
    $query   = array();

    $query[] = 'DELETE FROM ' . $options['table'];

    if(!empty($options['where'])) {
      $query[] = 'WHERE ' . $options['where'];
    }

    return implode(' ', $query);

  }

  /**
   * Builds a safe list of values for insert, select or update queries
   * 
   * @param mixed $values A value string or array of values
   * @param string $separator A separator which should be used to join values
   * @param boolean $set If true builds a set list of values for update clauses 
   * @return string
   */
  static public function values($values, $separator = ', ', $set = true) {

    if(!is_array($values)) return $values;

    if($set) {

      $output = array();

      foreach($values AS $key => $value) {
        if(in_array($value, static::$literals)) {
          $output[] = $key . ' = ' . $value;
        } elseif(is_array($value)) {
          $output[] = $key . " = '" . json_encode($value) . "'";
        } else {
          $output[] = $key . " = '" . db::escape($value) . "'";
        }
      }

      return implode($separator, $output);

    } else {
      
      $fields = array();
      $output = array();
      
      foreach($values AS $key => $value) {
        $fields[] = $key;
        if(in_array($value, static::$literals)) {
          $output[] = $value;
        } elseif(is_array($value)) {
          $output[] = "'" . db::escape(json_encode($value)) . "'";
        } else {
          $output[] = "'" . db::escape($value) . "'";
        }
      }
  
      return '(' . implode($separator, $fields) . ') VALUES (' . implode($separator, $output) . ')'; 
    
    }

  }

}