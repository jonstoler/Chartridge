<?php

namespace Kirby\Toolkit;

use Iterator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Collection 
 * 
 * An iterator base for every array, 
 * which needs a little more love
 *
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Collection implements Iterator {

  // holds all items in the collection
  protected $data = array();
  
  // holds the optional pagination object after $this->paginate()
  protected $pagination = null;
  
  /**
   * Creates a new collection
   * 
   * @param array $array the initial array for the iterator
   */ 
  public function __construct($array=array()) {
    
    if(!is_array($array)) return false;

    foreach($array as $key => $value) {
      $this->data['_' . $key] = $value;
    }
  }

  /**
   * Adds a new element or overwrites an existing
   * element in the array
   * 
   * Can also be used to set the entire array by 
   * passing the array as first argument instead of a key
   * 
   * @param string $key the name of the key, which should be set or replaced
   * @param mixed $value an array or object or whatever you want to store
   */ 
  public function set($key, $value = null) {
    
    if(is_array($key)) {
      foreach($key as $key => $value) {
        $this->set($key, $value);
      }
      return $this;    
    }
    
    if($value === false || $value === null) {
      unset($this->{$key});
      return $this;
    }
    
    $this->data['_' . $key] = $value;
    return $this;
  }
      
  /**
   * Magic setter
   * 
   * Can be used to simply set values like this
   * `$mycollection->mykey = 'My value'`;
   * 
   * @param string $key the name of the key, which should be set or replaced (auto-filled by PHP)
   * @param mixed $value an array or object or whatever you want to store
   */ 
  public function __set($key, $value) {
    $this->set($key, $value);
  }


  /**
   * Get an element from the array
   * 
   * @param string $key the name of the key to get. if no key is given, the entire array will be returned.
   * @param mixed $default the optional fallback value, which will be returned if the key has not been found
   * @return mixed Whatever has been found for the key
   */ 
  public function get($key = null, $default = null) {
    if(is_null($key)) return $this->data;
    return isset($this->data['_' . $key]) ? $this->data['_' . $key] : $default;  
  }

  /**
   * Magic getter
   * 
   * Can be used like this: 
   * `$mycollection->myvalue`
   * to get a part of the array
   * 
   * @param string $key the key to get. Will be auto-filled by PHP
   * @return mixed Whatever is stored for that key
   */
  public function __get($key) {
    return $this->get($key);
  }
  
  /** 
   * Magic getter method
   * 
   * The magic getter functions are used to provide 
   * an addtional way to get elements from the array.
   * I.e. `$mycollection->myvalue()`
   * 
   * @param string $key the name of the key, which should be returned
   * @return mixed Whatever is stored for that key
   */
  public function __call($key, $args) {
    return $this->get($key);
  }

  /** 
   * Checks if the current key is set
   * 
   * `isset($mycollection->mykey)`
   * 
   * @param string $key the key to check
   * @return boolean
   */
  public function __isset($key) {
    return isset($this->data['_' . $key]) ? true : false;
  }

  /** 
   * Removes an element from the array by key
   * 
   * `unset($mycollection->mykey)`
   * 
   * @param string $key the name of the key
   */
  public function __unset($key) {
    unset($this->data['_' . $key]);
  }

  /** 
   * Moves the cusor to the first element of the array
   */
  public function rewind() {
    reset($this->data);
  }

  /** 
   * Returns the current element of the array
   * 
   * @return mixed
   */
  public function current() {
    return current($this->data);
  }

  /** 
   * Returns the current key from the array
   * 
   * @return string
   */
  public function key() {
    return $this->cleankey(key($this->data));
  }

  /** 
   * Returns an array of all keys from the array
   * 
   * @return array
   */
  public function keys() {
    $keys  = array_keys($this->data);
    $clean = array();
    foreach($keys as $key) {
      $clean[] = $this->cleankey($key);
    }
    return $clean;
  }

  /** 
   * Moves the cursor to the next element in the array
   * and returns it
   * 
   * @return mixed
   */
  public function next() {
    return next($this->data);
  }

  /** 
   * Moves the cursor to the previous element in the array
   * and returns it
   * 
   * @return mixed
   */
  public function prev() {
    return prev($this->data);
  }

  /** 
   * Returns the nth element from the array
   * 
   * @return mixed
   */
  public function nth($n) {
    $array = array_values($this->data);
    return (isset($array[$n])) ? $array[$n] : false;
  }

  /** 
   * Checks if an element is valid
   * This is needed for the Iterator implementation
   * 
   * @return boolean
   */
  public function valid() {
    $key = key($this->data);
    return ($key !== null && $key !== false);
  }

  /**
   * Removes all data from the $data array
   * Can also be used to set a fresh array.
   * 
   * @param array $data a new array
   */
  public function reset($data = array()) {
    // remove the old data
    $this->data = array();
    // set the new data    
    if(!empty($data)) $this->set($data);
  }

  /** 
    * Adds a new element to the collection
    * 
    * It's basically an alternative for set()
    * 
    * @param string $key
    * @param mixed $value
    * @return object returns the current object to make it chainable
    */  
  public function add($key, $value) {
    return $this->set($key, $value);
  }
  
  /** 
    * Replaces an element in the collection
    * 
    * It's yet another alternative for set()
    * 
    * @param string $key
    * @param mixed $value
    * @return object returns the current object to make it chainable
    */  
  public function replace($key, $value) {
    return $this->set($key, $value);
  }

  /** 
    * Removes an element from the current collection
    * 
    * It's an alternative for unset($object->element)
    * 
    * @param string $key
    * @return object returns the current object to make it chainable
    */  
  public function remove($key = null) {

    if(is_null($key)) { 
      $this->reset();
      return $this;
    }

    unset($this->data['_' . $key]);
    return $this;
  }
  
  /** 
   * Find a bunch of elements from the collection
   * and return a new collection with those
   * 
   * @param args any number of arguments. each must be a key to look for
   * @return object a new collection object
   */
  public function find() {
    
    $result = array();
    $self   = clone $this;
        
    foreach(func_get_args() as $key) {
      $result[$key] = $this->get($key);
    }
            
    $self->reset($result);    

    // only return one element if there's just one
    if($self->count() == 1) return $self->first();
    
    return $self;

  }

  /** 
   * Apply a callback to each element
   * 
   * @param func $callback the callback function
   */
  public function each($callback) {
    $index=0;
    foreach($this->get() as $key => $value) {
      $callback($value, $index, $key);
      $index++;    
    }  
  }
    
  /**
   * Counts all elements in the array
   * 
   * @return int 
   */      
  public function count() {
    return count($this->data);
  }  

  /**
   * Returns the first element from the array
   * 
   * @return mixed
   */      
  public function first() {
    $array = $this->data;
    return array_shift($array); 
  }

  /**
   * Returns the last element from the array
   * 
   * @return mixed
   */      
  public function last() {
    $array = $this->data;
    return array_pop($array); 
  }

  /**
   * Tries to find the key for the given element
   * 
   * @param  mixed $needle the element to search for
   * @return mixed the name of the key or false
   */      
  public function keyOf($needle) {
    return $this->cleankey(array_search($needle, $this->data));
  }

  /**
   * Tries to find the index number for the given element
   * 
   * @param  mixed $needle the element to search for
   * @return mixed the name of the key or false
   */      
  public function indexOf($needle) {
    return array_search($needle, array_values($this->data));
  }

  // Methods which clone the current object

  /**
   * Filter the elements in the array by a callback function
   * 
   * @param  func $callback the callback function
   * @return object a new filtered collection 
   */      
  public function filter($callback) {
    $self = clone $this;
    $self->data = array_filter($self->data, $callback);
    return $self;
  }

  /**
   * Shuffle all elements in the array
   * 
   * @return object a new shuffled collection 
   */      
  public function shuffle() {
    // shuffle array but keep the keys
    $self = clone $this;
    $keys = array_keys($self->data); 
    shuffle($keys); 
    $self->data = array_merge(array_flip($keys), $self->data); 
    return $self;
  }

  /**
   * Returns a new collection without the given element(s)
   * 
   * @param args any number of keys, passed as individual arguments
   * @return object a new collection without the element(s)
   */      
  public function not() {
    $args = func_get_args();
    $self = clone $this;
    foreach($args as $kill) {
      unset($self->data['_' . $kill]);
    }
    return $self;
  }

  /**
   * Returns a new collection without the given element(s)
   * 
   * @param args any number of keys, passed as individual arguments
   * @return object a new collection without the element(s)
   */      
  public function without() {
    $args = func_get_args();
    return call_user_func_array(array($this, 'not'), $args);
  }

  /**
   * Returns a slice of the collection
   * 
   * @param int $offset The optional index to start the slice from
   * @param int $limit The optional number of elements to return
   * @return object a new collection 
   */      
  public function slice($offset=null, $limit=null) {
    if($offset === null && $limit === null) return $this;
    $self = clone $this;
    $self->data = (array_slice($this->data, $offset, $limit));
    return $self;
  }

  /**
   * Returns a new collection with a limited number of elements
   * 
   * @param int $limit The number of elements to return
   * @return object a new collection 
   */      
  public function limit($limit) {
    return $this->slice(0, $limit);
  }

  /**
   * Returns a new collection starting from the given offset
   * 
   * @param int $offset The index to start from
   * @return object a new collection 
   */      
  public function offset($offset) {
    return $this->slice($offset);
  }

  /**
   * Returns the array in reverse order
   * 
   * @return object a new collection in reverse order 
   */      
  public function flip() {
    $self = clone $this;
    $self->data = array_reverse($self->data, true);
    return $self;
  }

  /**
   * Filters the current collection by a field, operator and search value
   * 
   * @return object a new filtered collection
   */      
  public function filterBy() {

    $args     = func_get_args();
    $field    = a::get($args, 0);
    $operator = '=='; 
    $value    = a::get($args, 1);
    $split    = a::get($args, 2);
  
    $operators = array('!=', '==', '*=', '>', '<', '>=', '<=');

    if(is_string($value) && in_array($value, $operators)) {
      $operator = $value;
      $value    = a::get($args, 2);
      $split    = a::get($args, 3);
    }          
    
    $collection = clone $this;

    switch($operator) {

      // ignore matching elements
      case '!=':

        foreach($collection->toArray() as $key => $item) {
          if($split) {
            $values = str::split((string)$this->filterByValue($item, $field), $split);
            if(in_array($value, $values)) $collection->remove($key);
          } else if($this->filterByValue($item, $field) == $value) {
            $collection->remove($key);
          }

        }
        break;    
      
      // search
      case '*=':
        
        foreach($collection->toArray() as $key => $item) {
          if($split) {
            $values = str::split((string)$this->filterByValue($item, $field), $split);
            foreach($values as $val) {
              if(str::contains($val, $value) == false) {
                $collection->remove($key);
                break;
              }
            }
          } else if(str::contains($this->filterByValue($item, $field), $value) == false) {
            $collection->remove($key);
          }

        }

        break;

      // greater than
      case '>':

        foreach($collection->toArray() as $key => $item) {
          if($this->filterByValue($item, $field) > $value) continue;
          $collection->remove($key);
        }

        break;

      // less than
      case '<':

        foreach($collection->toArray() as $key => $item) {
          if($this->filterByValue($item, $field) < $value) continue;
          $collection->remove($key);
        }

        break;

      // greater than and equal to
      case '>=':

        foreach($collection->toArray() as $key => $item) {
          if($this->filterByValue($item, $field) >= $value) continue;
          $collection->remove($key);
        }

        break;

      // less than and equal to
      case '<=':

        foreach($collection->toArray() as $key => $item) {
          if($this->filterByValue($item, $field) <= $value) continue;
          $collection->remove($key);
        }

        break;
                            
      // take all matching elements          
      default:

        foreach($collection->toArray() as $key => $item) {

          if($split) {
            $values = str::split((string)$this->filterByValue($item, $field), $split);
            if(!in_array($value, $values)) $collection->remove($key);
          } else if($this->filterByValue($item, $field) != $value) {            
            $collection->remove($key);
          }
        
        }

        break;

    }

    return $collection;

  }   

  /**
   * Makes sure to provide a valid value for each filter method
   * no matter if an object or an array is given
   * 
   * @param mixed $item
   * @param string $field
   * @return mixed
   */
  protected function filterByValue($item, $field) {
    if(is_array($item)) {
      return a::get($item, $field);
    } else if(is_object($item)) {
      return $item->$field();
    } else {
      return false;
    }
  } 

  // Conversion Helpers
  
  /**
   * Converts the current object into an array
   * 
   * @return array
   */      
  public function toArray() {
    $clean = array();
    foreach($this->data as $dirtyKey => $value) {
      $clean[ $this->cleankey($dirtyKey) ] = $value;
    }
    $this->rewind();
    return $clean;
  }

  /**
   * Converts the current object into a json string
   * 
   * @return string
   */      
  public function toJSON() {
    return json_encode($this->toArray());
  }

  /**
   * Add pagination
   *
   * @param int $limit the number of items per page
   * @param array $options and optional array with options for the pagination class
   * @return object a sliced set of data
   */
  public function paginate($limit, $options = array()) {

    if(is_a($limit, 'Kirby\\Toolkit\\Pagination')) {
      $this->pagination = $limit;
      return $this;
    }

    $pagination = new Pagination($this->count(), $limit, $options);
    $pages = $this->slice($pagination->offset(), $pagination->limit());
    $pages->pagination = $pagination;

    return $pages;

  }
    
  /**
   * Get the previously added pagination object
   *
   * @return object
   */
  public function pagination() {
    return $this->pagination;
  }

  /**
   * Groups the collection by a given field
   *
   * @param string $field 
   * @return object A new collection with an item for each group and a subcollection in each group
   */
  public function groupBy($field, $i = true) {

    $groups = array();

    foreach($this->toArray() as $key => $item) {
  
      // get the value to group by      
      $value = $this->filterByValue($item, $field);

      // make sure that there's always a proper value to group by      
      if(!$value) raise('Invalid grouping value for key: ' . $key);

      // make sure we have a proper key for each group
      if(is_object($value) or is_array($value)) raise('You cannot group by arrays or objects');

      // ignore upper/lowercase for group names
      if($i) $value = str::lower($value);

      if(!isset($groups[$value])) {
        // create a new entry for the group if it does not exist yet      
        $groups[$value] = new Collection(array($key => $item));
      } else {
        // add the item to an existing group
        $groups[$value]->$key = $item;
      }

    }

    return new Collection($groups);

  }

  // Private Methods
  
  /**
   * Removes the leading underscore from internal key
   * 
   * @param string $dirtyKey the key with the leading underscore
   * @return string the clean version of the key
   */      
  private function cleankey($dirtyKey) {
    return preg_replace('!^(_)!', '', $dirtyKey);  
  }

}