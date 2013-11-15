<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * Search 
 * 
 * A simple base class for searches, 
 * which helps with extracting search words, 
 * handling stopwords and building a sql search clause.
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Search {

  protected $options = array();
  protected $fields  = array();
  protected $query   = null;

  /**
   * Constructor
   * 
   * @param string $query
   * @param array $fields
   * @param array $params
   */
  public function __construct($query, $fields, $params = array()) {
    $this->query   = $query;
    $this->fields  = $fields;
    $this->options = array_merge($this->defaults(), $params);
  }

  /**
   * Returns all default values for the search object's option array
   * 
   * @return array
   */
  protected function defaults() {
    return array(
      'stopwords' => array(),
      'fields'    => array(), 
      'minlength' => 2,
      'maxwords'  => 10, 
      'operators' => array('word' => 'AND', 'field' => 'OR')
    );
  }

  /**
   * Returns the raw search query
   * 
   * @return string
   */
  public function query() {
    return $this->query;
  }

  /**
   * Returns the array of search fields
   * 
   * @return array
   */
  public function fields() {
    return $this->fields;
  }

  /**
   * Splits the search query into search words 
   * and strips all non-word characters
   * It also removes stopwords and makes sure that
   * search words are long enough. 
   * 
   * @return array
   */
  public function words() {

    $words = preg_replace('/[^\pL]/u',',', preg_quote($this->query));
    $words = str::split($words, ',', $this->options['minlength']);

    // remove stopwords 
    if(!empty($this->options['stopwords'])) {
      $words = array_diff($words, $this->options['stopwords']);
    }

    // limit the number of words
    $words = array_slice($words, 0, $this->options['maxwords']);

    return $words;

  }  

  /**
   * Builds the sql search clause
   * 
   * @return string
   */
  public function sql() {

    if(empty($this->fields)) return null;

    $clause = array();      

    foreach($this->fields as $field) {
      
      $sql = array(); 

      foreach($this->words() as $word) {
        $sql[] = $field . ' LIKE "%' . db::escape($word) . '%"';
      }

      $clause[] = '(' . implode(' ' . trim($this->options['operators']['word']) . ' ', $sql) . ')';

    }

    return implode(' ' . trim($this->options['operators']['field']) . ' ', $clause);

  }

  /**
   * Shortens strings but keeps words
   * Can be used to shorten strings starting from the beginning or the end
   * 
   * @param string $text
   * @param int $size The maximum character length for the final string
   * @param boolean $reverse If true, the string will be started from the end. 
   * @return string
   */
  protected function subwords($text, $size = 50, $reverse = false) {

    if($reverse) $text = strrev($text);

    $wrap = wordwrap($text, $size, '@@@@');
    $text = str::substr($text, 0, strpos($wrap,'@@@@'));

    if($reverse) $text = strrev($text);

    return $text;

  }

  /**
   * Generates a smart Googlish search excerpt with highlighted search words
   * Shortens parts between search words intelligently. 
   * 
   * @param string $text
   * @param int $size The max length of strings before and after search words
   * @return string
   */
  public function excerpt($text, $size = 20) {

    $tokens     = array();
    $text       = trim(strip_tags($text));
    $text       = preg_replace('!\s+!m', ' ', $text);
    $textlength = str::length($text);

    $words = array();

    foreach($this->words() as $word) {
      $words[] = '!\b(' . preg_quote($word) . ')\b!i';
    }

    $text   = preg_replace($words, '@@@@$1@@@@', $text, 1);
    $parts  = (preg_split('!(@@@@.*?@@@@)!i', $text, -1, PREG_SPLIT_DELIM_CAPTURE));
    $result = array();
    $count  = 0;

    foreach($parts as $part) {

      if(str::contains($part, '@@@@')) {
        // search word
        $result[] = preg_replace('!@@@@(.*?)@@@@!i', '<strong>$1</strong>', $part);
      } else {
        
        $length = str::length($part);

        if($length > $size * 2) {
    
          if($count == 0) {
            $part = '…' . $this->subwords($part, $size * 2, true);
          } else if($count == count($parts)-1) {
            $part = $this->subwords($part, $size * 2) . '…';
          } else {
            $part = $this->subwords($part, $size) . '…' . $this->subwords($part, $size, true);            
          }
        
        }

        // in between
        $result[] = $part;
  
      }

      $count++;

    }

    return implode($result);

  }

}