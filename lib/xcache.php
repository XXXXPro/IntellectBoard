<?php 
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2014, 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Модуль кеширования данных с помощью xCache 
 *  ================================ */

class Library_xcache extends Library implements iCache {
  function __construct() {
    $this->xcache = (function_exists('xcache_set')); 
  }
  function get($id) {
    if ($this->xcache) return xcache_get($id);
    else return NULL;
  }
  
  function set($id,$data) {
    if ($this->xcache) xcache_set($id,$data);
  }

  function clear($id) {
    if ($this->xcache) xcache_unset($id);
  }
  
  function clear_all() {
    if ($this->xcache) xcache_unset_by_prefix('');
  }
}