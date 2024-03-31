<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2009, 2011-2014 4X_Pro, INTBLITE.RU
 *  http://intbpro.ru
 *  Модуль поиска
 *  ================================ */

/**  **/
class search extends Application {
  function action_view() {
    $mode = $this->get_opt('search_mode');
    
    if ($mode==='yandex') return $this->search_yandex();
    elseif ($mode==='google') return $this->search_google();
    elseif ($mode==='fulltext') return $this->search_fulltext();
    elseif ($mode==='sphinx') return $this->search_sphinx();
    else $this->output_403('Поиск отключен администрацией форума или некорректно настроен!');
  }
  
  function search_yandex() {
    $yandex_id = $this->get_opt('search_yandex_id');
    if (!$yandex_id) $this->output_403('Поиск с помощью Яндекса не настроен, обратитесь к администратору форума, чтобы он это сделал.');
    $this->out->yandex_id = $yandex_id;
    return 'search/yandex.tpl';
  }
  
  function search_google() {
    $google_id = $this->get_opt('search_google_id');
    if (!$google_id) $this->output_403('Поиск с помощью Google не настроен, обратитесь к администратору форума, чтобы он это сделал.');
    $this->out->google_id = $google_id;
    return 'search/google.tpl';
  }
  
  function search_fulltext() {
    if (!$this->db->has_fulltext()) trigger_error('Используемая база данных не поддерживает полнотекстовый поиск! Попросите администратора изменить настройки поиска!',E_USER_ERROR);

    // Поиск возможен только в тех разделах, в которых у пользователя есть права на чтение, 
    // при этом если пользователь не выбирал разделы для поиска явно, из поиска будут исключены разделы с признаком is_flood      
    $forum_ids = $this->get_forum_list('read',0,empty($_REQUEST['extdata']['by_forum']));  
    if (!$this->is_post()) { // если поисковый запрос не введен, выводим форму для его ввода
      $this->prepare_search_form();
      return 'search/view.tpl';
    }
    else { // иначе осуществляем поиск и делаем редирект на результат
      if (empty($_REQUEST['search']['query']) || empty($forum_ids)) {
        if (empty($forum_ids)) $this->message('Вам недоступен ни один из разделов',1);
        else $this->message('Текст запроса не может быть пустым',1);
        $this->prepare_search_form();
        return 'search/view.tpl';
      }
      if (!empty($_REQUEST['extdata']['by_forum'])) { // если пользователь выбрал только какие-то определенные размеры
        $selected_ids = $_REQUEST['extdata']['selected'];
        $forum_ids = array_intersect($forum_ids,$selected_ids);
      }      
      $this->check_timeout();
      
      $data['query']=$_REQUEST['search']['query'];
      $data['owner']=$this->get_uid();
      $data['output_mode']=(isset($_REQUEST['search']['output_mode'])) ? $_REQUEST['search']['output_mode'] : 'posts'; // определяем, как ищем: по сообщениям или темам
      if ($data['output_mode']=='topics') $data['search_type']=1; // в зависимости от этого сохраняем соответствующий режим поиска  
      else $data['search_type']=0;
      $data['time']=$this->time;
      if (!empty($_REQUEST['extdata'])) $data['extdata']=serialize($_REQUEST['extdata']);
      else $data['extdata']='';
      $this->db->insert(DB_prefix.'search',$data);
      $data['id']=$this->db->insert_id();
      
      if (!$data['id']) trigger_error('Неизвестная ошибка при сохранении поискового запроса!',E_USER_ERROR);
      
      if ($data['search_type']==0) { // если ищем по сообщениям 
        $sql = 'INSERT INTO '.DB_prefix.'search_result (sid,oid,relevancy) '.
           'SELECT '.intval($data['id']).', p.id, '.$this->db->full_relevancy('tx.data',$_REQUEST['search']['query']).' AS relevancy '.
           'FROM '.DB_prefix.'text tx '.
           'LEFT JOIN '.DB_prefix.'post p ON (p.id=tx.id) '.
           'LEFT JOIN '.DB_prefix.'topic t ON (p.tid=t.id) '.
           'LEFT JOIN '.DB_prefix.'forum f ON (f.id=t.fid) '.
           'WHERE tx.type=16 AND '.$this->db->full_match('data',$_REQUEST['search']['query']).' AND p.status=\'0\' AND t.status=\'0\' '.
           'AND '.$this->db->array_to_sql($forum_ids,'f.id');
        if (!empty($_REQUEST['extdata']['by_date'])) {
          if (!empty($_REQUEST['extdata']['start_date'])) $sql.=' AND p.postdate>='.strtotime($_REQUEST['extdata']['start_date']);
          if (!empty($_REQUEST['extdata']['end_date'])) $sql.=' AND p.postdate<='.(strtotime($_REQUEST['extdata']['end_date'])+24*60*60-1);
        }
        if (!empty($_REQUEST['extdata']['by_value']) && !empty($_REQUEST['extdata']['flood'])) {
          if ($_REQUEST['extdata']['flood']=='noflood') $sql.=' AND p.value!=\'-1\'';
          elseif ($_REQUEST['extdata']['flood']=='valued') $sql.=' AND p.value=\'1\'';
        }
      }
      else { // если ищем по темам
        $sql = 'INSERT INTO '.DB_prefix.'search_result (sid,oid,relevancy) '.
           'SELECT '.intval($data['id']).', t.id, '.$this->db->full_relevancy('t.title, t.descr',$data['query']).' AS relevancy '.
           'FROM '.DB_prefix.'topic t '.
           'LEFT JOIN '.DB_prefix.'forum f ON (f.id=t.fid) '.
           'WHERE '.$this->db->full_match('t.title,t.descr',$data['query']).' AND t.status=\'0\''.
           'AND '.$this->db->array_to_sql($forum_ids,'f.id');      
      }
      echo "SQL ".$sql;
      $this->db->query($sql);
      $this->redirect($this->http($this->url('search/'.$data['id'].'/')));   
    }    
  }
  
  function search_sphinx() {
    $forum_ids = $this->get_forum_list('read',0,empty($_REQUEST['extdata']['by_forum'])); // поиск возможен только в тех разделах, в которых у пользователя есть права на чтения
    if (!$this->is_post()) { // если поисковый запрос не введен, выводим форму для его ввода
      $this->prepare_search_form();
      return 'search/view.tpl';
    }
    else { // иначе осуществляем поиск и делаем редирект на результат
      if (empty($_REQUEST['search']['query']) || empty($forum_ids)) {
        if (empty($forum_ids)) $this->message('Вам недоступен ни один из разделов',1);
        else $this->message('Текст запроса не может быть пустым',1);
        $this->prepare_search_form();
        return 'search/view.tpl';
      }
      if (!empty($_REQUEST['extdata']['by_forum'])) { // если пользователь выбрал только какие-то определенные размеры
        $selected_ids = $_REQUEST['extdata']['selected'];
        $forum_ids = array_intersect($forum_ids,$selected_ids);
      }
      $this->check_timeout();
      
      $data['query']=$_REQUEST['search']['query'];
      $data['owner']=$this->get_uid();
      $data['output_mode']=(isset($_REQUEST['search']['output_mode'])) ? $_REQUEST['search']['output_mode'] : 'posts'; // определяем, как ищем: по сообщениям или темам
      if ($data['output_mode']=='topics') $data['search_type']=1; // в зависимости от этого сохраняем соответствующий режим поиска
      else $data['search_type']=0;
      $data['time']=$this->time;
      if (!empty($_REQUEST['extdata'])) $data['extdata']=serialize($_REQUEST['extdata']);
      else $data['extdata']='';
      $this->db->insert(DB_prefix.'search',$data);
      $sr['sid']=$this->db->insert_id();
  
      if (!$sr['sid']) trigger_error('Неизвестная ошибка при сохранении поискового запроса!',E_USER_ERROR);
      $cond = array();
  
      if (!empty($_REQUEST['extdata']['by_date'])) {
        if (!empty($_REQUEST['extdata']['start_date'])) $cond['start_date']=strtotime($_REQUEST['extdata']['start_date']);
        if (!empty($_REQUEST['extdata']['end_date'])) $cond['end_date']=(strtotime($_REQUEST['extdata']['end_date'])+24*60*60-1);
      }
      if (!empty($_REQUEST['extdata']['by_value']) && !empty($_REQUEST['extdata']['flood'])) {
        if ($_REQUEST['extdata']['flood']=='noflood') $cond['value']=array('0','1');
        elseif ($_REQUEST['extdata']['flood']=='valued') $cond['value']=array('1');
      }
     
      /** @var Library_sphinx $sphinx_lib */
      $sphinx_lib = $this->load_lib('sphinx',true);
       
      $oids = $sphinx_lib->search($_REQUEST['search']['query'],$forum_ids,$cond,$data['search_type']);

      foreach ($oids as $value) {
        $value['sid']=$sr['sid'];
        $this->db->insert(DB_prefix.'search_result',$value);
      }      

      $this->redirect($this->http($this->url('search/'.$sr['sid'].'/')));
    }
  }

  /** Поиск всех сообщений пользователя **/
  function action_user_posts() {
    if (empty($_GET['id']) || intval($_GET['id'])<=AUTH_SYSTEM_USERS) $this->output_403('Некорректный идентификатор пользователя');
    $uid = intval($_GET['id']);
    
    if ($this->bot_id!=0) $this->output_403('Поисковым роботам запрещено пользоваться этой функцией!');
    $this->check_timeout();
    
    $userdata=$this->load_user($uid,0);
    if (empty($userdata)) $this->output_404('Пользователь с таким идентификатором не найден!');         
    $data['query']=$userdata['display_name'];
    $data['owner']=$this->get_uid();
    $data['output_mode']='posts';
    $data['search_type']=empty($_GET['valued']) ? 2 : 3;
    $data['extdata']=serialize($uid);
    $data['time']=$this->time;
    $this->db->insert(DB_prefix.'search',$data);
    $sid = $this->db->insert_id();
    $forum_ids = $this->get_forum_list('read'); // поиск возможен только в тех разделах, в которых у пользователя есть права на чтения

    $sql = 'INSERT INTO '.DB_prefix.'search_result (sid,oid,relevancy) '.
      'SELECT '.intval($sid).',p.id, p.postdate FROM '.DB_prefix.'post p, '.DB_prefix.'topic t '.
      'WHERE p.uid='.intval($uid).' AND p.status=\'0\' AND p.tid=t.id AND t.status=\'0\' AND '.$this->db->array_to_sql($forum_ids,'t.fid');
    if (!empty($_GET['valued'])) $sql.=' AND p.value=\'1\'';
    $this->db->query($sql);
    $this->redirect($this->http($this->url('search/'.$sid.'/')));
  }
  
  function action_user_topics() {
   if (empty($_GET['id']) || intval($_GET['id'])<=AUTH_SYSTEM_USERS) $this->output_403('Некорректный идентификатор пользователя');
   $uid = intval($_GET['id']);
  
   if ($this->bot_id!=0) $this->output_403('Поисковым роботам запрещено пользоваться этой функцией!');
   $this->check_timeout();
  
   $userdata=$this->load_user($uid,0);
   if (empty($userdata)) $this->output_404('Пользователь с таким идентификатором не найден!');
   $data['query']=$userdata['display_name'];
   $data['output_mode']='topics';
   $data['search_type']=4;
   $data['extdata']=serialize($uid);
   $data['time']=$this->time;
   $this->db->insert(DB_prefix.'search',$data);
   $sid = $this->db->insert_id();
   $forum_ids = $this->get_forum_list('read'); // поиск возможен только в тех разделах, в которых у пользователя есть права на чтения
  
   $sql = 'INSERT INTO '.DB_prefix.'search_result (sid,oid,relevancy) '.
     'SELECT '.intval($sid).',t.id, t.last_post_time FROM '.DB_prefix.'topic t '.
     'LEFT JOIN '.DB_prefix.'post p ON (t.first_post_id=p.id) '.
     'WHERE p.uid='.intval($uid).' AND t.status=\'0\' AND '.$this->db->array_to_sql($forum_ids,'t.fid');
   $this->db->query($sql);
   $this->redirect($this->http($this->url('search/'.$sid.'/')));
  }  
  
  /** Подготовка данных для вывода формы поиска (выставление лимита времени поиска по умолчанию, списка разделов и т.п. **/
  function prepare_search_form() {
    if (isset($_REQUEST['search'])) $this->out->search=$_REQUEST['search'];
    else {
      $this->out->search['output_mode'] = 'posts';
      $this->out->extdata['flood'] = 'noflood';
      $this->out->extdata['start_date']=date('d.m.Y',$this->time-365*24*60*60);
      $this->out->extdata['end_date']=date('d.m.Y');
    }
    $this->out->forum_list = $this->get_forum_list('read',1);
  }
  
  function action_results() {
    if (empty($_REQUEST['id'])) $this->output_404('Не указан идентификатор поиска');
    $id = intval($_REQUEST['id']);
    $sql = 'SELECT * FROM '.DB_prefix.'search WHERE id='.intval($id);
    $search = $this->db->select_row($sql);
    if (empty($search)) $this->output_404('Неправильный идентификатор поиска');
    if ($search['owner']!=$this->get_uid() && $search['owner']>AUTH_SYSTEM_USERS) $this->output_404('Неправильный идентификатор поиска');
    $this->out->search=$search;
    
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'search_result WHERE sid='.intval($id);
    $count = $this->db->select_int($sql);
    
    $pages['total']=$count;
    $pages['page']=isset($_GET['page']) ? intval($_GET['page']) : 1; // никакую страницу не надо показывать как выделенную

    $pperpage = false;
    if (!$pperpage) $pperpage = $this->get_opt('posts_per_page','user'); // берем из настроек пользователя
    if (!$pperpage) $pperpage = $this->get_opt('posts_per_page');  // берем из настроек сайта в целом
    if (!$pperpage) $pperpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль

    $this->out->forum_list = $this->get_forum_list('read',1);
    $this->out->extdata = (!empty($search['extdata']) ? unserialize($search['extdata']) : false);
        
    $tlib = $this->load_lib('topic',true);
    $cond['search']=$search['id'];
    $cond['order']='relevancy';
    $cond['sort']='DESC';
    $cond['user']=true;
    if ($search['output_mode']==='posts') {
      $pages['perpage']=$pperpage;
       
      $pagedata = $this->get_pages($pages);
      $this->out->pages = $pagedata;
       $cond['perpage']=$pagedata['perpage'];
      $cond['start']=$pagedata['start'];
      $cond['topics']=true;
      
      $posts = $tlib->get_posts($cond);
       $bbcode = $this->load_lib('bbcode');
      $this->out->posts=array();
      foreach ($posts as $post) {
        $post['text']=$bbcode->parse_msg($post);
        $post['signature']=$bbcode->parse_sig($post['signature']);
        $this->out->posts[]=$post;
      }
      $this->out->mod_no_marks=true;
    }
    else {
      $tperpage = false;
      if (!$tperpage) $tperpage = $this->get_opt('topics_per_page','user'); // берем из настроек пользователя
      if (!$tperpage) $tperpage = $this->get_opt('topics_per_page');  // берем из настроек сайта в целом
      if (!$tperpage) $tperpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль
      $pages['perpage']=$tperpage;
      
      $pagedata = $this->get_pages($pages);
      $this->out->pages = $pagedata;
       $cond['perpage']=$pagedata['perpage'];
      $cond['start']=$pagedata['start'];
      
      $cond['forums']=true;
      $cond['first']=true;
      $cond['last']=true;
      $cond['views']=true;
      
      $this->out->topics = $tlib->list_topics($cond);
      for ($i=0, $count=count($this->out->topics); $i<$count; $i++) { // генерируем страницы
        $tpages['total']=$this->out->topics[$i]['post_count'];
        $tpages['perpage']=$tperpage;
        $tpages['page']=NULL; // никакую страницу не надо показывать как выделенную
        if (isset($_SESSION['topic'.$this->out->topics[$i]['id']]) && isset($_SESSION['topic'.$this->out->topics[$i]['id']]['perpage'])) $tpages['perpage'] = intval($_SESSION['topic'.$this->out->topics[$i]['id']]['perpage']);
        $this->out->topics[$i]['pages']=$this->get_pages($tpages,false,false);
      }      
    }    
  }
  
  function check_timeout() {
    $timeout = $this->get_opt('search_timeout');
    if (empty($timeout)) $timeout=2;
    $antibot = $this->load_lib('antibot',false);
    if ($antibot) {
      if (!$antibot->timeout_check('search',$timeout)) {
        $this->output_403($this->incline($timeout,'Поиск разрешен не чаще чем раз в %d секунду!','Поиск разрешен не чаще чем раз в %d секунды!','Поиск разрешен не чаще чем раз в %d секунд!'));
      }
    }
  }
  
  function set_title() {
    $result = parent::set_title();
    if ($this->action=='results') $result='Результаты поиска | '.$result;
    else $result='Поиск | '.$result;
    return $result;
  }
  
  function set_location() {
    $result = parent::set_location();
    if ($this->action=='results') $result[]=array('Результаты поиска');
    else $result[]=array('Поиск по форуму');
    return $result;  
  }
  
  function get_action_name() {
    return 'Проводит поиск по форуму';
  }
}