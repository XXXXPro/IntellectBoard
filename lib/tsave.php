<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Библиотека операций сохранения для тем, сообщений, рейтингов, голосований и т.п.
 *  ================================ */

class Library_tsave extends Library {
/** Сохраняет сообщение в базу. Если id не выставлен, оно сохраняется как новое, а id проставляется, иначе -- обновляется. 
* Никаких проверок прав доступа при этом не выполняется, они должны делаться в action'ах.
* Некоторые данные (например, время отправки или количество редактирований) фиксируются автоматически, если не включен режим переопределения.
* @param $data array Хеш-массив с данными, текст сообщения должно быть в ключе text (он сохраняется в таблицу text, остальное идет в таблицу post)
* @param $override boolean Режим переопределения. Если он выключен (а это сделано по умолчанию), то данные об авторе сообщения, времени отправки, 
* количестве редактирований фиксируются автоматически, если включен -- могут быть выставлены заранее. 
**/
  function save_post(&$data,$override=false) {
    $text = $data['text'];
    unset($data['text']);  
    if (empty($data['typograf'])) $data['typograf']="0";
    if (empty($data['html'])) $data['html']="0";
    if (empty($data['smiles'])) $data['smiles']="0";
    if (empty($data['links'])) $data['links']="0";
    if (empty($data['locked'])) $data['locked']="0";
    $data['status']=(string)intval($data['status']);
    
    if (empty($data['id'])) { // если сообщение новое
      if (empty($data['postdate'])) $data['postdate']=Library::$app->time;
      if (empty($data['tid']) || !$override) $data['tid']=Library::$app->topic['id'];
      if (empty($data['ip']) || !$override)  $data['ip']=Library::$app->get_ip();
      if (empty($data['author']) || (!$override && !Library::$app->is_guest()))  $data['author']=Library::$app->get_username();
      if (empty($data['uid']) || !$override) $data['uid']=Library::$app->get_uid();
      $result=Library::$app->db->insert(DB_prefix.'post',$data);
      if ($result) $data['id']=Library::$app->db->insert_id();
    }
    else {
      if (empty($data['editcount']) || !$override) $data['editcount']=isset($data['editcount']) ? $data['editcount']+1 : 1 ;
      if (empty($data['editor_id']) || !$override) $data['editor_id']=Library::$app->get_uid();
      // значения по умолчанию
      $result = Library::$app->db->update(DB_prefix.'post',$data,'id='.intval($data['id']));
      $sql = 'DELETE FROM '.DB_prefix.'text WHERE type=16 AND id='.intval($data['id']);
      Library::$app->db->query($sql);    
    }
    if ($result) {
      $txtdata=array('id'=>$data['id'],'type'=>16,'data'=>$text,'tx_lastmod'=>Library::$app->time); // 16 -- код форумных сообщений в универсальном хранилище текстов (таблице text) 
      Library::$app->db->insert(DB_prefix.'text',$txtdata);
    }
    return $result;
  }
  
  /** Сохраняет информацию о теме в базу
  * Никаких проверок прав доступа при этом не выполняется, они должны делаться в action'ах.
  * Некоторые данные (в частности, lastmod и номер форума для новой темы) фиксируются автоматически, если не включен режим переопределения
  * @param $data array Хеш-массив с данными темы
  * @param $override boolean Режим переопределения.
  **/
  function save_topic(&$data,$override=false) {
    if (empty($data['id'])) { // если создаем новую тему
      if (empty($data['fid']) || !$override) $data['fid']=Library::$app->forum['id']; // если не включено переопределение, то тема создается в текущем разделе
      if (empty($data['lastmod']) || !$override) $data['lastmod']=Library::$app->time;
      if (empty($data['last_post_time']) || !$override) $data['last_post_time']=Library::$app->time;
      if (empty($data['owner']) || !$override) {        
        $data['owner']= Library::$app->forum['selfmod']==1 ? Library::$app->get_uid() : 0; // если режим самомодерации включён, создатель темы сразу назначается её куратором
      }
      if (empty($data['sticky']))$data['sticky']="0";
      if (empty($data['sticky_post']))$data['sticky_post']="0";
      if (empty($data['locked']))$data['locked']="0";
      $result=Library::$app->db->insert(DB_prefix.'topic',$data);
      $data['id']=Library::$app->db->insert_id();
    }
    else {
      if (empty($data['lastmod']) || !$override) $data['lastmod']=Library::$app->time;  
      $result=Library::$app->db->update(DB_prefix.'topic',$data,'id='.intval($data['id']));      
    }
    return $result;
  }

  /** Сохранение данных о проголосовавшем пользователе **/
  function save_vote($data,$override=false) {
    if (empty($data['uid']) || !$override) $data['uid']=Library::$app->get_uid();
    if (empty($data['tid']) || !$override) $data['tid']=Library::$app->topic['id'];
    if (empty($data['time']) || !$override) $data['time']=Library::$app->time;
    if (empty($data['ip']) || !$override) $data['ip']=Library::$app->get_ip();
    $result=Library::$app->db->insert(DB_prefix.'vote',$data);
    if ($result) {
      $sql = 'UPDATE '.DB_prefix.'poll_variant SET count=count+1 WHERE id='.intval($data['pvid']).' AND tid='.intval($data['tid']);
      Library::$app->db->query($sql);
      $sql = 'UPDATE '.DB_prefix.'topic SET lastmod='.intval(Library::$app->time).' WHERE id='.intval($data['tid']);
      Library::$app->db->query($sql);      
    }    
    return $result; 
  }
  
  /** Сохранение данных об изменении рейтинга сообщения **/
  function save_rating($data,$override=false) {
    if (empty($data['uid']) || !$override) $data['uid']=Library::$app->get_uid();
    if (empty($data['time']) || !$override) $data['time']=Library::$app->time;
    if (empty($data['ip']) || !$override) $data['ip']=Library::$app->get_ip();
    if (!empty($data['valued'])) { $value=true; unset($data['valued']); }
    else $value=false;
    if (!empty($data['flood'])) { $flood=true; unset($data['flood']); }
    else $flood=false;
    $tid = $data['tid'];
    unset($data['tid']);
    $uid_rated=$data['uid_rated'];
    unset($data['uid_rated']); // 
      
    $result=Library::$app->db->insert(DB_prefix.'rating',$data);
    if ($result) {
      $sql = 'UPDATE '.DB_prefix.'post SET rating=rating+'.floatval($data['value']);
      if ($value) $sql.=', value=\'1\'';
      elseif ($flood) $sql.=', value=\'-1\'';
      $sql.=' WHERE id='.intval($data['id']);
      Library::$app->db->query($sql);
      $sql = 'UPDATE '.DB_prefix.'user_ext SET rating=rating+'.floatval($data['value']).' WHERE id='.intval($uid_rated);
      Library::$app->db->query($sql);
      if ($value || $flood) { // если сообщение объявлено флудом или же ценным, нужен пересчет темы
        $premodlib = Library::$app->load_lib('moderate',false);
        if ($premodlib) $premodlib->topic_resync($tid);
      }
      else { // иначе просто обновляем время последней модификации темы на текущее, чтобы при обновлении рейтинги отобразились корректно 
        $sql = 'UPDATE '.DB_prefix.'topic SET lastmod='.intval(Library::$app->time).' WHERE id='.intval($tid);
        Library::$app->db->query($sql); 
      }
    }
    return $result; 
  }

  /** Сохранение опроса и вариантов для него. Варианты с пустым текстом удаляются **/
  function save_poll($tid,$poll,$votes) {
    $data['question']=$poll['question'];
    $data['id']=$tid;
    $data['endtime']=empty($poll['limit']) ? 0 : Library::$app->time+$poll['period']*24*60*60;
    if (empty($poll['edit'])) Library::$app->db->insert(DB_prefix.'poll', $data);
    else Library::$app->db->update(DB_prefix.'poll', $data,'id='.intval($tid));
    foreach ($votes as $id=>$vote) {
      if ($id==0 && is_array($vote)) {
        for ($i=0,$count=count($vote);$i<$count;$i++) {
          $vote[$i]['tid']=$tid;
          if ($vote[$i]['text']) Library::$app->db->insert(DB_prefix.'poll_variant', $vote[$i]);
        }
      } 
      elseif ($vote['text']=='') { // если текст опроса пуст, удаляем его
        $sql = 'DELETE FROM '.DB_prefix.'vote WHERE tid='.intval($tid).' AND pvid='.intval($id);
        Library::$app->db->query($sql);
        $sql = 'DELETE FROM '.DB_prefix.'poll_variant WHERE tid='.intval($tid).' AND id='.intval($id);
        Library::$app->db->query($sql);         
      }
      else { // иначе вносим изменения
        $vote['tid']=$tid; // в целях безопасности, чтобы нельзя было подменить tid
        unset($vote['count']); // для защиты от изменения счетчика
        Library::$app->db->update(DB_prefix.'poll_variant', $vote, 'id='.intval($id).' AND tid='.intval($tid));
      }
    }
  }
  
  /** Удаление голосования **/
  function delete_vote($tid) {
    $sql = 'DELETE FROM '.DB_prefix.'vote WHERE tid='.intval($tid);
    Library::$app->db->query($sql);
    $sql = 'DELETE FROM '.DB_prefix.'poll_variant WHERE tid='.intval($tid);
    Library::$app->db->query($sql);
    $sql = 'DELETE FROM '.DB_prefix.'poll WHERE id='.intval($tid);
    Library::$app->db->query($sql);
  }
    
  function increment($pdata,$newtopic=false,$lock=false) {
    if ($newtopic) { // если создана новая тема, то изменяем данные в форуме, а в теме обновляем только last_post_id, так как на момет ее создания id сообщения не был известен, а все остальное должно было быть выставлено сразу, в значениях по умолчанию
      $sql = 'UPDATE '.DB_prefix.'forum '.
      'SET lastmod='.intval(Library::$app->time).', last_post_id='.intval($pdata['id']).', topic_count=topic_count+1, post_count=post_count+1 '.
      'WHERE id='.intval(Library::$app->forum['id']);
      Library::$app->db->query($sql);
      $sql = 'UPDATE '.DB_prefix.'topic '. 
      'SET first_post_id='.intval($pdata['id']).', last_post_id='.intval($pdata['id']).',  post_count=post_count+1 ';
      if ($lock) $sql.=', locked=\'1\' ';      
      $sql.='WHERE id='.intval($pdata['tid']);
      Library::$app->db->query($sql);
    }
    else {
      $sql = 'UPDATE '.DB_prefix.'forum '.
      'SET lastmod='.intval(Library::$app->time).', last_post_id='.intval($pdata['id']).', post_count=post_count+1  ';
      $sql.='WHERE id='.intval(Library::$app->forum['id']);
      Library::$app->db->query($sql);
      
      $sql = 'UPDATE '.DB_prefix.'topic '.
      'SET lastmod='.intval(Library::$app->time).', last_post_time='.intval(Library::$app->time).', last_post_id='.intval($pdata['id']).', post_count=post_count+1 ';
      if ($lock) $sql.=', locked=\'1\' ';      
      $sql.='WHERE id='.intval($pdata['tid']);
      Library::$app->db->query($sql);      
    }
  }

  /** Получение данных о собщении из формы, проверка необходимых прав и выставление недостающих настроек
  * @param $raw array Массив данных из формы (обычно берется из $_POST['post'])
  **/
  function get_post_data($raw,$perms) {
    $result['html']=(!empty($raw['html'])) ? $perms['html'] : '0';
    $result['bcode']=(!empty($raw['bcode'])) ? $perms['bcode'] : '0';
    $result['smiles']=(!empty($raw['smiles']) && $perms['smiles']>0) ? '1' : '0';
    $result['links']=(!empty($raw['links'])) ? '1' : '0';
    $result['typograf']=(!empty($raw['typograf'])) ? '1' : '0';
    $result['value']=(!empty($raw['value']) && $perms['value']) ? $raw['value'] : '0'; // сообщение могут делать ценным или флудом только те, у кого на это есть разрешение, остальным сбрасываем значение в ноль
    if ($perms['lock']) $result['locked']=(!empty($raw['locked'])) ? '1' : '0'; // только модераторы могут закрывать редактирование сообщения    
    $result['text']=$raw['text'];
    if (!empty($raw['author'])) $result['author']=$raw['author'];
    if (!empty($raw['postdate']) && $perms['postdate']) { // если есть права на изменение даты сообщения, то заполняем ее, иначе — оставляем пустой, и по умолчанию будет взято текущее время в save_topic      
      $result['postdate']=strtotime($raw['postdate']);
      if ($result['postdate']) $result['postdate'] = $result['postdate'] - Library::$app->get_opt('timezone', 'user'); // корректировка с учетом часового пояса
    }
    // TODO: идентификатор сообщения и, возможно, другие данные
    return $result;
  }
  
  /** Получение данных о теме, проверка необходимых прав и выставление недостающих настроек 
  * @param $raw array Массив данных из формы (обычно берется из $_POST['topic'])
  **/
  function get_topic_data($raw,$perms) {
    $result['sticky']=(!empty($raw['sticky']) && Library::$app->is_moderator()) ? true: false;
    if ($perms['sticky_post']) $result['sticky_post']=(!empty($raw['sticky_post'])) ? true : false;
    elseif (!Library::$app->forum['sticky_post']==0) $result['sticky_post']=false;
    else $result['sticky_post']=true;
    if ($perms['lock']) $result['locked']=(!empty($raw['locked'])) ? true : false; // только модераторы могут закрывать тему галочкой в форме
    
    $fields = array('title','descr','hurl');
    foreach ($fields as $field) $result[$field]=$raw[$field];
    return $result;
  }
}    
