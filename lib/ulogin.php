<?php
class Library_ulogin extends Library implements iSocial {
  function social_login() {
    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
    $user = json_decode($s, true);
    
    if (!empty($user['nickname'])) $result['contact_id']=$user['nickname'];
    else $result['contact_id']=$user['uid'];
    // специфические настройки для отдельных сетей
    if ($user['network']==='mailru') {
      $result['contact_id']=str_replace('http://my.mail.ru/','',$user['identity']);
    }
    if ($user['network']==='gmail') {
      $result['contact_id']=str_replace('@gmail.com','',$user['email']);
    }
    if ($user['network']==='vkontakte' && empty($user['nickname'])) {
      $result['contact_id']='id'.$user['uid'];
    }
    if ($user['network']==='odnoklassniki' && empty($user['nickname'])) {
      $result['contact_id']=str_replace('http://www.odnoklassniki.ru/profile/','',$user['profile']);
    }
    if ($user['network']==='facebook' && empty($user['nickname'])) {
      $result['contact_id']=$user['uid'];
      
    }
    if ($user['network']==='webmoney') {
      $result['contact_id']=str_replace(array('https://','.wmkeeper.com/'),'',$user['identity']);
    }
    if ($user['network']==='openid') {
      $result['contact_id']=$user['profile'];
    }
    if ($user['network']==='livejournal') {
      $result['contact_id']=str_replace(array('http://','.livejournal.com/'),'',$user['profile']);
    }
    
    $result['basic']['login']=str_replace(array('http://','https://'),'',$result['contact_id'].'_'.$user['network']); // формируем логин как идентификаор_соцсеть и удаляем префиксы http, если они вдруг попали в него     
    $result['basic']['display_name']=$user['first_name'].' '.$user['last_name'];
    $result['basic']['email']=$user['email'];
    if (!empty($user['bdate'])) $result['basic']['birthdate']=$user['bdate'];
    if (empty($user['sex'])) $user['sex']='U';
    $result['basic']['gender']=$user['sex']==2 ? 'M' : ($user['sex']==1 ? 'F' : 'U');
    if (!empty($user['city'])) {
      $result['basic']['location']=$user['city'];
      if (!empty($user['country'])) $result['basic']['location'].=', '.$user['country'];
    }
    
    $result['contact_type']=$user['network'];
    $result['avatar_url']=(!empty($user['photo'])) ? $user['photo'] : false;
    $result['photo_url']=(!empty($user['photo_big'])) ? $user['photo_big'] : false;
    $result['confirmed']=($user['verified_email']==1);

    return $result;
  }
}