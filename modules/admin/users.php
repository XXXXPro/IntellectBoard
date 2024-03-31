<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2014-2015, 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Модуль работы с пользователями для Центра Администрирования  Intellect Board 3 Pro
  *  ================================ */

class users extends Application_Admin {
  function get_groups($superuser=true) {
    $sql = 'SELECT level, CONCAT(name,\' (\',level,\') \') FROM '.DB_prefix.'group ';
    if (!$superuser) $sql.='WHERE founder=\'0\' '; // если пользователь не является Основателем, скрываем все группы со статусом Основателя
    $sql.='ORDER BY level';
    return $this->db->select_simple_hash($sql);
    
  }
  function action_groups() {
    $sql = 'SELECT * FROM '.DB_prefix.'group ORDER BY level';
    $this->out->groups = $this->db->select_all($sql);
    
    // получаем группы, в которые записаны NewUser и Guest
    $sql = 'SELECT group_id FROM '.DB_prefix.'user_ext WHERE id=1';
    $this->out->guest_level = $this->db->select_int($sql);
    $sql = 'SELECT group_id FROM '.DB_prefix.'user_ext WHERE id=3';
    $this->out->newuser_level = $this->db->select_int($sql);

    $this->out->founder = $this->is_admin(true); // проверяем, является ли пользователь основателем (founder), чтобы дать ему возможность удалять группы и создавать новые    
  }
    
  function action_new_group() {
    if (!$this->is_admin(true)) { // если нет прав founder, то создавать группу пользователь не имеет права
      $this->message('Вы не можете создавать группы, так как не имеете прав основателя!',3);
      return 'admin/main.tpl';
    }
    $this->out->fields = $this->get_access_fields();
    if ($this->is_post()) {
      if (empty($_POST['access'])) $_POST['access']=array();
      $level=intval($_POST['group']['level']);
      $errors = array();
      $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'group WHERE level='.intval($level);
      $group_exists = $this->db->select_int($sql);
      if ($group_exists) $errors[]=array('text'=>'Такой уровень доступа уже существует!','level'=>3);
      if (empty($_POST['group']['name'])) $errors[]=array('text'=>'Не указано название уровня доступа!','level'=>3);
      
      if (!empty($errors)) $this->message($errors);
      else {
        $_POST['group']['max_attach']=intval($_POST['group']['max_attach']);
        $result=$this->db->insert(DB_prefix.'group',$_POST['group']);
        if ($result) {
/*          $access = $_POST['access'];
          $access['fid']=0;
          $access['gid']=$level;
          $result =  $this->db->insert(DB_prefix.'access',$access);*/
          $parent_group = $_POST['parent_group'];
          $sql = 'SELECT * FROM '.DB_prefix.'access WHERE gid='.intval($parent_group);
          $access = $this->db->select_all($sql);
          for ($i=0, $count=count($access); $i<$count; $i++) {
            $access[$i]['gid']=$level;
            $this->db->insert(DB_prefix.'access', $access[$i]);
          }
        }
        if ($result) {
          if ($this->get_opt('userlib_logs')>2) $this->log_entry('user',20,'userlib.php','Пользователь '.$this->get_userlogin().' создал группу с уровнем доступа '.intval($_POST['group']['level']).'.');    
          $this->message('Уровень доступа создан!',1);
          $this->redirect($this->http(str_replace('new_group.htm','groups.htm',$_SERVER['REQUEST_URI'])));
        }                
      }
      $this->out->group=$_POST['group'];
      $this->out->access=$_POST['access'];
    }
    else {
      $this->out->group=array('special'=>true,'floodtime'=>'30','links_mode'=>'none');
      $this->out->parent_groups = $this->get_groups(true);
    }
  }
  
  function action_edit_group() {
    $level = intval($_REQUEST['level']);
    $this->out->fields = $this->get_access_fields(); // получаем список полей с правами доступа
    $this->out->founder = $this->is_admin(true); // проверяем, является ли пользователь основателем (founder), чтобы дать ему возможность редактировать общие настройки группы
    
    $sql = 'SELECT * FROM '.DB_prefix.'group WHERE level='.intval($level);
    $this->out->group = $this->db->select_row($sql);

    if (empty($this->out->group)) {
      $this->message('Уровень '.intval($level).' не найден!',3);
      return 'admin/main.tpl';
    }
    
    if ($this->is_post()) {
      if ($this->is_admin(true)) { // редактировать базовые свойства группы может только founder
        $gdata = $_POST['group'];
        $gdata['special'] = !empty($_POST['group']['special']) ? '1' : '0';
        $gdata['team'] = !empty($_POST['group']['team']) ? '1' : '0';
        $gdata['admin'] = !empty($_POST['group']['admin']) ? '1' : '0';
        $gdata['founder'] = !empty($_POST['group']['founder']) ? '1' : '0';
        $gdata['custom_title'] = !empty($_POST['group']['custom_title']) ? '1' : '0';
        $gdata['max_attach'] =intval($_POST['group']['max_attach']);
        
        $this->db->update(DB_prefix.'group',$gdata,'level='.intval($level));
        $this->message('Общие настройки группы сохранены!',1);
      }
      foreach ($_POST['delete'] as $fid=>$value) {
        $sql = 'DELETE FROM '.DB_prefix.'access WHERE gid='.intval($level).' AND fid='.intval($fid);
        $this->db->query($sql);
      }
      foreach ($_POST['access'] as $fid=>$access) if (empty($_POST['delete'][$fid])) {
        $rights = array();
        foreach ($this->out->fields as $field) $rights[$field]=!empty($access[$field])  ? '1' : '0';
        $this->db->update(DB_prefix.'access',$rights,'gid='.intval($level).' AND fid='.intval($fid));
      }
      $this->message('Настройки прав для разделов сохранены!',1);
      foreach ($_POST['new'] as $number=>$data) {
        if ($data['fid']!=0) { // если пользователь не выбрал "не добавлять"
          foreach ($this->out->fields as $field) $data[$field]=!empty($data[$field]);
          $data['gid']=$level;
          $this->db->insert(DB_prefix.'access',$data);
        }
      }
      if ($this->get_opt('userlib_logs')>2) $this->log_entry('user',21,'admin/users.php','Пользователь '.$this->get_userlogin().' отредактировал группу '.intval($level).'.');    
      $this->reset_session_cache();
      $this->redirect($this->http($_SERVER['REQUEST_URI']));      
    }
    $sql = 'SELECT a.*, f.id, f.title, f.hurl FROM '.DB_prefix.'access a '.
      'LEFT JOIN '.DB_prefix.'forum f ON (a.fid=f.id) '.
      'WHERE gid='.intval($level).' ORDER BY f.sortfield';
    $acl=$this->db->select_hash($sql,'fid');
    $acl[0]['title']='Права по умолчанию'; // Для прав доступа, у которых указан нулевой раздел, т.е. весь форум в целом
    
    $sql = 'SELECT f.id, f.title, f.hurl, f.parent_id FROM '.DB_prefix.'forum f '.
    ' WHERE owner<='.intval(AUTH_SYSTEM_USERS).' ORDER BY sortfield';
    $forums = $this->db->select_hash($sql,'id');

    foreach ($acl as $fid=>$data) { // просчитываем, на какие разделы наследуются права
      $acl[$fid]['subforums']=array();
      foreach ($forums as $subfid=>$fdata) {
        if ($fid!=$subfid && $this->is_inherited($subfid,$fid,$acl,$forums)) $acl[$fid]['subforums'][]=$fdata;
      }
    }
    
    $this->out->new_forums = array('0'=>'Не добавлять');
    foreach ($forums as $fid=>$value) if (empty($acl[$fid])) $this->out->new_forums[$fid]=$value['title'];
    
    $this->out->acl=$acl;
  }
  
  function is_inherited($subfid,$fid,$acl,$forums) {
    if (empty($forums[$subfid])) return false; // чтобы не было зацикливания    
    $parent = $forums[$subfid]['parent_id'];
    if ($parent==$fid && empty($acl[$subfid])) return true; // если добрались до того, что fid является родительским разделом $subfid, 
    if (!empty($acl[$parent]) || !empty($acl[$subfid])) return false; // если оказалось, что в acl для родительского раздела есть запись, и мы до этого не вышли по предыдущему условию, то тот раздел не совпадает с fid и поэтому считаем, что он не наследуется
    return $this->is_inherited($parent, $fid, $acl, $forums);
  }
  
  function action_delete_group() {
    if (!$this->is_admin(true)) { // если нет прав founder, то создавать группу пользователь не имеет права
      $this->message('Вы не можете создавать группы, так как не имеете прав основателя!',3);
      return 'admin/main.tpl';
    }
    $level = intval($_REQUEST['level']);
    if ($this->is_post() && !empty($_POST['confirm'])) {
      $new_level = intval($_POST['new_level']);
      // переносим пользователей в другую группу
      $sql = 'UPDATE '.DB_prefix.'user_ext SET group_id='.intval($new_level).' WHERE group_id='.intval($level);
      $this->db->query($sql);
      // удаляем права доступа
      $sql = 'DELETE FROM '.DB_prefix.'access WHERE gid='.intval($level);
      $this->db->query($sql);
      // и наконец удаляем саму группу
      $sql = 'DELETE FROM '.DB_prefix.'group WHERE level='.intval($level);
      $this->db->query($sql);
      $this->reset_session_cache(); // сбрасываем кеш сессий, чтобы у перенесенных пользователей обновились права
      if ($this->get_opt('userlib_logs')>2) $this->log_entry('user',21,'admin/users.php','Пользователь '.$this->get_userlogin().' удалил группу '.intval($level).'.');
      $this->message('Уровень доступа удален!',1);
      $this->redirect($this->http(str_replace('delete_group.htm','groups.htm',$_SERVER['REQUEST_URI'])));      
    }
    $sql = 'SELECT level,name FROM '.DB_prefix.'group ORDER BY level';
    $this->out->groups = $this->db->select_simple_hash($sql);
    if (count($this->out->groups)<=1) {
      $this->message('Данная группа доступа &mdash; последняя! Вы не можете ее удалить, иначе форум станет недоступным!',3);
      return 'admin/main.tpl';      
    }
    $this->out->level = $level;
    $this->out->name = $this->out->groups[$level];
    unset($this->out->groups[$level]);
  }
  
  function action_contacts() {
    if ($this->is_post()) {
      if (!empty($_POST['delete'])) {
        $sql = 'DELETE FROM '.DB_prefix.'user_contact WHERE '.$this->db->array_to_sql($_POST['delete'],'cid');
        $this->db->query($sql);
        $sql = 'DELETE FROM '.DB_prefix.'user_contact_type WHERE '.$this->db->array_to_sql($_POST['delete'],'cid');
        $this->db->query($sql);
      }
      foreach ($_POST['items'] as $cid=>$data) if (empty($_POST['delete']) || !in_array($cid, $_POST['delete'])) {
        $data['c_permission']=!empty($data['c_permission']) ? '1' : '0';
        $this->db->update(DB_prefix.'user_contact_type',$data,'cid='.intval($cid));
      }
      if (!empty($_POST['newitems'])) foreach ($_POST['newitems'] as $data) if (!empty($data['c_title'])) {
        $data['c_permission']=!empty($data['c_permission']) ? '1' : '0';
        $this->db->insert(DB_prefix.'user_contact_type',$data);
      }
      $this->message('Изменения сохранены!',1);
      $this->redirect($this->http($_SERVER['REQUEST_URI']));
    }
    
    $sql = 'SELECT * FROM '.DB_prefix.'user_contact_type ORDER BY c_sort';
    $this->out->contacts = $this->db->select_all($sql);
  }
  
  function action_moderators() {
    if ($this->is_post()) {
      if (empty($_POST['role'])) $this->message('Не указаны роли для назначения!',2);
      elseif (empty($_POST['user'])) $this->message('Не выбран ни один пользователь!',2);
      elseif (empty($_POST['forums'])) $this->message('Не выбран ни один раздел!',2);
      else foreach ($_POST['role'] as $role) {
        foreach ($_POST['forums'] as $forum) {
          foreach ($_POST['user'] as $user) {
            $data = array('role'=>$role,'fid'=>$forum,'uid'=>$user);
            $this->db->insert_ignore(DB_prefix.'moderator',$data);
            $udata = $this->load_user($user,0);
            if ($this->get_opt('userlib_logs')>2) $this->log_entry('user',13,'userlib.php','Пользователь '.$this->get_userlogin().' назначил '.$udata['name'].' ('.$udata['display_name'].') модератором в разделе '.intval($forum).'.');
          }
        }
        $this->clear_cached('Moderators_'.$forum); 
      } 
    }
    $sql = 'SELECT uid, fid, role, display_name, avatar '.
    'FROM '.DB_prefix.'moderator m,  '.DB_prefix.'user u '.
    'WHERE m.uid=u.id ORDER BY fid';
    $this->out->moderators = $this->db->select_super_hash($sql,'fid');
    $sql = 'SELECT f.id, f.title FROM '.DB_prefix.'forum f ORDER BY f.sortfield';
    $this->out->forums = array('0'=>'Глобальные права на все разделы')+$this->db->select_simple_hash($sql);
    $sql = 'SELECT g.name, g.level FROM '.DB_prefix.'group g WHERE team=\'1\' ORDER BY level';
    $this->out->groups = $this->db->select_all($sql);
    $sql = 'SELECT u.id, u.display_name, u.avatar '.
    'FROM '.DB_prefix.'user u, '.DB_prefix.'user_ext ue, '.DB_prefix.'group g '.
    'WHERE u.id=ue.id AND ue.group_id=g.level AND g.team=\'1\' AND u.status=\'0\' AND u.id>'.AUTH_SYSTEM_USERS.' '.
    'ORDER BY u.display_name';
    $this->out->users = $this->db->select_all($sql);
    $this->out->del_key = $this->gen_auth_key(false,'delete_mod');
    $this->out->del_all_key = $this->gen_auth_key(false,'delete_mod_all');
  }
  
  function action_delete_mod() {
    if (empty($_REQUEST['authkey'])) $this->output_403('Неправильный ключ аутентификации!');
    $sql = 'DELETE FROM '.DB_prefix.'moderator WHERE uid='.intval($_REQUEST['uid']).' AND fid='.intval($_REQUEST['fid']).' AND role=\''.$this->db->slashes($_REQUEST['role']).'\'';
    $this->db->query($sql);
    if ($this->get_opt('userlib_logs')>2) $this->log_entry('user',12,'userlib.php','Пользователь '.$this->get_userlogin().' снял роль пользователя '.$_POST['uname'].') в разделе '.intval($_REQUEST['fid']).'.');    
    $this->message('Выбранная роль удалена!',1);
    $this->redirect($this->http(dirname($_SERVER['REQUEST_URI']).'/moderators.htm'));    
  }
  
  function action_delete_mod_all() {
    if (empty($_POST['uname'])) $this->message('Не указано имя пользователя',3);
    else {
      $userlib = $this->load_lib('userlib',true);
      $uid = $userlib->get_uid_by_name($_POST['uname']);
      if ($uid) {
        $sql = 'DELETE FROM '.DB_prefix.'moderator WHERE uid='.intval($uid);
        $this->db->query($sql);
        if ($this->get_opt('userlib_logs')>2) $this->log_entry('user',12,'userlib.php','Пользователь '.$this->get_userlogin().' снял все роли пользователя '.$_POST['uname'].'). ');
        $this->message('Все выбранные роли пользователя '.htmlspecialchars($_POST['uname']).' удалены!',1);
      }
      else $this->message('Пользователь '.htmlspecialchars($_POST['uname']).' не найден!',2);
    }
    $this->redirect($this->http(dirname($_SERVER['REQUEST_URI']).'/moderators.htm'));
  }
  
  function action_users() {
    $userlib = $this->load_lib('userlib',true);
    /* @var $userlib Library_userlib */
    
    $this->out->letters1 = $userlib->get_letters(false,true); // true означает, что при составлении списка включаются также буквы, на которые начинаются имена неактивных пользователей
    $this->out->start_letter = isset($_GET['letter']) ? $_GET['letter'] : $this->out->letters1[0]; // если первая буква не указана явно, берем перую из тех, что есть в списке
    $this->out->letters2 = $userlib->get_letters($this->out->start_letter,true);
    $this->out->start_letter2 = isset($_GET['letter2']) ? $_GET['letter2'] : $this->out->letters2[0]; // если вторая буква не указана явно, берем первую из тех, что есть в списке вторых букв
    if (!empty($_GET['show'])) $this->out->show=$_GET['show'];
    
    if (empty($_GET['show']) && empty($_GET['search'])) { // если не указан вывод пользователей по определенному признаку и не делался поиск
      $cond = array('status'=>'all','letter'=>$this->out->start_letter.$this->out->start_letter2,'all_data'=>true);
    }
    elseif (!empty($_GET['search'])) {
      $this->out->search = $_GET['search'];
      $cond = array('status'=>'all','search'=>$this->out->start_letter.$this->out->start_letter2);
    }
    elseif ($_GET['show']==='banned') {
      $this->out->show='banned';
      $cond = array('banned'=>true);
    }
    elseif ($_GET['show']==='unconfirmed') {
      $this->out->show='unconfirmed';
      $cond = array('status'=>'1');
    }
    elseif ($_GET['show']==='team') {
      $this->out->show='team';
      $sql = 'SELECT level FROM '.DB_prefix.'group WHERE team=\'1\'';
      $groups = $this->db->select_all_numbers($sql);
      $cond = array('group'=>$groups,'status'=>'all');
    }
    elseif ($_GET['show']==='last') {
      $cond = array('status'=>'all','perpage'=>10,'order'=>'reg_date','sort'=>'DESC');
    }
    $cond['ext_data']=true;
    $cond['all_data']=true;
    $cond['last_visit']=true;
    $cond['group_data']=true;
    $this->out->users = $userlib->list_users($cond);
    
    $timeout = $this->get_opt('online_time') or 15;
    $this->out->lasttime = $this->time - $timeout*60;
  }
  
  function action_user_view() {
    $userlib=$this->load_lib('userlib',true);
    /* @var $userlib Library_userlib */
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid=intval($_REQUEST['uid']);
    
    $udata=$this->load_user($uid,2);
    if (empty($udata) || empty($udata['basic'])) $this->output_404('Пользователя с таким идентификатором не существует!');
    
    $sql='SELECT MAX(visit1) FROM ' . DB_prefix . 'last_visit WHERE uid=' . intval($uid);
    $this->out->lastvisit=$this->db->select_int($sql);
    
    for($i=0, $count=count($udata['contacts']); $i < $count; $i++) {
      if (substr($udata['contacts'][$i]['link'],0,2) == '%s' &&       // защищемся от ссылок без http:// в начале, если в
      strpos($udata['contacts'][$i]['value'],'http://') === false && strpos($udata['contacts'][$i]['value'],'https://') === false && strpos($udata['contacts'][$i]['value'],'ftp://') === false) $udata['contacts'][$i]['value']='http://' . $udata['contacts'][$i]['value'];
    }
    $this->out->userdata=$udata;
    $this->out->founder = $this->is_admin(true); // для того, чтобы не показывать те действия, которые не founder-у недоступны

    $this->out->ban_key = $this->gen_auth_key(false,'user_ban');
    $this->out->activate_key = $this->gen_auth_key(false,'user_activate');
    
    $pmlib = $this->load_lib('privmsg',false);
    /* @var $pmlib Library_privmsg */
    if ($pmlib) {
      $this->out->pm_total = $pmlib->count_threads(array('uid'=>$uid));
      $this->out->pm_lastday = $pmlib->count_threads(array('uid'=>$uid,'lasttime'=>$this->time-24*60*60));
    }
    
    $warnlib = $this->load_lib('warning',false);
    /* @var $warnlib Library_warning */
    if ($warnlib) {
      $cond['moderator']=true;
      $cond['links']=true;
      $cond['limit']=isset($_REQUEST['all_warnings']) ? false : 10; // если не указано "показывать все предупреждения", показываем только 10 последних
      $this->out->warnings = $warnlib->list_warnings($uid,$cond);
      $this->out->warn_key = $this->gen_auth_key(false,'user_delete_warning');
    }
    $forumlib = $this->load_lib('forums',false);
    /* @var $forumlib Library_forums */
    if ($forumlib) {
      $this->out->personal_forums = $forumlib->list_forums(array('owner'=>$uid));
    }
  }
    
  /** Удаление вынесенного предупреждения **/
  function action_user_delete_warning() {
    if (empty($_REQUEST['authkey'])) $this->output_403('Некорректный ключ аутентификации');
    if (empty($_REQUEST['uid']) || empty($_REQUEST['warn_id'])) $this->output_403('Некорректный идентфиикатор пользователя или предупржедения'); 
    $warnlib = $this->load_lib('warning',true);
    /* @var $warnlib Library_warning */
    $warnlib->delete_warnings($_REQUEST['uid'], array($_REQUEST['warn_id']));
    $this->message('Предупреждение удалено!',1);
    $this->redirect($this->http(dirname($_SERVER['REQUEST_URI']).'/user_view.htm?uid='.$_REQUEST['uid']));
  }
  
  function action_user_change_group() {
    $userlib=$this->load_lib('userlib',true);
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid=intval($_REQUEST['uid']);
    
    $udata=$this->load_user($uid,2);
    if (empty($udata) || empty($udata['basic'])) $this->output_404('Пользователя с таким идентификатором не существует!');
    if (!$this->is_admin(true) && $udata['ext_data']['founder']) $this->output_403('Нельзя изменить уровень доступа пользователя со статусом "Основатель".');
    $this->out->userdata = $udata;
    
    if ($this->is_post()) {
      $sql = 'SELECT founder FROM '.DB_prefix.'group WHERE level='.intval($_POST['level']);
      $founder = $this->db->select_int($sql);
      if ($founder && !$this->is_admin(true)) { // если запрошена установка уровня founder, а текущий пользователь сам founderом не является
        $this->output_403('Вы не можете поставить статус Основателя кому-либо, не являясь Основателем сами',3);
      }
      $sql = 'UPDATE '.DB_prefix.'user_ext SET group_id='.intval($_POST['level']).' WHERE id='.intval($uid);
      $result=$this->db->query($sql);
      if ($result) {
        $this->reset_session_cache();
        // запишем изменение в лог, если оно включено
        if ($this->get_opt('userlib_logs')>1) $this->log_entry('user',11,'userlib.php','Пользователь '.$this->get_userlogin().' изменил уровень доступа пользователя '.$udata['login'].' ('.$udata['display_name'].') на '.intval($_POST['level']).'.');
        $this->message('Уровень доступа пользователя изменен!',1);
        $this->redirect($this->http(dirname($_SERVER['REQUEST_URI']).'/user_view.htm?uid='.$uid));
      }
      else $this->message('Произошла ошибка при изменении статуса!',3);
    }
    $this->out->groups = $this->get_groups($this->is_admin(true));
    $this->out->authkey = $this->gen_auth_key();
  }
  
  function action_user_delete() {
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid=intval($_REQUEST['uid']);    
    if (!$this->is_admin(true)) { // менять наиболее критичные настройки могут только Основатели форума
      $this->message('Только Основатели форума могут удалять пользователей!',2);
      return 'main.tpl';
    }
    if ($uid==$this->get_uid()) {
      $this->message('Вы не можете удалить с форума самого себя!',3);
      return 'main.tpl';
    }
    $this->out->udata = $this->load_user($uid,0);    
    if ($this->is_post()) {
      if ($this->out->udata['display_name']===$_POST['confirm_name']) {
        $dellib = $this->load_lib('delete',true);
        /** @var Library_delete $dellib */
        $dellib->delete_users(array($uid));
        if ($this->get_opt('userlib_logs')>1) $this->log_entry('user',14,'admin/users.php','Пользователь '.$this->get_userlogin().' удалил пользователя '.$this->out->udata['display_name'].'.');    
        $this->message('Пользователь '.$this->out->udata['display_name'].' удален!',1);
        $this->redirect($this->http(dirname($_SERVER['REQUEST_URI']).'/users.htm'));
      } 
      else $this->message('Некорректно введено подтверждение имени удаляемого пользователя!',2);      
    }
  }
  
  function action_user_ban() {
    if (empty($_REQUEST['authkey'])) $this->output_403('Некорректно указан ключ аутентификации!');
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid=intval($_REQUEST['uid']);
    
    $userlib=$this->load_lib('userlib',true);
    $udata=$this->load_user($uid,2);
    if (empty($udata) || empty($udata['basic'])) $this->output_404('Пользователя с таким идентификатором не существует!');
    if (!$this->is_admin(true) && $udata['ext_data']['founder']) $this->output_403('Нельзя изгнать пользователя со статусом "Основатель".');

    $sql = 'UPDATE '.DB_prefix.'user_ext SET banned_till=0 WHERE id='.intval($uid);
    $this->db->query($sql);
    if (!empty($_REQUEST['unban'])) $status=0;
    else $status=2;
    $sql = 'UPDATE '.DB_prefix.'user SET status=\''.intval($status).'\' WHERE id='.intval($uid);
    $this->db->query($sql);
    $this->reset_session_cache();
    // пишем бан в лог, если это включено в настройках
    if ($this->get_opt('userlib_logs')>1) $this->log_entry('user',10,'userlib.php','Пользователь '.$this->get_userlogin().' забанил пользователя '.$udata['login'].' ('.$udata['display_name'].'). ');
    $this->message(($status==2) ? 'Пользователь изгнан с форума.' : 'Пользователь возвращен на форум.',1);
    $this->redirect($this->http(dirname($_SERVER['REQUEST_URI']).'/user_view.htm?uid='.$uid));    
  }

  function action_user_activate() {
    if (empty($_REQUEST['authkey'])) $this->output_403('Некорректно указан ключ аутентификации!');
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid=intval($_REQUEST['uid']);
  
    $sql = 'UPDATE '.DB_prefix.'user SET status=\'0\' WHERE id='.intval($uid);
    $this->db->query($sql);
    $this->message('Пользователь активирован.',1);
    $this->redirect($this->http(dirname($_SERVER['REQUEST_URI']).'/user_view.htm?uid='.$uid));
  }  
  
  function action_user_edit() {
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid=intval($_REQUEST['uid']);
    $userlib=$this->load_lib('userlib',true);    
    $this->out->allow_template = true; // при редактировании из админки можно редактировать все

    $templatelib = $this->load_lib('template',false);
    if ($templatelib) $this->out->user_templates = array(''=>'Стиль сайта по умолчанию')+$templatelib->get_list($this->is_admin()); // если пользователь -- админ, он может выбрать любой шаблон, иначе -- только незаблокированные

    if ($this->is_post()) {
      $result=$userlib->update_user($_POST['basic'],$_POST['settings'],$_POST['contacts'],$_POST['interests_str'],true); // валидация делается внутри процедуры update
      if ($result) $this->message('Профиль пользователя изменен!',1);
      if ($uid==1 && $this->get_cached('Guest')!==NULL) { // если редактировали профиль гостя и он закеширован, обновляем кеш принудительно
        $tmpdata = $this->load_user(1,1);
        $this->set_cached('Guest', $tmpdata);
      }      
      $this->redirect($this->http($_SERVER['REQUEST_URI']));
    }
    else {
      $data=$this->load_user($uid,2); // загрузка профиля NewUser со всеми данными, включая настройки
      unset($data['basic']['password']); // сбрасываем пароль
      $data['interests_str']=is_array($data['interests']) ? join(', ',$data['interests']) : '';
      for ($i=0; $i<3; $i++) $data['contacts'][]=array('cid'=>0,'value'=>'');
    }
    $sql = 'SELECT cid,c_title FROM '.DB_prefix.'user_contact_type ORDER BY c_sort';
    $this->out->contact_types = $this->db->select_simple_hash($sql);
    $this->out->contact_types=array('0'=>'Нет')+$this->out->contact_types; // добавляем элемент "Нет", чтобы можно было удалить ненужный контакт
    $this->out->formdata = $data;
    $this->out->timezones = Library_userlib::$timezones;
    $this->out->referer = '';
    // список вкладок профиля
    $profile_tabs = preg_replace('|\s+,|',',',$this->get_opt('userlib_profile_tabs')); //
    if (empty($profile_tabs)) $profile_tabs = 'basic,signature,avatar,bio,settings,notify,contacts';
    $this->out->profile_tabs = explode(',',$profile_tabs);   
   
    $this->out->admin_edit_mode = true;
    return '../user/update.tpl';
  }
  
  /** Работа с заблокированными IP-адресами **/
  function action_ip() {
    if ($this->is_post()) {
      $sql = 'DELETE FROM '.DB_prefix.'banned_ip';
      $this->db->query($sql);
      for ($i=1, $count=count($_POST['ips']['start']); $i<=$count;$i++) {
        if ($_POST['ips']['start'][$i]) {
          $data['start'] = ip2long($_POST['ips']['start'][$i]);
          $data['end'] = empty($_POST['ips']['end'][$i]) ? $data['start'] : ip2long($_POST['ips']['end'][$i]);
          $data['till'] = $_POST['ips']['till'][$i]!=-1 ? $this->time+$_POST['ips']['till'][$i]*60 : 0xFFFFFFFF;
          if ($data['start'] && $data['end']) $this->db->insert(DB_prefix.'banned_ip', $data); // если начальный и конечный IP не пустые, т.е. преобразовались корректно
        }
      }
      $this->message('Список заблокированных IP сохранен',1);
      $this->redirect($this->http($_SERVER['REQUEST_URI']));      
    }
      $sql = 'SELECT "start", "end", "till" FROM '.DB_prefix.'banned_ip WHERE till>='.intval($this->time).' ORDER BY start';
      $ips = $this->db->select_all($sql);
      $this->out->ips = array();
      for ($i=0, $count=count($ips); $i<$count; $i++) {
        $till = ($ips[$i]['till']==0xFFFFFFFF) ? -1 : floor(($ips[$i]['till']-$this->time)/60); 
        $this->out->ips[]=array('start_ip'=>long2ip($ips[$i]['start']),'end_ip'=>long2ip($ips[$i]['end']),'till'=>$till);
      }
      for ($i=0; $i<5; $i++) $this->out->ips[]=array();// добавляем пустые массивы, чтобы были пустые поля для добавления новых IP-адресов 
  }
  
  /** Редактирование описания роли пользователя в команде **/
  function action_user_role() {
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid=intval($_REQUEST['uid']);    
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);
      /* @var $misclib Library_misc */
      $misclib->save_text($_POST['text'], $uid, 33); // 33 -- текст с описанием роли пользователя
      $this->message('Описание роли пользователя сохранено!',1);
      $this->redirect($this->http($_SERVER['REQUEST_URI']));
    }
    else {
      $this->out->role = $this->get_text($uid, 33); // 33 -- текст с описанием роли пользователя
      $this->out->uid = $uid;
      $udata =$this->load_user($uid,0);
      $this->out->display_name = $udata['display_name'];
    }
  }

  function action_view_visited() {
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid=intval($_REQUEST['uid']);    
    $this->out->udata = $this->load_user($uid,0);
    if (empty($this->out->udata)) $this->output_404('Нет такого пользователя!');
    $sql = 'SELECT t.title, f.hurl AS f_hurl, f.id AS fid, CONCAT(f.hurl,\'/\',CASE WHEN t.hurl!=\'\' THEN t.hurl ELSE CAST(t.id AS CHAR(11)) END,\'/\') AS full_hurl, f.title AS f_title, lv.visit1 FROM '.DB_prefix.'topic t, '.DB_prefix.'forum f, '.DB_prefix.'last_visit lv'.
    ' WHERE t.fid=f.id AND lv.oid=t.id AND lv.uid='.intval($uid).' AND lv.type=\'topic\' ORDER BY visit1 DESC';
    $this->out->viewed_topics = $this->db->select_all($sql);
  }
}

