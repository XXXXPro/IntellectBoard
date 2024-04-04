<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.05
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url https://www.intbpro.ru
 *  Преобразование EXIF-геоданных в числовой формат
 *  Код взят из https://stackoverflow.com/questions/2526304/php-extract-gps-exif-data
 *  ================================ */

 class Library_exifgps extends Library {

/** Преобразование координат для EXIF */
  function calc_gps($coordinate, $hemisphere) {
    if (is_string($coordinate)) {
      $coordinate = array_map("trim", explode(",", $coordinate));
    }
    for ($i = 0; $i < 3; $i++) {
      $part = explode('/', $coordinate[$i]);
      if (count($part) == 1) {
        $coordinate[$i] = $part[0];
      } else if (count($part) == 2) {
        $coordinate[$i] = floatval($part[0])/floatval($part[1]);
      } else {
        $coordinate[$i] = 0;
      }
    }
    list($degrees, $minutes, $seconds) = $coordinate;
    $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
    return $sign * ($degrees + $minutes/60 + $seconds/3600);
  }  
}