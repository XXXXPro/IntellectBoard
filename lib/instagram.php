<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2018 4X_Pro, INTBPRO.RU
 *  Intellect Board Pro
 *  Библиотека получения и вывода изображений с Instagram
 *  ================================ */

 class Library_instagram extends Library{
   function cron_getdata($params) {
      $params = explode(',',$params);
      $url = 'https://graph.instagram.com/me/media?fields=caption,media_type,media_url,thumbnail_url,id,permalink,timestamp&access_token='.$params[0];

      $max_size = !empty($params[1]) ? $params[1] : 180;
      $quality = !empty($params[2]) ? $params[2] : 90;
      
      $dl_lib = Library::$app->load_lib('download',false);
      if ($dl_lib) {
        $filename = BASEDIR.'tmp/instagram.json';
        $dl_lib->get($url,$filename);
        $img_lib = Library::$app->load_lib('image',false);
        if (is_readable($filename) && $img_lib) {
          $data = json_decode(file_get_contents($filename),true);
          if (empty($data)) return;
          for ($i=0,$count=count($data['data']);$i<$count;$i++) {
            $remote_url = $data['data'][$i]['media_url'];
            if ($data['data'][$i]['media_type']=='VIDEO') $remote_url = $data['data'][$i]['thumbnail_url'];            
            $local_file = BASEDIR.'www/f/instagram/'.$data['data'][$i]['id'].'.jpg';
            $image = $img_lib->load($remote_url);
            if (!$image) Library::$app->log_entry('instagram', E_USER_ERROR, __FILE__, print_r($data['data'][$i], true)); // логгируем ошибки для упрощения отладки
            else $img_lib->save_fit_to($image,$max_size,$max_size,$local_file,IMAGETYPE_JPEG,$quality);
          }
        }
      }
   }

   function cron_refresh($params) {
     $params = explode(',',$params); 
     $url = "https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=".$params[0];
     $dl_lib = Library::$app->load_lib('download',false);
     if ($dl_lib) {
       $dl_lib->get($url,BASEDIR.'logs/instagram_refresh.log');
       unlink(BASEDIR.'logs/instagram_refresh.log');
     }
   }
   
   function block_instagram($params) {
     $filename = BASEDIR.'/tmp/instagram.json';
     $params =explode(',',$params);
     $limit = !empty($params[0]) ? $params[0] : 5;
     $size = !empty($params[1]) ? $params[1] : 0;
     if (!is_readable($filename)) return false;     
     $data = json_decode(file_get_contents($filename),true);
     if (empty($data)) return false;
     $result = array();
     for ($i=0,$count=count($data['data']);$i<min($count,$limit);$i++) {
       $title =  isset($data['data'][$i]['caption']) ? $data['data'][$i]['caption'] : '';
       $title = preg_replace('|#[а-яА-ЯёЁ]+|u','',$title); // удаляем хеш-теги, чтобы не засорять описание фото 
       $created_time = strtotime($data['data'][$i]['timestamp']); // в новой API время нужно парсить
       if (is_readable(BASEDIR.'www/f/instagram/'.$data['data'][$i]['id'].'.jpg')) $src = Library::$app->url('f/instagram/'.$data['data'][$i]['id'].'.jpg');
       else $src = $data['data'][$i]['media_url'];
       $result[]=array('src'=>$src,'title'=>$title,'created'=>$created_time,'href'=>$data['data'][$i]['permalink']);
       Library::$app->lastmod=max(Library::$app->lastmod,$created_time);
     }
     
     return array('instagram/block.tpl',$result);
   }
 }