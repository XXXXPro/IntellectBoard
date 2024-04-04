<?php

/** ================================
*  @package IntBPro
*  @author 4X_Pro <admin@openproj.ru>
*  @version 3.0
*  @copyright 2014-2015, 4X_Pro, INTBPRO.RU
*  @url http://intbpro.ru
*  Модуль вспомогательных действий для Центра Администрирования  Intellect Board 3 Pro
*  ================================ */

class misc extends Application_Admin {
  function action_badwords() {
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);
      $misclib->save_text($_POST['stopwords'],0,4);
      $misclib->save_text($_POST['badwords'],0,5);
    }
    $this->out->stopwords=$this->get_text(0, 4);
    $this->out->badwords=$this->get_text(0, 5);
  }

  function action_massmail() {
    if ($this->is_post()) {
      if (empty($_POST['mail']['text'])) $this->message('Текст сообщения не может быть пустым!',3);
      elseif (empty($_POST['mail']['groups'])) $this->message('Не указана ни одна группа получателей',3);
      else {
        $this->session();
        if (empty($_POST['mail']['step']) || $_POST['mail']['step']<=0) $_POST['mail']['step']=100;
        $_SESSION['do_massmail']=$_POST['mail'];
        $_SESSION['do_massmail']['sent']=0;
        $_SESSION['do_massmail']['errors']=0;
        $this->meta_redirect('?do=0','Начинаем отправку почты...');
        return 'main.tpl';
      }
      $this->out->mail=$_POST['mail'];
    }
    if (isset($_GET['do'])) {
      $this->session();
      $result = $this->do_massmail(intval($_GET['do']),$_SESSION['do_massmail']);
      $processed=intval($_GET['do'])+$_SESSION['do_massmail']['step'];
      if ($result) {
        $this->meta_redirect('?do='.$processed,$this->incline($processed,'Отправлено %d письмо...','Отправлено %d письма...','Отправлено %d писем...'));
        return 'main.tpl';
      }
      $this->out->mail=$_SESSION['do_massmail'];
      $sent_msg = $this->incline($_SESSION['do_massmail']['sent'],'Отправлено %d письмо.','Отправлено %d письма.','Отправлено %d писем.');
      if ($_SESSION['do_massmail']['errors'])  $sent_msg .= 'Ошибок вознкло: '.$_SESSION['do_massmail']['errors'];
      $this->message('Отправка писем завершена! '.$sent_msg, 1);
      unset($_SESSION['do_massmail']); // чистим данные рассылки, так как они больше не нужны
    }
    $sql = 'SELECT level, name FROM '.DB_prefix.'group ORDER BY level';
    $this->out->groups = $this->db->select_all($sql);
    if (!$this->is_post() && !isset($_GET['do'])) {
      $this->out->is_new=1;
      $this->out->mail['step']=100;
    }
  }

  /** Собственно сама отправка, вызываемая циклически с помощью редиректов или AJAX **/
  function do_massmail($offset,$data) {
    $sql = 'SELECT u.email, u.display_name, u.id FROM '.DB_prefix.'user u, '.
        DB_prefix.'user_settings us, '.DB_prefix.'user_ext ue '.
        'WHERE u.id=us.id AND u.id=ue.id AND u.status=\'0\' AND us.email_broadcasts=\'1\' AND u.id>'.intval(AUTH_SYSTEM_USERS).
        ' AND '.$this->db->array_to_sql($data['groups'],'ue.group_id');
    $emails = $this->db->select_all($sql,$offset,$data['step']);
    $common_subj = 'Рассылка форума "'.$this->get_opt('site_title').'"';
    $maildata['subj']=!empty($data['subj']) ? $data['subj'].' :: '.$common_subj : $common_subj;
    $maildata['html']=true;
    $maildata['template']='admin/misc/mail_mass.tpl';
    $maildata['list-id']='Forum Broadcast Mail <forum-broadcast.'.$_SERVER['HTTP_HOST'].'>';
    for ($i=0,$count=count($emails); $i<$count; $i++) {
      $maildata['to']=$emails[$i]['email'];
      $maildata['to_name']=$emails[$i]['display_name'];
      $maildata['data']['text']=str_replace('%username%',$emails[$i]['display_name'],$data['text']);
      $maildata['unsubscribe']=$this->http($this->url('user/unsubscribe_mass.htm?authkey='.$this->gen_auth_key($emails[$i]['id'],'unsubscribe_mass',$this->url('user/'))));
      $maildata['data']['key1']=$this->gen_auth_key($emails[$i]['id'],'unsubscribe_mass',$this->url('user/'));
      $maildata['data']['key2']=$this->gen_auth_key($emails[$i]['id'],'unsubscribe_all',$this->url('user/'));;
      $this->mail($maildata);
      $_SESSION['do_massmail']['sent']++;
    }
    return ($count==$data['step']); // возвращаем true в том случае, если обработку требуется продолжить (то есть обработано было столько писем, сколько должно было быть обработано за 1 шаг -- это служит индикатором того, что есть еще необработанные письма
  }

  function action_resync() {
    if ($this->is_post()) {
      if (empty($_POST['objects'])) $this->message('Не выбран ни один объект для пересинхронизации',2);
      else {
        $this->session();
        if (empty($_POST['step']) || $_POST['step']<=0) $_POST['step']=1000;
        $_SESSION['do_resync']['objects']=$_POST['objects'];
        $_SESSION['do_resync']['topics']=0;
        $_SESSION['do_resync']['users']=0;
        $_SESSION['do_resync']['step']=$_POST['step'];
        if (!empty($_POST['objects']['topics'])) $this->meta_redirect('?do_topics=0','Начинаем пересинхронизацию тем...');
        else $this->meta_redirect('?do_users=0','Начинаем пересинхронизацию пользователей...');
        return 'main.tpl';
      }
    }
    if (isset($_GET['do_topics'])) {
      $this->session();
      $result = $this->do_resync_topics(intval($_GET['do_topics']),$_SESSION['do_resync']['step']);
      $processed=intval($_GET['do_topics'])+$_SESSION['do_resync']['step'];
      if ($result) {
        $this->meta_redirect('?do_topics='.$processed,$this->incline($processed,'Обработано %d тема...','Обработано %d темы...','Обработано %d тем...'));
        return 'main.tpl';
      }
      elseif (isset($_SESSION['do_resync']['objects']['users'])) {
        $this->meta_redirect('?do_users=0', 'Начинаем пересинхронизацию пользователей...');
        return 'main.tpl';
      }
      else {
        $this->message('Пересинхронизация разделов и тем завершена! Обработано тем: '.$_SESSION['do_resync']['topics']);
        unset($_SESSION['do_resync']);
      }
    }
    if (isset($_GET['do_users'])) {
      $this->session();
      $result = $this->do_resync_users(intval($_GET['do_users']),$_SESSION['do_resync']['step']);
      $processed=intval($_GET['do_users'])+$_SESSION['do_resync']['step'];
      if ($result) {
        $this->meta_redirect('?do_users='.$processed,$this->incline($processed,'Обработан %d пользователь...','Обработано %d пользователя...','Обработано %d пользователей...'));
        return 'main.tpl';
      }
      if (!empty($_SESSION['do_resync']['objects']['topics'])) $this->message('Пересинхронизация разделов и тем завершена! Обработано тем: '.$_SESSION['do_resync']['topics']);
      $this->message('Пересинхронизация пользователей завершена! Обработано пользователей: '.$_SESSION['do_resync']['users']);
      unset($_SESSION['do_resync']);
    }
  }

  function do_resync_topics($offset,$limit) {
    $modlib = $this->load_lib('moderate',true);
    $tlib = $this->load_lib('topic',true);
    $topics=$tlib->list_topics(array('start'=>$offset,'perpage'=>$limit)); // TODO: при таком запросе удаленные темы и темы на премодерации останутся необработанными, подумать, правильное ли это решение или нужно добавить all=true
    for ($i=0, $count=count($topics);$i<$count;$i++) $modlib->topic_resync($topics[$i]['id']);
    $_SESSION['do_resync']['topics']+=$count;
    if ($count<$limit) { // если это последний проход do-функции, пересинхронизируем разделы
      $sql = 'SELECT id FROM '.DB_prefix.'forum';
      $forums = $this->db->select_all_numbers($sql);
      for ($i=0, $count2=count($forums);$i<$count2;$i++) $modlib->forum_resync($forums[$i]);
    }
    return ($count==$limit);
  }

  function do_resync_users($offset,$limit) {
    $userlib = $this->load_lib('userlib',true);
    $users=$userlib->list_users(array('start'=>$offset,'perpage'=>$limit)); // TODO: при таком запросе удаленные темы и темы на премодерации останутся необработанными, подумать, правильное ли это решение или нужно добавить all=true
    for ($i=0, $count=count($users);$i<$count;$i++) $userlib->user_resync($users[$i]['id']);
    $_SESSION['do_resync']['users']+=$count;
    return ($count==$limit);
  }

  function action_cache_reset() {
    $outlib = $this->load_lib($this->template_lib,true); // отсутствие парсера должно вызывать фатальный шаблон, поэтому ставим true
    if ($outlib instanceof iParser) {
      $outlib->clear_cache();
      $this->message('Кеш шаблонизатора очищен!',1);
    }
    if ($this->server_cache instanceof iCache) {
      $this->server_cache->clear_all();
      $this->message('Серверный кеш очищен!',1);
    }
    $this->reset_session_cache();
    $this->message('Кеш сессионных данных сброшен.',1);
    if (function_exists('opcache_reset')) { // сброс кеша самого PHP 
      opcache_reset();
      $this->message('PHP opcache сброшен.', 1);
    }
    return 'main.tpl';
  }

  function action_smiles() {
    $sql = 'SELECT * FROM '.DB_prefix.'smile ORDER BY sortfield'; // извлекаем данные напрямую, а не через load_smiles, так как нам нужно поле sortfield в явном виде, а также для обхода кеша
    $this->out->smiles = $this->db->select_hash($sql,'code');
    if ($this->is_post()) {
      if (!empty($_POST['delete'])) foreach ($_POST['delete'] as $code=>$del) {
        $sql = 'DELETE FROM '.DB_prefix.'smile WHERE code=\''.$this->db->slashes($code).'\'';
        $this->db->query($sql);
        unlink(BASEDIR.'www/sm/'.$this->out->smiles[$code]['file']);
        unset($this->out->smiles[$code]);
      }
      if (!empty($_POST['smiles'])) foreach ($_POST['smiles'] as $code=>$data) {
        if ($code===$data['code'] || !isset($this->out->smiles[$data['code']])) {
        $this->db->update(DB_prefix.'smile',$data,'code=\''.$this->db->slashes($code).'\'');
          if ($code!==$data['code']) { // если мы поменяли обозначение смайлика
            unset($this->out->smiles[$code]);
            $this->out->smiles[$data[$code]['code']]=$data;
          }
        }
        else $this->message('Смайлик '.$code.' не может быть изменен, так как другой смайлик с таким кодом уже есть в системе!',2);
      }
      if (!empty($_FILES['newsmiles'])) {
        $imglib = $this->load_lib('image',true);
        $uploaded=0;
        for ($i=0,$count=count($_FILES['newsmiles']['tmp_name']); $i<$count; $i++) {
          if (is_uploaded_file($_FILES['newsmiles']['tmp_name'][$i])) {
            $imgdata=$imglib->load($_FILES['newsmiles']['tmp_name'][$i]);
            if (!empty($imgdata)) {
              $file = preg_replace('|[^\w\.\-]+|','_',$_FILES['newsmiles']['name'][$i]); // заменяем все подозрительные символы в имени файла на прочерки
              $code = ':'.substr($_FILES['newsmiles']['name'][$i],0,strrpos($_FILES['newsmiles']['name'][$i],'.')).':';
              if (!isset($this->out->smiles[$code])) {
                move_uploaded_file($_FILES['newsmiles']['tmp_name'][$i], BASEDIR.'www/sm/'.$file);
                $data=array('code'=>$code,'file'=>$file,'descr'=>'','sortfield'=>1000+$uploaded);
                if ($this->db->insert(DB_prefix.'smile',$data)) $uploaded++;
              }
              else $this->message('Смайлик '.$code.' не может быть загружен, так как другой смайлик с таким кодом уже есть в системе! Измените код уже существующего смайлика или переименуйте файл с новым!',2);
            }
            else {
              $this->message('Один из загруженных файлов не является изображением!',2);
              unlink($_FILES['tmp_name']['newsmiles'][$i]);
            }
          }
        }
        if ($uploaded) $this->message($this->incline($uploaded,'%d новый смайлик загружен','%d новых смайлика загружено','%d новых смайликов загружено'),1);
      }
      $this->message('Изменения сохранены!',1);
      $this->clear_cached('Smiles');
      $this->redirect($this->http($_SERVER['REQUEST_URI']));
    }
  }

  function action_counters() {
    $files = array('counter_h','counter_t','counter_f');
    if (!$this->is_admin(true)) { // менять наиболее критичные настройки могут только Основатели форума
      $this->message('Только Основатели форума могут редактировать счетчики!',2);
      return 'main.tpl';
    }
    if (!is_writable(BASEDIR.'template/def/')) $this->message('Каталог template/def недоступен для записи, при создании/сохранении файлов счетчиков могут быть проблемы!',2);
    if ($this->is_post()) {
      foreach ($files as $filename) {
        if (!empty($_POST[$filename])) {
          file_put_contents(BASEDIR.'template/def/'.$filename.'.tpl', $_POST[$filename]);
        }
        elseif (file_exists(BASEDIR.'template/def/'.$filename.'.tpl')) unlink(BASEDIR.'template/def/'.$filename.'.tpl');
      }
      $this->message('Изменения сохранены!',1);
      $this->redirect($this->http($_SERVER['REQUEST_URI']));
    }
    else {
      foreach ($files as $filename) if (file_exists(BASEDIR.'template/def/'.$filename.'.tpl')) $this->out->$filename=file_get_contents(BASEDIR.'template/def/'.$filename.'.tpl');
    }
  }

  function action_stats() {
    $period=(isset($_GET['period'])) ? intvaL($_GET['period']) : 7; // по умолчанию период рассмотрения для статистики равен 7 дням
    $time1 = $this->time - 2*$period*24*60*60;
    $time2 = $this->time - $period*24*60*60;

    // TODO: возможно, имеет смысл извлекать данные не прямыми запросами, а с помощью соответствующих библиотек
    // но это только даст лишнюю нагрузку на сервер без какой-либо пользы

    // подсчет количества регистраций пользователей
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'user_ext WHERE id>'.intval(AUTH_SYSTEM_USERS).' AND reg_date>='.intval($time1).' AND reg_date<'.intval($time2);
    $data['users'][1]=$this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'user_ext WHERE id>'.intval(AUTH_SYSTEM_USERS).' AND reg_date>='.intval($time2);
    $data['users'][2]=$this->db->select_int($sql);

    // количество сообщений
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'post WHERE status=\'0\' AND postdate>='.intval($time1).' AND postdate<'.intval($time2);
    $data['posts'][1]=$this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'post WHERE status=\'0\' AND postdate>='.intval($time2);
    $data['posts'][2]=$this->db->select_int($sql);

    // количество новых тем
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'topic t, '.DB_prefix.'post p WHERE t.status=\'0\' AND t.first_post_id=p.id AND p.postdate>='.intval($time1).' AND p.postdate<'.intval($time2);
    $data['topics'][1]=$this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'topic t, '.DB_prefix.'post p WHERE t.status=\'0\' AND t.first_post_id=p.id AND p.postdate>='.intval($time2);
    $data['topics'][2]=$this->db->select_int($sql);

    // количество активных тем
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'topic t WHERE t.status=\'0\' AND t.last_post_time>='.intval($time1).' AND t.last_post_time<'.intval($time2);
    $data['active_topics'][1]=$this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'topic t WHERE t.status=\'0\' AND t.last_post_time>='.intval($time2);
    $data['active_topics'][2]=$this->db->select_int($sql);

    // количество посещений тем зарегистрированными пользователями
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'last_visit WHERE type=\'topic\' AND visit1>='.intval($time1).' AND visit1<'.intval($time2);
    $data['visits'][1]=$this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'last_visit WHERE type=\'topic\' AND visit1>='.intval($time2);
    $data['visits'][2]=$this->db->select_int($sql);

    // количество ЛС
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'privmsg_post WHERE postdate>='.intval($time1).' AND postdate<'.intval($time2);
    $data['pm'][1]=$this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'privmsg_post WHERE postdate>='.intval($time2);
    $data['pm'][2]=$this->db->select_int($sql);

    // число сообщений на пользователя и в среднем в теме
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'user WHERE status=\'0\' AND id>'.intval(AUTH_SYSTEM_USERS);
    $usercount = $this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'post WHERE status=\'0\'';
    $postcount = $this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'topic WHERE status=\'0\'';
    $topiccount = $this->db->select_int($sql);
    $data['per_user'][2]=($usercount>0) ? sprintf('%.3f',$postcount/$usercount) : 'не определено';
    $data['per_user'][1]=($usercount-$data['users'][2]>0) ? sprintf('%.3f',($postcount-$data['posts'][2])/($usercount-$data['users'][2])) : 'не определено';
    $data['per_topic'][2]=($topiccount>0) ? sprintf('%.3f',$postcount/$topiccount) : 'не определено';
    $data['per_topic'][1]=($topiccount-$data['topics'][2]>0) ? sprintf('%.3f',($postcount-$data['posts'][2])/($topiccount-$data['topics'][2])) : 'не определено';


    foreach ($data as $key=>$value) {
      if ($value[1]>0) $data[$key][3]=sprintf("%d%%",100*($value[2]-$value[1])/$value[1]);
      elseif ($value[2]!=0) $data[$key][3]='+++';
      else $data[$key][3]='';
    }

    $this->out->stats = $data;
    $this->out->time1 = $time1;
    $this->out->time2 = $time2;
    $this->out->now = $this->time;

    $this->out->period = $period;

    $tlib = $this->load_lib('topic',false);
    if ($tlib) {
      $sql = 'SELECT t.id, COUNT(p.id) AS post_count FROM '.DB_prefix.'topic t, '.DB_prefix.'post p '.
        'WHERE p.postdate>='.intval($time2).' AND p.tid=t.id AND p.status=\'0\' AND t.status=\'0\' '.
        'GROUP BY t.id';
      $tids = $this->db->select_simple_hash($sql,25);
      $this->out->active_topics = $tlib->list_topics(array('id'=>array_keys($tids),'forums'=>true));
      for ($i=0, $count=count($this->out->active_topics); $i<$count; $i++) $this->out->active_topics[$i]['active_posts']=$tids[$this->out->active_topics[$i]['id']];
      usort($this->out->active_topics, function($a,$b) { return $b["active_posts"]-$a["active_posts"]; });

      $sql = 'SELECT t.id, COUNT(lv.oid) visit_count FROM '.DB_prefix.'topic t, '.DB_prefix.'last_visit lv '.
        'WHERE t.status=\'0\' AND t.id=lv.oid AND lv.type=\'topic\' AND lv.visit1>='.intval($time2).' '.
        'GROUP BY t.id HAVING COUNT(lv.oid) > 0';
      $tids = $this->db->select_simple_hash($sql,25);
      $this->out->visited_topics = $tlib->list_topics(array('id'=>array_keys($tids),'forums'=>true));
      for ($i=0, $count=count($this->out->visited_topics); $i<$count; $i++) $this->out->visited_topics[$i]['visits']=$tids[$this->out->visited_topics[$i]['id']];
      usort($this->out->visited_topics, function($a,$b) { return $b["visits"]-$a["visits"]; });
    }
  }

  function action_stats_graph() {
    $timelimit = !isset($_GET['all_time']) ? $this->time-90*24*60*69 : false;
    $this->out->timelimit = $timelimit;

    // считаем регистрации по дням
    if ($timelimit) $sqline = 'reg_date>'.intval($timelimit);
    else $sqline = '1=1';
    $sql = 'SELECT COUNT(*) AS ucount, DATE(FROM_UNIXTIME(reg_date)) AS uday FROM '.DB_prefix.'user u, '.DB_prefix.'user_ext ue WHERE status=\'0\' AND u.id=ue.id AND u.id>'.intval(AUTH_SYSTEM_USERS).' AND '.$sqline.
    ' GROUP BY DATE(FROM_UNIXTIME(reg_date))';
    $this->out->udata = $this->db->select_all($sql);
    $this->out->urecord = 0;
    for ($i=0, $count=count($this->out->udata);$i<$count;$i++) {
      $this->out->udata[$i]['uday']=strtotime($this->out->udata[$i]['uday']);
      if ($this->out->urecord['ucount']<$this->out->udata[$i]['ucount']) $this->out->urecord=$this->out->udata[$i];
    }

    // и сообщения по дням
    if ($timelimit) $sqline = 'postdate>'.intval($timelimit);
    else $sqline = '1=1';
    $sql = 'SELECT COUNT(*) AS pcount, DATE(FROM_UNIXTIME(postdate)) AS pday FROM '.DB_prefix.'post WHERE status=\'0\' AND '.$sqline.
    ' GROUP BY DATE(FROM_UNIXTIME(postdate))';
    $this->out->pdata = $this->db->select_all($sql);
    $this->out->precord = 0;
    for ($i=0, $count=count($this->out->pdata);$i<$count;$i++) {
      $this->out->pdata[$i]['pday']=strtotime($this->out->pdata[$i]['pday']);      
      if ($this->out->precord['pcount']<$this->out->pdata[$i]['pcount']) $this->out->precord=$this->out->pdata[$i];
    }

    // темы по дням
    if ($timelimit) $sqline = 'postdate>'.intval($timelimit);
    else $sqline = '1=1';
    $sql = 'SELECT COUNT(*) AS tcount, DATE(FROM_UNIXTIME(postdate)) AS tday FROM '.DB_prefix.'topic t, '.DB_prefix.'post p WHERE p.status=\'0\' AND t.status=\'0\' AND t.first_post_id=p.id AND '.$sqline.
    ' GROUP BY DATE(FROM_UNIXTIME(postdate))';
    $this->out->tdata = $this->db->select_all($sql);
    $this->out->trecord = 0;
    for ($i=0, $count=count($this->out->tdata);$i<$count;$i++) {
      $this->out->tdata[$i]['tday']=strtotime($this->out->tdata[$i]['tday']);      
      if ($this->out->trecord['tcount']<$this->out->tdata[$i]['tcount']) $this->out->trecord=$this->out->tdata[$i];
    }

    // ЛС по дням
    if ($timelimit) $sqline = 'postdate>'.intval($timelimit);
    else $sqline = '1=1';
    $sql = 'SELECT COUNT(*) AS pmcount, DATE(FROM_UNIXTIME(postdate)) AS pmday FROM '.DB_prefix.'privmsg_post WHERE 1=1 AND '.$sqline.
    ' GROUP BY DATE(FROM_UNIXTIME(postdate))';
    $this->out->pmdata = $this->db->select_all($sql);
    if (count($this->out->pmdata)>0) {
      $this->out->pmrecord = 0;
      for ($i=0, $count=count($this->out->pmdata);$i<$count;$i++) {
        $this->out->pmdata[$i]['pmday']=strtotime($this->out->pmdata[$i]['pmday']);
        if ($this->out->pmrecord['pmcount']<$this->out->pmdata[$i]['pmcount']) $this->out->pmrecord=$this->out->pmdata[$i];
      }
    }
  }

  function action_trashbox() {
    if (!$this->is_admin(true)) { // менять наиболее критичные настройки могут только Основатели форума
      $this->message('Только Основатели форума могут очищать Корзину!',2);
      return 'main.tpl';
    }
    if ($this->is_post() && !empty($_POST['confirm'])) {
      $dellib = $this->load_lib('delete',true);
      /* @var $dellib Library_delete */
      $timelimit = $this->time - $_POST['days']*24*60*60;
      if (!empty($_POST['posts'])) $dellib->delete_older_posts($timelimit);
      if (!empty($_POST['topics'])) $dellib->delete_older_topics($timelimit);
      $this->message('Очистка помеченных к удалению тем и сообщений произведена',1);
      $this->redirect($this->http($_SERVER['REQUEST_URI']));
    }
  }

  function action_user_logs() {
    $opts = $this->get_user_logs_params();
    $logdata=array();

    // $this->get_pages не используем, поскольку общее количество страниц узнаем только в конце
    $count=0; // счетчик найденных результатов
    $page=(isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1; // номер страницы лога
    $start = ($page-1)*$opts['perpage']; // смещение нужной страницы в логе

    if (!empty($opts['names'])) {
      $names = explode(',',$opts['names']);
      for ($i=0, $count=count($names);$i<$count;$i++) $names[$i]=trim($names[$i]);
    }
    for ($curtime=$opts['start_time'];$curtime<$opts['end_time'];$curtime+=24*60*60) {
      $date = date('Y-m-d',$curtime);
      $filename = BASEDIR.'logs/visits/'.$date.'.csv';
      if (file_exists($filename)) {
        $fh=fopen($filename,'r');
        if ($fh) while ($logitem=fgetcsv($fh)) {
          // формат лога: Время, Url, Action, Имя пользователя, IP, User_agent, Cookie ID, Referer, Описание действия
          // Номера полей:  0     1      2            3          4      5          6          7            8
          $logitem[9]=$date; // добавляем дату
          $matched = true; // проверка, что считанная строка удовлетворяет нашим критериям (пока пусто)
          if (!empty($names)) {
            for ($i=0, $count=count($names);$i<$count;$i++) $matched = $matched && $logitem[3]==$names[$i];
          }
          if (!empty($opts['cookie'])) $matched = $matched && ($opts['cookie']==$logitem[6]);
          if (!empty($opts['ip'])) $matched = $matched && strpos($logitem[4],$opts['ip'])!==false;
          if (!empty($opts['action'])) $matched = $matched && ($opts['action']==$logitem[2]);
          if (!empty($opts['url'])) $matched = $matched && strpos($logitem[1],$opts['url'])!==false;
          if (!empty($opts['agent'])) $matched = $matched && strpos($logitem[5],$opts['agent'])!==false;
          if (!empty($opts['referer'])) $matched = $matched && strpos($logitem[7],$opts['referer'])!==false;
          if ($matched) {
            $count++;
            if ($count>=$start && $count<$start+$opts['perpage']) $logdata[] = $logitem; // если запись лога находится на той странице, которую выводим, добавляем ее в массив для вывода
          }
        }
        fclose($fh);
      }
    }
    $this->out->log_items = $logdata;
    $this->out->total = $count;
    $this->out->page = $page;
    $this->out->pages = ceil($count/$opts['perpage']);
    $this->out->opts = $opts;
  }

  // сохраняет настройки фильтра для логов в сессию
  function action_params_user_log() {
    // вызов $this->session() не нужен, так как для работы с АЦ сессия есть всегда
    if (!isset($_REQUEST['clear'])) $_SESSION['user_log_params']=$_REQUEST['opts'];
    else unset($_SESSION['user_log_params']);
    $this->redirect($this->http(str_replace('params_user_log.htm','user_logs.htm',$_SERVER['REQUEST_URI'])));
  }

  function get_user_logs_params() {
    if (empty($_SESSION['user_log_params'])) {
      $result['perpage']=20; // число запией лога на страницу
      $result['start_date']=date('d.m.Y',$this->time);
      $result['end_date']=$result['start_date'];
    }
    else {
      $result=$_SESSION['user_log_params'];
    }

    $result['start_time']=strtotime($result['start_date']);
    $result['end_time']=strtotime($result['end_date'])+24*60*60-1;
    if ($result['perpage']<=0) $result['perpage']=20;
    return $result;
  }
}
