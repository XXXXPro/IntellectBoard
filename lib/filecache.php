<?php 
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Модуль кеширования данных в текстовых файлах 
 *  ================================ */

 class Library_filecache extends Library implements iCache {
    private $app;
    private $dir;
    
    function __construct(Application $theapp) {
       $this->app=$theapp;
       $this->dir = $theapp->get_opt('cache_file_dir');
       if (!$this->dir) $this->dir = BASEDIR.'tmp/cache';
       if (!file_exists($this->dir)) mkdir($this->dir); // если кеш-каталога не существует, пытаемся его создать
    }
    
    private function filename($id) {
       return $this->dir.'/'.md5($id).'.tmp';
    }
    
    /** Получение данных из кеша. Если данные не найдены, возвращаем NULL **/
    function get($id) {
       $fname = $this->filename($id);
       if (file_exists($fname)){
          $data = @file_get_contents($fname);
         if (!empty($data)) return unserialize($data);
         else return NULL;
       }
       else return NULL;
    }
    
    /** Сохранение данных в кеш **/
    function set($id,$data) {
       return @file_put_contents($this->filename($id),serialize($data));       
    }
    
    /** Удаление данных из кеша **/ 
    function clear($id) {
       $fname = $this->filename($id);
       if (file_exists($fname)) unlink($fname);
    }
    
    function clear_all() {
     $files=glob($this->dir.'/*.tmp');
     for ($i=0, $count=count($files); $i<$count; $i++) unlink($files[$i]);
    }
 }
