<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.02
 *  @copyright 2018 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Библиотека для вывода различных вспомогательных блоков для subactions
 *  ================================ */

class Library_blocks extends Library {
   
   /** Вывод блока с шаблоном, путь к которому указан в параметре. Пригодится для тех случаев, когда нужно выводить блоки со стаитчным HTML в зависимости от раздела или действия.
    **/
   function block_dummy($template) {
      if (!empty($template)) return array($template,array());
      else return false;
   }
   
   /** Вывод блока объявления. Поддерживает 3 режима:
    * 0 — вывод только главного объявления
    * 1 — вывод объявления разела, если оно есть, иначе — главного объявления форума
    * 2 — вывод только объявления раздела
   **/
   function block_announce($mode=1) {
     $result=false;
     if ($mode==0 || empty(Library::$app->forum['id'])) { // режим 0 -- показ главного объявления на всех страницах
       if ($mode!=2) $result=Library::$app->get_text(0, 1);
     }
     elseif (!empty(Library::$app->forum['id'])) { // если мы в разделе
       $result = Library::$app->get_text(Library::$app->forum['id'], 1);
       if (trim($result)=='' && $mode!=2) $result = Library::$app->get_text(0, 1); // если нет локального объявления, выводим главное      
     }
     if (empty($result)) return false; // если текст объявления пуст, возвращаем false, чтобы не покдлючать лишний файл шаблона
     return array('announce.tpl',$result);
   }

   /** Вывод последнего времени онлайн для указанных пользователей.
    * Полезно, если нужно показать, что создатель сайта или кто-то из админов или других важных людей форума онлайн. **/
   function block_is_online($users) {
      $users = explode(',',$users);
      if (empty($users)) return false;
      $sql = 'SELECT uid, MAX(visit1) FROM '.DB_prefix.'last_visit WHERE '.Library::$app->db->array_to_sql($users,'uid');
      $result = Library::$app->db->select_simple_hash($sql);
      $online_time = Library::$app->get_opt('online_time'); // количество миут, в течение которых пользователь считается присутстующим на форуме
      if (empty($online_time)) $online_time = 5; // значение по умолчанию, если время онлайн не определено
      $result['limit_time'] = Library::$app->time - $online_time*60;
      return array('empty',$result);
   }
   
   /** Вывод уведомления о непрочитанных личных сообщениях (если они включены в настройках через опцию enable_privmsg) **/
   function block_pm_unread() {
     if (!Library::$app->get_opt('enable_privmsg') || Library::$app->is_guest()) return false;
     $sql = 'SELECT SUM(unread) AS unread, MAX(last_post_date) AS last '.
            'FROM '.DB_prefix.'privmsg_thread_user thu '.
            'WHERE uid='.intval(Library::$app->get_uid());
     $data = Library::$app->db->select_row($sql);
     Library::$app->lastmod = max(Library::$app->lastmod, $data['last']);
     return array('privmsg/pm_notify.tpl',$data['unread']);      
   }

   /** Вывод содержимого кеша микроблога в блок  **/
   function block_microblog($params) {
     $params = explode(',',$params);
     if (empty($params[0])) {
       _dbg('Не указан номер раздела-микроблога для показа последних сообщений!');
     }
     $topics = array();

     $flib = Library::$app->load_lib('forums',false);
     if (!$flib) return false; // если библиотеку тем загрузить не удалось, выходим, не отображая ничего
     $forum = $flib->get_forum($params[0],true);
     Library::$app->lastmod = max($forum['lastmod'],Library::$app->lastmod);

     return array('micro/last.tpl',$forum);
   }

   /** Список количества сообщений на премодерации по всем разделам. Выводится только для админов. **/
   function block_premoderate($params) {
     if (!Library::$app->is_admin()) return false;

     $sql = 'SELECT f.id, f.hurl, f.title, COUNT(*) AS post_count, MAX(p.postdate) AS post_time '.
        'FROM '.DB_prefix.'forum f, '.DB_prefix.'topic t, '.DB_prefix.'post p '.
        'WHERE p.status=\'1\' AND p.tid=t.id AND t.fid=f.id '.
        'GROUP BY f.id, f.hurl, f.title HAVING post_count>0';
     $premoderate_forums = Library::$app->db->select_all($sql);
     
     foreach ($premoderate_forums as $item) Library::$app->lastmod=max(Library::$app->lastmod,$item['post_time']);
     return array('empty',$premoderate_forums);
   }

   /** Количество просмотров раздела **/
   function block_forum_views($forum_id=false) {
     if (!$forum_id && Library::$app->forum) $forum_id=Library::$app->forum['id'];
     if (!$forum_id) return false;
     $sql = 'SELECT views FROM '.DB_prefix.'views WHERE oid='.intval($forum_id).' AND type=\'forum\'';
     $views = Library::$app->db->select_int($sql);
     return array('empty',$views);
   }

   /** Количество тегов, использованных в разделе **/
   function block_tag_count($forum_id=false) {
     if (!$forum_id && Library::$app->forum) $forum_id=Library::$app->forum['id'];
     $sql = 'SELECT COUNT(DISTINCT tagname) FROM '.DB_prefix.'tagname tn, '.DB_prefix.'tagentry te, '.DB_prefix.'topic t WHERE
     t.id=te.item_id AND tn.id=te.tag_id AND tn.type=0';
     if ($forum_id) $sql.=' AND t.fid='.intval($forum_id);
     $views = Library::$app->db->select_int($sql);
     return array('empty',$views);
   }

   /** Количество тегов, использованных в разделе. Передаваемые параметры: лимит количества тегов, номер раздела форума, сортировка по количеству **/
   function block_tag_list($params='') {
     $params=explode(',',$params);
     $limit=(!empty($params[0])) ? $params[0] : false;
     $forum_id=(!empty($params[1])) ? $params[1] : false;
     if (!$forum_id && Library::$app->forum) $forum_id=Library::$app->forum['id'];
     $sql = 'SELECT tagname, COUNT(t.id) AS tagcount FROM '.DB_prefix.'tagname tn, '.DB_prefix.'tagentry te, '.DB_prefix.'topic t WHERE
     t.id=te.item_id AND tn.id=te.tag_id AND tn.type=0';
     if ($forum_id) $sql.=' AND t.fid='.intval($forum_id);
     $sql.=' GROUP BY tagname HAVING tagcount>0';
     if (empty($params[2])) {
      $sql.=' ORDER BY tagcount DESC';
     }
     $result['tags'] = Library::$app->db->select_simple_hash($sql);
     $result['limit'] = $limit;
     $result['total'] = array_sum($result['tags']);
     if (!empty($result['tags'])) $result['max'] = max($result['tags']);
     if ($forum_id==Library::$app->forum['id']) $result['forum']=Library::$app->forum;
     else {
      $flib = Library::$app->load_lib('forums',false);
      if ($flib) {
         $result['forum']=$flib->get_forum($forum_id,false);
      }
     }
     return array('stdforum/taglist.tpl',$result);
   }
   
   /** Вывод блока самых активных тем на форуме (последняя активность была не менне указанного количества дней назад) с сортировкой по числу сообщений
    **/
  function block_active_topics($params='') {
    $params = explode(',',$params);
    if (count($params)>=3) $cond['fid']=array_slice($params,2);
    else $cond['fid'] = Library::$app->get_forum_list('read');
    $cond['perpage']=isset($params[0]) ? intval($params[0]) : 3; // по умолчанию выводим три темы
    $days = !empty($params[1]) ? intval($params[1]) : 30; // по умолчанию выводим за месяц
    $cond['after_time']=Library::$app->time-$days*24*60*60;
    $cond['first']=true;
    $cond['last']=true;
    $cond['forums']=true; // нужны и данные о разделах
    $cond['not_flood']=true; // не выводим темы из флуд-разделов
    $cond['order']='post_count'; // сортируем по количеству постов
    $cond['sort']='DESC'; // причем последние темы выводим сверху
    if (count($params)<3) $cond['forumtype']='stdforum'; // выводим новые темы только из стандартных разделов, если список разделов не задан явно
    /** @var Library_topic */
    $tlib = Library::$app->load_lib('topic',false);
    if ($tlib) {
      $topics = $tlib->list_topics($cond);
      if (empty($topics)) return false;
      foreach ($topics as $topic) Library::$app->lastmod=max(Library::$app->lastmod,$topic['last_post_date']);
    }
    return array('stdforum/ta_block.tpl',array('topics'=>$topics));   
  }
  
   /** Вывод блока самых популярных тем на форуме (последняя активность была не менне указанного количества дней назад) с сортировкой по числу просмотров
    **/
  function block_popular_topics($params='') {
    $params = explode(',',$params);
    if (count($params)>=3) $cond['fid']=array_slice($params,2);
    else $cond['fid'] = Library::$app->get_forum_list('read');
    $cond['perpage']=isset($params[0]) ? intval($params[0]) : 3; // по умолчанию выводим три темы
    $days = !empty($params[1]) ? intval($params[1]) : 30; // по умолчанию выводим за месяц
    $cond['after_time']=Library::$app->time-$days*24*60*60;
    $cond['first']=true;
    $cond['last']=true;
    $cond['views']=true;
    $cond['forums']=true; // нужны и данные о разделах
    $cond['not_flood']=true; // не выводим темы из флуд-разделов
    $cond['order']='views'; // сортируем по количеству просмотров
    $cond['sort']='DESC'; // причем последние темы выводим сверху
    if (count($params)<3) $cond['forumtype']='stdforum'; // выводим новые темы только из стандартных разделов, если список разделов не задан явно
    /** @var Library_topic */
    $tlib = Library::$app->load_lib('topic',false);
    if ($tlib) {
      $topics = $tlib->list_topics($cond);
      if (empty($topics)) return false;
      foreach ($topics as $topic) Library::$app->lastmod=max(Library::$app->lastmod,$topic['last_post_date']);
    }
    return array('stdforum/tp_block.tpl',array('topics'=>$topics));   
  }

  /** Вывод новых тем на форуме (созданных не более чем указанное количество дней назад) с сортировкой по убыванию времени создания
  *  Параметры: количество тем, период (в днях), разделы через запятую.
  **/
  function block_new_topics($params='') {
    $params = explode(',',$params);
    if (count($params)>=3) $cond['fid']=array_slice($params,2);
    else $cond['fid'] = Library::$app->get_forum_list('read');
    $cond['perpage']=isset($params[0]) ? intval($params[0]) : 3; // по умолчанию выводим три темы
    $days = !empty($params[1]) ? intval($params[1]) : 30; // по умолчанию выводим за месяц
    $cond['create_time']=Library::$app->time-$days*24*60*60;
    $cond['first']=true;
    $cond['forums']=true; // нужны и данные о разделах
    $cond['not_flood']=true; // не выводим темы из флуд-разделов
    $cond['order']='first_post_date'; // сортируем по дате создания
    $cond['sort']='DESC'; // причем последние темы выводим сверху
    if (count($params)<3) $cond['forumtype']='stdforum'; // выводим новые темы только из стандартных разделов, если список разделов не задан явно
    /** @var Library_topic */
    $tlib = Library::$app->load_lib('topic',false);
    if ($tlib) {
      $topics = $tlib->list_topics($cond);
      if (empty($topics)) return false;
      foreach ($topics as $topic) Library::$app->lastmod=max(Library::$app->lastmod,$topic['first_post_date']);
    }
    return array('stdforum/tn_block.tpl',array('topics'=>$topics));
  }
}
