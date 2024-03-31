<?php
/** ================================
*  @package IntBPro
*  @author 4X_Pro <admin@openproj.ru>
*  @version 3.0
*  @copyright 2007,2009-2011, 2013-2014 4X_Pro, INTBPRO.RU
*  @url http://intbpro.ru
*  Модуль модерации тем и сообщений
*  ================================ */

require_once(BASEDIR.'modules/stdforum.php');

class moderate extends stdforum {
  function process() {
    if (!$this->is_moderator()) $this->output_403('Вы не являетесь модератором данного раздела!');
    else {
      $this->out->authkey = $this->gen_auth_key();
      if ($this->is_post() || $this->action=='delete_post' || $this->action=='delete_topic' || $this->action=='rollback') {
        if (empty($_REQUEST['authkey']) || $_REQUEST['authkey']!=$this->out->authkey) $this->output_403('Неправильный ключ аутентификации!');
      }
      return parent::process();
    }
  }

  function action_mod_forum() {
    if (isset($_REQUEST['page'])) $_REQUEST['page']=str_replace('.htm','',$_REQUEST['page']); // "костыль" для корректной работы разбиения на страницы
    if ($this->is_post()) $this->mod_forum();
    $fid = $this->forum['id'];
    $tlib = $this->load_lib('topic',true);

    list($cond,$need_count,$perpage,$tperpage)=$this->view_forum_build_cond($fid); // формируем массив $cond с параметрами для выборки темы
    $cond['subscr']=false;
    $cond['new_time']=false;
    $cond['timelimit']=false;
    $cond['poll']=false;
    
    $this->out->pages=$this->view_forum_pagedata($perpage, $cond, $need_count);
    $cond['start']=$this->out->pages[0]['start'];
    $cond['perpage']=$perpage;

    $sticky =array();
    if (!$need_count) { // sticky-темы выдаем только в том случае, если нет каких-то сложных условий фильтрации
      $cond['sticky']=true;
      $this->out->sticky=$tlib->list_topics($cond);
      $cond['sticky']=false;
    }



    $this->out->topics=array_merge($this->out->sticky,$tlib->list_topics($cond));

    $this->out->forumlist = $this->get_forum_list('topic',1);
    unset($this->out->forumlist[$fid]); // удаляем из списка текущий форум, чтобы не переносить темы в самого себя
  }

  /** Выполнение действий по модерации раздела (открытие/закрытие, приклеивание, перенос тем)
  * Вынесено в отдельную процедуру исключительно ради удобства редактирования
  **/
  function mod_forum() {
    $tlib = $this->load_lib('topic',true);
    $fid = $this->forum['id'];
    $modlib = $this->load_lib('moderate',true);

    $name = 'sticky';
    $data = array();
    if (is_array($_POST['old_'.$name])) foreach ($_POST['old_'.$name] as $key=>$value) {
      if ($value==1 && empty($_POST[$name][$key])) $data[$key]=0;
      elseif ($value==0 && !empty($_POST[$name][$key])) $data[$key]=1;
    }
    if (!empty($data)) {
      $modlib->stick_topics($data,$fid);
    }

    $name = 'sticky_post';
    $data = array();
    if (is_array($_POST['old_'.$name])) foreach ($_POST['old_'.$name] as $key=>$value) {
      if ($value==1 && empty($_POST[$name][$key])) $data[$key]=0;
      elseif ($value==0 && !empty($_POST[$name][$key])) $data[$key]=1;
    }
    if (!empty($data)) {
      $modlib->stick_posts($data,$fid);
    }

    $name = 'locked';
    $data = array();
    if (is_array($_POST['old_'.$name])) foreach ($_POST['old_'.$name] as $key=>$value) {
      if ($value==1 && empty($_POST[$name][$key])) $data[$key]=0;
      elseif ($value==0 && !empty($_POST[$name][$key])) $data[$key]=1;
    }
    if (!empty($data)) {
      $modlib->lock_topics($data,$fid);
    }
    
    $name = 'favorites';
    $data = array();
    if (is_array($_POST['old_'.$name])) foreach ($_POST['old_'.$name] as $key=>$value) {
      if ($value==1 && empty($_POST[$name][$key])) $data[$key]=0;
      elseif ($value==0 && !empty($_POST[$name][$key])) $data[$key]=1;
    }
    if (!empty($data)) {
      $modlib->fav_topics($data,$fid);
    }
    

    if (!empty($_POST['actions'])) {
      $delete = array();
      $move = array();

      foreach ($_POST['actions'] as $tid=>$value) {
        if ($value==='m') $move[]=$tid;
        elseif ($value==='d') $delete[$tid]='2'; // статус 2 означает "помечена как удаленная"
      }

      if (!empty($move) && !empty($_POST['moveforum'])) {
        $newfid = intval($_POST['moveforum']);
        if (!$this->check_access('topic',$newfid)) $this->output_403('У вас нет прав на перенос тем в этот раздел!');
        $modlib->move_topics($move,$fid,$newfid);
      }
      if (!empty($delete)) {
        $modlib->status_topics($delete,$fid);
      }
    }
    $this->redirect($this->referer());
  }

  /** Расщепление темы или склеивание двух выбранных тем.
   * Особенность работы: сообщения для переноса предварительно выбираются через action_mark_messages
   * по ссылкам, показываемыем при обычном просмотре темы модератором.
   * Номера выбранных сообщений хранятся в сесии в ключе moderate_<номер_темы>
   **/
  function action_move_posts() {
    $tlib = $this->load_lib('topic',true);
    if (empty($this->topic)) $this->output_403('Не указан идентификатор  темы!');
    $fid = $this->forum['id'];
    $modlib = $this->load_lib('moderate',true);
    $tid = $this->topic['id'];

    $this->session();
    $pids = (!empty($_SESSION['moderate_'.$tid])) ? $_SESSION['moderate_'.$tid] : array();
    $this->out->pids = $pids;
    if ($this->is_post()) {
      if ($_POST['items']==='all') { // если указано, что нужно провести действие над всеми сообщениями темы, то обнуляем массив $pids и записываем в него идентификаторы всех сообщений
        $posts=$tlib->get_posts(array('tid'=>$tid));
        $pids = array();
        for ($i=0, $count=count($posts); $i<$count;$i++) $pids[]=$posts[$i]['id'];
      }
      $subaction=$_POST['subaction'];
      if ($subaction=='split') {
        if ($_POST['items']==='all') {
          $this->message('Перенос всех сообщений старой темы в новую не имеет смысла!',2);
          return;
        }
        $perms = $tlib->get_permissions();
        $new_t_data = $_REQUEST['topic'];
        if ($this->forum['selfmod']>0) $new_t_data['owner']=$this->topic['owner']; // если кураторство включено, отделяемая тема получает того же куратора, что и текущая
        $errors = $this->topic_pre_check($new_t_data, $perms);
        if (empty($errors)) {
          /** @var Library_tsave $tsave */
          $tsave = $this->load_lib('tsave',true);
          if ($tsave->save_topic($new_t_data,true)) { // если тему удалось создать
            $modlib->move_posts($pids,$this->topic['id'],$new_t_data['id']);
            if (empty($new_t_data['hurl'])) $new_t_data['hurl']=$new_t_data['id'];
            unset($_SESSION['moderate_'.$tid]);
            $this->redirect($this->http($this->url($this->forum['hurl'].'/'.$new_t_data['hurl'].'/'))); // после выполнения действия делаем редирект в новую тему
          }
          else $this->message('При создании темы возникла ошибка!',3);
        }
        else $this->message($errors);
      }
      elseif ($subaction=='join') {
        $new_tid = intval($_POST['new_tid']);
        $new_t_data = $tlib->get_topic($new_tid);
        if (empty($new_t_data)) $this->message('Указанной темы не существует!');
        else if (!$this->check_access('post',$new_t_data['fid'])) $this->message('У вас нет прав на отправку сообщений в указанную тему!',3);
        else {
          $modlib->move_posts($pids,$this->topic['id'],$new_tid);
          if ($_POST['items']==='all') { // если перенесли всю тему, то удаляем старую (в ней осталось только сообщение о переносе), причем не заносим это в лог
            $modlib->status_topics(array($tid=>'2'),$fid,array('nolog'=>true));
          }
          unset($_SESSION['moderate_'.$tid]);
          $this->redirect($this->http($this->url($new_t_data['full_hurl']))); // после выполнения действия делаем редирект в новую тему
        }
      }
      elseif ($subaction=='delete') {
        if ($_POST['items']==='all') { // если удаляем всю тему
          $modlib->status_topics(array($tid=>'2'),$fid);
        }
        else { // если удаляем выбранные сообщения
          $pdata = array();
          for ($i=0, $count=count($pids);$i<$count;$i++) $pdata[$pids[$i]]=2;
          $modlib->status_posts($pdata,$tid);
        }
        unset($_SESSION['moderate_'.$tid]);
        $this->redirect($this->http($this->url($this->forum['hurl'].'/update_extdata.htm'))); // редиректим на обновление метаданных форума
      }
    }
  }

  /** Добавление идентификатора выбранного сообщения в сесиию (ключ moderate_<номер_темы>) для дальнейших действий с ним **/
  function action_mark_post() {
    if (empty($_REQUEST['id']) || empty($this->topic)) $this->output_403('Не указан идентификатор сообщения или идентификатор темы!');
    $pid = $_REQUEST['id'];
    $tid = intval($this->topic['id']);
    $this->session();
    $_SESSION['starttime']=$this->time; // чтобы страница обновлялась сразу
    if (empty($_SESSION['moderate_'.$tid])) $_SESSION['moderate_'.$tid]=array();
    if (empty($_REQUEST['unmark'])) { // если добавляем сообщение, а не убираем
      if (array_search($pid, $_SESSION['moderate_'.$tid])===false) {
        if (!is_array($pid)) array_push($_SESSION['moderate_'.$tid],intval($pid));
        else $_SESSION['moderate_'.$tid]=array_merge($_SESSION['moderate_'.$tid],$pid);
        $result['result']='marked';
      }
      else $result['result']='unmarked';
    }
    else {
      $key=array_search($pid, $_SESSION['moderate_'.$tid]);
      if ($key!==false) {
        unset($_SESSION['moderate_'.$tid][$key]);
        $result['result']='unmarked';
      }
      else $result['result']='nochange';
    }
    if ($this->get_request_type()!=1) { // если запрос делается не через AJAX, делаем редирект на первое из сообщений
      if (!is_array($pid)) $this->post_redirect($pid);
      else $this->post_redirect($pid[0]);
    }
    else $this->output_json($result);
  }

  /** Премодерация сообщений **/
  function action_premod() {
    $fid = $this->forum['id'];
    $tlib = $this->load_lib('topic',true);

    $cond['fid']=$fid; // выбираем все сообщения раздела, стоящие на премодерации, с получением информации о темах, в которых они расположены, и приложенных файлах
    if (!empty($this->out->topic)) $cond['tid']=$this->topic; // если указана конкретная тема, выводим сообщения только из нее.
    $cond['topics']=true;
    $cond['files']=true;
    $cond['premod']=true;
    $cond['user']=true;
    $cond['order']='t.last_post_time DESC, postdate DESC';

    $data = $tlib->get_posts($cond);
    $bcode= $this->load_lib('bbcode',false);
    if ($bcode) {
      for ($i=0, $count=count($data); $i<$count; $i++) {
        $data[$i]['text']=$bcode->parse_msg($data[$i]);
        $data[$i]['signature']=$bcode->parse_sig($data[$i]['signature'],'allow');
      }
    }
    $this->out->posts = $data;
    $this->out->premod_mode = true;
    $this->out->accept_key=$this->gen_auth_key(false,'accept',$this->url('moderate/'.$this->forum['hurl'].'/'));
    $this->out->delete_key=$this->gen_auth_key($this->get_uid(),'delete_post',$this->url('moderate/'.$this->forum['hurl'].'/'));
  }

  function action_accept() {
    if (empty($_REQUEST['id'])) $this->output403('Не указан номер сообщения для обработки!');
    $pid = intval($_REQUEST['id']);
    $tlib = $this->load_lib('topic',true);
    $modlib = $this->load_lib('moderate',true);
    /* @var $modlib Library_moderate */
    $tid = $this->check_topic_by_pid($pid);
    $posts = $tlib->get_posts(array('tid'=>$tid,'id'=>$pid,'all'=>1));
    $opts['nousersync']=($posts[0]['status']==1); // если выводим сообщение с премодерации, то встроенными в библиотеку средствами пересчет сообщений делать не надо, делаем
    $modlib->status_posts(array($pid=>0),$tid,$opts);
    if ($posts && $posts[0]['status']==1) { // если сообщение находилось на премодерации, то нужно увеличить счетчик пользователя и
      $notify_lib = $this->load_lib('notify',false);
      if ($notify_lib) {
        $bbcode = $this->load_lib('bbcode',false);
        if ($bbcode) $parsed=$bbcode->parse_msg($posts[0]);
        $notify_lib->new_post($posts[0],$this->topic,$this->forum,$parsed);
      }
      if ($this->forum['is_stats']) { // если раздел является статистически значимым, увеличиваем счетчик
        $userlib = $this->load_lib('userlib',false);
        if ($userlib) {
          $userlib->increment_user($posts[0]['uid']);
        }
      }
    }
    if ($this->get_request_type()!=1) $this->redirect($this->referer()); //$this->url('moderate/'.$this->topic['full_hurl'].'premod.htm'
    else $this->output_json(array('result'=>'done'));
  }

  /** Проверка, что сообщение действительно находится в этом разделе
   * и установка $this->topic данными соответствующей темы, чтобы корректно работала самомодерация
   **/
  function check_topic_by_pid($pid) {
    $sql= ' SELECT t.* FROM '.DB_prefix.'post p, '.DB_prefix.'topic t '.
        'WHERE p.id='.intval($pid).' AND p.tid=t.id';
    $topic = $this->db->select_row($sql);
    if ($topic['fid']!=$this->forum['id']) $this->output_403('Указанное сообщение не принадлежит данному разделу!');
    $this->topic=$topic;
    return $topic['id'];
  }

  function action_view_log() {
    $modlib = $this->load_lib('moderate',true);

    $time = $this->time;
    $show = isset($_REQUEST['show']) ? $_REQUEST['show'] : false;
    $this->out->show = $show;

    if ($show==='week') {
      $cond['time'] = ($time-7*24*60*60);
    }
    // выводим сообщения за месяц (для простоты будем считать его равным 30 дням всегда)
    elseif ($show==='month') {
      $cond['time'] = ($time-30*24*60*60);
    }
    // выводим сообщения за последние 3 месяца (точнее, 90 дней)
    elseif ($show==='3months') {
      $cond['time'] = ($time-90*24*60*60);
    }
    elseif ($show==='all') { // если выводим все записи лога, то доп. условий не надо
    }
    else { // по умолчанию выводим модераторские действия за последние 3 дня
      $cond['time'] = ($time-3*24*60*60);
    }

    $cond['fid'] = $this->forum['id'];
    $cond['tid'] = (!empty($this->topic)) ? $this->topic['id'] : false;
    $this->out->mod_items = $modlib->get_actions($cond);
    $this->out->rollback_key =  $this->gen_auth_key(false,'rollback'); // ключ для выполнения операции отката
  }

  function action_rollback() {
    if (empty($_REQUEST['id'])) $this->output_403('Не указан идентификатор сообщения!');
    $id = intval($_REQUEST['id']);
    $modlib = $this->load_lib('moderate',true);
    $result=$modlib->rollback($id);
    if ($this->get_request_type()!=1) {
      if ($result) $this->message('Откат модераторского действия выполнен!',1);
      else $this->message('При откате модераторского действия произошла непредвиденная ошибка',3);
      if (!empty($this->topic)) $this->redirect($this->http($this->url('moderate/'.$this->topic['full_hurl'].'/view_log.htm')));
      $this->redirect($this->http($this->url('moderate/'.$this->forum['hurl'].'/view_log.htm')));
    }
    else $this->output_json(array('result'=>($result) ? 'done' : 'error'));
    $this->redirect($this->http($this->url('moderate')));
  }

  function action_trashbox() {
    $fid = $this->forum['id'];
    $tlib = $this->load_lib('topic',true);

    $cond['fid']=$fid; // выбираем все сообщения раздела, стоящие на премодерации, с получением информации о темах, в которых они расположены, и приложенных файлах
    if (!empty($this->out->topic)) $cond['tid']=$this->topic['id']; // если указана конкретная тема, выводим сообщения только из нее.
    $cond['topics']=true;
    $cond['files']=true;
    $cond['deleted']=true;
    $cond['user']=true;
    $cond['order']='t.last_post_time DESC, postdate DESC';

    $data = $tlib->get_posts($cond);
    $bcode= $this->load_lib('bbcode',false);
    if ($bcode) {
      for ($i=0, $count=count($data); $i<$count; $i++) {
        $data[$i]['text']=$bcode->parse_msg($data[$i]);
        $data[$i]['signature']=$bcode->parse_sig($data[$i]['signature'],'allow');
      }
    }
    $this->out->posts = $data;
    $this->out->trashbox_mode = true;
    $this->out->accept_key=$this->gen_auth_key(false,'accept',$this->url('moderate/'.$this->forum['hurl'].'/'));
  }

  function action_delete_post() {
    if (empty($_REQUEST['id'])) $this->output_403('Не указан номер сообщения для удаления!');
    $pid = intval($_REQUEST['id']);
    $tid = $this->check_topic_by_pid($pid);
    /** @var Library_moderate $modlib */
    $modlib = $this->load_lib('moderate',true);
    $modlib->status_posts(array($pid=>2),$tid);
    if ($this->get_request_type()!=1) {
      $this->redirect($this->http($this->url($this->forum['hurl'].'/update_extdata.htm')));
    }
    else $this->output_json(array('result'=>'done'));
  }

  function action_delete_topic() {
    $fid = $this->forum['id'];
    $tid = $this->topic['id'];
    $modlib = $this->load_lib('moderate',true);
    $modlib->status_topics(array($tid=>2),$fid);
    $this->redirect($this->http($this->url($this->forum['hurl'].'/update_extdata.htm')));
  }

  function action_edit_rules() {
    if (!$this->get_opt('moder_edit_rules') && !$this->is_admin()) $this->output_403('Редактировать правила раздела могут только администраторы!');
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);
      $misclib->save_text($_POST['text'],$this->forum['id'],0);
      $this->out->static_text = $_POST['text'];
    }
    else {
      $this->out->static_text = $this->get_text($this->forum['id'],0); // получаем текст статической страницы (для большинства разделов 2 -- это код вводного текста, но для статического/контейнера -- основного)
    }
  }

  function action_curator() {
    if (empty($this->topic['id'])) $this->output_403('Не указана тема для назначения куратора!');
    $tid = intval($this->topic['id']);
    if ($this->is_post()) {
      $newname = isset($_POST['owner']) ? $_POST['owner'] : '';
      if (empty($newname)) {
        $sql = 'UPDATE '.DB_prefix.'topic SET owner=0 WHERE id='.$tid;
        $this->db->query($sql);
        $this->message('Куратор темы удалён.',2);
        $this->redirect($this->http($this->url('moderate/' . $this->topic['full_hurl'] . '/curator.htm')));
      }
      else {
        $sql = 'SELECT id FROM '.DB_prefix.'user WHERE display_name=\''.$this->db->slashes($newname).'\'';
        $uid = $this->db->select_int($sql);
        if (empty($uid)) $this->message('Пользователь '.$newname.' не найден!',3);
        else {
          $sql = 'UPDATE '.DB_prefix.'topic SET owner='.intval($uid).' WHERE id='.$tid;
          $this->db->query($sql);
          $this->message('Пользователь '.$newname.' назначен куратором темы!');
          $this->redirect($this->http($this->url('moderate/' . $this->topic['full_hurl'] . '/curator.htm')));
        }
      }
      $this->out->owner = $newname;
    }
    else {
      if ($this->topic['owner']) $this->out->owner=$this->load_user($this->topic['owner'],0)['display_name'];
    }
    $this->out->true_moderator = $this->is_moderator(false);
  }

  function action_edit_foreword() {
    if (!$this->get_opt('moder_edit_foreword') && !$this->is_admin()) $this->output_403('Редактировать вводное слово раздела могут только администраторы!');
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);
      $text = $_POST['text'];
      if (trim(strip_tags($text))==='') $text='';
      $misclib->save_text($text,$this->forum['id'],2);
      $this->out->static_text = $text;
    }
    else {
      $this->out->static_text = $this->get_text($this->forum['id'],2); // получаем текст статической страницы (для большинства разделов 2 -- это код вводного текста, но для статического/контейнера -- основного)
    }
    $this->out->authkey=$this->gen_auth_key();
  }

  function set_location() {
    $result = parent::set_location();
    $result[1]=array($this->forum['title'],$this->url($this->forum['hurl'].'/'));
    if (!empty($this->topic)) $result[2]=array($this->topic['title'],$this->url($this->topic['full_hurl']));
    if ($this->action==='move_posts') { $result[]=array('Разделение темы');  }
    if ($this->action==='trashbox') $result[]=array('Корзина');
    if ($this->action==='premod') $result[]=array('Премодерация');
    if ($this->action==='view_log') $result[]=array('Лог действий');
    if ($this->action==='mod_forum') $result[]=array('Модерирование раздела');
    if ($this->action==='edit_rules') $result[]=array('Редактирование правил');
    if ($this->action==='edit_foreword') $result[]=array('Редактирование вступительного слова');
    if ($this->action === 'curator') $result[] = array('Назначение куратора');
    return $result;
  }

  function set_rss() {
    return false;
  }

  function set_lastmod() {
    $this->lastmod = $this->time; // модераторские страницы обновляем всегда!
  }

  function set_title() {
    if ($this->action==='move_posts') $result='Разделение темы';
    if ($this->action==='trashbox') $result='Корзина';
    if ($this->action==='premod') $result='Премодерация';
    if ($this->action==='view_log') $result='Лог действий';
    if ($this->action==='mod_forum') $result='Модерирование раздела';
    if ($this->action==='edit_rules') $result='Редактирование правил';
    if ($this->action==='edit_foreword') $result='Редактирование вступительного слова';
    if ($this->action==='curator') $result = 'Назначение куратора';
    if (!empty($this->topic)) $result=$this->topic['title'];
    else $result=$this->forum['title'];
    return $result;
  }

  function get_action_name() {
     return 'Выполняет модераторские действия';
  }

}
