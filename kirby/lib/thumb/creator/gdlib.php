<?php

namespace Kirby\Toolkit\Thumb\Creator;

use Kirby\Toolkit\Thumb\Creator;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * GDLib Thumb Creator
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class GDLib extends Creator {

  /**
   * Check if the Creator is runnable
   */
  protected function check() {

    // check for a valid GD lib installation    
    if(!function_exists('gd_info')) raise('GD Lib is not installed');

  }

  /**
   * Run the thumb creator code
   */
  public function run() {

    // make enough memory available to scale bigger images
    ini_set('memory_limit', $this->thumb->options['memory']);
    
    // create the gd lib image object
    switch($this->thumb->image->mime()) {
      case 'image/jpeg':
        $image = @imagecreatefromjpeg($this->thumb->image->root()); 
        break;
      case 'image/png':
        $image = @imagecreatefrompng($this->thumb->image->root()); 
        break;
      case 'image/gif':
        $image = @imagecreatefromgif($this->thumb->image->root()); 
        break;
      default:
        raise('The image mime type is invalid');
        break;
    }       

    // check for a valid created image object
    if(!$image) raise('The image could not be created');

    // cropping stuff needs a couple more steps              
    if($this->thumb->options['crop'] == true) {

      // Starting point of crop
      $startX = floor($this->thumb->tmp->width()  / 2) - floor($this->thumb->result->width() / 2);
      $startY = floor($this->thumb->tmp->height() / 2) - floor($this->thumb->result->height() / 2);
          
      // Adjust crop size if the image is too small
      if($startX < 0) $startX = 0;
      if($startY < 0) $startY = 0;
      
      // create a temporary resized version of the image first
      $thumb = imagecreatetruecolor($this->thumb->tmp->width(), $this->thumb->tmp->height()); 
      $thumb = $this->keepColor($thumb);
      imagecopyresampled($thumb, $image, 0, 0, 0, 0, $this->thumb->tmp->width(), $this->thumb->tmp->height(), $this->thumb->source->width(), $this->thumb->source->height()); 
      
      // crop that image afterwards      
      $cropped = imagecreatetruecolor($this->thumb->result->width(), $this->thumb->result->height()); 
      $cropped = $this->keepColor($cropped);
      imagecopyresampled($cropped, $thumb, 0, 0, $startX, $startY, $this->thumb->tmp->width(), $this->thumb->tmp->height(), $this->thumb->tmp->width(), $this->thumb->tmp->height()); 
      imagedestroy($thumb);
      
      // reasign the variable
      $thumb = $cropped;

    } else {
      $thumb = imagecreatetruecolor($this->thumb->result->width(), $this->thumb->result->height()); 
      $thumb = $this->keepColor($thumb);
      imagecopyresampled($thumb, $image, 0, 0, 0, 0, $this->thumb->result->width(), $this->thumb->result->height(), $this->thumb->source->width(), $this->thumb->source->height()); 
    }    
  
    // convert the thumbnail to grayscale    
    if($this->thumb->options['grayscale']) {
      imagefilter($thumb, IMG_FILTER_GRAYSCALE);
    }

    // convert the image to a different format
    if($this->thumb->options['to']) {

      switch($this->thumb->options['to']) {
        case 'jpg': 
          imagejpeg($thumb, $this->thumb->root(), $this->thumb->options['quality']); 
          break;
        case 'png': 
          imagepng($thumb, $this->thumb->root(), 0); 
          break; 
        case 'gif': 
          imagegif($thumb, $this->thumb->root()); 
          break;        
      }

    // keep the original file's format
    } else {

      switch($this->thumb->image->mime()) {
        case 'image/jpeg': 
          imagejpeg($thumb, $this->thumb->root(), $this->thumb->options['quality']); 
          break;
        case 'image/png': 
          imagepng($thumb,  $this->thumb->root(), 0); 
          break; 
        case 'image/gif': 
          imagegif($thumb,  $this->thumb->root()); 
          break;
      }

    }

    imagedestroy($thumb);
    
  }
 
  /**
   * Keeps the transparent background on gifs and pngs
   * 
   * @param object $image
   * @return object
   */
  protected function keepColor($image) {
    imagesavealpha($image, true);
    $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $color);
    return $image;
  }
  
}