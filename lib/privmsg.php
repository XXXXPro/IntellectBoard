<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Библиотека для работы с личными сообщениями
 *  ================================ */
 
class Library_privmsg extends Library {
  function count_threads($cond) {
    if (empty($cond)) trigger_error('Условие выборки не может быть пустым',E_USER_ERROR);
    $where = '1=1';
    if (!empty($cond['uid'])) $where.=' AND uid='.intval($cond['uid']);
    if (!empty($cond['lasttime'])) $where.= ' AND last_post_date>'.intval($cond['lasttime']);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'privmsg_thread_user WHERE '.$where; // получаем общее количество тем
    return Library::$app->db->select_int($sql);    
  }
  
  /** Получение списка цепочек сообщений
  * @param array $cond Массив параметров для выборки. Может содержать:
  *   pm_thread -- идентификатор конкретной цепочки
  *   uid -- идентификатор пользователя, цепочки которого должны быть выбраны
  *   users -- признак того, что необходимо также получить данные об именах пользователей, участвующих в цепочке обсуждения
  * @return array Массив с данными о цепочках сообщений **/
  function get_threads($cond) {
    if (empty($cond)) trigger_error('Условие выборки не может быть пустым',E_USER_ERROR);
    $where = '1=1';
    
    if (!empty($cond['uid'])) $where.=' AND uid='.intval($cond['uid']);
    if (!empty($cond['pm_thread'])) $where.=' AND id='.intval($cond['pm_thread']);

    $sql = 'SELECT pt.id,  pt.title, pu.total, pu.last_post_date, pu.unread '. //
    'FROM '.DB_prefix.'privmsg_thread_user pu, '.DB_prefix.'privmsg_thread pt ';
    $sql.='WHERE '.$where.' AND pt.id=pu.pm_thread ';
    if (empty($cond['order'])) $cond['order']='pu.last_post_date';
    $cond['desc']=(isset($cond['desc']) && $cond['desc']==false) ? ' ASC' : ' DESC'; 
    $sql.='ORDER BY '.$cond['order'].$cond['desc'];
    
    if (empty($cond['start'])) $cond['start']=false;
    if (empty($cond['perpage'])) $cond['perpage']=false;
    $threads = Library::$app->db->select_all($sql,$cond['start'],$cond['perpage']);
    
    if (!empty($cond['users'])) { // если запрошено извлечение данных о пользователях
      $ids =array(); // выбираем все ID цепочек сообщений
      for ($i=0, $count=count($threads); $i<$count; $i++) {
        $ids[]=$threads[$i]['id'];
        $threads[$i]['users']=array();
      }
      if (!empty($ids)) {
        $sql = 'SELECT u.id AS uid, u.display_name, pm_thread ';
        if (!empty($cond['relations'])) $sql.=', ur.type ';
        $sql.='FROM '.DB_prefix.'privmsg_thread_user pu, '.DB_prefix.'user u ';
        if (!empty($cond['relations'])) $sql.='LEFT JOIN '.DB_prefix.'relation ur ON (u.id=ur.from_ AND ur.to_='.intval($cond['uid']).') ';
        $sql.='WHERE pm_thread IN ('.join(',',$ids).') AND pu.uid=u.id';
        $udata = Library::$app->db->select_super_hash($sql,'pm_thread');
        for ($i=0, $count=count($threads); $i<$count; $i++) {
          $threads[$i]['users']=$udata[$threads[$i]['id']];
        }
      }
    }
    
    return $threads;    
  }
  
  function get_messages($cond) {
    if (empty($cond)) trigger_error('Условие выборки не может быть пустым',E_USER_ERROR);
    $where = 'pp.id=pl.pm_id';
    
    if (!empty($cond['pm_thread'])) $where.=' AND pp.pm_thread='.intval($cond['pm_thread']);
    if (!empty($cond['uid'])) $where.=' AND pl.uid='.intval($cond['uid']);
    if (!empty($cond['postdate'])) $where.=' AND pp.postdate>'.intval($cond['postdate']);

    $sql = 'SELECT pp.id, pp.uid, text, postdate, html, bcode, smiles, links, typograf, u.display_name, u.avatar '.
    'FROM '.DB_prefix.'privmsg_link pl, '.DB_prefix.'privmsg_post pp '.
    'LEFT JOIN '.DB_prefix.'user u ON (pp.uid=u.id) '.
    'WHERE '.$where.' ';

    if (empty($cond['order'])) $cond['order']='pp.postdate';
    $cond['desc']=(!isset($cond['desc']) || $cond['desc']==false) ? ' ASC' : ' DESC'; 
    $sql.='ORDER BY '.$cond['order'].$cond['desc'];
    
    if (empty($cond['start'])) $cond['start']=false;
    if (empty($cond['perpage'])) $cond['perpage']=false;
    
    $pm = Library::$app->db->select_all($sql,$cond['start'],$cond['perpage']);
    return $pm; 
  }
  
  function save_message($data,$override=false) {
    //Library::$app->db->begin();
    $newtopic = false;    
    if (empty($data['post']['pm_thread'])) { // если для сообщения еще не создана еще цепочка сообщений
      $newtopic= true;
      // создаем цепочку (thread)
      $thdata['title']=$data['thread']['title'];
      Library::$app->db->insert(DB_prefix.'privmsg_thread',$thdata);
      $data['post']['pm_thread']=Library::$app->db->insert_id();

      $thudata['pm_thread']=$data['post']['pm_thread'];
      // сохраняем привязку thread к пользователям и задаем начальные данные о количестве сообщений
      for ($i=0, $count=count($data['uids']); $i<$count; $i++) {
        $thudata['uid']=$data['uids'][$i];
        Library::$app->db->insert(DB_prefix.'privmsg_thread_user',$thudata);
      }
    }

    // TODO: добавить фильтр
    if (!$override || empty($data['post']['postdate'])) $data['post']['postdate']=Library::$app->time;
    if (!$override || empty($data['post']['uid'])) $data['post']['uid']=Library::$app->get_uid();
    Library::$app->db->insert(DB_prefix.'privmsg_post',$data['post']); // сохраняем собственно сообщение

    $thldata['pm_id']=Library::$app->db->insert_id();
    for ($i=0, $count=count($data['uids']); $i<$count; $i++) {
      $thldata['uid']=$data['uids'][$i];
      Library::$app->db->insert(DB_prefix.'privmsg_link',$thldata);
    }
    
    $sql = 'UPDATE '.DB_prefix.'privmsg_thread_user SET '.
      'total=total+1, unread=CASE WHEN uid='.intval(Library::$app->get_uid()).' THEN unread ELSE unread+1 END, last_post_date='.intval(Library::$app->time).' '.
      'WHERE pm_thread='.intval($data['post']['pm_thread']).' AND '.Library::$app->db->array_to_sql($data['uids'],'uid');
    Library::$app->db->query($sql);
       
    return array($data['post']['pm_thread'],$thldata['pm_id']); // возвращаем массив из номера темы и номера добавленного сообщения
    
    //Library::$app->db->commit();  
  }  
  
  /** Отписывание пользователя от темы с удалением ссылок на его сообщения в ней **/
  function unsubscribe($thread,$uid) {
    $sql = 'SELECT id FROM '.DB_prefix.'privmsg_post WHERE pm_thread='.intval($thread);
    $ids = Library::$app->db->select_all_numbers($sql);   
    if (!empty($ids)) {
      $sql = 'DELETE FROM '.DB_prefix.'privmsg_link WHERE '.Library::$app->db->array_to_sql($ids,'pm_id').' AND uid='.intval($uid);
      Library::$app->db->query($sql);
    }
    $sql = 'DELETE FROM '.DB_prefix.'privmsg_thread_user WHERE pm_thread='.intval($thread).' AND uid='.intval($uid);
    Library::$app->db->query($sql);
    $this->clear_unused($thread); // если в результате остались бесхозные сообщения, или даже тема целиком, подчищаем их
  }
  
  /** Выборочное удаление нескольких сообщений без отписывания от темы **/
  function delete($thread,$ids,$uid) {
    $sql = 'SELECT id FROM '.DB_prefix.'privmsg_post WHERE pm_thread='.intval($thread).' AND '.Library::$app->db->array_to_sql($ids,'id');
    $trust_ids = Library::$app->db->select_all_numbers($sql);
    if (!empty($trust_ids)) {
      $sql = 'DELETE FROM '.DB_prefix.'privmsg_link WHERE '.Library::$app->db->array_to_sql($trust_ids,'pm_id').' AND uid='.intval($uid);
      Library::$app->db->query($sql);
      $this->clear_unused($thread); // если в результате остались бесхозные сообщения, подчищаем их
      $sql = 'SELECT COUNT(*) AS total, MAX(postdate) AS last FROM '.DB_prefix.'privmsg_link pl, '.DB_prefix.'privmsg_post pp '.
      'WHERE pl.pm_id = pp.id AND pp.pm_thread='.intval($thread).' AND pl.uid='.intval($uid);
      $update = Library::$app->db->select_row($sql);
      $sql = 'UPDATE '.DB_prefix.'privmsg_thread_user SET total='.intval($update['total']).', unread=0, last_post_date='.intval($update['last']).' WHERE pm_thread='.intval($thread).' AND uid='.intval($uid);
      Library::$app->db->query($sql);
    }
  }
  
  /** Удаление тем и сообщений, которые недоступны ни одному из участников обсуждения **/
  function clear_unused($thread) {
    $sql = 'SELECT pp.id FROM '.DB_prefix.'privmsg_post pp '.
    ' LEFT JOIN '.DB_prefix.'privmsg_link pl ON (pl.pm_id=pp.id) '.
    ' WHERE pp.pm_thread='.intval($thread).' '.
    ' GROUP BY pp.id HAVING COUNT(pl.pm_id)=0';
    $ids = Library::$app->db->select_all_numbers($sql);
    if (is_array($ids) && count($ids)>0) {
      $sql = 'DELETE FROM '.DB_prefix.'privmsg_post WHERE '.Library::$app->db->array_to_sql($ids,'id');
      Library::$app->db->query($sql);
    }
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'privmsg_thread_user WHERE pm_thread='.intval($thread);
    $count = Library::$app->db->select_int($sql);
    if ($count==0) {
      $sql = 'DELETE FROM '.DB_prefix.'privmsg_thread WHERE id='.intval($thread);
      Library::$app->db->query($sql);
    }
  }
  
  /** Отметка темы или вообще всех сообщений как прочитанных **/
  function mark_read($uid,$thread=false) {
    $sql = 'UPDATE '.DB_prefix.'privmsg_thread_user SET unread=0 WHERE uid='.intval($uid);
    if ($thread) $sql.=' AND pm_thread='.intval($thread);
    Library::$app->db->query($sql);    
  }
    
  function get_permissions() {
    return array('html'=>false, 'bcode'=>true, 'smiles'=>true);
  }
  
  function set_new_post($perms) {
    $perms['links']=true;
    return $perms;
  }  
}
