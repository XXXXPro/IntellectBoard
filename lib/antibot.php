<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  Intellect Board Pro
 *  Библиотека защиты от ботов и флуда
 *  На данный момент поддерживает показ картинки (CAPTCHA)
 *  и задержки по времени при выполнении повторяющихся действий
 *  ================================ */

 class Library_antibot extends Library{
   private $allowed_symbols = "0123456789"; // строка используемых в CAPTCHA символов
   /** Генерирует картинку с кодом captcha и контрольное значение для него
    * @return string строка-идентификатор CAPTCHA, он же является именем файла, лежащего в /files/captcha/ и имеющего расширение .jpg
    * **/

   function captcha_generate() {
     $length=mt_rand(5,6); // длина CAPTCHA -- от 5 до 6 символов
     $keystring = '';
     $symbols_length = strlen($this->allowed_symbols);
     for ($i=0; $i<$length; $i++) {
       $keystring.=substr($this->allowed_symbols,mt_rand(0,$symbols_length-1),1);
     }
     $key = substr(hash('sha256',microtime().$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].rand()),0,32); // генерируем уникальный ключ-идентификатор CAPTCHA
     if (Library::$app->db->insert(DB_prefix.'captcha',array('hash'=>$key,
       'code'=>$keystring,'active'=>'1','lastmod'=>Library::$app->time,'ip'=>ip2long($_SERVER['REMOTE_ADDR'])))!==false) {
       $this->captcha_create_image($keystring,$key);
       Library::$app->out->captcha_key = $key;
       Library::$app->out->captcha_code = Library::$app->time % 10000;
     }
     else return false;
   }

   /**  Сохраняет выбранный CAPTCHA-код в JPEG-файл
   * В данной процедуре используется код из решения KCAPTCHA (см. http://captcha.ru), распространяемый по лицензии LGPL
   **/
   function captcha_create_image($keystring,$name) {
     $allowed_symbols = $this->allowed_symbols;
     $alphabet = "0123456789abcdefghijklmnopqrstuvwxyz"; # do not change without changing font files!

     $length = strlen($keystring); # random 5 or 6

     $width = 120;
     $height = 60;
     $fluctuation_amplitude = 5;
     $no_spaces = true;

     $show_credits = false; # set to false to remove credits line. Credits adds 12 pixels to image height
     $credits = 'www.captcha.ru'; # if empty, HTTP_HOST will be shown

     # CAPTCHA image colors (RGB, 0-255)
     $foreground_color = array(mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
     $background_color = array(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));

     # JPEG quality of CAPTCHA image (bigger is better quality, but larger file size)
     $jpeg_quality = 80;

     $fonts=array();
     $fontsdir_absolute=BASEDIR.'/lib/antibot';
     $handle = opendir($fontsdir_absolute);
     if ($handle) {
       while (false !== ($file = readdir($handle))) {
         if (preg_match('/\.png$/i', $file)) {
           $fonts[]=$fontsdir_absolute.'/'.$file;
         }
       }
       closedir($handle);
     }

     $alphabet_length=strlen($alphabet);

     $font_file=$fonts[mt_rand(0, count($fonts)-1)];
     $font=imagecreatefrompng($font_file);
     imagealphablending($font, true);
     $fontfile_width=imagesx($font);
     $fontfile_height=imagesy($font)-1;
     $font_metrics=array();
     $symbol=0;
     $reading_symbol=false;

     // loading font
     for($i=0;$i<$fontfile_width && $symbol<$alphabet_length;$i++) {
       $transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

       if (!$reading_symbol && !$transparent) {
         $font_metrics[$alphabet[$symbol]]=array('start'=>$i);
         $reading_symbol=true;
         continue;
       }

       if ($reading_symbol && $transparent) {
         $font_metrics[$alphabet[$symbol]]['end']=$i;
         $reading_symbol=false;
         $symbol++;
         continue;
       }
     }

     $img=imagecreatetruecolor($width, $height);
     imagealphablending($img, true);
     $white=imagecolorallocate($img, 255, 255, 255);
     $black=imagecolorallocate($img, 0, 0, 0);

     imagefilledrectangle($img, 0, 0, $width-1, $height-1, $white);

     // draw text
     $x=1;
     for($i=0;$i<$length;$i++) {
       $m=$font_metrics[$keystring[$i]];
       $y=mt_rand(-$fluctuation_amplitude, $fluctuation_amplitude)+($height-$fontfile_height)/2+2;

       if ($no_spaces) {
         $shift=0;
         if ($i>0) {
           $shift=10000;
           for ($sy=7;$sy<$fontfile_height-20;$sy+=1){
             for ($sx=$m['start']-1;$sx<$m['end'];$sx+=1) {
               $rgb=imagecolorat($font, $sx, $sy);
               $opacity=$rgb>>24;
               if ($opacity<127) {
                 $left=$sx-$m['start']+$x;
                 $py=$sy+$y;
                 if ($py>$height) break;
                 for($px=min($left,$width-1);$px>$left-12 && $px>=0;$px-=1) {
                   $color=imagecolorat($img, (int)$px, (int)$py) & 0xff;
                   if($color+$opacity<190) {
                     if($shift>$left-$px) $shift=$left-$px;
                     break;
                   }
                 }
                 break;
               }
             }
           }
           if ($shift==10000) {
             $shift=mt_rand(4,6);
           }
         }
       }
       else {
         $shift=1;
       }
       imagecopy($img, $font, (int)($x-$shift), (int)$y, (int)$m['start'], 1, (int)($m['end']-$m['start']), $fontfile_height);
       $x+=$m['end']-$m['start']-$shift;
     }


     $center=$x/2;

     // credits. To remove, see configuration file
     $img2=imagecreatetruecolor($width, $height+($show_credits?12:0));
     $foreground=imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
     $background=imagecolorallocate($img2, $background_color[0], $background_color[1], $background_color[2]);
     imagefilledrectangle($img2, 0, 0, $width-1, $height-1, $background);
     imagefilledrectangle($img2, 0, $height, $width-1, $height+12, $foreground);
     $credits=empty($credits)?$_SERVER['HTTP_HOST']:$credits;
     imagestring($img2, 2, $width/2-imagefontwidth(2)*strlen($credits)/2, $height-2, $credits, $background);

     // periods
     $rand1=mt_rand(750000,1200000)/10000000;
     $rand2=mt_rand(750000,1200000)/10000000;
     $rand3=mt_rand(750000,1200000)/10000000;
     $rand4=mt_rand(750000,1200000)/10000000;
     // phases
     $rand5=mt_rand(0,31415926)/10000000;
     $rand6=mt_rand(0,31415926)/10000000;
     $rand7=mt_rand(0,31415926)/10000000;
     $rand8=mt_rand(0,31415926)/10000000;
     // amplitudes
     $rand9=mt_rand(330,420)/110;
     $rand10=mt_rand(330,450)/110;

     //wave distortion

     for($x=0;$x<$width;$x++) {
       for($y=0;$y<$height;$y++) {
         $sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
         $sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

         if($sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1) continue;
         else {
           $color=imagecolorat($img, (int)$sx, (int)$sy) & 0xFF;
           $color_x=imagecolorat($img, (int)($sx+1), (int)$sy) & 0xFF;
           $color_y=imagecolorat($img, (int)$sx, (int)($sy+1)) & 0xFF;
           $color_xy=imagecolorat($img, (int)($sx+1), (int)($sy+1)) & 0xFF;
         }

         if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255) {
           continue;
         }
         else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0) {
           $newred=$foreground_color[0];
           $newgreen=$foreground_color[1];
           $newblue=$foreground_color[2];
         }
         else {
           $frsx=$sx-floor($sx);
           $frsy=$sy-floor($sy);
           $frsx1=1-$frsx;
           $frsy1=1-$frsy;
           $newcolor=(
             $color*$frsx1*$frsy1+
             $color_x*$frsx*$frsy1+
             $color_y*$frsx1*$frsy+
             $color_xy*$frsx*$frsy);

           if($newcolor>255) $newcolor=255;
           $newcolor=$newcolor/255;
           $newcolor0=1-$newcolor;

           $newred=$newcolor0*$foreground_color[0]+$newcolor*$background_color[0];
           $newgreen=$newcolor0*$foreground_color[1]+$newcolor*$background_color[1];
           $newblue=$newcolor0*$foreground_color[2]+$newcolor*$background_color[2];
         }

         imagesetpixel($img2, (int)$x, (int)$y, imagecolorallocate($img2, (int)$newred, (int)$newgreen, (int)$newblue));
       }
     }

    $stream = fopen("php://memory", "w+"); // выводим картинку не в файл, а в поток в памяти
    stream_filter_append($stream, 'convert.base64-encode', STREAM_FILTER_WRITE); // сразу преобразуем в base64-encoded через фильтр, так как с бинарной строкой работа бывает некорректной
    imagejpeg($img2, $stream, $jpeg_quality); // собственно, вывод
    rewind($stream);
    Library::$app->out->captcha_data = stream_get_contents($stream);
    fclose($stream);    
   }

   /** Проверка CAPTCHA-кода. В случае, если код верен, возвращает true, а если параметр deactivate включен, то и деактивирует код.
    *
    */
  function captcha_check($deactivate=true) {
    if (Library::$app->get_opt('captcha')==1) { // проверка обычной KCAPTCHA через запись в таблице captcha
      $key = isset($_POST['captcha_key']) ? $_POST['captcha_key'] : '';
      $value = isset($_POST['captcha_value']) ? $_POST['captcha_value'] : '';
      $time1 = $_POST['captcha_timecode'];
      $time2 = Library::$app->time % 10000;

      $sql = 'SELECT code FROM '.DB_prefix.'captcha WHERE hash=\''.Library::$app->db->slashes($key).'\' AND active=\'1\'';
      $code = trim(Library::$app->db->select_str($sql));
      if ((($time2-$time1) % 10000)<3 || $time1>10000) return false; // если с момента генерации CAPTCHA до ее ввода прошло меньше трех секунд, то это, скорее всего, бот
      if ($code!=false && $code==$value) { // если код, введенный пользователем и сохраненный в базе совпадают, и при этом не пустые
        if ($deactivate) {
          $sql = 'UPDATE '.DB_prefix.'captcha SET active=\'0\' WHERE hash=\''.Library::$app->db->slashes($key).'\'';
          Library::$app->db->query($sql);
        }
        return true;
      }
      else return false;
    } elseif (Library::$app->get_opt('captcha')==2) { // проверка ReCAPTCHA от Google
      $postdata = http_build_query(array('secret' => Library::$app->get_opt('captcha_secret_key'),'response' => $_POST['g-recaptcha-response']));
      $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
      if (!function_exists('curl_init')) {
        $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
            )
        );

        $context  = stream_context_create($opts);
        $data = file_get_contents($recaptcha_url, false, $context);
      } else {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $recaptcha_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $data = curl_exec($curl);
        curl_close($curl);
      }
      if (!$data) return false;
      $data = json_decode($data, true);
      return !empty($data['success']);
    }
    else return true; // если CAPTCHA выключена вообще, считаем, что она пройдена
  }

   /** Функция подчистки таблицы CAPTCHA. Предполагается ее вызов через crontab
    *
    * @param int $interval интервал в часах, за который следует удалять данные.
    */
  function cron_captcha_clear($interval=24) {
    $time = time()-$interval*60*60;

    Library::$app->db->lock_tables(DB_prefix.'captcha',true); // блокируем таблицу на момент выборки
    $sql = 'SELECT hash FROM '.DB_prefix.'captcha WHERE lastmod<='.$time.' OR active=\'0\'';
    $olds = Library::$app->db->select_all_strings($sql);
    $sql = 'DELETE FROM '.DB_prefix.'captcha WHERE lastmod<='.$time.' OR active=\'0\'';
    Library::$app->db->query($sql);
    Library::$app->db->unlock_tables(DB_prefix.'captcha');

    for ($i=0, $count=count($olds); $i<$count; $i++) { // удаляем файлы CAPTCHA
      $filename=BASEDIR.'www/f/cap/'.$olds[$i].'.jpg';
      if (file_exists($filename)) unlink($filename);
    }
  }

   /** Производит проверку на то, что после последнего действия прошел определенный промежуток времени (истек таймаут)
    *
    * @param string $action условное наименование действия (например, register для регистрации, search для поиска)
    * @param int $delay задержка в секундах, которая должна пройти для после такого же действия (или его попытки) для данного пользователя (если он зарегистрирован) или с того же IP (для гостя)
    * @return boolean TRUE -- если таймаут истек и действие следует разрешить, FALSE -- в противном случае
    *
    * Примечание: периодически (желательно два-три раза в сутки) следует производить подчистку через crontab.
    */
   function timeout_check($action,$delay) {
     $uid = Library::$app->is_guest() ? 0 : Library::$app->get_uid(); // получаем UID пользователя или false, если это гость
     $curtime = time();
     $lasttime = $curtime-$delay;
     $ip=ip2long($_SERVER['REMOTE_ADDR']); // получаем адрес пользователя и преобразуем его в цифровой вид
     if ($uid==false) {
       $sql = 'SELECT time FROM '.DB_prefix.'timeout WHERE ip='.intval($ip).' AND time>='.intval($lasttime).' AND time<='.intval($curtime).' AND action=\''.Library::$app->db->slashes($action).'\'';
     }
     else {
       $sql = 'SELECT time FROM '.DB_prefix.'timeout WHERE uid='.intval($uid).' AND time>='.intval($lasttime).' AND time<='.intval($curtime).' AND action=\''.Library::$app->db->slashes($action).'\'';
     }
     $count=Library::$app->db->select_int($sql);
     if ($count==0) {
       Library::$app->db->insert(DB_prefix.'timeout',array('time'=>$curtime,'action'=>$action,'ip'=>$ip,'uid'=>$uid));
       return true;
     }
     else return false;
   }

   /** Функция подчистки таблицы таймаутов. Предполагается ее вызов через crontab
    *
    * @param int $interval интервал в часах, за который следует удалять данные.
    */
   function cron_timeout_clear($interval=24) {
     $curtime = time();
     $lasttime = $curtime-$interval*60*60;
     $sql = 'DELETE FROM '.DB_prefix.'timeout WHERE time<'.intval($lasttime);
     Library::$app->db->query($sql);
   }
 }
