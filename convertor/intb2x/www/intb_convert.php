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
        php intb.php <база_IntB2> <префикс_IntB2> <каталог_IntB2> <обработка_файлов> <очистить_базу>
    где:
        <база_IntB2> — база, в которую установлен Intellect Board 2.22. Она должна быть доступна для того же пользователя, от имени которого идет подключение к базе 3.x с привелегией SELECT.
        <префикс_IntB2> — префикс таблиц Intellect Board 2.22
        <каталог_IntB2> — путь, где установлен IntB 2.x. Можно указывать как абсолютный, так и путь относительно текущего каталога
        <обработка_файлов> — если равна "none", никакого переноса не производится, если "move" — файлы переносятся, в остальных случаях — копируются
        <очистить_базу> — если здесь указать ненулевое значение, из базы будут удалены все имеющиеся данные     
        ';
    exit();
  }
  
  function action_usage_form() {
    header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>    
<html><head><title>Преобразователь базы Intellect Board 2.22 ==> 3.00</title>
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
<div><label><span>Название базы данных IntB 2.22</span><input name="old_db" type="text" size="30" /></label></div>
<div><label><span>Префикс таблиц IntB 2.22</span><input name="old_prefix" type="text" size="10" /></label></div>
<div><label><span>Путь к файлами IntB 2.22 на сервере</span><input name="old_dir" type="text" size="40" /></label></div>
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
    echo "Преобразование базы данных Intellect Board: \n";
    echo 'Проверяем доступ к базе данных Intellect Board 2.22... ';
    $sql = 'SELECT * FROM '.$this->old_db.'.'.$this->old_prefix.'Forum LIMIT 1';
    $result = $this->db->query($sql);
    if ($this->db->error_num()!=0) {
      echo 'Ошибка подключения! '.$this->db->error_str()."\n";
      echo 'Продолжение невозможно, завершаем работу преобразователя!';
      exit();
    }
    else echo "Ok\n";
    echo "Проверяем путь к файлам Intellect Board... ";
    if (!file_exists($this->old_dir.'/files')) {
      echo 'Ошибка: не удалось найти каталог ! '.$this->old_dir."/files/\n";
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
    
    echo "Преобразуем категории...\n";
    if ($truncate) {
      $sql = 'TRUNCATE TABLE '.DB_prefix.'category';
      $this->db->query($sql);
    }
    
    $sql ='INSERT INTO '.DB_prefix.'category (id,title,sortfield) '.
      'SELECT ct_id+'.intval($this->cat_offset).', ct_name, ct_sortfield FROM '.$this->old_db.'.'.$this->old_prefix.'Category';
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
        SELECT f_id+'.intval($this->forum_offset).',f_title, f_descr, IF(f_link="",f_id,f_link), 
            IF(f_tpid=6,"link",IF(f_tpid=2 OR f_tpid=8,"statpage","stdforum")), f_status,
            f_ctid+'.intval($this->cat_offset).', IF(f_bcode,"1","0"), IF(f_rate,"1","0"), IF(f_nostats!=0,"0","1"), 
            IF(f_smiles>0,16,0) AS smiles, IF (f_parent!=0,f_parent+'.intval($this->forum_offset).',0), 
            f_nonewpic, f_newpic, f_update, f__tcount, f__pcount, f__lastpostid+'.intval($this->post_offset).', 
            IF(f_hidden,"0","1") AS f_start, f_sortfield, 1             
        FROM '.$this->old_db.'.'.$this->old_prefix.'Forum WHERE f_tpid IN (1,2,3,6,7,8,10,11,12)';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'views (
          oid,type,views
        )
        SELECT t_fid+'.intval($this->forum_offset).', "forum", SUM(t__views)
        FROM '.$this->old_db.'.'.$this->old_prefix.'Topic t, '.$this->old_db.'.'.$this->old_prefix.'TopicVC tv
        WHERE t.t_id=tv.tid GROUP BY t_fid+'.intval($this->forum_offset);
    $this->db->query($sql);    
    echo "Преобразуем правила разделов...\n";
    $sql = 'INSERT INTO '.DB_prefix.'text (oid, type, data)
        SELECT f_id+'.intval($this->forum_offset).', 0, f_rules FROM 
        FROM '.$this->old_db.'.'.$this->old_prefix.'Forum WHERE f_tpid IN (1,2,3,7,8,10,11,12)';
    
    echo "Преобразуем текст разделов типа statpage...\n";
    $sql = 'INSERT INTO '.DB_prefix.'text (oid, type, data)
        SELECT f_id+'.intval($this->forum_offset).', 1, f_text FROM
        FROM '.$this->old_db.'.'.$this->old_prefix.'Forum WHERE f_tpid IN (1,2,3,7,8,10,11,12)';
    echo "Преобразуем разделы-ссылки типа link...\n";
    $sql = 'INSERT INTO '.DB_prefix.'text (oid, type, data)
        SELECT f_id+'.intval($this->forum_offset).', 3, CONCAT("a:1:{s:3:\"url\";s:",LENGTH(f_url),":\"",f_url,"\";}") FROM
        FROM '.$this->old_db.'.'.$this->old_prefix.'Forum WHERE f_tpid=6';
    
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
        SELECT t_id+'.intval($this->topic_offset).', t_fid+'.intval($this->forum_offset).',
            t_title, t_descr, IF(t_link="",t_id,t_link), "0", 
            IF(t__status=0,"0","1"), t__startpostid+'.intval($this->post_offset).', t__lastpostid+'.intval($this->post_offset).', t__lasttime,
            t__pcount, p.p_uid+'.intval($this->user_offset).', IF(t__sticky,"1","0"), IF(t__stickypost,"1","0"),  t__lasttime
            FROM '.$this->old_db.'.'.$this->old_prefix.'Topic t, '.$this->old_db.'.'.$this->old_prefix.'Post p 
            WHERE t.t_id=p.p_tid AND p.p_id=t.t__startpostid';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'views (
          oid,type,views
        )
        SELECT tid+'.intval($this->topic_offset).', "topic", t__views 
            FROM '.$this->old_db.'.'.$this->old_prefix.'TopicVC t';
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
          p_id+'.intval($this->post_offset).', p_tid+'.intval($this->topic_offset).', p_uid+'.intval($this->user_offset).',
          p_uname, p__time, IF(p__html,"1","0"), IF(p__bcode,"1","0"), IF(p__smiles,"1","0"), p__ip, IF(p__premoderate,"1","0")
            FROM '.$this->old_db.'.'.$this->old_prefix.'Post p';
    $this->db->query($sql);
    
    $sql ='INSERT INTO '.DB_prefix.'text (
          id, type, data, tx_lastmod
        )
        SELECT
          p_id+'.intval($this->post_offset).', 16, p_text, GREATEST(p__time,p__edittime)
            FROM '.$this->old_db.'.'.$this->old_prefix.'Post p';
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
          u_id+'.intval($this->user_offset).', u__name, u__password, u_encrypted, u__name, u__title, 
          IF(u_gender=0,"F",IF(u_gender=1,"M","U")), CONCAT(u_byear,"-",u_bmonth,"-",u_bday), 
              u_location, u__canonical, u_signature,
            FLOOR(RAND()*0x80000000),u__email, IF(u__level=-1,"2",IF(u__active=1,"0","1")), u_realname     
        FROM '.$this->old_db.'.'.$this->old_prefix.'User u WHERE u_id>3';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'user_settings (
          id, topics_per_page, posts_per_page, msg_order, 
          timezone, signatures, avatars, smiles, 
          show_birthdate, email_pm, 
          email_broadcasts, hidden, goto
        )
        SELECT 
          u_id+'.intval($this->user_offset).', u_tperpage, u_mperpage, IF(u_sortposts=0,"Asc","Desc"),
          u_timeregion, IF(u_nosigns=1,"0","1"), IF(u_showavatars,"1","0"), IF(u_usesmiles,"1","0"), 
          IF(u_bmode=3,"1",IF(u_bmode=2,"3",IF(u_bmode=1,"3","0"))), IF(u_pmnotify,"1","0"), 
          IF(u_nomails=1,"0","1"), IF(u_hidden,"1","0"), u_goto+1         
        FROM '.$this->old_db.'.'.$this->old_prefix.'User u WHERE u_id>3';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'user_ext (
          id, group_id, reg_date, reg_ip, warnings, banned_till
        )
        SELECT u_id+'.intval($this->user_offset).', u__level, u__regdate, 0, -2*u__warnings, u__warntime
        FROM '.$this->old_db.'.'.$this->old_prefix.'User u WHERE u_id>3';
    $this->db->query($sql);
    $sql = 'UPDATE '.DB_prefix.'user_ext SET warnings=0 WHERE warnings<0';
    $this->db->query($sql);
    $sql = 'UPDATE '.DB_prefix.'user_ext SET group_id=1024 WHERE group_id>=1000';
    $this->db->query($sql);
    
    echo "Преобразуем предупреждения пользователей...\n";
    $sql ='INSERT INTO '.DB_prefix.'user_warning (
          id, uid, warntime, moderator, 
          value, warntill, descr
        )
        SELECT uw_id, uw_uid+'.intval($this->user_offset).', NOW(), uw_warner+'.intval($this->user_offset).',
          -uw_value*2, uw_validtill, uw_comment 
        FROM '.$this->old_db.'.'.$this->old_prefix.'UserWarning WHERE uw_value<0';
    $this->db->query($sql);
    echo "Создаем отметки о прочтении...\n";
    $sql ='INSERT INTO '.DB_prefix.'mark_all (
          uid, fid, mark_time
        )
        SELECT u_id+'.intval($this->user_offset).', 0, NOW()
        FROM '.$this->old_db.'.'.$this->old_prefix.'User WHERE u_id>3';
    $this->db->query($sql);
    
    echo "Преобразуем опросы...\n";
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
    $this->db->query($sql);
    
    echo "Преобразуем подписки и закладки...\n";
    if ($truncate) {
      $sql = 'TRUNCATE '.DB_prefix.'last_visit';
      $this->db->query($sql);
    }
    $sql ='INSERT INTO '.DB_prefix.'last_visit (
          oid, type, uid, visit1, visit2, 
          bookmark, subscribe, lastmail
        )
        SELECT DISTINCT
          t_id+'.intval($this->topic_offset).', "topic", COALESCE(b.uid,s.uid)+'.intval($this->user_offset).', NOW(), NOW(), 
            IF(b.tid IS NULL,"0","1"), IF(s.tid IS NULL,"0","1"), 0
        FROM '.$this->old_db.'.'.$this->old_prefix.'Topic t
            LEFT JOIN '.$this->old_db.'.'.$this->old_prefix.'Bookmark b ON (t.t_id=b.tid AND b.uid>'.AUTH_SYSTEM_USERS.')
            LEFT JOIN '.$this->old_db.'.'.$this->old_prefix.'Subscription s ON (t.t_id=s.tid AND s.uid>'.AUTH_SYSTEM_USERS.')
            WHERE (b.tid IS NOT NULL) OR (s.tid IS NOT NULL)';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'last_visit (
          oid, type, uid, visit1, visit2,
          bookmark, subscribe, lastmail
        )
        SELECT DISTINCT
          fid+'.intval($this->forum_offset).', "forum", uid+'.intval($this->user_offset).', NOW(), NOW(),
            "0","1", 0
        FROM '.$this->old_db.'.'.$this->old_prefix.'Subscription 
            WHERE uid>'.AUTH_SYSTEM_USERS.' AND tid=4294967294';
    $this->db->query($sql);

    echo "Преобразуем список друзей и список игнорируемых...\n";
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
    $this->db->query($sql);
    
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
        SELECT pm_id+'.intval($this->pm_thread_offset).', pm_subj 
        FROM '.$this->old_db.'.'.$this->old_prefix.'PersonalMessage
            WHERE pm__box=0';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'privmsg_post (
          id, pm_thread, subscribers, 
          uid, text, postdate, html, bcode, smiles 
        )
        SELECT pm_id+'.intval($this->pm_thread_offset).',pm_id+'.intval($this->pm_post_offset).',2,
          pm__correspondent+'.intval($this->user_offset).',pm_text,pm__senddate,IF(pm__html=1,"1","0"),IF(pm_bcode,"1","0"),IF(pm_smiles,"1","0") 
        FROM '.$this->old_db.'.'.$this->old_prefix.'PersonalMessage
            WHERE pm__box=0';
    $this->db->query($sql);
    $sql ='INSERT INTO '.DB_prefix.'privmsg_thread_user (
          pm_thread,uid,total,unread,last_post_date
        )
        SELECT pm_id+'.intval($this->pm_thread_offset).',pm__owner+'.intval($this->user_offset).',1,IF(pm__readdate=0,1,0), pm__senddate
        FROM '.$this->old_db.'.'.$this->old_prefix.'PersonalMessage
            WHERE pm__box=0';
    $this->db->query($sql);    
    $sql ='INSERT INTO '.DB_prefix.'privmsg_thread_user (
          pm_thread,uid,total,unread,last_post_date
        )
        SELECT pm_id+'.intval($this->pm_thread_offset).',pm__correspondent+'.intval($this->user_offset).',1,0, pm__senddate
        FROM '.$this->old_db.'.'.$this->old_prefix.'PersonalMessage
            WHERE pm__box=0 AND pm__correspondent!=pm__owner';    
    $this->db->query($sql);    
    $sql ='INSERT INTO '.DB_prefix.'privmsg_link (
          pm_id,uid
        )
        SELECT pm_id+'.intval($this->pm_post_offset).',pm__correspondent+'.intval($this->user_offset).'
        FROM '.$this->old_db.'.'.$this->old_prefix.'PersonalMessage
            WHERE pm__box=0';
    $this->db->query($sql);    
    $sql ='INSERT INTO '.DB_prefix.'privmsg_link (
          pm_id,uid
        )
        SELECT pm_id+'.intval($this->pm_post_offset).',pm__owner+'.intval($this->user_offset).'
        FROM '.$this->old_db.'.'.$this->old_prefix.'PersonalMessage
            WHERE pm__box=0 AND pm__correspondent!=pm__owner';
    $this->db->query($sql);
    
    echo "Преобразуем смайлики....\n";
    if ($truncate) {
      $sql = 'TRUNCATE '.DB_prefix.'smiles';
      $this->db->query($sql);
    }
    $sql ='INSERT INTO '.DB_prefix.'smile (
          code, file, mode
        )
        SELECT sm_code, sm_file, IF(sm_show,"dropdown","more") 
        FROM '.$this->old_db.'.'.$this->old_prefix.'Smile';
    $this->db->query($sql);
    
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
        SELECT SUBSTRING(MD5(CONCAT(file_key,"'.$this->get_opt('site_secret').'",file_name,file_id)) FROM 1 FOR 12),
          p_id+'.intval($this->post_offset).', 1, file_name, file_size, 
          IF(POSITION("image" IN file_type)>0,"image","attach"), SUBSTRING(file_name FROM POSITION("." IN file_name)+1 FOR 3)  
        FROM '.$this->old_db.'.'.$this->old_prefix.'Post, '.$this->old_db.'.'.$this->old_prefix.'File
            WHERE p_attach>0 AND p_attach=file_id';
    $this->db->query($sql);
    if ($this->file_function) {
      $sql='SELECT file_id, SUBSTRING(MD5(CONCAT(file_key,"'.$this->get_opt('site_secret').'",file_name,file_id)) FROM 1 FOR 12) AS newkey, p_id+'.intval($this->post_offset).' AS newid 
      FROM '.$this->old_db.'.'.$this->old_prefix.'Post, '.$this->old_db.'.'.$this->old_prefix.'File
      WHERE p_attach>0 AND p_attach=file_id';
      $files = $this->db->select_all($sql);
      for ($i=0, $count=count($files); $i<$count; $i++) {
        $func=$this->file_function;
        $func($this->old_dir.'/files/'.$files[$i]['file_id'].'.htm',BASEDIR.'www/f/up/1/'.intval($files[$i]['newid']).'-'.$files[$i]['newkey'].'.dat');
        //echo $this->old_dir.'/files/'.$files[$i]['file_id'].'.htm ==> '.BASEDIR.'www/f/up/1/'.$files[$i]['newkey'].'.dat'."\n";            
      }
      unset($files);
    }    
    
    echo "Преобразуем фотогалереи...\n";
    $sql ='SELECT SUBSTRING(MD5(CONCAT(ph_key,"'.$this->get_opt('site_secret').'",ph_id)) FROM 1 FOR 12) AS newkey,
          p_id+'.intval($this->post_offset).' AS post_id, t_id
        FROM '.$this->old_db.'.'.$this->old_prefix.'Photo ph, '.$this->old_db.'.'.$this->old_prefix.'Topic t, '.$this->old_db.'.'.$this->old_prefix.'Post p
            WHERE ph.ph_tid=t_id AND p.p_tid=t.t_id AND p.p_id=t.t__startpostid';
    $photos = $this->db->select_all($sql);   
    for ($i=0, $count=count($photos); $i<$count; $i++) {
      $filedata = array('fkey'=>$photos[$i]['newkey'],'oid'=>$photos[$i]['post_id'],
          'type'=>"1",'filename'=>'photo_'.$photos[$i]['t_id'].'.jpg','size'=>filesize($this->old_dir.'/photos/'.$photos[$i]['t_id'].'.jpg'),'format'=>'image','extension'=>"jpg");
      $this->db->insert(DB_prefix.'file', $filedata);      
      if ($this->file_function) {
        $func=$this->file_function;
        $func($this->old_dir.'/photos/'.$photos[$i]['t_id'].'.jpg',BASEDIR.'www/f/up/1/'.intval($photos[$i]['post_id']).'-'.$photos[$i]['newkey'].'.dat');
//        echo $this->old_dir.'/photos/'.$photos[$i]['t_id'].'.jpg ==> '.BASEDIR.'www/f/up/1/'.intval($photos[$i]['post_id']).'-'.$photos[$i]['newkey'].'.dat';        
      }
    }
    echo "Преобразуем статьи...\n";
    $sql = 'SELECT a.*, p.p_id+'.intval($this->post_offset).' AS new_id 
        FROM '.$this->old_db.'.'.$this->old_prefix.'Article a, '.$this->old_db.'.'.$this->old_prefix.'Topic t, '.$this->old_db.'.'.$this->old_prefix.'Post p
        WHERE a.a_tid=t_id AND p.p_tid=t.t_id AND p.p_id=t.t__startpostid';
    $articles = $this->db->select_all($sql);
    for ($i=0, $count=count($articles); $i<$count;$i++) {
      $buffer = "\n\n[i]Автор: [b]";
      if ($articles[$i]['a_authormail']) $buffer.='[email='.$articles[$i]['a_authormail'].']'.$articles[$i]['a_author'].'[/email]';
      else $buffer.=$articles[$i]['a_author'];
      if ($articles[$i]['a_origin']) {
        $buffer.="[/b]\nПервоисточник: [b]";
        if ($articles[$i]['a_originurl']) $buffer.='[url='.$articles[$i]['a_originurl'].']'.$articles[$i]['a_origin'].'[/url]';
        else $buffer.=$articles[$i]['a_origin'];
        $buffer.='[/b][/i]';
      }
      $sql = 'UPDATE '.DB_prefix.'text SET data=CONCAT(data,"'.$this->db->slashes($buffer).'") WHERE id='.intval($articles[$i]['new_id']).' AND type=16';
      $this->db->query($sql);  
    }  
    echo "Преобразуем аватары и фото пользователей...\n";
    $sql = 'SELECT u_id+'.intval($this->user_offset).' AS newid, u_avatartype, u__avatar, u__pavatar_id, u__photo_id
        FROM  '.$this->old_db.'.'.$this->old_prefix.'User
        WHERE (u_avatartype!=0 OR u__photo_id!=0) AND u_id>3';
    $users = $this->db->select_all($sql);
    $imglib = $this->load_lib('image',false);
    /* @var $imglib Library_image */
    for ($i=0, $count=count($users); $i<$count;$i++) {
      if ($users[$i]['u_avatartype']==1) $oldname=$this->old_dir.'/avatars/'.$users[$i]['u__avatar'];
      elseif ($users[$i]['u_avatartype']==2) $oldname=$users[$i]['u__avatar'];
      elseif ($users[$i]['u_avatartype']==3) $oldname=$this->old_dir.'/files/'.$users[$i]['u__pavatar_id'].'.htm';
      else $oldname = false;
      if ($oldname && file_exists($oldname)) {
        $imgdata = $imglib->load($oldname);
        if (!$imgdata) $newname=false;
        else {
          $ext = $imglib->get_extension($imgdata['type']);
          $newname = BASEDIR.'www/f/av/'.intval($users[$i]['newid']).'.'.$ext;
          $imglib->unload($imgdata);
        }
        if ($newname) {
          $sql = 'UPDATE '.DB_prefix.'user SET avatar="'.$this->db->slashes($ext).'" WHERE id='.intval($users[$i]['newid']);
          $this->db->query($sql);
          if ($this->file_function) {
            $func=$this->file_function;
            $func($oldname,$newname);
            echo $oldname.' ==> '.$newname."\n";
          }          
        }
      }
      $newname=false;
      if ($users[$i]['u__photo_id']) {
        $oldname=$this->old_dir.'/files/'.$users[$i]['u__photo_id'].'.htm';
        $imgdata = $imglib->load($oldname);
        if (!$imgdata) $newname=false;
        else {
          $ext = $imglib->get_extension($imgdata['type']);
          $newname = BASEDIR.'www/f/ph/'.intval($users[$i]['newid']).'.'.$ext;
          $imglib->unload($imgdata);
        }
        if ($newname) {
          $sql = 'UPDATE '.DB_prefix.'user SET photo="'.$this->db->slashes($ext).'" WHERE id='.intval($users[$i]['newid']);
          $this->db->query($sql);
          if ($this->file_function) {
            $func=$this->file_function;
            $func($oldname,$newname);
//            echo $oldname.' ==> '.$newname."\n";
          }
        }        
      }
    }

    echo "Выставляем права доступа на личные разделы...\n";
    $sql = 'SELECT u_id+'.intval($this->user_offset).' AS newid, 
        u__blog_fid+'.intval($this->forum_offset).' AS new_blog 
        FROM  '.$this->old_db.'.'.$this->old_prefix.'User            
        WHERE u_id>3 AND u__blog_fid>0';    
    $blogs = $this->db->select_all($sql);
    for ($i=0, $count=count($blogs);$i<$count;$i++) {
      $sql = 'UPDATE '.DB_prefix.'forum SET owner='.intval($blogs[$i]['newid']).', locked="1" WHERE id='.intval($blogs[$i]['new_blog']);
      $this->db->query($sql);
    }
    $sql = 'SELECT u_id+'.intval($this->user_offset).' AS newid,
        u__gallery_fid+'.intval($this->forum_offset).' AS new_photo
        FROM  '.$this->old_db.'.'.$this->old_prefix.'User
        WHERE u_id>3 AND u__blog_fid>0';
    $gals = $this->db->select_all($sql);
    for ($i=0, $count=count($gals);$i<$count;$i++) {
      $sql = 'UPDATE '.DB_prefix.'forum SET owner='.intval($gals[$i]['newid']).', locked="1" WHERE id='.intval($gals[$i]['new_photo']);
      $this->db->query($sql);
    }

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