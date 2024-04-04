<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Библиотека загрузки удаленных файлов
 *  ================================ */

class Library_download extends Library {
  /** Скачивание файла по HTTP с удаленного сервера.
  * @param $url string URL удаленного файла, который нужно скопировать
  * @param $local string Имя и путь, куда будет сохранен локальный файл (каких-либо проверок имени не производится, они должны быть сделаны в action)
  * @result boolean TRUE, если копирование прошло успешно.  
  **/
  function get($url,$local) {
    if (ini_get('allow_url_fopen')==true) { // если разрешено открывать удаленные файлы
      return copy($url,$local); // то пользуемся внутренней функцией копирования, так как это выгоднее по памяти и скорости
    }
    elseif (function_exists('curl_init') && $curl = curl_init()) { // если установлен модуль CURL, пользуемся им
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION,true);
      curl_setopt($curl, CURLOPT_POST, false);
      $file_data = curl_exec($curl);
      $req_info = curl_getinfo($curl);
      if ($req_info['http_code'] != 200) Library::$app->log_entry('download', E_USER_ERROR, __FILE__, print_r($req_info, true)); // логгируем ошибки для упрощения отладки
      if (curl_errno($curl)!=0) return false; // если при скачивании возникла ошибка, возвращаем код -2
      curl_close($curl);    
      $fh = fopen($local,'wb+');
      if (!$fh) return false; // если не удалось открыть файл для записи, возвращаем ошибку -1
      fwrite($fh,$file_data);
      fclose($fh);
      return true;
    }
    return false;
  }
  
  /** Проверка доступности удаленного файла
  * @param $url string URL проверяемого удаленного файла
  * **/
  function check($url) {
    if (ini_get('allow_url_fopen')==true) { // если разрешено открывать удаленные файлы
      $fh= @fopen($url,'rb');
      if (!$fh) return false; 
      else fclose($fh);
      return true;
    }
    elseif (function_exists('curl_init') && $curl = curl_init()) { // если установлен модуль CURL, пользуемся им
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER,false); // не сохраняем полученные данные
      curl_setopt($curl, CURLOPT_POST, false);
      curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
      curl_setopt($curl, CURLOPT_FORBID_REUSE, true);  
      curl_setopt($curl, CURLOPT_NOBODY, true);
      curl_exec($curl);
      if (curl_errno($curl)!==0) return false; // если в процессе произошла ошибка, то считаем, что файла не существует
      $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      return $status==200; // если статус отличается от 200, то считаем, что файла не существует
    }
    return false;    
  }  
}
