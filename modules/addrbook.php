<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Модуль работы с адресной книгой
 *  ================================ */

class addrbook extends Application {
  function process() {
    if ($this->is_guest()) $this->output_403('Гостям запрещается пользоваться адресной книгой');
    return parent::process();
  }
  
  function set_lastmod() {
    $this->lastmod=$this->time;
  }
  
  function action_view() {
    $uid = $this->get_uid();
    'ORDER BY u.display_name'; // TODO: подумать, возможно, сделать возможность сортировки по каким-то другим полям*/
    $sql = 'SELECT u.id, u.login, u.display_name, lv.visit1, u.status, u.gender, CASE WHEN g.custom_title=\'1\' THEN u.title ELSE g.name END AS title, u.avatar, rlb.type AS relback '.
    'FROM '.DB_prefix.'user_ext ue '.
    'CROSS JOIN '.DB_prefix.'relation rl '.
    'CROSS JOIN '.DB_prefix.'group g '.
    'CROSS JOIN '.DB_prefix.'user u '. 
    'LEFT JOIN '.DB_prefix.'last_visit lv ON (oid=0 AND lv.type=\'forum\' AND uid=u.id) '.
    'LEFT JOIN '.DB_prefix.'relation rlb ON (rlb.to_=rl.from_ AND rlb.from_=rl.to_) '.    
    'WHERE rl.from_='.intval($uid).' AND rl.to_=u.id AND rl.type=\'friend\' AND u.id=ue.id AND ue.group_id=g.level '.
    'ORDER BY u.display_name'; // TODO: подумать, возможно, сделать возможность сортировки по каким-то другим полям
    // TODO: переделать выборку, чтобы делалась с использованием userlib
    $this->out->friends = $this->db->select_all($sql);
    $online = $this->get_opt('online_time');
    if (!$online) $online = 15;
    $this->out->lasttime  = $this->time - $online*60; // определяем момент времени, после которого пользователь считается еще находящимся онлайн
    
    $sql = 'SELECT uc.uid, uc.value, uct.icon, uct.c_title, uct.link '.
    'FROM '.DB_prefix.'relation rl, '.DB_prefix.'user_contact uc, '.DB_prefix.'user_contact_type uct '.
    'WHERE rl.from_='.intval($uid).' AND rl.to_=uc.uid AND type=\'friend\' AND uc.cid=uct.cid ';
    $this->out->contacts = $this->db->select_super_hash($sql,'uid');
    
    $this->out->del_key=$this->gen_auth_key($uid,'delete');
    $this->out->add_key=$this->gen_auth_key($uid,'add');
  }
  
  function action_blacklist() {
    $uid = $this->get_uid();
    $sql = 'SELECT u.id, u.login, u.display_name, CASE WHEN g.custom_title=\'1\' THEN u.title ELSE g.name END AS title '.
    'FROM '.DB_prefix.'user u, '.DB_prefix.'user_ext ue, '.DB_prefix.'relation rl, '.DB_prefix.'group g '. 
    'WHERE rl.from_='.intval($uid).' AND rl.to_=u.id AND rl.type=\'ignore\' AND u.id=ue.id AND ue.group_id=g.level '.
    'ORDER BY u.display_name'; // TODO: подумать, возможно, сделать возможность сортировки по каким-то другим полям
    // TODO: подумать, возможно, вынести выборку пользователей в отдельную процедуру в userlib
    $this->out->ignored = $this->db->select_all($sql);

    $this->out->del_key=$this->gen_auth_key($uid,'delete');
    $this->out->add_key=$this->gen_auth_key($uid,'add');    
  }
  
  function action_delete() {
    if (empty($_GET['authkey'])) $this->output_403('Ошибка ключа авторизации!');
    if (empty($_GET['id'])) $this->message('Не указан идентификатор пользователя',3);
    else {
      $uid=$this->get_uid();

      $sql = 'DELETE FROM '.DB_prefix.'relation WHERE "from_"='.intval($uid).' AND "to_"='.intval($_GET['id']);
      $this->db->query($sql);
    }
    if ($this->get_request_type()!=1) {
    $this->message('Пользователь удален!',1);
    $this->redirect($this->referer());
    }
    else $this->output_json(array('result'=>'done'));
  }
  
  function action_add() {
    if (empty($_GET['authkey'])) $this->output_403('Ошибка ключа авторизации!');
    if (empty($_GET['type']) || ($_GET['type']!=='friend' && $_GET['type']!=='ignore')) $this->message('Некорректно указано, куда добавлять пользователя',3);
    elseif (empty($_GET['id']) && empty($_GET['logins'])) $this->message('Не указан идентификатор пользователя',3);
    else {
      if (!empty($_GET['id'])) $ids=array(intval($_GET['id']));
      else {
        $logins = explode(',',$_GET['logins']);
        $count = count($logins);
        $ids = array();
        if (!$count) $this->message('Не указан идентификатор пользователя',3);
        else {
          $userlib = $this->load_lib('userlib',true);
          for ($i=0; $i<$count; $i++) {
            $tmpid = $userlib->get_uid_by_display_name(trim($logins[$i])); 
            if ($tmpid) $ids[]=intval($tmpid);
            else $this->message('Пользователь '.htmlspecialchars($logins[$i]).' не найден!',2);
          }
        }        
      }
      $uid=$this->get_uid();
      // сначала удаляем предыдущие вхождения (на случай, например, если пользователя из друзей сразу переносят в игнор-лист или наоборот)
      if (count($ids)>0) {
        $sql = 'DELETE FROM '.DB_prefix.'relation WHERE "from_"='.intval($uid).' AND "to_" IN ('.join($ids).')'; // ids уже пропущены через intval, поэтому безопасны
        $this->db->query($sql);
      }
      for ($i=0, $count=count($ids); $i<$count; $i++) {
        $data['from_']=$uid;
        $data['to_']=$ids[$i];
        $data['type']=$_GET['type'];
        if ($uid==$ids[$i]) { // проверка на попытки добавить себя в друзья или игнор 
          $this->message('Нельзя добавлять в друзья или игнор-лист самого себя!',2);
          unset($ids[$i]);
        }
        else $this->db->insert_ignore(DB_prefix.'relation',$data); 
      } 
    }
    $count=count($ids);
    if ($count==1) $this->message('Пользователь добавлен!',1);
    elseif ($count>1) $this->message('Пользователи добавлены!',1);
    $this->redirect($this->referer());
  }
  
  function set_title() {
    $result=false;
    if ($this->action==='view') {
      $result=' Адресная книга | '.$this->get_opt('site_title');
    }
    elseif ($this->action==='blacklist') {
      $result=' Список игнорируемых пользователей | '.$this->get_opt('site_title');
    }
    return $result;
  }
  
  function set_location() {
    $result = parent::set_location();
    if ($this->action==='view') {
      $result[]=array('Адресная книга');
    }
    elseif ($this->action==='blacklist') {
      $result[]=array('Список игнорируемых пользователей');
    }
    return $result;
  }  
}
