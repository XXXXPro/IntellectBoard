<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Модуль работы с закладками, обновившимся/непрочитанными сообщениями, "Избранным форума"
 *  ================================ */

class bookmark extends Application {
  function get_data($cond) {
    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку
    /* @var $tlib Library_topic */
    $cond['fid'] = $this->get_forum_list('read');
    $cond['forums']=true;
    $cond['first']=true;
    $cond['last']=true;
    $cond['views']=true;
    $cond['polls']=true;

     if (!$this->is_guest()) { // если пользователь -- не гость, учитываем новые сообщения
      $sql = 'SELECT mark_time FROM '.DB_prefix.'mark_all WHERE fid=0 AND uid='.intval($this->get_uid());
      $cond['subscr']=true;
      $cond['new_time']=$this->db->select_int($sql);
    }

    $pagedata['total']=$tlib->count_topics($cond);
    $pagedata['page'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
    $pagedata['perpage'] = $this->get_opt('topics_per_page','user');
    if (!$pagedata['perpage']) $pagedata['perpage'] = $this->get_opt('topics_per_page');
    if (!$pagedata['perpage']) $pagedata['perpage'] = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль
    $this->out->pages=$this->get_pages($pagedata,false,true);
    $cond['perpage']=$this->out->pages['perpage'];
    $cond['start']=$this->out->pages['start'];

    $this->out->topics=$tlib->list_topics($cond);

    $pperpage = false;
    if (!$pperpage) $pperpage = $this->get_opt('posts_per_page','user'); // берем из настроек пользователя
    if (!$pperpage) $pperpage = $this->get_opt('posts_per_page'); // берем из настроек пользователя
    if (!$pperpage) $pperpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль

    for ($i=0, $count=count($this->out->topics); $i<$count; $i++) { // генерируем страницы
      $tpages['total']=$this->out->topics[$i]['post_count'];
      $tpages['perpage']=$pperpage;
      $tpages['page']=NULL; // никакую страницу не надо показывать как выделенную
      if (isset($_SESSION['topic'.$this->out->topics[$i]['id']]) && isset($_SESSION['topic'.$this->out->topics[$i]['id']]['perpage'])) $tpages['perpage'] = intval($_SESSION['topic'.$this->out->topics[$i]['id']]['perpage']);
      $this->out->topics[$i]['pages']=$this->get_pages($tpages,false,false);
    }
  }

  function action_view() {
    if ($this->is_guest()) $this->output_403('Гостям не разрешается использовать форумные закладки!',true);
    $cond['bookmark']=true;
    $this->get_data($cond);
    $this->out->delete_items=true; // дополнительное поле, чтобы можно было удалять закладки
    $this->out->checkbox_name='bookmark';
  }

  /** Удаление неиспользуемых данных **/
  function action_delete() {
    $uid = $this->get_uid();
    if (!empty($_REQUEST['bookmark'])) {
      $sql = 'UPDATE '.DB_prefix.'last_visit SET bookmark=\'0\' WHERE uid='.intval($uid).
      ' AND type=\'topic\' AND '.$this->db->array_to_sql($_REQUEST['bookmark'],'oid');
      $this->db->query($sql);
    }
    $this->redirect($this->referer());
  }

/* Просмотр тем, на которые отвечал пользователь */
  function action_mytopics() {
    if ($this->is_guest()) $this->output_403('Гостям не разрешается просматривать список тем со своими сообщениями!',true);
    $cond['starter_id']=$this->get_uid(); // TODO: подумать, возможно, со временем нужно будет сделать возможность смотреть темы любого пользователя
    $this->get_data($cond);
    return 'bookmark/view.tpl';
  }

/* Просмотр тем, на которые подписан пользователь */
  function action_subscr() {
    if ($this->is_guest()) $this->output_403('Гостям не разрешается использовать подписку!',true);
    $uid = $this->get_uid();
    $cond['subscribed']=true;
    $this->get_data($cond);

    $sql = 'SELECT f.id,f.title,f.hurl, lv.subscribe FROM '.DB_prefix.'forum f '.
    'LEFT JOIN '.DB_prefix.'last_visit lv ON (lv.uid='.intval($uid).' AND lv.oid=f.id AND lv.type=\'forum\') '.
    'ORDER BY sortfield';
    $forums = $this->db->select_all($sql);
    $this->out->forums=array();
    for ($i=0, $count=count($forums); $i<$count; $i++) if ($this->check_access('view',$forums[$i]['id'])) $this->out->forums[]=$forums[$i];

    $sql = 'SELECT lv.subscribe FROM '.DB_prefix.'last_visit lv WHERE lv.oid=\'0\' AND lv.type=\'forum\' AND lv.uid='.intval($uid);
    $this->out->subscribe_all=$this->db->select_int($sql);

    $this->out->authkey=$this->gen_auth_key($uid,'unsubscr',$this->url('bookmark/'));
  }

  function action_unsubscr() {
    if ($this->is_guest()) $this->output_403('Гостям не разрешается использовать подписку!',true);
    if (empty($_REQUEST['authkey'])) $this->output_403('Ошибка авторизации по ключу');
    $uid = $this->get_uid();
    if (!empty($_REQUEST['subscribe'])) {
      $sql = 'UPDATE '.DB_prefix.'last_visit SET subscribe=\'0\' WHERE uid='.intval($uid).
      ' AND type=\'topic\' AND '.$this->db->array_to_sql($_REQUEST['subscribe'],'oid');
      $this->db->query($sql);
      $rows = $this->db->affected_rows();
      if ($rows) $this->message('Вы отписались от '.$this->incline($rows,'%d темы','%d тем','%d тем').'.',1);
    }
    if (isset($_REQUEST['unsubscribe_forum'])) {
      $sql = 'UPDATE '.DB_prefix.'last_visit SET subscribe=\'0\' WHERE uid='.intval($uid).
      ' AND type=\'forum\' AND oid='.intval($_REQUEST['unsubscribe_forum']);
      $this->db->query($sql);
      if ($_REQUEST['unsubscribe_forum']) $this->message('Вы отписаны от уведомлений в разделе.',1);
      else $this->message('Вы отписались от уведомлений на всем форуме.',1);
    }
    if (!empty($_REQUEST['forums'])) {
      $sql = 'UPDATE '.DB_prefix.'last_visit SET subscribe=\'0\' WHERE uid='.intval($uid).
      ' AND type=\'forum\'';
      $this->db->query($sql);
      if (!empty($_REQUEST['subscribe_forum'])) {
        foreach($_REQUEST['subscribe_forum'] as $oid) { // на случай, если пользователь ещё не заходил в разделы
          $data['oid']=intval($oid);
          $data['uid']=intval($uid);
          $data['type']='forum';
          $data['subscribe']='1';
          $data['visit1']=$this->time;
          $this->db->insert_ignore(DB_prefix.'last_visit',$data);
        }
        $sql = 'UPDATE '.DB_prefix.'last_visit SET subscribe=\'1\' WHERE uid='.intval($uid).
        ' AND type=\'forum\' AND '.$this->db->array_to_sql($_REQUEST['subscribe_forum'],'oid');
        $this->db->query($sql);
      }
    }
    $this->redirect($this->referer());
  }

/* Просмотр обновившихся тем */
  function action_updated() {
    $period = $this->get_opt('topics_period','user');
    if ($period<=0 || $period>30) $period=30; // если у пользователя не выставлен лимит или он слишком велик, выставляем его равным 30 дням во избежание выгрузки всей базы
    $cond['after_time']=$this->time-$period*24*60*60;
    $cond['not_flood']=true;
    $cond['order']='last_post_date';
    $this->get_data($cond);
    return 'bookmark/view.tpl';
  }

/* Посмотр непрочитанных */
  function action_unread() {
    if ($this->is_guest()) $this->output_403('Гостям не разрешается использовать подписку!',true);
    $cond['newposts']=true;
    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку
    $cond['fid'] = $this->get_forum_list('read');
    $cond['forums']=true;
    $cond['last']=true;
    $cond['order']='f.sortfield, last_post_time';
    $cond['not_flood']=true;

     if (!$this->is_guest()) { // если пользователь -- не гость, учитываем новые сообщения
      $sql = 'SELECT mark_time FROM '.DB_prefix.'mark_all WHERE fid=0 AND uid='.intval($this->get_uid());
      $cond['subscr']=true;
      $cond['new_time']=$this->db->select_int($sql);
    }

    $this->out->topics=$tlib->list_topics($cond);
    // из-за специфического вывода "непрочитанных" обходимся без пагинации
  }

/* Просмотр тем без ответов */
  function action_unanswered() {
    $cond['not_flood']=true;
    $cond['unanswered']=true;
    $this->get_data($cond);
    return 'bookmark/view.tpl';
  }

/*Просмотр тем в списке избранных тем форума */
  function action_favorites() {
    $cond['favorites']=true;
    $this->get_data($cond);
    return 'bookmark/view.tpl';
  }

function action_updated_rss() {
    $this->out->intb->link=$this->http($this->url('newtopics/'));
    $this->out->intb->descr=$this->get_opt('site_title');

    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку

    // Формируем условие выборки: в RSS выдаем все сообщения, не являющиеся флудом
    // из разделов, на которые у пользователя есть права чтения
    // с получением данных о темах, но без рейтингов и расширенной информации о пользователях
    $cond['noflood']=true;
    $cond['fid'] = $this->get_forum_list('read',0,true);
    $cond['topics'] = true;
    $cond['sort']='DESC';

    // определяем время, за которое выдавать сообщения в RSS-поток.
    $period = 1; // если период для выдачи сообщений не указан явно, выдаем за последние сутки
    $cond['after_time']=max(intval($this->if_modified_time),$this->time-$period*24*60*60);
    $limit = $this->get_opt('rss_max_items');
    if (!$limit) $limit=250;
    $cond['offset']=0;
    $cond['perpage']=$limit; // ограничиваем число выводимых сообщений в RSS во избежание DoS-атак и перегрузки сервера

    $data = $tlib->get_posts($cond);
//    if (empty($data)) $this->output_304();
    $bbcode = $this->load_lib('bbcode');
    $count=count($data);
    for ($i=0; $i<$count; $i++) {
      $data[$i]['text']=$bbcode->parse_msg($data[$i]);
      $data[$i]['link']=$this->http($this->url($data[$i]['full_hurl'].'post-'.$data[$i]['id'].'.htm'));
      $data[$i]['title']=$data[$i]['t_title'].', сообщение от '.($this->long_date($data[$i]['postdate']));
    }
    $this->out->items=$data;
  }

  function set_lastmod() {
/*    $max=0;
    for ($i=0, $count=count($this->out->topics); $i<$count; $i++) {
      if ($this->out->topics[$i]['last_post_time']>$max) $max=$this->out->topics[$i]['last_post_time'];
    }
    $this->lastmod = max($this->lastmod,$max);*/
    $this->lastmod=$this->time; // принудительно выставляем обновление страницы, т.к. использование кеширования в данном случае потребует слишком сложных запросов
  }

    function set_title() {
    $result=false;
    if ($this->action==='view') $result = 'Ваши закладки';
    elseif ($this->action==='mytopics') $result = 'Созданные вами темы';
    elseif ($this->action==='subscr') $result = 'Ваши подписки';
    elseif ($this->action==='updated') $result = 'Обновившиеся темы';
    elseif ($this->action==='unread') $result = 'Непрочитанные темы';
    elseif ($this->action==='unanswered') $result = 'Темы без ответов';
    elseif ($this->action==='favorites') $result = 'Избранные темы форума';
    if ($this->action==='updated_rss') $result = 'Новые сообщения форума «'.$this->get_opt('site_title').'»';
    $result.=' :: '.$this->get_opt('site_title');
    return $result;
  }

  function set_location() {
    $result = parent::set_location();
    if ($this->action==='view') $result[]=array('Закладки');
    elseif ($this->action==='mytopics') $result[]=array('Ваши темы');
    elseif ($this->action==='subscr') $result[]=array('Подписки');
    elseif ($this->action==='updated') $result[]=array('Обновившиеся темы');
    elseif ($this->action==='unread') $result[]=array('Непрочитанные темы');
    elseif ($this->action==='unanswered') $result[]=array('Темы без ответов');
    elseif ($this->action==='favorites') $result[]=array('Избранные темы');
    return $result;
  }

  function get_action_name() {
    if ($this->action==='view') $result='Просматривает темы в закладках';
    elseif ($this->action==='mytopics') $result='Просматривает список созданных им тем';
    elseif ($this->action==='subscr') $result='Просматривает настройки подписки на темы и разделы';
    elseif ($this->action==='updated') $result='Просматривает список обновившихся тем';
    elseif ($this->action==='unread') $result='Просматривает список непрочитанных тем';
    elseif ($this->action==='unanswered') $result='Просматривает темы без ответов';
    elseif ($this->action==='favorites') $result='Просматривает избранные темы форума';
    else $result=parent::get_action_name();
     return $result;
  }

  function get_request_type() {
    if ($this->action==='updated_rss') return 2;
    else return parent::get_request_type();
  }

  function set_rss() {
    return array(array('url'=>$this->url('newtopics/rss.htm'),'title'=>'Подписка на обновления'));
  }
}
