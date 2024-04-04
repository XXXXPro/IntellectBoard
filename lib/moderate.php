<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Библиотека модераторских действий и пересинхронизации тем и разделов
 *  ================================ */

 class Library_moderate extends Library {   
    /** Перенос сообщений в другую тему. 
    * @param $pids array Массив ID сообщений, которые требуется перенести
    * @param $old_topic integer Номер темы, из которой осуществляется перенос
    * @param $new_topic integer Номер темы, в которую осуществляется перенос
    * @param $params array Хеш с настройками переноса темы:
    * nosync -- не проводить пересинхронизацию тем и разделов
    * nolog -- не делать записи в лог модерации
    * nomsg -- не вставлять сообщения от имени System том, что был сделан перенос 
    * 
    **/
    function move_posts($pids,$old_topic,$new_topic,$opts=array()) {
       $sql = 'UPDATE '.DB_prefix.'post SET tid='.intval($new_topic).' '.
       'WHERE '.Library::$app->db->array_to_sql($pids,'id').' AND tid='.intval($old_topic);
       $result=Library::$app->db->query($sql);
     $result = $result && Library::$app->db->affected_rows();       
       
       if ($result) {
          if (empty($opts['nosync']) || empty($opts['nolog']) || empty($opts['nomsg'])) { // если синхронизация тем не отключена
             if (!empty(Library::$app->topic) && Library::$app->topic['id']==$old_topic) { // если тема совпадает с той, которая загружена как текущая, берем данные оттуда, чтобы не делать лишний запрос
                $old_data = Library::$app->topic;                 
             }
             else {          
              $sql = 'SELECT fid, t.title, CONCAT(f.hurl,\'/\',CASE WHEN t.hurl!=\'\' THEN t.hurl ELSE CAST(t.id AS CHAR(11)) END,\'/\') AS full_hurl '.
              'FROM '.DB_prefix.'topic t, '.DB_prefix.'forum f '.
              'WHERE t.id='.intval($old_topic).' AND f.id=t.fid';
              $old_data = Library::$app->db->select_row($sql);
            }
         $sql = 'SELECT fid, t.title, CONCAT(f.hurl,\'/\',CASE WHEN t.hurl!=\'\' THEN t.hurl ELSE CAST(t.id AS CHAR(11)) END,\'/\') AS full_hurl '.
         'FROM '.DB_prefix.'topic t, '.DB_prefix.'forum f '.
         'WHERE t.id='.intval($new_topic).' AND f.id=t.fid';
           $new_data = Library::$app->db->select_row($sql);
           
           if (empty($opts['nomsg'])) { // если не отключена вставка сообщений о переносе
              $tsave=Library::$app->load_lib('tsave',false);
              $sql = 'SELECT MIN(postdate) FROM '.DB_prefix.'post '.
              'WHERE '.Library::$app->db->array_to_sql($pids,'id').' AND tid='.intval($new_topic).' AND status=\'0\'';
              $mintime = Library::$app->db->select_int($sql);
              if ($mintime) {
             $pdata_old = array('tid'=>$old_topic,
               'postdate'=>$mintime-1,
               'uid'=>2,
               'author'=>'System',
               'html'=>1,
               'text'=>'Некоторые сообщения перенесены в тему &laquo;<a href="'.Library::$app->url($new_data['full_hurl']).'">'.htmlspecialchars($new_data['title']).'</a>&raquo;');
             if ($tsave) $tsave->save_post($pdata_old,true);
             $pdata_new = array('tid'=>$new_topic,
               'postdate'=>$mintime-1,
               'uid'=>2,
               'author'=>'System',
               'html'=>1,
               'text'=>'К данной теме присоединены сообщения из темы &laquo;<a href="'.Library::$app->url($old_data['full_hurl']).'">'.htmlspecialchars($old_data['title']).'</a>&raquo;');           
             if ($tsave) $tsave->save_post($pdata_new,true);
           }
           }
            
         if (empty($opts['nosync'])) { // если в настройках не указан отказ от пересинхронизации
              $this->topic_resync($old_topic);
              $this->topic_resync($new_topic);
             $this->forum_resync($old_data['fid']);
             if ($old_data['fid']!==$new_data['fid']) $this->forum_resync($new_data['fid']); // проверка нужна для того, чтобы дважды не пересинхронизировать один и тот же форум
           }
         } 
         if (empty($opts['nolog'])) { // если не отключео сохранение в лог
            $logdata=array('type'=>3,'tid'=>$old_topic,'data'=>array('pids'=>$pids,'tid'=>$new_topic));
            if (!empty($pdata_old)) $logdata['data']['old_move_msg']=$pdata_old['id'];
            if (!empty($pdata_new)) $logdata['data']['new_move_msg']=$pdata_new['id'];   
           $this->log_action($logdata);
         }
       }
       return $result;
    }
    
    /** Массовое изменение свойств сообщений. 
    * Изменения признаков блокировки сообщений, статуса удален/на модерации и т.п. очень похожи, поэтому имеет смысл сделать общую процедуру для них всех.
    * @param $pids array Хеш, где ключами являются ID сообщений, а значения содержат то, что будет записано в столбец, имя которого указано в $set_name
    * @param $tid integer Номер темы, в которой находятся обрабатываемые сообщения
    * @param $set_name string Имя столбца, в который будут записаны изменяемые данные.
    * @param $opcode integer Код действия для записи в лог модераторских действий
    * @param $params array Хеш с настройками переноса темы:    
    * nosync -- не проводить пересинхронизацию разделов
    * nolog -- не делать записи в лог модерации
    **/
    private function change_posts_state($pids,$tid,$set_name,$opcode,$opts=array()) {
       $vkeys = array();
       foreach ($pids as $key=>$value) $vkeys[$value][]=$key; // пе
       
       if (empty($opts['nolog'])) {
          $pkeys = array_keys($pids);
         $sql = 'SELECT id,"'.Library::$app->db->slashes($set_name).'" FROM '.DB_prefix.'post '.
         'WHERE '.Library::$app->db->array_to_sql($pkeys,'id').' AND tid='.intval($tid);
         $undo = Library::$app->db->select_simple_hash($sql);
       }
       
       $result = false;
       foreach ($vkeys as $val=>$ids) {
         $sql = 'UPDATE '.DB_prefix.'post SET "'.Library::$app->db->slashes($set_name).'"=\''.Library::$app->db->slashes($val).'\' '.
         'WHERE '.Library::$app->db->array_to_sql($ids,'id').' AND tid='.intval($tid);
         $result = (Library::$app->db->query($sql) && Library::$app->db->affected_rows()) || $result;
     }
     
     if (empty($opts['nosync'])) { // если в настройках не указан отказ от пересинхронизации
       $this->topic_resync($tid);
       if (!empty(Library::$app->topic['id']) && $tid==Library::$app->topic['id']) $fid=Library::$app->topic['fid']; // если запрос выполнен для текущей темы, то берем ее раздел из объекта
       else {
          $sql = 'SELECT fid FROM '.DB_prefix.'topic WHERE id='.intval($tid);
          $fid = Library::$app->db->select_int($sql);
       }
       $this->forum_resync($fid);
     }
       
       if ($result) {
          // при изменении статуса сообщений 
         if (empty($opts['nolog'])) { // если не отключео сохранение в лог
            $logdata=array('type'=>$opcode,'tid'=>$tid,'data'=>$undo);
            if (count($pids)===1) $logdata['pid']=$pkeys[0]; // если затронуто всего одно сообщение, то запись в логе будет приписана ему
           $this->log_action($logdata);
         }
       }
       return $result;
    }
    
    /** Блокировка/разблокировка редактирования сообщений **/
    function lock_posts($pids,$tid,$opts=array()) {
       if (!isset($opts['nosync'])) $opts['nosync']=true; // при блокировке/разблокировке сообщений нет смысла делать пересинхронизацию по умолчанию
       return $this->change_posts_state($pids,$tid,'locked',4);
    }
    
    /** Изменение состояния сообщений (нормальное, на премодерации, удалено) **/
    function status_posts($pids,$tid,$opts=array()) {
      if (!isset($opts['nolog'])) $opts['nolog']=true; // изменение статуса сообщений не логгируется, так как откат производится не через лог, а через Корзину      
       $result=$this->change_posts_state($pids,$tid,'status',2,$opts);
       if ($result && empty($opts['nousersync'])) {
         // обновляем время последней модификации сообщения при его удалении/восстановлении 
         // это нужно, чтобы не требовалось хранить отдельную дату удаления (дата удаления нужна для "очистки корзины" в АЦ)
         $sql='UPDATE '.DB_prefix.'text SET tx_lastmod='.intval(Library::$app->time).' WHERE type=16 AND '.Library::$app->db->array_to_sql(array_keys($pids),'id');
         Library::$app->db->query($sql);
         
          $uids = $this->get_post_owners($tid,array_keys($pids));
          $userlib = Library::$app->load_lib('userlib',false);
          if ($userlib) for ($i=0, $count=count($uids);$i<$count;$i++) $userlib->user_resync($uids[$i]);
       }
       return $result;
    }
    
    /** Перенос тем в другой раздел  
    * @param $tids array Массив ID тем, которые требуется перенести
    * @param $old_forum integer Номер темы, из которой осуществляется перенос
    * @param $new_forum integer Номер темы, в которую осуществляется перенос
    * @param $params array Хеш с настройками переноса темы:
    * nosync -- не проводить пересинхронизацию разделов
    * nolog -- не делать записи в лог модерации
    **/
    function move_topics($tids,$old_forum,$new_forum,$opts=false) {
       $sql = 'UPDATE '.DB_prefix.'topic SET fid='.intval($new_forum).' '.
       'WHERE '.Library::$app->db->array_to_sql($tids,'id').' AND fid='.intval($old_forum);
       $result=Library::$app->db->query($sql);
     $result = $result && Library::$app->db->affected_rows();
       
       if ($result) {           
       if (empty($opts['nosync'])) { // если в настройках не указан отказ от пересинхронизации
         $this->forum_resync($old_forum);
         if ($old_forum!==$new_forum) $this->forum_resync($new_forum); // проверка нужна для того, чтобы дважды не пересинхронизировать один и тот же форум
       }
         if (empty($opts['nolog'])) { // если не отключео сохранение в лог
            $logdata=array('type'=>16,'fid'=>$old_forum,'data'=>array('tids'=>$tids,'fid'=>$new_forum));
            if (count($tids)===1) $logdata['tid']=$tids[0]; // если тема всего одна, то  
           $this->log_action($logdata,true);
         }
       }
       return $result;
    }
    
    /** Массовое изменение свойств сообщений. 
    * Изменения признаков блокировки сообщений, статуса удален/на модерации и т.п. очень похожи, поэтому имеет смысл сделать общую процедуру для них всех.
    * @param $pids array Хеш, где ключами являются ID сообщений, а значения содержат то, что будет записано в столбец, имя которого указано в $set_name
    * @param $tid integer Номер темы, в которой находятся обрабатываемые сообщения
    * @param $set_name string Имя столбца, в который будут записаны изменяемые данные.
    * @param $opcode integer Код действия для записи в лог модераторских действий
    * @param $params array Хеш с настройками переноса темы:    
    * nosync -- не проводить пересинхронизацию разделов
    * nolog -- не делать записи в лог модерации
    **/
    private function change_topics_state($tids,$fid,$set_name,$opcode,$opts=false) {
       $vkeys = array();
       foreach ($tids as $key=>$value) $vkeys[$value][]=$key; // пе
       
       if (empty($opts['nolog'])) {
          $tkeys = array_keys($tids);
         $sql = 'SELECT id,"'.Library::$app->db->slashes($set_name).'" FROM '.DB_prefix.'topic '.
         'WHERE '.Library::$app->db->array_to_sql($tkeys,'id').' AND fid='.intval($fid);
         $undo = Library::$app->db->select_simple_hash($sql);
       }
       
       $result = false; // в этой переменной будем отслеживать, были ли сделаны хоть какие-то изменения в БД
       foreach ($vkeys as $val=>$ids) {
         $sql = 'UPDATE '.DB_prefix.'topic SET "'.Library::$app->db->slashes($set_name).'"=\''.Library::$app->db->slashes($val).'\' '.
         'WHERE '.Library::$app->db->array_to_sql($ids,'id').' AND fid='.intval($fid);
         $result = (Library::$app->db->query($sql) && Library::$app->db->affected_rows()) || $result;
     }
     
     if (empty($opts['nosync'])) { // если в настройках не указан отказ от пересинхронизации
       $this->forum_resync($fid);
     }
       
       if ($result) {
          // при изменении статуса сообщений 
         if (empty($opts['nolog'])) { // если не отключео сохранение в лог
            $logdata=array('type'=>$opcode,'fid'=>$fid,'data'=>$undo);
            if (count($tids)===1) $logdata['tid']=$tkeys[0]; // если затронуто всего одно сообщение, то запись в логе будет приписана ему  
           $this->log_action($logdata,true);
         }
       }
       return $result;
    }

    /** Блокировка/разблокировка редактирования тем **/
    function lock_topics($tids,$fid,$opts=false) {
       if (!isset($opts['nosync'])) $opts['nosync']=true; // при блокировке/разблокировке сообщений нет смысла делать пересинхронизацию по умолчанию
       return $this->change_topics_state($tids,$fid,'locked',18,$opts);
    }
    
    /** Приклеивание/ отклеивание тем **/
    function stick_topics($tids,$fid,$opts=false) {
      if (!isset($opts['nosync'])) $opts['nosync']=true; // при приклеивании тем нет смысла делать пересинхронизацию по умолчанию
      return $this->change_topics_state($tids,$fid,'sticky',19,$opts);
    }
    
    /** Приклеивание/ отклеивание первого сообщения в теме **/
    function stick_posts($tids,$fid,$opts=false) {
      if (!isset($opts['nosync'])) $opts['nosync']=true; 
      return $this->change_topics_state($tids,$fid,'sticky_post',20,$opts);
    }
    
    /** Добавление/удаление тем из "Избранного" форума **/
    function fav_topics($tids,$fid,$opts=false) {
      if (!isset($opts['nosync'])) $opts['nosync']=true; // при изменении статуса «В Избранном» нет необходимости в пересинхронизации
      return $this->change_topics_state($tids,$fid,'favorites',21,$opts);
    }
    
    /** Изменение состояния тем целиком (нормальное, на премодерации, удалено) **/
    function status_topics($tids,$fid,$opts=false) {
//      if (!isset($opts['nolog'])) $opts['nolog']=true; // TODO: пока откат удаления темы целиком будет делаться не через Корзину, а через Лог действий, но над этим еще стоит подумать
       $result=$this->change_topics_state($tids,$fid,'status',17,$opts);
       if ($result) {
         $uids = $this->get_post_owners($tid);
         $userlib = Library::$app->load_lib('userlib',false);
         if ($userlib) for ($i=0, $count=count($uids);$i<$count;$i++) $userlib->user_resync($uids[$i]);
       }
       return $result;
    }
        
    // TODO: подумать, нужна ли copy_posts
    
    /** Пересчет показателей (ресинхронизация) темы: даты первого и последнего сообщения, количества сообщений и т.п.
    * При пересчете делается допущение, что самое последнее по времени сообщение всегда обладает наибольшим id во всей теме.
    * **/
    function topic_resync($tid) {
       $sql = 'SELECT COALESCE(MAX(id),0) AS last_post_id, COALESCE(MIN(id),0) AS first_post_id, '.
       'COALESCE(MAX(postdate),0) AS last_post_time, COALESCE(COUNT(*),0) AS post_count, '.
       'COALESCE(SUM(CAST(value=\'-1\' AS INTEGER)),0) AS flood_count, COALESCE(SUM(CAST(value=\'1\' AS INTEGER)),0) AS valued_count '.
       'FROM '.DB_prefix.'post WHERE tid='.intval($tid).'  AND status=\'0\''; // статистику темы считаем только по общедоступным сообщениям, без удаленных или на премодерации
       $topic_data = Library::$app->db->select_row($sql);
       $topic_data['lastmod']=Library::$app->time; // при пересинхронизации темы всегда временем последней синхронизации считаем текущее
       if (intval($topic_data['post_count'])===0) $topic_data['status']=2; // если в теме не осталось ни одного сообщения с нормальным статусом, помечаем ее как удаленную
       else $topic_data['status']=0; // если в теме появились нормальные сообщения, то меняем ее статус на 0
       return Library::$app->db->update(DB_prefix.'topic',$topic_data,'id='.intval($tid));
    }

    /** Пересчет показателей (ресинхронизация) раздела: даты последнего сообщения и количества тем и сообщений.
    * При пересчете делается допущение, что все темы раздела уже ресинхронизированы.
    * **/    
    function forum_resync($fid=false) {
       if (!$fid) $fid=Library::$app->forum['id'];
       $sql = 'SELECT MAX(last_post_id) AS last_post_id, COUNT(*) AS topic_count, SUM(post_count) AS post_count '.
       'FROM '.DB_prefix.'topic WHERE fid='.intval($fid).' AND status=\'0\'';
       $forum_data = Library::$app->db->select_row($sql);
       if (empty($forum_data['last_post_id'])) $forum_data['last_post_id']=0;
       if (empty($forum_data['post_count'])) $forum_data['post_count']=0;
       $forum_data['lastmod']=Library::$app->time; // при пересинхронизации темы всегда временем последней синхронизации считаем текущее
       return Library::$app->db->update(DB_prefix.'forum',$forum_data,'id='.intval($fid));       
    }
    
    /** Сохранение модераторского действия в лог вместе с даннными для его отката. Данные для отката должны содержаться в $data['data'], вид этих данных зависит от выполняемого действия
    * В настоящее время поддерживаются следующие действия:
    * 1 -- редактирование сообщения (данные для отката: post, хеш со старыми данными сообщения, text -- старый текст сообщения)
    * 2 -- изменение статуса (доступно/на модерации/удалено) сообщений (данные для отката: хеш с информацией о предыдущем состоянии сообщений, где ключ хеша -- состояния, а значение -- массив сообщений в этом состоянии)
    * 3 -- перенос нескольких сообщений в другую тему (данные для отката: pids -- номера перенесенных сообщений, tid -- номер новой темы)
    * 4 -- блокировка редактирования сообщений (данные для отката: суперхеш, где ключами являются предыдущие состояния (0 и 1), а значениями -- массивы тем, которые находлись в соответствующем состоянии до выполнения операции  )
    * 16 -- перенос тем в другой раздел (данные для отката: tids -- идентификаторы тем, fid -- номер нового раздела)
    * 17 -- изменение статуса тем (данные для отката: старый статус темы)
    * 18 -- закрытие/открытие тем (суперхеш, где ключами являются предыдущие состояния (0 и 1), а значениями -- массивы тем, которые находлись в соответствующем состоянии до выполнения операции  )
    * 19 -- приклеивание/отклеивание тем
    * 32 -- бан пользователя (не реализовано)
    * 33 -- вынесение предупреждения или поощрения (не реализовано)
    **/
    function log_action($data,$override=false) {
       if (empty($data['fid'])) $data['fid']=(isset(Library::$app->forum) && Library::$app->forum['id']) ? Library::$app->forum['id'] : 0; // если не включено переопределение, то тема создается в текущем разделе
       if (empty($data['tid'])) $data['tid']=(isset(Library::$app->topic) && Library::$app->topic['id']) ? Library::$app->topic['id'] : 0; // если не включено переопределение, то тема создается в текущем разделе
       if (empty($data['tid'])) $data['pid']=0;
       if (empty($data['time']) || !$override) $data['time']=Library::$app->time; // по умолчанию берем текущее время
       if (empty($data['uid']) || !$override) $data['uid']=Library::$app->get_uid(); // и текущего пользователя 
       if (empty($data['data'])) $data['data']='';
       else $data['data']=serialize($data['data']);
       
       if (empty($data['type'])) { // если код модераторского действия не задан
          trigger_error('Не определен код модераторского действия, сохранение в базу произведено не будет!',E_USER_WARNING);
          return false;
       }
       return Library::$app->db->insert(DB_prefix.'log_action',$data);
    }
    
    /** Откат действия, выполненного модератором 
    **/
    function rollback($id) {
       $sql = 'SELECT * FROM '.DB_prefix.'log_action WHERE id='.intval($id);
       $logdata = Library::$app->db->select_row($sql);
       if (!$logdata) return false; // если не удалось достать данные, возвращаем ошибку и ничего не делаем
       if ($logdata['data']) $undo = unserialize($logdata['data']);
       else $undo = false;
       if (!empty($this->forum) && $this->forum['id']!=$logdata['fid']) Library::$app->output_403('Невозможно отменить действие, совершенное для другого раздела!');
       $code = intval($logdata['type']); // тип совершенной модератором операции
       Library::$app->db->begin();
       if ($code===1) { // отмена редактирования сообщения или темы
          $text = $undo['text'];
          $tx_lastmod=$undo['tx_lastmod'];
          unset($undo['text']);
          unset($undo['tx_lastmod']);
          $result=Library::$app->db->update(DB_prefix.'post',$undo,'id='.intval($logdata['pid']).' AND tid='.intval($logdata['tid'])); // откатываем данные сообщения (часть после AND нужна во избежание ситуаций, когда сообщение было сначала отредактировано, а потом перенесено)
          if ($result) {
            Library::$app->db->update(DB_prefix.'text',array('data'=>$text,'tx_lastmod'=>$tx_lastmod),'id='.intval($undo['id']).' AND type=16'); // откатываем текст сообщения
            if (!empty($undo['topic'])) Library::$app->db->update(DB_prefix.'topic',$undo['topic'],'id='.intval($logdata['tid']).' AND fid='.intval($logdata['fid'])); // откатываем данные сообщения (часть после AND нужна во избежание ситуаций, когда сообщение было сначала отредактировано, а потом перенесено) 
            $this->topic_resync($logdata['tid']);
            $this->forum_resync($logdata['fid']);
          }
       }
       elseif ($code===2) { // отмена удаления сообщения
          $result=$this->status_posts($undo,$logdata['tid'],array('nolog'=>true));   
       }
       elseif ($code===3) { // отмена переноса сообщений
          if (!empty($undo['old_move_msg'])) { // удаляем сообщения о переносе
             $sql = 'DELETE FROM '.DB_prefix.'post WHERE id='.intval($undo['old_move_msg']);
             Library::$app->db->query($sql);
          }
          if (!empty($undo['new_move_msg'])) {
             $sql = 'DELETE FROM '.DB_prefix.'post WHERE id='.intval($undo['new_move_msg']);
             Library::$app->db->query($sql);
          }          
          $result=$this->move_posts($undo['pids'],$undo['tid'],$logdata['tid'],array('nomsg'=>true,'nolog'=>true));
       }
       elseif ($code===4) { // отмена блокировки редактирования сообщения
          $result=$this->lock_posts($undo,$logdata['tid'],array('nolog'=>true));
       }
       elseif ($code===16) { // отмена переноса тем
          $result=$this->move_topics($logdata['tid'],$undo['fid'],$logdata['fid'],array('nolog'=>true));
       }
       elseif ($code===17) { // отмена удаления/восстановления темы
          $result=$this->status_topics($undo,$logdata['fid'],array('nolog'=>true));          
       }
       elseif ($code===18) { // отмена закрытия/открытия темы
          $result=$this->lock_topics($undo,$logdata['fid'],array('nolog'=>true));          
       }
       elseif ($code===19) { // отмена приклеивания/отклеивания темы
         $result=$this->stick_topics($undo,$logdata['fid'],array('nolog'=>true));
       }
       elseif ($code===20) { // отмена приклеивания/отклеивания первого сообщения
         $result=$this->stick_posts($undo,$logdata['fid'],array('nolog'=>true));
       }
       elseif ($code===21) { // отмена  добавления/удаления из Избранного
         $result=$this->fav_posts($undo,$logdata['fid'],array('nolog'=>true));
       }
       if ($result){ // если откат операции прошел нормально, то удаляем данные о ней из базы
         $sql = 'DELETE FROM '.DB_prefix.'log_action WHERE id='.intval($id);
         Library::$app->db->query($sql);
       }
       // TODO: восстановление пользователя после бана, отмена редактирования темы, отмена изменений прав доступа
       Library::$app->db->commit();
       return $result;
    }
    
    /** Подсчет числа записей в логе модераторских действий **/
    function count_actions($cond) {
       $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'log_action WHERE 1=1 ';
       if (!empty($cond['fid'])) $sql.=' AND fid='.intval($cond['fid']);
       if (!empty($cond['tid'])) $sql.=' AND tid='.intval($cond['tid']);
       if (!empty($cond['pid'])) $sql.=' AND pid='.intval($cond['pid']);
       if (!empty($cond['time'])) $sql.=' AND time>='.intval($cond['time']);
       if (!empty($cond['type'])) $sql.=' AND type='.intval($cond['type']); // извлечение записей определенного типа
     return Library::$app->db->select_int($sql);
    }
    
    /** Извлечение записей из лога модераторских действий **/
    function get_actions($cond) {
       $sql = 'SELECT la.*, u.display_name FROM '.DB_prefix.'log_action la '.
           'LEFT JOIN '.DB_prefix.'user u ON (la.uid=u.id) '. 
           'WHERE 1=1 ';
       if (!empty($cond['fid'])) $sql.=' AND fid='.intval($cond['fid']);
       if (!empty($cond['tid'])) $sql.=' AND tid='.intval($cond['tid']);
       if (!empty($cond['pid'])) $sql.=' AND pid='.intval($cond['pid']);
       if (!empty($cond['time'])) $sql.=' AND time>='.intval($cond['time']);
       if (!empty($cond['type'])) $sql.=' AND type='.intval($cond['type']); // извлечение записей определенного типа
       $sql.= ' ORDER BY time DESC';
       $result=Library::$app->db->select_all($sql);
       for ($i=0, $count=count($result);$i<$count;$i++) {
           $result[$i]['data']=unserialize($result[$i]['data']);
           $result[$i]['descr']=$this->describe_action($result[$i]);           
       }
       return $result;
    }
    
    /** Возвращает человекочитаемое описание модераторского действия **/    
    function describe_action($data) {
      $type=$data['type'];
      if ($type==1) $result='Редактирование сообщения';
      elseif ($type==2) $result='Удаление сообщения';
      elseif ($type==3) $result='Перенос сообщения в другую тему';
      elseif ($type==4) $result='Запрет на редактирование сообщения';
      elseif ($type==16) {
        $result='<b>Перенос тем</b><br />';
        $result.='Темы ';
        foreach ($data['data']['tids'] as $curtid) $result.='<a href="#" class="topic_popup">#'.intval($curtid).'</a> ';
        $result.='были перенесены в раздел "'.htmlspecialchars($data['data']['fid']).'"';
      }
      elseif ($type==17) {
        $result='<b>Удаление тем</b>';
        foreach ($data['data'] as $curtid=>$status) $result.='<a href="#" class="topic_popup">#'.intval($curtid).'</a> ';
      }
      elseif ($type==18) {
        $result='<b>Закрытие/открытие тем:</b><ul>';
        foreach ($data['data'] as $curtid=>$status) $result.='<li><a href="#" class="topic_popup">#'.intval($curtid).'</a> &mdash; '.($status ? 'открыта' : 'закрыта').'</li>';
        $result.='</ul>';
      }
      elseif ($type==19) {
        $result='<b>Приклеивание/отклеивание темы:</b><ul>';
        foreach ($data['data'] as $curtid=>$status) $result.='<li><a href="#" class="topic_popup">#'.intval($curtid).'</a> &mdash; '.($status ? 'отклеена' : 'приклеена').'</li>';
        $result.='</ul>';
      }
      elseif ($type==20) {
        $result='<b>Приклеивание/отклеивание первого сообщения темы:</b><ul>';
        foreach ($data['data'] as $curtid=>$status) $result.='<li><a href="#" class="topic_popup">#'.intval($curtid).'</a> &mdash; '.($status ? 'отклеено' : 'приклеено').'</li>';
        $result.='</ul>';
      }
      elseif ($type==21) {
        $result='<b>Внесение/удаление темы в «Избранное» форума:</b><ul>';
        foreach ($data['data'] as $curtid=>$status) $result.='<li><a href="#" class="topic_popup">#'.intval($curtid).'</a> &mdash; '.($status ? 'внесено' : 'удалено').'</li>';
        $result.='</ul>';
      }      
      return $result;
    }
    
    /** Получение списка авторов сообщений. 
     * Нужно для уменьшения/увеличения счетчиков при пропуске сообщений с премодераци, 
     * удалении и восстановлении 
     * @param $tid integer Идентификатор темы
     * @param $pids mixed Идентификаторы сообщений, если нужна выборка не по всей теме, а только по определенным сообщениям
     * @return array Массив с идентфикаторами пользователей. (Только зарегистрированных, сообщения гостей не считаются.)
     * **/  
    function get_post_owners($tid,$pids=false) {
     $sql = 'SELECT DISTINCT uid FROM '.DB_prefix.'post p '.
      'WHERE p.uid>'.intval(AUTH_SYSTEM_USERS).' AND p.tid='.intval($tid);
     if ($pids) {
        if (is_array($pids)) $sql.=' AND '.Library::$app->db->array_to_sql($pids,'id');
        else $sql.=' AND id='.intval($pids);
     }
     return Library::$app->db->select_all_numbers($sql);      
    }
 }
