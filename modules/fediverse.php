<?php 

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.10
 *  @copyright 2021, 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Модуль работы с протоколами fediverse: ActivityPub, webfinger and so on
 *  ================================ */

class fediverse extends Application {
    function get_mime() {
        if ($this->action==='host_meta') return 'application/xrd+xml';
        if ($this->action==='webfinger') return 'application/jrd+json';
        return 'text/html; charset=utf-8';
    }

    function get_request_type() {
      return 4; // никаой обработки возвращаемых данных
    }    

    function action_host_meta() {
       $result = '<?xml version="1.0" encoding="UTF-8"?>
<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">
  <Link rel="lrdd" template="https://'.$_SERVER['HTTP_HOST'].'/.well-known/webfinger?resource={uri}"/>
</XRD>';
       return $result;
    }

    function action_webfinger() {
      if (empty($_GET['resource'])) {
        header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
        return '{"code":"rest_missing_callback_param","message":"\u041e\u0442\u0441\u0443\u0442\u0441\u0442\u0432\u0443\u0435\u0442 \u043f\u0430\u0440\u0430\u043c\u0435\u0442\u0440: resource","data":{"status":400,"params":["resource"]}}';
      }
      $result = false;
      if (substr($_GET['resource'],0,5)==='acct:') {
        $resource = substr($_GET['resource'],5);
        if (strpos($resource,'@')===false) {
          header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
          return '{"code":"rest_invalid_param","message":"\u041d\u0435\u0432\u0435\u0440\u043d\u044b\u0439 \u043f\u0430\u0440\u0430\u043c\u0435\u0442\u0440: resource","data":{"status":400,"params":{"resource":"resource \u043d\u0435 \u0441\u043e\u043e\u0442\u0432\u0435\u0442\u0441\u0442\u0432\u0443\u0435\u0442 \u043f\u0430\u0442\u0442\u0435\u0440\u043d\u0443 ^acct:([^@]+)@(.+)$."}}}';
        }
        list($name,$domain)=@explode('@',$resource);
        if ($domain!==$_SERVER['HTTP_HOST']) {
            $result = '{"code":"activitypub_wrong_host","message":"Resource host does not match site host","data":{"status":404}}';
        }
        else {       
          $user = $this->load_user(4,0,$name); // режим 1 — загрузку пользователя производим по логину, а не по uid
          if ($user) {
            $data = array();
            $data['subject']="acct:".$user['login'].'@'.$_SERVER['HTTP_HOST'];
            $profile = array($this->get_user_url());
            $data['aliases']=$profile;
            $data['links']=array(
                array('rel'=>'self',"type"=>"application/activity+json","href"=>$profile),
                array('rel'=>'http://webfinger.net/rel/profile-page',"type"=>"text/html","href"=>$profile),
                array('rel'=>'http://ostatus.org/schema/1.0/subscribe','template'=>$this->http($this->url('.activity_pub/authorize_interaction.php?uri={uri}')))
            );
            return json_encode($data); 
          }
          else $result = '{"code":"activitypub_user_not_found","message":"User not found","data":{"status":404}}';
        }
      }
      header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
      return $result;
    }
}