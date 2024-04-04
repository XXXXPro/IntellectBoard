<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2014 4X_Pro, INTBPRO.RU
 *  Intellect Board Pro
 *  Вспомогательные функции IntBoard:
 *    сохранение текстов для таблицы text 
 *  ================================ */

class Library_misc extends Library {
/** Сохранение универсального текстового поля. 
 * @param $text string Текст для сохранения в базу
 * @param $oid integer Идентификатор объекта, к которому относится текст (раздела,  темы, сообщения)
 * @param $type integer Тип сохраняемого текста. Возможные значения см. в docs/text **/  
  function save_text($text,$oid,$type) {
    $sql = 'DELETE FROM '.DB_prefix.'text WHERE id='.intval($oid).' AND type='.intval($type);
    Library::$app->db->query($sql);
    $data=array('data' => $text, 'id' => $oid, 'type' => $type, 'tx_lastmod' => Library::$app->time);
    $result=Library::$app->db->insert(DB_prefix.'text',$data);
    if ($result && $type<3) Library::$app->set_cached('Text'.$type.'_'.$oid, $data); // если данный тип текстов относится к кешируемым, сразу помещаем его в кеш
    return $result;        
  }
  
  function delete_text($oid,$type) {
    $sql = 'DELETE FROM '.DB_prefix.'text WHERE id='.intval($oid).' AND type='.intval($type);
    if ($type<3) Library::$app->clear_cached('Text'.$type.'_'.$oid); // если данный тип текстов относится к кешируемым, удаляем его из кеша
    return Library::$app->db->query($sql);
  }  
  
  /** Сохранение настроек в файл. Сохраняются все настройки, заданные в константах с префиксом CONFIG_ 
   * @param array $data Массив настроек, которые требуется переопределить по сравнению с имеющимися,
   *   где ключ -- имя настройки (без CONFIG_ в начале), значение -- значение настройки.
   * **/  
  function save_config($data) {
    $const = get_defined_constants(true);
    $buffer = "<?php\n";
    foreach ($const['user'] as $item=>$oldvalue) {
      if (strpos($item,'CONFIG_')===0) {
        $name = substr($item,7); 
        $value = isset($data[$name]) ? $data[$name] : $oldvalue;
        $buffer.="define('".addslashes($item)."','".addslashes($value)."');\n";
        unset($data[$name]);
      }
      if (strpos($item,'DB_')===0) $buffer.="define('".addslashes($item)."','".addslashes($oldvalue)."');\n";
    }
    // добавляем оставшиеся параметры, которые не были обработаны по причине отсутствия в старом конфиге
    if (!empty($data)) foreach ($data as $item=>$value) {
      if (substr($item,3)!=='DB_') $item='CONFIG_'.$item; // если параметр не связан с БД, то сохраняем его как config-параметр
      $buffer.="define('".addslashes($item)."','".addslashes($value)."');\n";      
    }
    rename(BASEDIR.'etc/ib_config.php','ib_config.old.php');
    $buffer.="define('INTB_LAST_CONFIG_TIME',".Library::$app->time.");\n";
    file_put_contents(BASEDIR.'etc/ib_config.php', $buffer);
    if (function_exists('opcache_invalidate')) opcache_invalidate(BASEDIR . 'etc/ib_config.php'); // сбрасываем PHP opcache, чтобы настройки применились сразу, а не с задержкой
  }
  /** Скачивание файла из заданного URL.  
   * 
   * @param string $url URL файла, который нужно скачать
   * @param string $filename Локальный путь для сохранения файла
   * @return integer Результат скачивания: 0 -- все Ok, отрицательные значения -- ошибки:
   *   -2 -- ошибка скачивания (скорее всего, 404)
   *   -1 -- ошибка записи на диск
   *   -3 -- нет доступных способов скачивания (сейчас поддерживаются только curl и fopen)
   */
  function download_file($url, $filename) {
    if (function_exists('curl_init') && $curl=curl_init()) { // если установлен модуль CURL, пользуемся им
      curl_setopt($curl,CURLOPT_URL,$url);
      curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
      curl_setopt($curl,CURLOPT_POST,false);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl,CURLOPT_MAXREDIRS,10);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      $file_data=curl_exec($curl);
      $error=curl_errno($curl);
      if ($error!=0) return -2; // если при скачивании возникла ошибка, возвращаем код -2
      curl_close($curl);
      $fh=fopen($filename,'w+');
      if (!$fh) return -1; // если не удалось открыть файл для записи, возвращаем ошибку -1
      fwrite($fh,$file_data);
      fclose($fh);
    }
    elseif (ini_get('allow_url_fopen')==1) {
    $fh1=fopen($url,'rb');
    if (!$fh1) return -2; // если не удалось открыть удаленный URL, возвращаем ошибку
    $fh2=fopen($filename,'w');
    if (!$fh2) return -1; // если не удалось открыть локальный файл для записи, возвращаем ошибку
    while ( $file_data=fread($fh1,128*1024) )
      fwrite($fh2,$file_data); // читаем файл блоками по 128 Кб и записываем на локаль
      fclose($fh1);
      fclose($fh2);
    }
    else return -3; // TODO: потом еще имеет смысл попробовать сокетами
    return 0; // если удалось все выполнить корректно
  }

  /** Создаёт задание для одноразового асинхронного выполнения
   * @param string $library Имя библиотеки, в которой находится выполняемая процедура
   * @param string $proc Имя процедуры. В библиотеке должна быть описана как метод с именем task_<имя_процедуры>
   * @param mixed $params Параметры, которые будут переданы в процедуру
   * @param integer $nextrun Время первой попытки асинхронного выполнения
   * Примечание: указанное время означает, что задание будет запущено не раньше этого времени.
   *  **/
  function create_task($library,$proc,$params,$nextrun=0) {
    $data['library']=$library;
    $data['proc']=$proc;
    $data['params']=serialize($params);
    $data['nextrun']=$nextrun;
    $result=Library::$app->db->insert(DB_prefix.'task',$data);
    return $result;
  }
}