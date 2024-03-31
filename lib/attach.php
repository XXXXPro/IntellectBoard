<?php
/** ================================
*  @package IntBPro
*  @author 4X_Pro <admin@openproj.ru>
*  @version 3.0
*  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
*  @url http://www.intbpro.ru
*  Модуль для обработки прикрепленных файлов
*  ================================ */

class Library_attach extends Library {
/** Проверка корректности загруженных файлов
 * @param array $files Массив с данными загруженных файлов (по формату такой же, как в $_FILES)
 * @param ineter $types Допустимые типы файлов (битовая маска):
 *       255 -- все, 1 -- только картинки, 2 -- видео, 4 -- аудио, 8 -- текст
 * @param integer $maxsize Максимальный размер файла (в байтах)
 * @param string $maxcount Максимальное количество файлов
 * @return array Массив с ошибками загрузки
 */
  function check_files($files,$maxsize=false,$types=0xFF,$maxcount=false) {
    $result = array();
    if (function_exists('finfo_file')) $finfo = finfo_open(FILEINFO_MIME_TYPE); // если подключено расширение finfo, проверяем тип файла через него
    else $finfo = false;
    $fcount=0;
    for ($i=0, $count=count($files['tmp_name']); $i<$count; $i++) {
      if (is_uploaded_file($files['tmp_name'][$i])) {
        if ($files['size'][$i]>$maxsize) $result[]=array('text'=>'Размер файла '.$files['name'][$i].' допускает предельно возможный.','level'=>2);
        if ($finfo) $mime = finfo_file($finfo, $files['tmp_name'][$i]);
        else $mime = $files['type'][$i];
        if (strpos($mime,'image/')===0) $type=1;
        elseif (strpos($mime,'video/')===0) $type=2;
        elseif (strpos($mime,'audio/')===0) $type=4;
        elseif (strpos($mime,'text/')===0) $type=8;
        else $type=128;
        if (($type & $types)==0) $result[]=array('text'=>'Файл '.$files['name'][$i].' имеет недопустимый тип.','level'=>2);
        $fcount++;
      }
      elseif ($files['tmp_name'][$i]!='') $result[]=array('text'=>'Файл '.$files['name'][$i].' загружен некорректно','level'=>2);
    }
    if ($fcount>$maxcount) $result[]=array('text'=>'Общее количество загруженных файлов превышает максимально допустимое.','level'=>2);
    if ($finfo) finfo_close($finfo);
    return $result;
  }

/** Обработка прикрепленных файлов и сохранение данных о них в базу
 * @param array $files Массив с данными о загруженных файлах  (по формату такой же, как в $_FILES)
 * @param int $oid Идентификатор объекта (например, сообщения), к которому прикрепляется файл
 * @param int $objtype Тип объекта, к которому привязан файл
 * @param bool $set_main Если true, то первому из загруженных файлов выставляется признак is_main
 */
  function process_files($files,$oid,$objtype=1,$set_main=true) {
    if (function_exists('finfo_file')) $finfo = finfo_open(FILEINFO_MIME_TYPE); // если подключено расширение finfo, проверяем тип файла через него
    else $finfo = false;
    $result=array();

    for ($i=0, $count=count($files['tmp_name']); $i<$count; $i++) {
      if (is_uploaded_file($files['tmp_name'][$i])) {
        $key = substr(hash('sha256',rand().$_SERVER['REMOTE_ADDR'].$_SERVER['REMOTE_PORT'].Library::$app->time.Library::$app->get_opt('site_secret').$files['name'][$i].$i),0,12);
        if ($finfo) $mime = finfo_file($finfo, $files['tmp_name'][$i]);
        else $mime = $files['type'][$i];
        $filename = BASEDIR.'/www/f/up/'.intval($objtype).'/'.intval($oid).'-'.$key.'.dat';
        if (move_uploaded_file($files['tmp_name'][$i], $filename)) {
          if (strpos($mime,'image/')===0) $type='image';
          elseif (strpos($mime,'video/')===0) $type='video';
          elseif (strpos($mime,'audio/')===0) $type='audio';
          elseif (strpos($mime,'text/')===0) $type='text';
          else $type='attach';
          $exif = null;
          if ($type==='image') { // уменьшаем загруженное изображение, если это необходимо
            if (function_exists('exif_read_data')) {
              $exif = exif_read_data($filename);
              if ($exif) {
                $exif_keys = array_keys($exif);
                foreach ($exif_keys as $k) { // фильтруем неизвестные теги, так как они могут содержать двоичные данные, от которых json_encode перестаёт работать
                  if (substr($k,0,12)==='UndefinedTag') unset($exif[$k]);
                }       
                $exifjson=json_encode($exif);
              }
            }
            $imglib = Library::$app->load_lib('image',false);
            if ($imglib) {
               $imgdata = $imglib->load($filename);
               if (!empty($imgdata)) {
                 $maxx = Library::$app->get_opt('attach_max_x') ?: 1200; // если не заданы максимальные размеры, вписываем фото в 1200x1080
                 $maxy = Library::$app->get_opt('attach_max_y') ?: 1080;
                 $qty=NULL; // для прочих форматов, кроме JPEG
                 if ($imgdata['type']==IMAGETYPE_JPEG) { // для JPEG берем качество из настройки качества пользовательского фото
                   $qty=Library::$app->get_opt('userlib_photo_jpeg_qty');
                   if (empty($qty)) $qty=80;
                 }
                 $imgdata = $imglib->fit_to($imgdata,$maxx,$maxy);
                 $imglib->save($imgdata,$filename,$qty);
              }
            }
          }
          $data=array('fkey'=>$key,'oid'=>intval($oid),'type'=>intval($objtype),'filename'=>$files['name'][$i],'size'=>$files['size'][$i],'format'=>$type,'is_main'=>($set_main ? '1' : '0'));
          $data['extension']=substr($files['name'][$i],strrpos($files['name'][$i], '.')+1); 
          if (!empty($exifjson)) $data['exif']=str_replace('\\u0000','',$exifjson); // замена \u0000 нужна для Postgres, который не позволяет хранить нулевой символ в тексте
          // пытаемся автоматически определить название фото                
          if (!empty($exif['COMPUTED']['UserComment'])) $data['descr']=$exif['COMPUTED']['UserComment'];
          elseif (!empty($exif['COMMENT'])) $data['descr']=join(' ',$exif['COMMENT']);
          elseif (!empty($exif['UserComment'])) $data['descr']=$exif['UserComment'];
          // определяем широту и долготу, если они есть в EXIF
          if (!empty($exif['GPSLatitude']) || !empty($exif['GPSLongitude'])) {
            /** @var Librrary_exifgps $gpslib */
            $gpslib = Library::$app->load_lib('exifgps',false);
            if ($gpslib) {
              if (!empty($exif['GPSLongitude'])) $data['geo_longtitude']=$gpslib->calc_gps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
              if (!empty($exif['GPSLatitude'])) $data['geo_latitude']=$gpslib->calc_gps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
            }
          }


          Library::$app->db->insert(DB_prefix.'file',$data);
          $set_main = false; // для остальных файлов (кроме первого) признак set_main в любом случае выставляем в false
          $data['oid']="$oid";
          $result[]=$data;
        }
      }
    }
    if ($finfo) finfo_close($finfo);
    return $result;
  }

  /** Привязка предварительно загруженных "ничейных" файлов к созданному объекту
   * @param array $keys Массив ключей файлов
   * @param integer $newoid Идентификатор созданного объекта
   * @param integer $type
   */
  function process_preuploads($keys,$newoid,$type=1) {
    for ($i=0, $count=count($keys); $i<$count; $i++) {
      rename(BASEDIR.'/www/f/up/'.intval($type).'/0-'.$key.'.dat', $newoid.'-'.$key);
    }
    $sql = 'UPDATE '.DB_prefix.'file SET oid='.intval($newoid).
    ' WHERE oid=0 AND type='.intval($type).'AND '.Library::$app->db->array_to_sql($keys,'fkey');
    Library::$app->db->query($sql);
  }

  /** Удаление прикрепленных к сообщению файлов
   * @param array $keys Массив ключей файлов, которые требуется удалить
   * @param integer $oid Идентификатор объекта, к которому прикреплены файлы
   * @param integer $type Тип объекта (по умолчанию равен 1 -- сообщение в разделе)
   */
  function delete_uploads($keys,$oid,$type=1) {
    $sql = 'DELETE FROM '.DB_prefix.'file WHERE oid='.intval($oid).
    ' AND type='.intval($type).' AND '.Library::$app->db->array_to_sql($keys,'fkey');
    Library::$app->db->query($sql);

    for ($i=0, $count=count($keys); $i<$count; $i++) {
      $filename=intval($type).'/'.$oid.'-'.$keys[$i].'.dat';
      if (Library::$app->valid_file($filename))
      unlink(BASEDIR.'/www/f/up/'.$filename);
      $dirs = glob(BASEDIR.'/www/f/up/'.intval($type).'/pr/*');
      for ($j=0,$count2=count($dirs);$j<$count2;$j++) if ($dirs[$j]!='.' && $dirs[$j]!='..') {
        array_map('unlink',glob($dirs[$j].'/'.$oid.'-'.$keys[$i].'*'));
      }
    }
  }

  /** Проверяет, что у объекта есть основной прикреплённый файл. Если такового нет, назначет основным файл с минимальным fkey.
   * @param integer $oid Идентификатор объекта, к которому прикреплены файлы
   * @param integer $type Тип объекта (по умолчанию равен 1 -- сообщение в разделе)
   */
  function check_main_attach($oid,$type=1) {
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'file WHERE oid='.intval($oid).' AND type='.intval($type).' AND is_main=\'1\'';
    $has_main = Library::$app->db->select_int($sql);

    if (!$has_main) {
      $sql = 'SELECT MIN(fkey) FROM '.DB_prefix.'file WHERE oid='.intval($oid).' AND type='.intval($type);
      $fkey = Library::$app->db->select_str($sql);
      $sql = 'UPDATE '.DB_prefix.'file SET is_main=\'1\' WHERE fkey=\''.Library::$app->db->slashes($fkey).'\' AND oid='.intval($oid).' AND type='.intval($type);
      Library::$app->db->query($sql);
    }
  }
}
