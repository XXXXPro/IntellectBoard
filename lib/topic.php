<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Библиотека базовых операций с темами и сообщениями
 *  ================================ */

class Library_topic extends Library {
  /** Получение базовой информации о теме. Используется, в основном, в init_object **/
  function get_topic($topic_url,$status=0,$user=false) {
      $user=$user && !Library::$app->is_guest();
      $params = array();
      $sql = 'SELECT t.id, t.fid, t.title, t.descr, CONCAT(\''.Library::$app->forum['hurl'].'/\',CASE WHEN hurl!=\'\' THEN hurl ELSE CAST(t.id AS VARCHAR(32)) END,\'/\') AS full_hurl, hurl, '.
        't.status, t.lastmod, post_count, flood_count, valued_count, owner, ext_status, last_post_time, first_post_id, last_post_id, sticky_post, t.locked, favorites ';
      if ($user) $sql.=', visit2, bookmark, subscribe, p.uid as first_post_uid, p.author AS first_post_author ';
      $sql.='FROM '.DB_prefix.'topic t ';
      if ($user) {
        $sql.='LEFT JOIN '.DB_prefix.'last_visit lv ON (lv.oid=t.id AND lv.type=\'topic\' AND lv.uid=?) ';
        $params[]=Library::$app->get_uid();
      }
      $sql.='LEFT JOIN '.DB_prefix.'post p ON (p.id=first_post_id) ';
      if (is_numeric($topic_url)) {
        $sql.='WHERE t.id=?';
        $params[]=intval($topic_url);
      }
      else { 
        $sql.='WHERE hurl=?';
        $params[]=$topic_url;
      }
      $sql.=' AND t.status=?'; // проверка на то, что тема действительно в этом разделе и доступна для чтения
      $params[] = $status;
      return Library::$app->db->select_row($sql,$params);
  }

  /** Получение информации о оообщениях
  * @param $cond array Массив данных с параметрами выборки.
  * На данный момент может включать в себя:
  * fid -- номер раздела (или массив с номерами нескольких разделов)
  * last -- получать данные о последнем сообщении (дата, id, автор)
  * first -- получать данные о стартовом сообщении (дата, id, автор)
  * first_text -- получать текст стартового сообщения
  * order -- столбец, по которому осуществляется сортировка (по умолчанию -- last_post_time)
  * sort -- сортировка (ASC/DESC, по умолчанию -- DESC)
  * owner -- выдача только тем, созданных пользователем с указанным идентификатором
  * views -- выдача количества просмотров темы
  * subscr -- получить также данные о подписке, дате последнего визита и т.п. (из таблицы prefix_last_visit)
  * new_time -- проверять темы на новые сообщения. Темы считаются новыми, если дата отправки последнего сообщения больше значения в last_visit1 из таблицы prefix_last_visit и значения, указанного в new_time (имеет смысл только при subscr=true)
  * valued -- выдача только тем, у которых не менее указаннного количества ценных сообщений
  * unanswered -- выдача только тем с одним сообщением (т.е. неотвеченных)
  * flood_limit -- выдача только тем, соотношение флуда в которых меньше указанного (во flood_limit должен быть безразмерный коэффициент от 0 до 1)
  * posted -- выдача только тем, в которые писал текущий пользователь (имеет смысл только при subscr=true)
  * forums -- получать данные о разделах, в которых размещены темы
  * forumtype -- только разделы указанных типов
  * bookmark -- выдача только тем, которые есть в закладках у пользователя
  * tags -- извлечь данные о тегах для каждой темы
  * create_time -- время создания темы должно быть меньше или равно указанному
  * after_time -- время последнего сообщения темы должно быть меньше или равно указанному
  * attach -- извлекается приложенный файл к первому сообщению со статусом is_main=1
  * attach_count -- подсчитывается общее количество приложенных к первому сообщению темы файлов
  * curator -- извлекаются данные (user id, login и display_name) о владельце/кураторе темы
  *
  * По умолчанию возвращаются только темы в нормальном состоянии, однако это можно изменить с помощью следующих параметров:
  * deleted -- возвращать только темы, помеченные к удалению
  * premod -- возвращать только темы, стоящие на премодерации
  * all -- возвращать все темы, статус может быть любым
  **/
  function list_topics($cond) {
    if (empty($cond)) trigger_error('Ошибка: массив условий выборки тем пуст! Прерываем работу во избежание выгрузки всей базы!',E_USER_ERROR);

    if (!empty($cond['with_tags'])) {
      /** @var Library_tags **/
      $taglib = Library::$app->load_lib('tags',false);
      $cond['id']=$taglib->get_ids_by_tags($cond['with_tags'],0);
    }

    $where = '1=1';
    if (empty($cond['deleted']) && empty($cond['premod']) && empty($cond['all'])) $where.=' AND t.status=\'0\''; // по умолчанию выбираем только темы с обычным статусом
    elseif (!empty($cond['deleted'])) $where.=' AND t.status=\'2\'';
    elseif (!empty($cond['premod'])) $where.=' AND t.status=\'1\'';

    if (!empty($cond['fid'])) {
      if (is_array($cond['fid'])) $where.=' AND '.Library::$app->db->array_to_sql($cond['fid'],'t.fid');
      else $where.=' AND t.fid='.intval($cond['fid']);
    }
    if (!empty($cond['id'])) {
      if (is_array($cond['id'])) $where.=' AND '.Library::$app->db->array_to_sql($cond['id'],'t.id');
      else $where.=' AND t.id='.intval($cond['id']);
    }
    if (isset($cond['sticky'])) $where.=' AND sticky=\''.intval($cond['sticky']).'\'';
    if (!empty($cond['after_time'])) $where.=' AND last_post_time>'.intval($cond['after_time']);
    if (!empty($cond['owner'])) $where.=' AND t.owner='.intval($cond['owner']);
    if (!empty($cond['valued'])) $where.=' AND t.valued_count>='.intval($cond['valued']);
    if (!empty($cond['unanswered'])) $where.=' AND t.post_count=1';
    if (!empty($cond['flood_limit'])) $where.=' AND t.flood_count/t.post_count<='.floatval($cond['flood_limit']);
    if (!empty($cond['posted'])) $where.=' AND lv.posted=\'1\'';
    if (!empty($cond['topic_title'])) $where.=' AND t.title LIKE \'%'.Library::$app->db->slashes($cond['topic_title']).'%\'';
    if (!empty($cond['bookmark'])) $where.=' AND lv.bookmark=\'1\'';
    if (!empty($cond['subscribed'])) $where.=' AND lv.subscribe=\'1\'';
    if (!empty($cond['forums']) && !empty($cond['not_flood'])) $where.=' AND f.is_flood=\'0\'';
    if (!empty($cond['forums']) && !empty($cond['forumtype'])) {
      if (!is_array($cond['forumtype'])) $cond['forumtype']=array($cond['forumtype']);
      $where.=' AND '.Library::$app->db->array_to_sql($cond['forumtype'],'module');
    }
    if (!empty($cond['favorites'])) $where.=' AND t.favorites=\'1\'';
    if (!empty($cond['new_time']) && !empty($cond['forums']) &&!empty($cond['newposts'])) $where.=' AND t.last_post_time>lv.visit1 AND t.last_post_time>COALESCE(ma.mark_time,0) AND t.last_post_time>'.intval($cond['new_time']);
    if (!empty($cond['search'])) $where.=' AND sr.sid='.intval($cond['search']).' AND sr.oid=t.id';
    if (!empty($cond['create_time']) && !empty($cond['first'])) $where.=' AND p2.postdate>'.intval($cond['create_time']);
    if (!empty($cond['first']) && !empty($cond['starter_id'])) $where.=' AND p2.uid='.intval($cond['starter_id']);

    $columns = '';
    if (!empty($cond['last'])) $columns.=', p1.author AS last_poster, p1.uid AS last_poster_id, p1.postdate AS last_post_date';
    if (!empty($cond['first'])) $columns.=', p2.author AS starter, p2.uid AS starter_id, p2.postdate AS first_post_date';
//    if (!empty($cond['first']) && !empty($cond['first_text'])) $columns.=', p2.html, p2.bcode, p2.smiles, p2.links, tx.data AS text';
    if (!empty($cond['forums'])) $columns.=', f.title AS forum_title, f.descr AS forum_descr, f.hurl AS forum_hurl, 
    CONCAT(f.hurl,\'/\', CASE WHEN t.hurl!=\'\' THEN t.hurl ELSE CAST(t.id AS VARCHAR(32)) END,\'/\') AS full_hurl';
    if (!empty($cond['views'])) $columns.=', v.views';
    if (!empty($cond['subscr']) || !empty($cond['posted'])) $columns.=', lv.bookmark, lv.subscribe, lv.posted ';
    if (!empty($cond['new_time']) && empty($cond['forums'])) $columns.=', CAST(COALESCE(t.last_post_time>lv.visit1,true) AND t.last_post_time>'.intval($cond['new_time']).' AS integer) AS new';
    if (!empty($cond['new_time']) && !empty($cond['forums'])) $columns.=', CAST(COALESCE(t.last_post_time>lv.visit1,true) AND t.last_post_time>COALESCE(ma.mark_time,0) AND t.last_post_time>'.intval($cond['new_time']).' AS integer) AS new';
    if (!empty($cond['polls'])) $columns.=', pl.id AS poll';
    if (empty($cond['forums'])) $columns.=', CASE WHEN t.hurl!=\'\' THEN CONCAT(t.hurl,\'/\') ELSE CONCAT(CAST(t.id AS VARCHAR(32)),\'/\') END AS t_hurl';
    if (!empty($cond['attach'])) $columns.=', TRIM(file.fkey) AS fkey, file.filename, file.size, file.format, file.extension';
    if (!empty($cond['first']) && !empty($cond['attach_count'])) $columns.=', (SELECT COUNT(*) FROM '.DB_prefix.'file fac WHERE fac.oid=p2.id AND fac.type=1) AS attach_count';
    if (!empty($cond['curator'])) $columns.=', cur.id AS curator_id, cur.login AS curator_login, cur.display_name AS curator_display_name';
    $uid = Library::$app->get_uid();

    $sql = 'SELECT t.* '.$columns.' FROM ';
    if (!empty($cond['search'])) $sql.=DB_prefix.'search_result sr, ';
    $sql.=DB_prefix.'topic t ';
    if (!empty($cond['last'])) $sql.='LEFT JOIN '.DB_prefix.'post p1 ON (t.last_post_id=p1.id) ';
    if (!empty($cond['first'])) $sql.='LEFT JOIN '.DB_prefix.'post p2 ON (t.first_post_id=p2.id) ';
//    if (!empty($cond['first']) && !empty($cond['first_text'])) $sql.='LEFT JOIN '.DB_prefix.'text tx ON (tx.id=p2.id AND tx.type=16) ';
//    if (!empty($cond['first']) && !empty($cond['first_user'])) $sql.='LEFT JOIN '.DB_prefix.'user u2 ON (u2.id=p2.author) ';
    if (!empty($cond['forums'])) $sql.='LEFT JOIN '.DB_prefix.'forum f ON (t.fid=f.id) ';
    if (!empty($cond['subscr']) || !empty($cond['bookmark']) || !empty($cond['new_time'])
       || !empty($cond['posted']) || !empty($cond['subscribed']) || !empty($cond['newposts'])) $sql.='LEFT JOIN '.DB_prefix.'last_visit lv ON (t.id=lv.oid AND lv.type=\'topic\' AND lv.uid='.intval($uid).') ';
    if (!empty($cond['views'])) $sql.='LEFT JOIN '.DB_prefix.'views v ON (t.id=v.oid AND v.type=\'topic\') ';
    if (!empty($cond['polls'])) $sql.='LEFT JOIN '.DB_prefix.'poll pl ON (t.id=pl.id) ';
    if (!empty($cond['new_time']) && !empty($cond['forums'])) $sql.='LEFT JOIN '.DB_prefix.'mark_all ma ON (ma.fid=t.fid AND ma.uid='.intval($uid).') ';
    if (!empty($cond['attach'])) $sql.='LEFT JOIN '.DB_prefix.'file file ON (t.first_post_id=file.oid AND file.type=1 AND is_main=\'1\') ';
    if (!empty($cond['curator'])) $sql.='LEFT JOIN '.DB_prefix.'user cur ON (t.owner>'. AUTH_SYSTEM_USERS.' AND t.owner=cur.id) ';

    $sql.='WHERE '.$where;
    $order = !empty($cond['order']) ? $cond['order'] : 'last_post_time';
    $sql.=' ORDER BY '.$order;
    if (empty($cond['sort']) || $cond['sort']=='DESC') $sql.=' DESC';

    $offset = isset($cond['start']) ? $cond['start'] : false; // граничные условия для LIMIT
    $count = isset($cond['perpage']) ? $cond['perpage'] : false;
    if (isset($cond['perpage']) && !isset($cond['start'])) $offset=0;

    $result=Library::$app->db->select_all($sql,$offset,$count);
    if (!empty($cond['tags'])) {
      /** @var Library_tags **/
      $taglib = Library::$app->load_lib('tags',false);
      if ($taglib) {
        $tids = array();
        for ($i=0, $count=count($result); $i<$count; $i++) $tids[]=$result[$i]['id'];
        $tags = $taglib->get_tags_by_ids($tids);
        for ($i=0, $count=count($result); $i<$count; $i++) $result[$i]['tags']=isset($tags[$result[$i]['id']]) ? $tags[$result[$i]['id']] : array();
      }
    }

    return $result;
  }

  /** Подсчет количества тем, удовлетворяющих заданных условиям
  * @param $cond array Массив данных с параметрами выборки.
  * На данный момент может включать в себя:
  * fid -- номер раздела (или массив с номерами нескольких разделов)
  * owner -- выдача только тем, созданных пользователем с указанным идентификатором
  * views -- выдача количества просмотров темы
  * after_time -- выдача тем, созданных после указанной даты
  * subscr -- получить также данные о подписке, дате последнего визита и т.п. (из таблицы prefix_last_visit)
  * valued -- выдача только тем, у которых не менее указаннного количества ценных сообщений
  * unanswered -- выдача только тем с одним сообщением (т.е. неотвеченных)
  * flood_limit -- выдача только тем, соотношение флуда в которых меньше указанного (во flood_limit должен быть безразмерный коэффициент от 0 до 1)
  * posted -- выдача только тем, в которые писал текущий пользователь (имеет смысл только при subscr=true)
  *
  * По умолчанию возвращаются только темы в нормальном состоянии, однако это можно изменить с помощью следующих параметров:
  * deleted -- возвращать только темы, помеченные к удалению
  * premod -- возвращать только темы, стоящие на премодерации
  * all -- возвращать все темы, статус может быть любым
  **/
  function count_topics($cond) {
    if (empty($cond)) trigger_error('Ошибка: массив условий выборки тем пуст! Прерываем работу во избежание выгрузки всей базы!',E_USER_ERROR);
    $where = '1=1';

    if (!empty($cond['with_tags'])) {
      /** @var Library_tags **/
      $taglib = Library::$app->load_lib('tags',false);
      $cond['id']=$taglib->get_ids_by_tags($cond['with_tags'],0);
    }
    if (!empty($cond['id'])) {
      if (is_array($cond['id'])) $where.=' AND '.Library::$app->db->array_to_sql($cond['id'],'t.id');
      else $where.=' AND t.id='.intval($cond['id']);
    }

    if (empty($cond['deleted']) && empty($cond['premod']) && empty($cond['all'])) $where.=' AND t.status=\'0\''; // по умолчанию выбираем только темы с обычным статусом
    elseif (!empty($cond['deleted'])) $where.=' AND t.status=\'2\'';
    elseif (!empty($cond['premod'])) $where.=' AND t.status=\'1\'';

    if (!empty($cond['fid'])) {
      $where.=' AND '.Library::$app->db->array_to_sql($cond['fid'],'t.fid');
    }
    if (isset($cond['sticky'])) {
      if (!$cond['sticky']) $where.=' AND sticky=\'0\'';
      else $where.=' AND sticky=\'1\'';
    }
    if (!empty($cond['after_time'])) $where.=' AND last_post_time>'.intval($cond['after_time']);
    if (!empty($cond['owner'])) $where.=' AND t.owner='.intval($cond['owner']);
    if (!empty($cond['valued'])) $where.=' AND t.valued_count>='.intval($cond['valued']);
    if (!empty($cond['unanswered'])) $where.=' AND t.post_count=1';
    if (!empty($cond['flood_limit'])) $where.=' AND t.flood_count/t.post_count<='.floatval($cond['flood_limit']);
    if (!empty($cond['subscr']) && !empty($cond['posted'])) $where.=' AND lv.posted=\'1\'';
    if (!empty($cond['topic_title'])) $where.=' AND t.title LIKE \'%'.Library::$app->db->slashes($cond['topic_title']).'%\'';
    if (!empty($cond['owner'])) $where.=' AND t.owner='.intval($cond['owner']);
    if (!empty($cond['bookmark'])) $where.=' AND lv.bookmark=\'1\'';
    if (!empty($cond['subscribed'])) $where.=' AND lv.subscribe=\'1\'';
    if (!empty($cond['forums']) && !empty($cond['not_flood'])) $where.=' AND f.is_flood=\'0\'';
    if (!empty($cond['favorites'])) $where.=' AND t.favorites=\'1\'';
    if (!empty($cond['first']) && !empty($cond['starter_id'])) $where.=' AND p2.uid='.intval($cond['starter_id']);    

    $uid = Library::$app->get_uid();

    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'topic t ';
    if (!empty($cond['forums'])) $sql.='LEFT JOIN '.DB_prefix.'forum f ON (t.fid=f.id) ';
    if (!empty($cond['subscr']) || !empty($cond['bookmark']) || !empty($cond['new_time'])
       || !empty($cond['posted']) || !empty($cond['subscribed'])) $sql.='LEFT JOIN '.DB_prefix.'last_visit lv ON (t.id=lv.oid AND lv.type=\'topic\' AND lv.uid='.intval($uid).') ';
    if (!empty($cond['new_time']) && !empty($cond['forums'])) $sql.='LEFT JOIN '.DB_prefix.'mark_all ma ON (ma.fid=t.fid AND ma.uid='.intval($uid).') ';
    if (!empty($cond['first'])) $sql.='LEFT JOIN '.DB_prefix.'post p2 ON (t.first_post_id=p2.id) ';
    $sql.='WHERE '.$where;

    return Library::$app->db->select_int($sql);
  }


  /** Получение информации о оообщениях
  * @param $cond array Массив данных с параметрами выборки.
  * На данный момент может включать в себя:
  * tid -- номер темы
  * id -- номер сообщения или массив с идентификаторами нескольких сообщений
  * fid -- номер раздела
  * order -- столбец, по которому осуществляется сортировка (по умолчанию -- postdate)
  * desc -- сортировка в обратном порядке (по умолчанию -- нет)
  * relation -- получать информацию об отношениях пользователей (для скрытия сообщений от игнорируемых)
  * ratings -- получение данных о том, было ли прорейтинговано сообщение текущим пользователем
  * По умолчанию возвращаются только сообщения в нормальном состоянии, однако это можно изменить с помощью следующих параметров:
  * deleted -- возвращать только темы, помеченные к удалению
  * premod -- возвращать только темы, стоящие на премодерации
  * all -- возвращать все темы, статус может быть любым
  **/
  function get_posts($cond) {
    if (empty($cond)) trigger_error('Ошибка: массив условий выборки сообщений пуст! Прерываем работу во избежание выгрузки всей базы!',E_USER_ERROR);
    $where = '1=1';
    $columns = '';

    if (!empty($cond['fid'])) $where.=' AND '.Library::$app->db->array_to_sql($cond['fid'],'t.fid');
    if (!empty($cond['tid'])) $where.=' AND p.tid='.intval($cond['tid']);
    $uid = Library::$app->get_uid();

    if (!empty($cond['id'])) {
       $where.=' AND '.Library::$app->db->array_to_sql($cond['id'],'p.id');
    }

    if (!empty($cond['user'])) $columns.=', u.login, u.display_name, u.gender, u.location, u.signature, u.avatar, u.status, '.
      'g.level, CASE WHEN u.title!=\'\' AND custom_title=\'1\' THEN u.title ELSE g.name END AS user_title, g.links_mode, '.
      'ue.post_count, ue.rating AS user_rating, ue.warnings, ue.reg_date, CAST(u.status=\'2\' OR ue.banned_till>='.intval(Library::$app->time).' AS int) AS banned, ue.banned_till';
    if (!empty($cond['topics'])) $columns.=', t.title AS t_title, CONCAT(f.hurl,\'/\',CASE WHEN t.hurl!=\'\' THEN t.hurl ELSE CAST(t.id AS VARCHAR(32)) END,\'/\') AS full_hurl, f.id AS fid, f.title AS f_title, f.hurl AS f_hurl';
    if (!empty($cond['relation'])) $columns .= ', rl.type AS relation';
    if (!empty($cond['ratings'])) $columns .= ', CAST(r.value IS NOT NULL AS INT) AS rated';
    if (empty($cond['notext'])) $columns.= ', tx.data AS text, tx.tx_lastmod '; // если не указана выборка "без текста", то получаем и текст сообщения
    if (!empty($cond['blocklinks'])) $columns.= ', blcklink.data AS blocklinks'; // если указана загрузка данных о блочных ссылках

    if (empty($cond['deleted']) && empty($cond['premod']) && empty($cond['all'])) $where.=' AND p.status=\'0\''; // по умолчанию выбираем только сообщения с обычным статусом
    elseif (!empty($cond['deleted'])) $where.=' AND p.status=\'2\'';
    elseif (!empty($cond['premod'])) $where.=' AND p.status=\'1\'';
    if (!empty($cond['valued'])) $where.=' AND p.value=\'1\'';
    elseif (!empty($cond['noflood'])) $where.=' AND p.value!=\'-1\'';
    if (!empty($cond['before_pid'])) $where.=' AND p.id<'.intval($cond['before_pid']);
    if (!empty($cond['after_pid'])) $where.=' AND p.id>'.intval($cond['after_pid']);
    if (!empty($cond['before_time'])) $where.=' AND p.postdate<'.intval($cond['before_time']);
    if (!empty($cond['after_time'])) $where.=' AND p.postdate>'.intval($cond['after_time']);
    if (!empty($cond['owner'])) $where.=' AND p.uid='.intval($cond['owner']);
    if (!empty($cond['search'])) $where.=' AND sr.sid='.intval($cond['search']).' AND sr.oid=p.id';

    $sql = 'SELECT p.*'.$columns.' FROM ';
    if (!empty($cond['search'])) $sql.=DB_prefix.'search_result sr, ';
    $sql.=DB_prefix.'post p ';
    if (empty($cond['notext'])) $sql.='LEFT JOIN '.DB_prefix.'text tx ON (p.id=tx.id AND tx.type=16) ';
    if (!empty($cond['user'])) {
      $sql.='LEFT JOIN '.DB_prefix.'user u ON (p.uid=u.id) '.
      'LEFT JOIN '.DB_prefix.'user_ext ue ON (p.uid=ue.id) '.
      'LEFT JOIN '.DB_prefix.'group g ON (ue.group_id=g.level) ';
    }
    if (!empty($cond['relation'])) $sql.='LEFT JOIN '.DB_prefix.'relation rl ON (rl.from_='.intval($uid).' AND rl.to_=p.uid) ';
    if (!empty($cond['ratings'])) $sql.='LEFT JOIN '.DB_prefix.'rating r ON (r.id=p.id AND r.uid='.intval($uid).') ';
    if (!empty($cond['topics']) || !empty($cond['fid'])) $sql.='LEFT JOIN '.DB_prefix.'topic t ON (t.id=p.tid) '.
                'LEFT JOIN '.DB_prefix.'forum f ON (f.id=t.fid) ';
    if (!empty($cond['blocklinks'])) $sql.='LEFT JOIN '.DB_prefix. 'text blcklink ON (blcklink.id=p.id AND blcklink.type=19) '; // 19 -- код типа данных для blocklink

    $order = !empty($cond['order']) ? $cond['order'] : 'postdate';
    $sql.='WHERE '.$where.' ORDER BY '.$order;
    if (!empty($cond['sort']) && $cond['sort']==='DESC') $sql.=' DESC';
    $offset = isset($cond['start']) ? $cond['start'] : false; // граничные условия для LIMIT
    $count = isset($cond['perpage']) ? $cond['perpage'] : false;
    if ($count!==false && $offset===false) $offset=0;

    $result=Library::$app->db->select_all($sql,$offset,$count);

    if (!empty($cond['attach'])) { // если запрошено получение прикрепленных файлов
      $pids = array();
      for ($i=0, $count=count($result);$i<$count;$i++) $pids[]=$result[$i]['id'];
      $sql = 'SELECT TRIM(fkey) AS fkey, oid, filename, size, format, is_main, descr, exif, geo_longtitude, geo_latitude FROM '.DB_prefix.'file '.
        'WHERE '.Library::$app->db->array_to_sql($pids,'oid').' AND type=\'1\'';
      $attaches = Library::$app->db->select_super_hash($sql,'oid');
      for ($i=0, $count=count($result);$i<$count;$i++) {
        $pid = $result[$i]['id'];
        if (!empty($attaches[$pid])) {
          $result[$i]['attach']=$attaches[$pid];
          foreach ($result[$i]['attach'] as &$item) { 
            $item['path']='f/up/1/'.$item['oid'].'-'.$item['fkey'].'/'.$item['filename']; // формируем path здесь, а не в шаблоне
            if (!empty($item['exif'])) $item['exif']=json_decode($item['exif'],true); // раскодируем из JSON EXIF-данные
            $item['extension']=pathinfo($item['filename'],PATHINFO_EXTENSION); // получаем расширение файла
          }
        }
      }
    }
    return $result;
  }

  /** Подсчет количества сообщений, удовлетворяющих указанным условиям
  * @param $cond array Массив данных с параметрами выборки.
  * На данный момент может включать в себя:
  * tid -- номер темы
  * pid -- номер сообщения или массив с идентификаторами нескольких сообщений
  * По умолчанию возвращаются только сообщения в нормальном состоянии, однако это можно изменить с помощью следующих параметров:
  * deleted -- возвращать только темы, помеченные к удалению
  * premod -- возвращать только темы, стоящие на премодерации
  * all -- возвращать все темы, статус может быть любым
  **/
  function count_posts($cond) {
    if (empty($cond)) trigger_error('Ошибка: массив условий выборки сообщений пуст! Прерываем работу во избежание выгрузки всей базы!',E_USER_ERROR);
    $where = '1=1';

    if (!empty($cond['fid'])) $where.=' AND p.tid=t.id AND t.fid='.intval($cond['fid']);
    if (!empty($cond['tid'])) $where.=' AND tid='.intval($cond['tid']);
    if (!empty($cond['id'])) {
      if (is_array($cond['id'])) $where.=' AND '.Library::$app->db->array_to_sql($cond['id'],'id');
      else $where.=' AND id='.intval($cond['id']);
    }

    if (empty($cond['deleted']) && empty($cond['premod']) && empty($cond['all'])) $where.=' AND p.status=\'0\''; // по умолчанию выбираем только сообщения с обычным статусом
    elseif (!empty($cond['deleted'])) $where.=' AND p.status=\'2\'';
    elseif (!empty($cond['premod'])) $where.=' AND p.status=\'1\'';

    if (!empty($cond['valued'])) $where.=' AND p.value=\'1\'';
    elseif (!empty($cond['noflood'])) $where.=' AND p.value!=\'-1\''; // TODO: подумать, возможно, скрытие флуд-сообщений лучше сделать на уровне отображения

    if (!empty($cond['before_pid'])) $where.='AND p.id<'.intval($cond['before_pid']);
    if (!empty($cond['after_pid'])) $where.='AND p.id>'.intval($cond['after_pid']);
    if (!empty($cond['owner'])) $where.=' AND p.uid='.intval($cond['owner']);

    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'post p ';
    if (!empty($cond['fid'])) $sql.=', '.DB_prefix.'topic t ';
    $sql.='WHERE '.$where;

    return Library::$app->db->select_int($sql);
  }

  /** Получение первых сообщений для указанных тем. Полезно для блогов, микроблогов и т.п. **/
  function get_first_posts($topics,$options = array()) {
    $pids = array();
    for ($i=0,$count=count($topics);$i<$count;$i++) {
      $pids[]=$topics[$i]['first_post_id'];
    }
    $options['id']=$pids;
    $options['fid']=Library::$app->forum['id'];
    $options['user']=true;
    $options['attach']=true;
    $options['blocklinks']=true;
    $posts = $this->get_posts($options);
    $bcode = Library::$app->load_lib('bbcode',false);
    for ($i=0,$count=count($topics);$i<$count;$i++) {
      for ($j=0,$count2=count($posts);$j<$count2;$j++) {
        if ($posts[$j]['tid']==$topics[$i]['id']) {
          if ($bcode) $posts[$j]['text']=$bcode->parse_msg($posts[$j]);
          $topics[$i]['post']=$posts[$j];
        }
      }
      $topics[$i]['del_key'] = Library::$app->gen_auth_key(false,'delete_topic',Library::$app->url('moderate/'.Library::$app->forum['hurl'].'/'.$topics[$i]['t_hurl']));
    }
    return $topics;
  }

  /** Получение данных об опросе
  **/
  function get_poll($tid) {
    $uid = Library::$app->get_uid();
    $sql = 'SELECT question, endtime, pvid FROM '.DB_prefix.'poll pl '.
    'LEFT JOIN '.DB_prefix.'vote v ON (pl.id=v.tid AND v.uid='.intval($uid).') '.
    'WHERE id='.intval($tid);
    $result = Library::$app->db->select_row($sql);
    if (!empty($result)) {
      $sql = 'SELECT id,text,count FROM '.DB_prefix.'poll_variant WHERE tid='.intval($tid);
      $result['variants'] = Library::$app->db->select_all($sql);
      $result['summ'] = 0;
      $result['max'] = 0;
      for ($i=0, $count=count($result['variants']); $i<$count; $i++) {
        $result['summ'] += $result['variants'][$i]['count'];
        if ($result['variants'][$i]['count']>$result['max']) $result['max']=$result['variants'][$i]['count'];
      }
      $result['closed'] = $result['endtime'] && $result['endtime']<Library::$app->time;
    }
    return $result;
  }


  /** Определяет возможности пользователя в текущем разделе или теме, которые будут учитываться в форме отправки сообщения
  * @return array Хеш-массив со списком разрешений. Содержит следующие ключи:
  *  html -- разрешено ли использование HTML
  *  bcode -- разрешено ли использование BoardCode
  *  smiles -- максимальное количество смайликов в сообщении
  *  attach -- максимальный размер прикрепляемых файлов
  *  links -- возможность использования гиперссылок
  *  topic -- возможность создания новых тем
  *  post -- возможность создания новых сообщений
  *  poll -- возможность создания опросов
  *  sticky -- право на создание приклееной темы
  *  sticky_post -- возможность влиять на настройку приклеивания первого сообщения в теме (TRUE -- пользователь может выбирать значение, FALSE -- определяется форумом)
  *  lock -- возможность закрыть тему или запретить редактирование сообщения
  *  favorites -- возможность добавить тему в "Избранное" форума
  *  delete -- право на удаление сообщения
  *  value -- права оценивать ценность поста
  *  backdate -- право отправлять сообщение с не-текущей датой
  *  tags -- право выставлять теги для темы
  *  ip -- право просматривать IP-адреса 
  **/
  function get_permissions() {
    $result['html']=Library::$app->check_access('html');
    $result['bcode']=Library::$app->forum['bcode'];
    $result['smiles']=Library::$app->forum['max_smiles'];
    $result['attach']=Library::$app->check_access('attach') && (Library::$app->forum['max_attach']>0);
    $result['links']=Library::$app->get_opt('links_mode','group');
    $result['topic']=Library::$app->check_access('topic') && empty(Library::$app->forum['locked']); // темы можно создавать, если есть права и форум не закрыт
    $result['post']=Library::$app->check_access('post') && ((empty(Library::$app->forum['locked']) && empty(Library::$app->topic['locked']) || Library::$app->is_moderator())); // сообщения можно писать, если есть права и не закрыт ни форум, ни тема
    $result['poll']=Library::$app->check_access('poll');
    $result['sticky']=Library::$app->is_moderator();
    $result['lock']=Library::$app->is_moderator();
    $result['favorites']=Library::$app->is_moderator();
    $result['delete']=Library::$app->is_moderator();
    $result['value']=Library::$app->is_moderator();
    $result['postdate']=Library::$app->is_moderator(); // разрешение изменять дату сообщения
    $result['tags']=Library::$app->forum['tags']==0 ? false : (Library::$app->forum['tags']==2 ? Library::$app->is_moderator() : true ); // если режим тегов равен 2, то их исползовать могут только модераторы, если 1 — все
    if (!Library::$app->forum['sticky_post']==1)  $result['sticky_post']=Library::$app->is_moderator();
    elseif (Library::$app->forum['sticky_post']==2) $result['sticky_post']=true;
    else $result['sticky_post']=false;
    $result['ip']=Library::$app->is_moderator(false); // IP-адреса могут видеть настоящие модераторы, но не кураторы
    return $result;
  }

  /** Установка значений по умолчанию для нового сообщения **/
  function set_new_post($perms) {
    $result['html']="0";
    $result['bcode']=$perms['bcode'];
    $result['smiles']=$perms['smiles']!=0;
    $result['links']="1";
    $result['typograf']="1";
    $session_name = defined('CONFIG_session') ? CONFIG_session : 'ib_sid';
    $result['author']=(Library::$app->is_guest() && !empty($_COOKIE[$session_name.'_guest'])) ? $_COOKIE[$session_name.'_guest'] : Library::$app->get_username();
    $result['uid']=Library::$app->get_uid();
    $result['value']="0";
    return $result;
  }

  /** Проверка на то, что HURL темы является уникальным **/
  function check_unique_hurl($topic) {
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'topic t WHERE hurl=\''.Library::$app->db->slashes($topic['hurl']).'\'';
    if (!empty($topic['id'])) $sql.=' AND t.id!='.intval($topic['id']);
    $count = Library::$app->db->select_int($sql);
    return $count==0;
  }
}
