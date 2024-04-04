<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Intellect Board Pro
 *  Библиотека работы с графическими файлами
 *  ================================ */

class Library_image {

/** Загрузка изображения в память. Определяются его тип и размеры, затем создается ресурс с самим изображением
 *
 * @param string $filename
 * @return mixed 
 */
  function load($filename) {
    if (strpos($filename,'://')===false && !is_readable($filename)) return false; // если файла нет, то сразу возвращаем false -- признак ошибки, но проверяем только для локальных файлов
    $data=getimagesize($filename);
    if (!$data) return false; // если не удалось загрузить файл с изображением и получить его данные
    $result['width']=$data[0];
    $result['height']=$data[1];
    $result['type']=$data[2];
    $result['filename']=$filename;
    if ($data[2]==IMAGETYPE_GIF) $result['resource']=imagecreatefromgif($filename);
    elseif ($data[2]==IMAGETYPE_JPEG) $result['resource']=imagecreatefromjpeg($filename);
    elseif ($data[2]==IMAGETYPE_PNG) $result['resource']=imagecreatefrompng($filename);
    elseif ($data[2]==IMAGETYPE_WEBP) $result['resource']=imagecreatefromwebp($filename);
//    elseif ($data[2]==IMAGETYPE_BMP) $result['resource']=imagecreatefrombmp($filename);
    else return false;    
    if (!is_resource($result['resource']) && get_class($result['resource'])!=='GdImage') return false; // если не удалось создать ресурс, сообщаем об ошибке -- возвращаем false
    if ($data[2]==IMAGETYPE_JPEG && function_exists('exif_read_data') ) { // если у нас JPEG и есть поддержка EXIF
      $exif=exif_read_data($filename);
      if (isset($exif['Orientation'])) switch ($exif['Orientation']) { // получаем ориентацию фото и поворачиваем изображение, чтобы отображалось правильно
        // Поворот на 180 градусов
        case 3: {
          $result['resource'] = imagerotate($result['resource'],180,0);
          break;
        }
        // Поворот вправо на 90 градусов
        case 6: {
          $result['resource'] = imagerotate($result['resource'],-90,0);
          list($result['height'],$result['width'])=array($result['width'],$result['height']);
          break;
        }
        // Поворот влево на 90 градусов
        case 8: {
          $result['resource'] = imagerotate($result['resource'],90,0);
          list($result['height'],$result['width'])=array($result['width'],$result['height']);
          break;
        }
      }
    }
    return $result;
  }

  function resize($imgdata,$newx,$newy) {
    if ($imgdata['type']!=IMAGETYPE_GIF) $newimg['resource']=imagecreatetruecolor($newx, $newy);
    else $newimg['resource']=imagecreate($newx, $newy);
    $newimg['type']=$imgdata['type'];
    $newimg['width']=$newx;
    $newimg['height']=$newy;
    if (function_exists('imagecopyresampled'))
      imagecopyresampled($newimg['resource'], $imgdata['resource'], 0, 0, 0, 0, $newx, $newy, $imgdata['width'], $imgdata['height']);
    else imagecopyresized($newimg['resource'], $imgdata['resource'], 0, 0, 0, 0, $newx, $newy, $imgdata['width'], $imgdata['height']);
    return $newimg;
  }

  function fit_to($imgdata,$newx,$newy) {
    if ($newx) $coeff_x = $newx/$imgdata['width'];
    else $coeff_x=10000000;
    if ($newy) $coeff_y = $newy/$imgdata['height'];
    else $coeff_y=10000000;
    if ($coeff_x<$coeff_y) $coeff_y=$coeff_x;
    else $coeff_x=$coeff_y;
    if ($coeff_x>1) $coeff_x=1; // увеличения изображения не должно произойти ни при каких обстоятельствах
    if ($coeff_y>1) $coeff_y=1;
    $newx = floor($imgdata['width']*$coeff_x);
    $newy = floor($imgdata['height']*$coeff_y);
    return $this->resize($imgdata,$newx,$newy);
  }

  function save($image,$filename=false,$options=NULL) {
    if (!$filename) $filename=$image['filename'];
    $type=$image['type'];
    if ($type==IMAGETYPE_GIF) $result=imagegif($image['resource'], $filename);
    elseif ($type==IMAGETYPE_JPEG) $result=imagejpeg($image['resource'], $filename,$options);
    elseif ($type==IMAGETYPE_PNG) $result=imagepng($image['resource'], $filename,$options);
//    elseif ($type==IMAGETYPE_BMP) $result=imagebmp($image['resource'], $filename,$options);
    else $result=false;
    return $result;
  }

  /** Сохраняет изображение с вписыванием в заданные размеры, если это необходимо
   *
   * @param array $imgdata Данные об изображении, полученные с помощью load
   * @param integer $width Максимальная ширина изображения. Если false -- нет ограничений.
   * @param integer $height Максимальная высота изображения. Если false -- нет ограничений.
   * @param string $filename Имя файла, в который следует сохранить изображение
   * @param integer $type Тип изображения в виде констант IMAGETYPE_*.
   * @param mixed $options Опции для сохранения (для JPEG -- качество, для PNG -- уровень компрессии).
   * @return boolean Результат операции (true -- файл сохранен).
   */
  function save_fit_to($imgdata,$width,$height,$filename,$type,$options=NULL) {
    if ($imgdata['height']<=$height && $imgdata['width']<=$width // если картинка вмещается в указанный размер и её не нужно поворачивать, просто копируем файл
    && (empty($imgdata['orientation']) || !in_array($imgdata['orientation'],array(3,6,8)))) { 
      if (is_uploaded_file($imgdata['filename'])) $result=move_uploaded_file($imgdata['filename'], $filename);
      else $result=copy($imgdata['filename'], $filename);
    }
    else {
      $newimg = $this->fit_to($imgdata, $width, $height);
      $result=$this->save($newimg,$filename,$options);
    }
    return $result;
  }
  
  function unload($imgdata) {
    imagedestroy($imgdata['resource']);
  }

  function get_extension($type) {
    if ($type==IMAGETYPE_GIF) $result='gif';
    elseif ($type==IMAGETYPE_JPEG) $result='jpg';
    elseif ($type==IMAGETYPE_PNG) $result='png';
    elseif ($type==IMAGETYPE_BMP) $result='bmp';
    else $result='';
    return $result;
  }
}
?>
