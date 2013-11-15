<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * 
 * Directory
 * 
 * This class makes it easy to create/edit/delete 
 * directories on the filesystem
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Dir {
  
  /**
   * Creates a new directory
   * 
   * <code>
   * 
   * $create = dir::make('/app/test/new-directory');
   * 
   * if($create) echo 'the directory has been created';
   * 
   * </code>
   * 
   * @param   string  $dir The path for the new directory
   * @return  boolean True: the dir has been created, false: creating failed
   */
  static public function make($dir, $recursive = true) {
    return (is_dir($dir)) ? true : @mkdir($dir, c::get('dir.make.permissions', 0755), $recursive);
  }

  /**
   * Reads all files from a directory and returns them as an array. 
   * It skips unwanted invisible stuff. 
   * 
   * <code>
   * 
   * $files = dir::read('mydirectory');
   * // returns array('file-1.txt', 'file-2.txt', 'file-3.txt', etc...);
   * 
   * </code>
   * 
   * @param   string  $dir The path of directory
   * @param   array   $ignore Optional array with filenames, which should be ignored
   * @return  mixed   An array of filenames or false
   */
  static public function read($dir, $ignore = array()) {
    if(!is_dir($dir)) return array();
    $skip = array_merge(c::get('dir.read.ignore'), $ignore);
    return (array)array_diff(scandir($dir),$skip);
  }

  /**
   * Moves a directory to a new location
   *
   * <code>
   * 
   * $move = dir::move('mydirectory', 'mynewdirectory');
   * 
   * if($move) echo 'the directory has been moved to mynewdirectory';
   * 
   * </code>
   * 
   * @param   string  $old The current path of the directory
   * @param   string  $new The desired path where the dir should be moved to
   * @return  boolean True: the directory has been moved, false: moving failed
   */  
  static public function move($old, $new) {
    if(!is_dir($old)) return false;
    return @rename($old, $new);
  }

  /**
   * Deletes a directory
   * 
   * <code>
   * 
   * $remove = dir::remove('mydirectory');
   * 
   * if($remove) echo 'the directory has been removed';
   * 
   * </code>
   * 
   * @param   string   $dir The path of the directory
   * @param   boolean  $keep If set to true, the directory will flushed but not removed. 
   * @return  boolean  True: the directory has been removed, false: removing failed
   */  
  static public function remove($dir, $keep=false) {
    if(!is_dir($dir)) return false;

    $handle = @opendir($dir);
    $skip   = array('.', '..');

    if(!$handle) return false;

    while($item = @readdir($handle)) {
      if(is_dir($dir . DS . $item) && !in_array($item, $skip)) {
        static::remove($dir . DS . $item);
      } else if(!in_array($item, $skip)) {
        f::remove($dir . DS . $item);
      }
    }

    @closedir($handle);
    if(!$keep) return rmdir($dir);
    return true;

  }

  /**
   * Flushes a directory
   * 
   * @param   string   $dir The path of the directory
   * @return  boolean  True: the directory has been flushed, false: flushing failed
   */  
  static public function clean($dir) {
    return static::remove($dir, true);
  }

  /**
   * Gets the size of the directory and all subfolders and files
   * 
   * @param   string   $dir The path of the directory
   * @param   boolean  $recursive 
   * @param   boolean  $nice returns the size in a human readable size 
   * @return  mixed  
   */  
  static public function size($dir, $recursive = true, $nice = false) {
    if(!file_exists($dir)) return false;
    if(is_file($dir))      return f::size($path, $nice);
    $size = 0;
    
    foreach(dir::read($dir) AS $file) {
      if(is_dir($dir . DS . $file) && $recursive) {
        $size += static::size($dir . DS . $file, true);
      } else {
        $size += f::size($dir . DS . $file);
      }
    }
    return ($nice) ? f::niceSize($size) : $size;
  }

  /**
   * Returns a nicely formatted size of all the contents of the folder
   * 
   * @param string $dir The path of the directory
   * @param boolean $recursive
   * @return mixed
   */
  static public function niceSize($dir, $recursive = true) {
    return static::size($dir, $recursive, true);
  } 

  /**
   * Recursively check when the dir and all 
   * subfolders have been modified for the last time. 
   * 
   * @param   string   $dir The path of the directory
   * @param   int      $modified internal modified store 
   * @return  int  
   */  
  static public function modified($dir, $modified = false) {
    if($modified === false) $modified = filemtime($dir);
    $files = static::read($dir);
    foreach($files AS $file) {
      if(!is_dir($dir . DS . $file)) continue;
      $filectime = filemtime($dir . DS . $file);
      $modified  = ($filectime > $modified) ? $filectime : $modified;
      $modified  = static::modified($dir . DS . $file, $modified);
    }
    return $modified;
  }

  /**
   * Checks if the dir is writable
   * 
   * @param string $dir
   * @return boolean
   */
  static public function writable($dir) {
    return is_writable($dir);
  }

  /**
   * Checks if the dir is readable
   * 
   * @param string $dir
   * @return boolean
   */
  static public function readable($dir) {
    return is_readable($dir);
  }

}