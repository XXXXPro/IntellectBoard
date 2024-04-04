<?php
define('BASEDIR','../');
require(BASEDIR.'etc/ib_config.php');

require BASEDIR.'lib/image.php'; 

class Preview {
  function main() {
    $source = 'www/f/up/'.$_REQUEST['dir'].'/'.$_REQUEST['filename'];
    $pos = strrpos($source, '.');
    if ($pos!==false) {
      $extension = substr($source, $pos);
      $source = str_replace($extension,'.dat', $source);
    }
    else $extension='';
    
    if (!$this->valid_file($source)) {
      header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      echo 'Некорректный путь исходного файла!';
      exit();
    }
    if (!in_array(strtolower($extension),array('.jpg','.jpeg','.bmp','.gif','.png'))) {
      header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      echo 'Подозрительное расширение файла!';
      exit();        
    }
    if (!file_exists(BASEDIR.$source)) {
      header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
      echo 'Не найден исходный файл для преобразования! ';
      exit();        
    }    
    $destdir = 'www/f/up/'.$_REQUEST['dir'].'/pr/'.
        (empty($_REQUEST['x']) ? '' : intval($_REQUEST['x'])).'x'.
        (empty($_REQUEST['y']) ? '' : intval($_REQUEST['y']));
    $dest = $destdir.'/'.$_REQUEST['filename'];
    if (!$this->valid_file($dest)) {
      header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      echo 'Некорректный путь файла с результатом (возможно, некорректно указаны размеры)!';
      exit();
    }
    if (!is_dir(BASEDIR.'www/f/up/'.intval($_REQUEST['dir']).'/pr')) mkdir(BASEDIR.'www/f/up/'.intval($_REQUEST['dir']).'/pr');    
    if (!is_dir(BASEDIR.$destdir)) mkdir(BASEDIR.$destdir); // создаем каталог, если его вдруг нет
    if (!is_writable(BASEDIR.$destdir)) {
      header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      echo 'Каталог недоступен для записи!';
      exit();        
    }

    // проверка того, что указанный размер файлов входит в список допустимых (нужно для защиты от перегрузки сервера и DoS-атак)
    // в файле imgsize.txt могут быть определены дополнительные размеры уменьшенных файлов
    if (is_readable(BASEDIR.'etc/imgsize.txt')) { 
      $allowed_sizes = file(BASEDIR.'etc/imgsize.txt',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    }
    else $allowed_sizes = array();
    // добавляем размеры из файла настроек
    $allowed_sizes = $allowed_sizes + array(CONFIG_posts_preview_x.'x'.CONFIG_posts_preview_y,
      CONFIG_userlib_photo_x.'x'.CONFIG_userlib_photo_y,CONFIG_gallery_preview_x.'x'.CONFIG_gallery_preview_y,
      CONFIG_gallery_mainpage_x.'x'.CONFIG_gallery_mainpage_y);

    $size_x = empty($_REQUEST['x']) ? 240 : intval($_REQUEST['x']);
    $size_y = empty($_REQUEST['y']) ? 180 : intval($_REQUEST['y']);
    if (!in_array($size_x.'x'.$size_y,$allowed_sizes)) {
      header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      echo 'Некорректный размер файла! Допускаются размеры, заданные в настройках движка или файле etc/imgsize.txt';
      exit();
    }
    
    $imglib = new Library_image();
    $imgdata = $imglib->load(BASEDIR.$source);
    if (empty($imgdata)) {
      header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
      echo 'Не получилось загрузить изображение!';
      exit();        
    }
    if ($imgdata['type']==IMAGETYPE_JPEG) {
      if (defined('CONFIG_posts_preview_jpeg_qty')) $qty=intval(CONFIG_posts_preview_jpeg_qty);
      else $qty=0;
      if (!$qty) $qty=70; // если качество не задано, задаем 70
    }
    elseif ($imgdata['type']==IMAGETYPE_PNG) $qty=6;
    else $qty=false;
    $imglib->save_fit_to($imgdata,$size_x,$size_y,BASEDIR.$dest,$imgdata['type'],$qty);
    
    header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
    header('Content-Type: image/'.$imglib->get_extension($imgdata['type']));
    $imglib->unload($imgdata);
    header('Content-Length: '.filesize(BASEDIR.$dest));
    header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T'));
    readfile(BASEDIR.$dest);
  }
  
  /** Функция проверяет имя файла на наличие недопустимых символов и, при необходимости, существование
  * При $debug!=false информация об отсутствии файла сохраняется в отладочную информацию. * */
  function valid_file($filename, $exist=false, $debug=false) {
    $result = (substr($filename, 0, 1) != '/' && substr($filename, 0, 1) != '\\' && substr($filename, 0, 1) != '~');
    if ($result) {
      $test = array('..', '://', '`', '\'', '"', ':', ';', ',', '&', '>', '<');
      for ($i = 0, $count = count($test); $i < $count && $result; $i++)
        $result = (strpos($filename, $test[$i]) === false);
    }
    if ($result && $exist) {
    $result = is_readable($filename);
    if (!$result && $debug)
      _dbg('Файл '.$filename.' не найден или недоступен для чтения!');
      }
      return $result;
    }  
}
$app = new Preview();
$app->main();
