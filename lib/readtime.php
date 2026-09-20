<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Библиотека подсчёта времени чтения
 *  ================================ */

class Library_readtime extends Library {
  function get_readtime($text,$html=true) {
    $img_count = 0;
    $code_count = 0;
    if ($html) {
      $img_count = substr_count($text,'<img ');
      $code_count = substr_count($html,'<code>');
      $text = preg_replace('<code>.*?</code>','',$text);
    }
    $text = 
  }

}
