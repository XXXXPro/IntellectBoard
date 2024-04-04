<?php

define('BASEDIR','../');
include BASEDIR.'etc/ib_config.php';
include BASEDIR.'app/app.php';

class Application_Convertor extends Application {
  private $cat_offset=0;
  private $forum_offset=0;
  private $topic_offset=0;
  private $user_offset=0;
  private $post_offset=0;
  private $poll_offset=0;
  private $pm_thread_offset=0;
  private $pm_post_offset=0;
  private $old_db='socioclub';
  private $old_prefix='soc_';
  private $file_function='copy'; 
  private $old_dir='../../socioclub';
  
  function init() {
    parent::init();
    ob_end_flush(); // при конвертации буферизация вывода не нужна   
  }
  
  function action_usage_cli() {
    echo 'Запуск: 
        php punbb.php <база_PunBB> <префикс_PunBB> <каталог_PunBB> <обработка_файлов> <очистить_базу>
    где:
        <база_PunBB> — база, в которую установлен PunBB 1.4.x. Она должна быть доступна для того же пользователя, от имени которого идет подключение к базе IntB 3.x с привелегией SELECT.
        <префикс_PunBB> — префикс таблиц PunBB 1.4.x.
        <каталог_PunBB> — путь, где установлен PunBB 1.4.x. Можно указывать как абсолютный, так и путь относительно текущего каталога
        <обработка_файлов> — если равна "none", никакого переноса не производится, если "move" — файлы переносятся, в остальных случаях — копируются
        <очистить_базу> — если здесь указать ненулевое значение, из базы IntB 3.x будут удалены все имеющиеся данные     
        ';
    exit();
  }
  
  function action_usage_form() {
    header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>    
<html><head><title>Преобразователь базы PunBB 1.4.x ==> Inellect Board 3.00</title>
<style type="text/css">
html { height: 100% }
body { height: 100%; padding: 0; margin: 0; font-size: 62.5%; font-family: Tahoma, Verdana, Arial, sans-serif }
form { padding: 0; margin: 0 }
fieldset { border: 0 }
legend { display: none }
button { padding: 5px 10px; font-size: 120% }        
#ib_all { margin: auto; height: 100%; padding: 0 50px; font-size: 1.25em; width: 992px; position: relative }
.submit { text-align: center }
form span { width: 40%; text-align: right; display: inline-block }
</style>
</head>
<body>
<div id="ib_all">
<h1>Преобразование базы данных</h1>
<form action="" method="post"><fielset><legend></legend>
<div><label><span>Название базы данных PunBB 1.4.x</span><input name="old_db" type="text" size="30" /></label></div>
<div><label><span>Префикс таблиц PunBB 1.4.x</span><input name="old_prefix" type="text" size="10" /></label></div>
<div><label><span>Путь к файлами PunBB 1.4.x на сервере</span><input name="old_dir" type="text" size="40" /></label></div>
<div><span>Обработка прикрепленных файлов</span><label>
<input type="radio" name="file_function" value="copy" checked="checked" />Скопировать</label> 
<input type="radio" name="file_function" value="rename"/>Перенести</label>
<input type="radio" name="file_function" value=""/>Не переносить файлы</label>
</div>
<div><span>Тип преобразования</span><label>
<input type="radio" name="truncate" value="" checked="checked" />С добавлением к существующему содержимому</label>
<input type="radio" name="truncate" value="1" />С очисткой базы</label> 
</div>
<div class="submit"><button type="submit">Начать преобразование</button></div>
</fielset></form>
</div>
</body>
</html>
<?php
  exit();   
  }
  
  function init_user() {}
  // именно здесь буде делаться весь вывод
  function process() {
    global $argv;
    $truncate=false;
    if (php_sapi_name()=='cli') {
      if (count($argv)<3) $this->action_usage_cli();
      $this->old_db=$argv[1];
      $this->old_prefix=$argv[2];
      $this->old_dir=$argv[3];
      if (empty($argv[4])) $argv[4]='copy';
      if ($argv[4]=='move') $this->file_function='rename';
      elseif ($argv[4]=='none') $this->file_function='';
      else $this->file_function='copy';
      if (!empty($argv[5])) $truncate=true;
    }
    else {
      if (empty($_REQUEST['old_db'])) $this->action_usage_form();
      $this->old_db=$_REQUEST['old_db'];
      $this->old_prefix=$_REQUEST['old_prefix'];
      $this->old_dir=$_REQUEST['old_dir'];
      $this->file_function=$_REQUEST['file_function'];
      $truncate=!empty($_REQUEST['truncate']);
      header('Content-Type: text/plain; charset=utf-8');
    }
    echo "Преобразование базы данных PunBB: \n";
    echo 'Проверяем доступ к базе данных PunBB 1.4.x... ';
    $sql = 'SELECT * FROM '.$this->old_db.'.'.$this->old_prefix.'forums LIMIT 1';
    $result = $this->db->query($sql);
    if ($this->db->error_num()!=0) {
      echo 'Ошибка подключения! '.$this->db->error_str()."\n";
      echo 'Продолжение невозможно, завершаем работу преобразователя!';
      exit();
    }
    else echo "Ok\n";
    echo "Проверяем путь к файлам PunBB... ";
    if (!file_exists($this->old_dir.'/extensions')) {
      echo 'Ошибка: не удалось найти каталог ! '.$this->old_dir."/extensions/\n";
      echo 'Продолжение невозможно, завершаем работу преобразователя!';
      exit();      
    }
    else echo "Ok\n";
    if (!$truncate) {
      echo "Вычисляем смещения идентификаторов...\n";
      $sql = 'SELECT MAX(id) FROM '.DB_prefix.'category';
      $this->cat_offset = $this->db->select_int($sql);
      $sql = 'SELECT MAX(id) FROM '.DB_prefix.'forum';
      $this->forum_offset = $this->db->select_int($sql); 
      $sql = 'SELECT MAX(id) FROM '.DB_prefix.'topic';
      $this->topic_offset = $this->db->select_int($sql);
      $sql = 'SELECT MAX(id) FROM '.DB_prefix.'post';
      $this->post_offset = $this->db->select_int($sql);
      $sql = 'SELECT MAX(id) FROM '.DB_prefix.'poll_variant';
      $this->poll_offset = $this->db->select_int($sql);
      $sql = 'SELECT MAX(id) FROM '.DB_prefix.'privmsg_thread';
      $this->pm_thread_offset = $this->db->select_int($sql);
      $sql = 'SELECT MAX(id) FROM '.DB_prefix.'privmsg_post';
      $this->pm_post_offset = $this->db->select_int($sql);
      $sql = 'SELECT MAX(id) FROM '.DB_prefix.'user';
      $this->user_offset = $this->db->select_int($sql)-3;      
    }
    else $this->user_offset = 2;
    
    echo "Преобразуем категории...\n";
    if ($truncate) {
      $sql = 'TRUNCATE TABLE '.DB_prefix.'category';
      $this->db->query($sql);
    }
    
    $sql ='INSERT INTO '.DB_prefix.'category (id,title,sortfield) '.
      'SELECT id+'.intval($this->cat_offset).', cat_name, disp_position FROM '.$this->old_db.'.'.$this->old_prefix.'categories';
    $this->db->query($sql);
        
    echo "Преобразуем разделы...\n";
    if ($truncate) {
      $sql = 'TRUNCATE TABLE '.DB_prefix.'forum';
      $this->db->query($sql);
      $sql = 'TRUNCATE TABLE '.DB_prefix.'text';
      $this->db->query($sql);
      $sql = 'TRUNCATE TABLE '.DB_prefix.'views';
      $this->db->query($sql);
    }
    
    $sql ='INSERT INTO '.DB_prefix.'forum (
          id, title, descr, hurl, 
          module, locked, 
          category_id, bcode, rate, is_stats, 
          max_smiles, parent_id,
          icon_nonew, icon_new, lastmod, topic_count, post_count, last_post_id, 
          is_start, sortfield, max_attach   
        )
        SELECT id+'.intval($this->forum_offset).',forum_name, forum_desc, f.id, 
            IF(redirect_url IS NOT NULL,"link","stdforum"), "0",
            cat_id+'.intval($this->cat_offset).', "1", "0", "0", 
            16, 0, 
            "", "", last_post, num_topics, num_posts, last_post_id+'.intval($this->post_offset).', 
            "1", disp_position, 1             
        FROM '.$this->old_db.'.'.$this->old_prefix.'forums f';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'views (
          oid,type,views
        )
        SELECT forum_id+'.intval($this->forum_offset).', "forum", SUM(num_views)
        FROM '.$this->old_db.'.'.$this->old_prefix.'topics t
        GROUP BY forum_id+'.intval($this->forum_offset);
    $this->db->query($sql);    

    echo "Преобразуем разделы-ссылки типа link...\n";
    $sql = 'INSERT INTO '.DB_prefix.'text (oid, type, data)
        SELECT id+'.intval($this->forum_offset).', 3, CONCAT("a:1:{s:3:\"url\";s:",LENGTH(redirect_url),":\"",redirect_url,"\";}") FROM
        FROM '.$this->old_db.'.'.$this->old_prefix.'forums WHERE redirect_url IS NOT NULL';
    
    echo "Преобразуем темы...\n";
    if ($truncate) {
      $sql = 'TRUNCATE TABLE '.DB_prefix.'topic';
      $this->db->query($sql);
    }
    $sql ='INSERT INTO '.DB_prefix.'topic (
          id, fid, title, descr, hurl, status,
          locked, first_post_id, last_post_id, lastmod, 
          post_count, owner, sticky, sticky_post, last_post_time
        )
        SELECT t.id+'.intval($this->topic_offset).', forum_id+'.intval($this->forum_offset).',
            subject, "", "", "0", 
            IF(closed,"1","0"), first_post_id+'.intval($this->post_offset).', last_post_id+'.intval($this->post_offset).', last_post,
            num_replies+1, p.poster_id+'.intval($this->user_offset).', IF(sticky,"1","0"), "0",  last_post
            FROM '.$this->old_db.'.'.$this->old_prefix.'topics t, '.$this->old_db.'.'.$this->old_prefix.'posts p
            WHERE t.id=p.topic_id AND p.id=t.first_post_id';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'views (
          oid,type,views
        )
        SELECT id+'.intval($this->topic_offset).', "topic", num_views 
            FROM '.$this->old_db.'.'.$this->old_prefix.'topics t';
    $this->db->query($sql);    
    
    echo "Преобразуем сообщения...\n";
    if ($truncate) {
      $sql = 'TRUNCATE TABLE '.DB_prefix.'post';
      $this->db->query($sql);
    }   
    $sql ='INSERT INTO '.DB_prefix.'post (
          id, tid, uid, author, postdate, html, bcode, smiles, ip, status
        )
        SELECT 
          p.id+'.intval($this->post_offset).', p.topic_id+'.intval($this->topic_offset).', p.poster_id+'.intval($this->user_offset).',
          poster, posted, "0", "1", IF(hide_smilies,"0","1"), INET_ATON(poster_ip), "0"
          FROM '.$this->old_db.'.'.$this->old_prefix.'posts p';
    $this->db->query($sql);
    
    $sql ='INSERT INTO '.DB_prefix.'text (
          id, type, data, tx_lastmod
        )
        SELECT
          p.id+'.intval($this->post_offset).', 16, p.message, GREATEST(p.posted,p.edited)
            FROM '.$this->old_db.'.'.$this->old_prefix.'posts p';
    $this->db->query($sql);

    echo "Преобразуем пользователей...\n";
    if ($truncate) {
      $sql = 'DELETE FROM '.DB_prefix.'user WHERE id>3';
      $this->db->query($sql);
      $sql = 'DELETE FROM '.DB_prefix.'user_ext WHERE id>3';
      $this->db->query($sql);
      $sql = 'DELETE FROM '.DB_prefix.'user_settings WHERE id>3';
      $this->db->query($sql);
      $sql = 'TRUNCATE '.DB_prefix.'user_warning';
      $this->db->query($sql);
      $sql = 'TRUNCATE TABLE '.DB_prefix.'mark_all';
      $this->db->query($sql);
    }
    $sql ='INSERT INTO '.DB_prefix.'user (
          id, login, password, pass_crypt, display_name, title, 
          gender, birthdate, 
          location, canonical, signature, 
          rnd, email, status, real_name
        )
        SELECT
          u.id+'.intval($this->user_offset).', u.username, password, "1", u.username, "", 
          "U", 0, 
              location, u.username, signature,
            FLOOR(RAND()*0x80000000),u.email, IF(bn.username IS NOT NULL,"2",IF(activate_key IS NULL,"0","1")), realname     
        FROM '.$this->old_db.'.'.$this->old_prefix.'users u 
        LEFT JOIN '.$this->old_db.'.'.$this->old_prefix.'bans bn ON (u.username=bn.username) WHERE u.id>1';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'user_settings (
          id, topics_per_page, posts_per_page, msg_order, 
          timezone, signatures, avatars, smiles, 
          show_birthdate, email_pm, 
          email_broadcasts, hidden, goto
        )
        SELECT 
          u.id+'.intval($this->user_offset).', 20, 20,"Asc",
          timezone*60*60, IF(show_sig=1,"0","1"), IF(show_avatars,"1","0"), IF(show_smilies,"1","0"), 
          "0", IF(email_setting,"1","0"), 
          IF(email_setting=1,"0","1"), "0", "0"         
        FROM '.$this->old_db.'.'.$this->old_prefix.'users u WHERE u.id>1';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'user_ext (
          id, group_id, reg_date, reg_ip, warnings, banned_till
        )
        SELECT u.id+'.intval($this->user_offset).', if(group_id=1,"1024","100"), registered, INET_ATON(registration_ip),0, 0
        FROM '.$this->old_db.'.'.$this->old_prefix.'users u WHERE u.id>1';
    $this->db->query($sql);

    echo "Создаем отметки о прочтении...\n";
    $sql ='INSERT INTO '.DB_prefix.'mark_all (
          uid, fid, mark_time
        )
        SELECT u.id+'.intval($this->user_offset).', 0, NOW()
        FROM '.$this->old_db.'.'.$this->old_prefix.'users u WHERE u.id>1';
    $this->db->query($sql);

    echo "Преобразуем аватары пользователей...\n";
    $sql = 'SELECT u.id+'.intval($this->user_offset).' AS newid, u.avatar, u.id  
        FROM  '.$this->old_db.'.'.$this->old_prefix.'users u 
        WHERE (u.avatar!=0) AND u.id>1';
    $users = $this->db->select_all($sql);
    for ($i=0, $count=count($users); $i<$count;$i++) {
      if ($users[$i]['avatar']==1) { $oldname=$this->old_dir.'/img/avatars/'.$users[$i]['id'].'.gif'; $ext='gif'; }
      elseif ($users[$i]['avatar']==2) { $oldname=$this->old_dir.'/img/avatars/'.$users[$i]['id'].'.jpg'; $ext='jpg'; }
      elseif ($users[$i]['avatar']==3) { $oldname=$this->old_dir.'/img/avatars/'.$users[$i]['id'].'.png'; $ext='png'; }
      else $oldname = false;
      if ($oldname && file_exists($oldname)) {
        $newname = BASEDIR.'www/f/av/'.intval($users[$i]['newid']).'.'.$ext;
          $sql = 'UPDATE '.DB_prefix.'user SET avatar="'.$this->db->slashes($ext).'" WHERE id='.intval($users[$i]['newid']);
          $this->db->query($sql);
          if ($this->file_function) {
            $func=$this->file_function;
            $func($oldname,$newname);
//            echo $oldname.' ==> '.$newname."\n";
          }          
      }
    }
    
/*    echo "Преобразуем опросы...\n";
    if ($truncate) {
      $sql = 'TRUNCATE '.DB_prefix.'poll';
      $this->db->query($sql);
      $sql = 'TRUNCATE TABLE '.DB_prefix.'poll_variant';
      $this->db->query($sql);
      $sql = 'TRUNCATE TABLE '.DB_prefix.'vote';
      $this->db->query($sql);      
    }    
    $sql ='INSERT INTO '.DB_prefix.'poll (
          id, question, endtime
        )
        SELECT
          pl_tid+'.intval($this->topic_offset).', pl_title, pl_enddate
        FROM '.$this->old_db.'.'.$this->old_prefix.'Poll pl';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'poll_variant (
          id, tid, text, count 
        )
        SELECT
          pv_id+'.intval($this->poll_offset).', pl_tid+'.intval($this->topic_offset).', pv_text, pv_count
        FROM '.$this->old_db.'.'.$this->old_prefix.'PollVariant pv, '.$this->old_db.'.'.$this->old_prefix.'Poll pl
        WHERE pv_plid=pl_id';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'vote (
          tid, uid, pvid, time, ip
        )
        SELECT
          tid+'.intval($this->topic_offset).', uid+'.intval($this->user_offset).', pvid+'.intval($this->poll_offset).', NOW(), 0
        FROM '.$this->old_db.'.'.$this->old_prefix.'Vote v';
    $this->db->query($sql);*/
    
    echo "Преобразуем подписки и закладки...\n";
    if ($truncate) {
      $sql = 'TRUNCATE '.DB_prefix.'last_visit';
      $this->db->query($sql);
    }
    $sql ='INSERT INTO '.DB_prefix.'last_visit (
          oid, type, uid, visit1, visit2, 
          bookmark, subscribe, lastmail
        )
        SELECT
          topic_id+'.intval($this->topic_offset).', "topic", user_id+'.intval($this->user_offset).', NOW(), NOW(), 
            "0", "1", 0
        FROM '.$this->old_db.'.'.$this->old_prefix.'subscriptions ';
    $this->db->query($sql);

/*    echo "Преобразуем список друзей и список игнорируемых...\n";
    if ($truncate) {
      $sql = 'TRUNCATE '.DB_prefix.'relation';
      $this->db->query($sql);
    }    
    $sql ='INSERT INTO '.DB_prefix.'relation (
          `from`, `to`, `type`
        )
        SELECT u_owner+'.intval($this->user_offset).', u_partner+'.intval($this->user_offset).', IF(u_status=-1,"ignore","friend") 
        FROM '.$this->old_db.'.'.$this->old_prefix.'AddrBook 
            WHERE u_owner>'.AUTH_SYSTEM_USERS.' AND u_partner >'.AUTH_SYSTEM_USERS;
    $this->db->query($sql);*/
   
  echo 'Проверяем расширение pun_pm... ';
  $sql = 'SELECT COUNT(*) FROM '.$this->old_db.'.'.$this->old_prefix.'extensions WHERE id="pun_pm"';
  if ($this->db->select_int($sql)) {
   echo "найдено!\n";
   echo "Преобразуем личные сообщения....\n";
    if ($truncate) {
      $sql = 'TRUNCATE '.DB_prefix.'privmsg_thread';
      $this->db->query($sql);
      $sql = 'TRUNCATE '.DB_prefix.'privmsg_thread_user';
      $this->db->query($sql);
      $sql = 'TRUNCATE '.DB_prefix.'privmsg_post';
      $this->db->query($sql);
      $sql = 'TRUNCATE '.DB_prefix.'privmsg_link';
      $this->db->query($sql);      
    }
    $sql ='INSERT INTO '.DB_prefix.'privmsg_thread (
          id, title
        )
        SELECT id+'.intval($this->pm_thread_offset).', IF(subject="","без темы",subject) 
        FROM '.$this->old_db.'.'.$this->old_prefix.'pun_pm_messages';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'privmsg_post (
          id, pm_thread, subscribers, 
          uid, text, postdate, html, bcode, smiles 
        )
        SELECT id+'.intval($this->pm_post_offset).',id+'.intval($this->pm_thread_offset).',2-deleted_by_sender-deleted_by_receiver,
          sender_id+'.intval($this->user_offset).',body,lastedited_at,"0","1","1" 
        FROM '.$this->old_db.'.'.$this->old_prefix.'pun_pm_messages';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'privmsg_thread_user (
          pm_thread,uid,total,unread,last_post_date
        )
        SELECT id+'.intval($this->pm_thread_offset).',sender_id+'.intval($this->user_offset).',1,IF(status="read",0,1),lastedited_at 
        FROM '.$this->old_db.'.'.$this->old_prefix.'pun_pm_messages';
    $this->db->query($sql);    
    $sql ='INSERT INTO '.DB_prefix.'privmsg_thread_user (
          pm_thread,uid,total,unread,last_post_date
        )
        SELECT id+'.intval($this->pm_thread_offset).',receiver_id+'.intval($this->user_offset).',1,IF(status="read",0,1),lastedited_at 
        FROM '.$this->old_db.'.'.$this->old_prefix.'pun_pm_messages';
    $this->db->query($sql);    
    
    $sql ='INSERT INTO '.DB_prefix.'privmsg_link (
          pm_id,uid
        )
        SELECT id+'.intval($this->pm_post_offset).',sender_id+'.intval($this->user_offset).'
        FROM '.$this->old_db.'.'.$this->old_prefix.'pun_pm_messages
            WHERE deleted_by_sender=0';
    $this->db->query($sql);    
    $sql ='INSERT INTO '.DB_prefix.'privmsg_link (
          pm_id,uid
        )
        SELECT id+'.intval($this->pm_post_offset).',receiver_id+'.intval($this->user_offset).'
        FROM '.$this->old_db.'.'.$this->old_prefix.'pun_pm_messages
            WHERE deleted_by_receiver=0';
    $this->db->query($sql);    
  }
  else echo "не найдено, пропускаем преобразование ЛС.\n";

  echo 'Проверяем расширение pun_attachment... ';
  $sql = 'SELECT COUNT(*) FROM '.$this->old_db.'.'.$this->old_prefix.'extensions WHERE id="pun_attachment"';
  if ($this->db->select_int($sql)) {
    echo "найдено!\n";
    echo "Преобразуем прикрепленные файлы...\n";
    if ($truncate) {
      $sql = 'TRUNCATE '.DB_prefix.'file';
      $this->db->query($sql);
    }
    $sql ='INSERT INTO '.DB_prefix.'file (
          fkey, 
          oid, type, filename, size, 
          format, extension 
        )
        SELECT SUBSTRING(MD5(CONCAT(file_path,"'.$this->get_opt('site_secret').'",filename,id)) FROM 1 FOR 12),
          post_id+'.intval($this->post_offset).', 1, filename, size, 
          IF(POSITION("image" IN file_mime_type)>0,"image","attach"), SUBSTRING(filename FROM POSITION("." IN filename)+1 FOR 3)  
        FROM '.$this->old_db.'.'.$this->old_prefix.'attach_files';
    $this->db->query($sql);
    if ($this->file_function) {
      $sql='SELECT id, file_path, SUBSTRING(MD5(CONCAT(file_path,"'.$this->get_opt('site_secret').'",filename,id)) FROM 1 FOR 12) AS newkey, post_id+'.intval($this->post_offset).' AS newid 
      FROM '.$this->old_db.'.'.$this->old_prefix.'attach_files';
      $files = $this->db->select_all($sql);
      for ($i=0, $count=count($files); $i<$count; $i++) {
        $func=$this->file_function;
        $func($this->old_dir.'/extensions/pun_attachment/attachments/'.$files[$i]['file_path'],BASEDIR.'www/f/up/1/'.intval($files[$i]['newid']).'-'.$files[$i]['newkey'].'.dat');
        //echo $this->old_dir.'/files/'.$files[$i]['file_id'].'.htm ==> '.BASEDIR.'www/f/up/1/'.$files[$i]['newkey'].'.dat'."\n";            
      }
      unset($files);
    }    
  }
  else echo "не найдено, пропускаем преобразование файлов.\n";

  echo 'Проверяем расширение nya_thanks... ';
  $sql = 'SELECT COUNT(*) FROM '.$this->old_db.'.'.$this->old_prefix.'extensions WHERE id="nya_thanks"';
  if ($this->db->select_int($sql)) {
    echo "найдено!\n";    
    echo "Преобразуем рейтинги сообщений и пользователей...\n";
    if ($truncate) {
      $sql = 'TRUNCATE TABLE '.DB_prefix.'rating';
      $this->db->query($sql);
    }

    $sql = 'INSERT INTO '.DB_prefix.'rating (
      id,uid,value,time,ip
    )
    SELECT DISTINCT post_id+'.intval($this->post_offset).', user_id+'.intval($this->user_offset).', 1, MIN(time), 0 
        FROM '.$this->old_db.'.'.$this->old_prefix.'thanks 
        GROUP BY post_id+'.intval($this->post_offset).', user_id+'.intval($this->user_offset);
    $this->db->query($sql);
  }
  else echo "не найдено, пропускаем преобразование рейтингов.\n";

    echo "Конвертация форума завершена!";
  }
   
  function output() {
    
  }
  
  function error_handler($errno, $errstr, $errfile, $errline) {
    echo "\n\nВозникла ошибка ".$errno.': '.$errstr."\n";
  }  
}

$app = new Application_Convertor();
$app->main();