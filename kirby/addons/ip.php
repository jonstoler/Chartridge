<?php

namespace Kirby\Toolkit;

// direct access protection
if(!defined('KIRBY')) die('Direct access is not allowed');

/**
 * IP 
 *
 * This class simplifies geolocation of
 * IP Addresses (powered by GeoIP). 
 * 
 * @package   Kirby Toolkit 
 * @author    Jonathan Stoler <jon@stoler.com>
 * @link      http://jonathan.stoler.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

// the server has this installed already
require_once('geoip/geoip.inc');
require_once('geoip/geoipregionvars.php');

class IP {

  protected static $ref = null;

  static private function open() {
    static::$ref = _geoip_open(KIRBY_TOOLKIT_ROOT_ADDONS . DS . 'geoip' . DS . 'GeoIP.dat', GEOIP_STANDARD);
  }
  static private function close() {
    _geoip_close(static::$ref);
  }


  static public function country($ip) {
    if(!static::$ref){
      static::open();
    }
    return _geoip_country_name_by_addr(static::$ref, $ip);
  }

}