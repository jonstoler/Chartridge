<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * Embed
 * 
 * Simple embedding of stuff like
 * flash, youtube videos, vimeo videos or gists
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Embed {

  /**
   * Embeds a flash file
   * 
   * @param string $url The url of the fla or swf
   * @param int $width 
   * @param int $height 
   * @param array $params Additional options (see $defaults array)
   * @return string
   */
  static public function flash($url, $width, $height, $params = array()) {

    $defaults = array(
      'allowScriptAccess' => 'always',
      'allowFullScreen'   => 'true'
    );

    $options = array_merge($defaults, $params);
    $content = array();
    $attr    = array();

    $content[] = '';
    $content[] = Html::tag('param', null, array('name' => 'movie', 'value' => $url));      

    foreach($options as $key => $value) {
      $content[]  = Html::tag('param', null, array('name' => $key, 'value' => $value));    
      $attr[$key] = $value; 
    }

    $content[] = Html::tag('embed', '', array_merge(array(
      'src'    => $url,
      'type'   => 'application/x-shockwave-flash',
      'width'  => $width,
      'height' => $height
    ), $attr));

    $content[] = '';

    return Html::tag('object', implode(PHP_EOL, $content), array('width' => $width, 'height' => $height));

  }

  /**
   * Embeds a youtube video by passing the Youtube url 
   * 
   * @param string $url Youtube url i.e. http://www.youtube.com/watch?v=d9NF2edxy-M
   * @param array $attr Additional attributes for the iframe 
   * @return string
   */
  static public function youtube($url, $attr = array()) {
    
    // http://www.youtube.com/embed/d9NF2edxy-M
    if(@preg_match('!youtube.com\/embed\/([a-z0-9_-]+)!i', $url, $array)) {
      $id = @$array[1];      
    // http://www.youtube.com/watch?feature=player_embedded&v=d9NF2edxy-M#!
    } elseif(@preg_match('!v=([a-z0-9_-]+)!i', $url, $array)) {
      $id = @$array[1];
    // http://youtu.be/d9NF2edxy-M
    } elseif(@preg_match('!youtu.be\/([a-z0-9_-]+)!i', $url, $array)) {
      $id = @$array[1];
    }
    
    // no id no result!    
    if(empty($id)) return false;
    
    // build the embed url for the iframe    
    $url = 'http://www.youtube.com/embed/' . $id;

    // default attributes
    $attr = array_merge(array(
      'frameborder'           => '0',
      'webkitAllowFullScreen' => 'true',
      'mozAllowFullScreen'    => 'true',
      'allowFullScreen'       => 'true', 
      'width'                 => '100%',
      'height'                => '100%',
    ), $attr);

    return Html::iframe($url, $attr);
        
  }

  /**
   * Embeds a vimeo video by passing the vimeo url 
   * 
   * @param string $url vimeo url i.e. http://vimeo.com/52345557
   * @param array $attr Additional attributes for the iframe 
   * @return string
   */
  static public function vimeo($url, $attr = array()) {

    // get the uid from the url
    $id = str::match($url, '!vimeo.com\/([0-9]+)!i', 1);
    
    // no id no result!    
    if(empty($id)) return false;    

    // build the embed url for the iframe    
    $url = 'http://player.vimeo.com/video/' . $id;

    // default attributes
    $attr = array_merge(array(
      'frameborder'           => '0',
      'webkitAllowFullScreen' => 'true',
      'mozAllowFullScreen'    => 'true',
      'allowFullScreen'       => 'true',
      'width'                 => '100%',
      'height'                => '100%',
    ), $attr);

    return Html::iframe($url, $attr);
      
  }

  /**
   * Embeds a github gist
   * 
   * @param string $url Gist url: i.e. https://gist.github.com/2924148
   * @param string $file The name of a particular file from the gist, which should displayed only. 
   * @return string
   */
  static public function gist($url, $file = null) {

    // url for the script file
    $url = $url . '.js';
    
    // load a specific file
    if(!is_null($file)) $url .= '?file=' . $file;
    
    // load the gist
    return Html::script($url);

  }

}