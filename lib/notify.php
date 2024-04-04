<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Библиотека уведомлений о новых темах, личных сообщениях и т.п.
 *  ================================ */

class Library_notify extends Library implements iNotifier {
  /** Уведомления о новом сообщении в уже существующей теме **/
  private function send_post_notification ($post,$topic,$forum,$parsed,$template) {
    $mdata['topic']=$topic;
    $mdata['forum']=$forum;
    $mdata['post']=$post;
    $mdata['parsed']=$parsed;
    
    $sql = 'SELECT u.id, u.email, u.display_name, us.email_fulltext, MAX(lv.type), lv.oid, ue.group_id '.
      'FROM '.DB_prefix.'last_visit lv, '.DB_prefix.'user u '.
      'LEFT JOIN '.DB_prefix.'user_settings us ON (u.id=us.id) '.
      'LEFT JOIN '.DB_prefix.'user_ext ue ON (u.id=ue.id) '.
      'WHERE ((lv.oid='.intval($topic['id']).' AND lv.type=\'topic\') OR '.
      '(lv.oid='.intval($forum['id']).' AND lv.type=\'forum\') OR '.
      '(lv.oid=0 AND lv.type=\'forum\')) '.
      'AND lv.uid=u.id AND u.status=\'0\' AND  lv.subscribe=\'1\' AND us.subscribe_mode=\'1\' '.
      'AND u.id!='.intval(Library::$app->get_uid()).' AND u.id>'.intval(AUTH_SYSTEM_USERS).
      ' GROUP BY u.id, u.email, u.display_name, us.email_fulltext, lv.type, lv.oid, ue.group_id';
    $users = Library::$app->db->select_all($sql);
    $parents = Library::$app->get_parent_forums($forum['id']);
    $userlib = Library::$app->load_lib('userlib',false);
    if (!$userlib) return; // если библиотека не загрузилась, то никакой рассылки, так как нельзя проверить права доступа   
    
    for ($i=0, $count=count($users);$i<$count;$i++) {
      $mdata['user']=$users[$i];
      $has_access = $userlib->ext_check_access($parents,$users[$i]['group_id'],'read');
      if ($has_access) { // отправляем письмо только в том случае, если права на чтение есть
        $mdata['unsubscribe_key']=Library::$app->gen_auth_key($users[$i]['id'],'unsubscr',Library::$app->url('bookmark/'));
        $mdata['unsubscribe_key2']=Library::$app->gen_auth_key($users[$i]['id'],'unsubscribe_all',Library::$app->url('user/'));;
        Library::$app->mail(array('to'=>$users[$i]['email'],'to_name'=>$users[$i]['display_name'],
          'subj'=>'Уведомление о новом сообщении в теме "'.$topic['title'].'"','unsubscribe'=>$unsubscribe,
          'template'=>$template,'data'=>$mdata,'html'=>true,'list-id'=>'Topic notification <topic.'.intval($topic['id']).'.'.$_SERVER['HTTP_HOST'].'>'));
      }
    }
  }
  
  /** Уведомление об отправке нового сообщения **/
  function new_post($post,$topic,$forum,$parsed) {
    $this->send_post_notification($post, $topic, $forum, $parsed,'stdforum/mail_newpost.tpl');
    /** @var Library_forums */
    $forumlib = Library::$app->load_lib('forums', false);
    if ($forumlib) {
      $fdata = $forumlib->get_forum(Library::$app->forum['id'], true); // нам нужны расширенные данные форума
      if (!empty($fdata['extdata']['telegram_id']) && !empty($fdata['extdata']['telegram_key'])) {
        if (!empty($fdata['extdata']['telegram_mode'])) {
          $full_hurl = Library::$app->http(Library::$app->url($topic['full_hurl'].'post-'.$post['id'].'.htm')); // ссылаемся не просто на тему, а на конкретное сообщение
          if ($fdata['extdata']['telegram_mode'] == 3) {
            $text = '<b>'.$post['author']."</b> ответил в теме: \r\n<a href=\"".$full_hurl."\">".$topic['title'].'</a>';
            $this->notify_tg($fdata['extdata']['telegram_key'],$text, $fdata['extdata']['telegram_id']);
          }
          if ($fdata['extdata']['telegram_mode'] == 4) {
            $max_len = 3600;
            if (function_exists('mb_strlen') && mb_strlen($parsed) > $max_len) {
              $parsed = mb_substr($parsed, 0, $max_len);
              $rpos = mb_strrpos($parsed, '.');
              $parsed = mb_substr($parsed, 0, $rpos);
              $parsed .= "[…]\r\n<a href=\"$full_hurl\">Читать продолжение на сайте</a>";
            } else $parsed .= "\r\n<a href=\"$full_hurl\">Прокомментировать на сайте</a>";
            $text = '<b>'.$post['author']."</b> написал:\r\n".$parsed;
            $this->notify_tg($text, $fdata['extdata']['telegram_id']);
          }
        }
      }
    }
  }
  
  /** Уведомление о создании новой темы **/
  function new_topic($post,$topic,$forum,$parsed) {
    $this->send_post_notification($post, $topic, $forum, $parsed, 'stdforum/mail_newtopic.tpl');
    /** @var Library_forums */
    $forumlib = Library::$app->load_lib('forums',false);
    if ($forumlib) {
      $fdata=$forumlib->get_forum(Library::$app->forum['id'],true); // нам нужны расширенные данные форума
      if (!empty($fdata['extdata']['telegram_id']) && empty($_POST['no_export'])) {
        if (!empty($fdata['extdata']['telegram_mode']) && !empty($fdata['extdata']['telegram_key'])) {
          $full_hurl = Library::$app->http(Library::$app->url($topic['full_hurl']));
          if ($fdata['extdata']['telegram_mode']==1 || $fdata['extdata']['telegram_mode']==3) {
            $text = '<b>'.$post['author']."</b> создал новую тему: \r\n<a href=\"".$full_hurl."\">".$topic['title'].'</a>';
            $this->notify_tg($text, $fdata['extdata']);
          }
          if ($fdata['extdata']['telegram_mode'] == 2 || $fdata['extdata']['telegram_mode'] == 4) {
            $max_len = 3600;
            if ($forum['module']!=='micro') $parsed='<b>'.htmlspecialchars($topic['title'])."</b>\r\n".$parsed;
            if (function_exists('mb_strlen') && mb_strlen($parsed)>$max_len) {
              $parsed = mb_substr($parsed,0, $max_len);
              $rpos = mb_strrpos($parsed,'.');
              $parsed = mb_substr($parsed, 0, $rpos);
              if ($fdata['module']!=='micro') $parsed.="[…]\r\n<a href=\"$full_hurl\">Читать продолжение на сайте</a>";
            }
            elseif ($fdata['module']!=='micro') $parsed .= "\r\n<a href=\"$full_hurl\">Прокомментировать на сайте</a>";
            $text = '<b>'.$post['author']."</b> написал:\r\n".$parsed;
            $this->notify_tg($text, $fdata['extdata']);
          }
        }
      }
      if (!empty($fdata['extdata']['lj_login']) && empty($_POST['no_export'])) {
        $tags = !empty($_POST['tagline']) ? $_POST['tagline'] : false;
        $this->notify_lj($parsed,$topic,$fdata['extdata'],$tags);
      }
      if (!empty($fdata['extdata']['vk_user']) && empty($_POST['no_export'])) {
        $tags = !empty($_POST['tagline']) ? $_POST['tagline'] : false;
        $this->notify_vk($parsed,$topic,$fdata['extdata'],$tags);
      }
    }
  }
  
  /** Уведомление о новом личном сообщении **/
  function new_pm($thread,$pm,$parsed,$sender,$reply_mail) {
    $sql = 'SELECT u.id, u.email, u.display_name, us.email_fulltext '.
        'FROM '.DB_prefix.'privmsg_thread_user thu, '.DB_prefix.'user u '.
        'LEFT JOIN '.DB_prefix.'user_settings us ON (u.id=us.id) '.
        'WHERE thu.pm_thread='.intval($thread['id']).' AND thu.uid=u.id AND us.email_pm=\'1\' '.
        'AND u.id!='.intval(Library::$app->get_uid()).' AND u.id>'.intval(AUTH_SYSTEM_USERS);
    $users = Library::$app->db->select_all($sql);
    $mdata['sender']=$sender;
    $mdata['parsed']=$parsed;
    $mdata['thread']=$thread;
    $mdata['pm']=$pm;
    for ($i=0, $count=count($users);$i<$count;$i++) {
      $mdata['user']=$users[$i];
      Library::$app->mail(array('to'=>$users[$i]['email'],'to_name'=>$users[$i]['display_name'],
          'subj'=>$thread['title'],'from_name'=>$sender,'reply'=>$reply_mail,
          'template'=>'privmsg/mail_notify.tpl','data'=>$mdata,'html'=>true,'list-id'=>'PM Notify <pm.'.$_SERVER['HTTP_HOST'].'>'));
    }
  }
  
  /** Уведомление о регистрации нового пользователя. 
   * В качестве адреса для ответа указываем Email отправителя, чтобы пользователь мог ответить через кнопку Reply в почтовом клиенте **/
  function new_user($udata,$activate_mode) {
    $userlib = Library::$app->load_lib('userlib');
    $admins=$userlib->get_admins();
    $udata['activate_mode'] = $activate_mode;
    for ($i=0,$count=count($admins);$i<$count;$i++) {
      $udata['admin_name'] = $admins[$i]['login'];
      Library::$app->mail(array('to'=>$admins[$i]['email'],'subj'=>'Новый пользователь на форуме '.Library::$app->get_opt('site_title'),
          'to_name'=>$admins[$i]['login'],'template'=>'user/mail_newuser.tpl','data'=>$udata,'html'=>true,'list-id'=>'New user <newuser.'.$_SERVER['HTTP_HOST'].'>'));
    }    
  }
  
  /* Экспорт записи в ЖЖ (пока используется только модулем blog) */
  function notify_lj($parsed,$topic,$ljdata,$tags=false) {
       if ($tags) $parsed="lj-tags: ".$tags."\n\n".$parsed;
       if (!empty($ljdata['lj_text'])) {
          $ljtext=$ljdata['lj_text'];
          if (strpos($ljtext,'{{')!==false) {
            $ljtext = str_replace('{{','<a href="'.Library::$app->http(Library::$app->url(Library::$app->topic['full_hurl'])).'">',$ljtext);
            $ljtext = str_replace('}}','</a>',$ljtext);
          }
          else {
            $ljtext = '<a href="'.Library::$app->http(Library::$app->url($topic['full_hurl'])).'">'.$ljtext.'</a>';
          }
          $parsed.="\n".$ljtext;
       }
       if (preg_match('|\[teaserbreak(=[^\]]*)?\]|',$parsed,$match)) {
        $parsed = str_replace($match[0],'<lj-cut>',$parsed);
        $parsed.='</lj-cut>';
       }
       Library::$app->mail(array('to'=>$ljdata['lj_login'].'+'.$ljdata['lj_pin'].'@post.livejournal.com','to_name'=>$ljdata['lj_login'],
          'subj'=>$topic['title'],
          'template'=>'blog/ljmail.tpl','data'=>array('text'=>$parsed),'html'=>true));   
  }

  function notify_vk($parsed,$topic,$vkdata,$tags=false) {
      $text = strip_tags($parsed);
      if ($tags) {
        $text.="\n#".join(' #',array_map('trim',explode(',',$tags)));
      }
      $topic_url = Library::$app->http(Library::$app->url($topic['full_hurl']));      
      if (strpos($parsed,'<code')!==false || strpos($parsed,'<pre')!==false) {
        $text = $topic['title']." \r\n".$topic['descr']." \r\n".$topic_url;
      }
      else {
        $text = preg_replace('|<a\W[^>]*?href=[\'"]([^>]+?)[\'"][^>]*?>(.*?)</a>|u','$2 ($1)',$text);
      }
      $curl = curl_init();
      curl_setopt_array($curl, [
          CURLOPT_URL => 'https://api.vk.com/method/wall.post',
          CURLOPT_POST => true,
          CURLOPT_SSL_VERIFYPEER => true,
          CURLOPT_SSL_VERIFYHOST => true,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POSTFIELDS => [
              'owner_id' => $vkdata['vk_user'],
              'message' => $text,
              'access_token' => $vkdata['vk_token'],
              'v' => '5.85',
              'copyright' => $topic_url,
              'guid' => Library::$app->topic['full_hurl']
          ]
      ]);
      curl_exec($curl);
      $req_info = curl_getinfo($curl);
      if ($req_info['http_code']!=200) Library::$app->log_entry('vk',E_USER_ERROR,__FILE__,print_r($req_info,true)); // логгируем ошибки для упрощения отладки
  }

  /** Отправка уведомления в Telgram-канал. Telegram API key берётся из telegram_key в глобальных настройках. 
   * Текст отправляется в HTML-формате с предварительной обработкой с помощью strip_tags, чтобы остались только разрешённые в Telegram теги.
   */
  function notify_tg($text,$tg_data) {
    $api_key = $tg_data['telegram_key'];
    if ($api_key) {
      $params['text']=strip_tags($text, '<a><b><strong><i><em><u><ins><s><strike><del><code><pre>');
      $params['chat_id']=$tg_data['telegram_id'];
      $params['parse_mode'] = 'HTML';
      $params['disable_web_page_preview']=1;
      $tg_endpoint = 'https://api.telegram.org/bot'.$api_key.'/sendMessage';
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_URL, $tg_endpoint);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
      curl_exec($ch);
      $req_info = curl_getinfo($ch);
      if ($req_info['http_code'] != 200) Library::$app->log_entry('telegram', E_USER_ERROR, __FILE__, print_r($req_info, true)); // логгируем ошибки для упрощения отладки
    }
  }
}