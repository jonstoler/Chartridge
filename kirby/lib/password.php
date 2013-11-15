<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Password
 * 
 * Password encryption class
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Password {

  /**
   * Generates a salted hash for a plaintext password
   * 
   * @param string $plaintext
   * @return string
   */
  static public function hash($plaintext) {
    $salt = substr(str_replace('+', '.', base64_encode(sha1(str::random(), true))), 0, 22);
    return crypt($plaintext, '$2a$10$' . $salt);
  }

  /**
   * Checks if a password matches the encrypted hash
   * 
   * @param string $plaintext
   * @param string $hash
   * @return boolean
   */
  static public function match($plaintext, $hash) {
    return crypt($plaintext, $hash) === $hash;
  }  

}