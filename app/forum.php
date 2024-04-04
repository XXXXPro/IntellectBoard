<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.05
 *  @copyright 2007,2010, 2012-2013, 2018, 2021-2023 4X_Pro, INTBPRO.RU
 *  @url https://intbpro.ru
 *  Корневой модуль раздела сайтового движка Intellect Board 3 Pro
 *  ================================ */

require_once(BASEDIR.'app/app.php');

class Application_Forum extends Application {
  /** Данные о разделе */
  public $forum = null;
  /** Данные о теме */
  public $topic = null;

  /**     Подгрузка данных о разделе и проверки прав доступа и типа модуля. */
  function init_object() {
    if (empty($_REQUEST['f'])) {
            $this->output_404('Некорректно указан идентификатор форума!');
    }
    $forum_url = $_REQUEST['f'];

    $sql = 'SELECT id, module, hurl, title, descr, lastmod, template, parent_id, owner, '.
            'template_override, bcode, max_smiles, topic_count, is_stats, is_flood, sort_mode, locked, '.
            'sort_column, polls, selfmod, sticky_post, rate, rate_value, rate_flood, max_attach, attach_types, post_count, tags, webmention, micropub ';
    if (!$this->is_guest()) {
            $sql.=', visit2 FROM '.DB_prefix.'forum f '.
            ' LEFT JOIN '.DB_prefix.'last_visit lv ON (lv.oid=f.id AND lv.type=\'forum\') ';
    }
    else $sql.='FROM '.DB_prefix.'forum f ';
    if (is_numeric($forum_url)) $sql.='WHERE id=?';
    else $sql.='WHERE hurl=?';
    $this->forum = $this->db->select_row($sql,array($forum_url));

    if (!$this->forum || !$this->check_access('view')) { // если в базе форума с таким ID/HURL не нашлось или пользователю нельзя даже знать о его существовании
            $this->output_404('Запрошенный адрес не существует!');
    }

    if (get_class($this)!=$this->forum['module'] && get_class($this)!=='moderate') { // проверяем корректность типа раздела на случай, если обработка идет не тем скриптов из-за ошибок роутинга в .htaccess
      trigger_error('Тип раздела не соответствует указанному в базе! Работа скрипта прекращена во избежание ошибок. Если вы являетесь администратором этого форума, проверьте правила в файле .htaccess',E_USER_ERROR);
    }

    if (!$this->check_access('read')) {
            $this->output_403('У вас недостаточно прав для просмотра этого раздела!');
    }

    // если задан не только раздел, но и тема
    if (!empty($_REQUEST['t'])) {
      $topic_url = $_REQUEST['t'];
      $tlib = $this->load_lib('topic',true); // если библиотеку работы с темами/сообщениями загрузить не удастся, то считаем это фатальной ошибкой
      $this->topic = $tlib->get_topic($topic_url,0,true);
      
      $result = true;
      if (!$this->topic) $result = false;
      elseif ($this->topic['fid']!=$this->forum['id']) { // если неправильно указан forum id, нужно сделать редирект (полезно для перемещенных тем)
        if ($this->check_access('view',$this->topic['fid'])) {
          $flib = $this->load_lib('forums');
          if (!$flib) $result=false;
          else {
            $newforum = $flib->get_forum($this->topic['fid']);
            if (!$newforum) $result=false;
            else {
              $newurl = $newforum['hurl'].'/';
              $newurl.= (empty($this->topic['hurl'])) ? $this->topic['id'] : $this->topic['hurl'];
              $this->redirect($this->http($this->url($newurl.'/')));
            }
          }
        }
        else $result=false;
      }
      if (!$result) { // если в базе форума с таким ID/HURL не нашлось
        $this->output_404('Запрошенная тема не существует или была удалена!');
      }      
      
      if ($this->topic['hurl'] && $_REQUEST['t']!==$this->topic['hurl']) { // если у темы есть HURL, а ее загрузили по id
        $this->redirect($this->http($this->url($this->topic['full_hurl'])),true);
      }
      $this->out->topic=$this->topic;
    }

    $this->out->forum=$this->forum;
    $this->out->is_moderator = $this->is_moderator(); // для того, чтобы иметь возможность определить в шаблоне, нужно ли выводить модераторские опции
  }

  function init_style() {
    parent::init_style();
    if ($this->forum['template']) { // если в настройках раздела указан какой-то шаблон
      if ($this->forum['template_override']) $this->style=$this->forum['template']; // если включено безусловное переопределение, то заменяем текущий шаблон на тот, который указан в настройках раздела
      elseif (!$this->get_opt('template','user')) $this->style=$this->forum['template']; // если у пользователя не выбран шаблон (т.е. стоит в настройках использовать шаблон сайта), используем шаблон раздела
    }
  }

/** Отслеживание и обновление времени последнего визита **/
  function init_last_visit() {
    if (!$this->is_guest()) {
      if (isset($this->forum) && isset($this->forum['id']) && $this->forum['id']!=0) $forum_id=$this->forum['id'];
      else $forum_id=0;
      $curtime = intval(time());
      $inactive = $this->get_opt('online_time');
      if (!$inactive) $inactive=15;
      $lasttime = $curtime - $inactive*60; // если последнее действие пользователя было до этого времени, переносим его в visit2 (время последнего прошлого визита), иначе оставляем без изменений
      $sql = 'UPDATE '.DB_prefix.'last_visit SET visit2=CASE WHEN visit2<'.$lasttime.' THEN visit1 ELSE visit2 END, visit1='.$curtime.
      ' WHERE uid='.intval($this->get_uid()).' AND (((oid='.intval($this->forum['id']).' OR oid=0) AND type=\'forum\')'; // одновременно обновляем и для текущего раздела, и для форума в целом (записи с fid=0)
      if (!empty($this->topic)) $sql.=' OR (oid='.intval($this->topic['id']).' AND type=\'topic\'))';
      else $sql.=')'; // закрывающая скобка для второй части условия
      $this->db->query($sql);
      $affected =$this->db->affected_rows();

      if ((empty($this->topic) && $affected<2) || (!empty($this->topic) && $affected<3)) {
        $data['uid']=$this->get_uid();
        $data['visit1']=$curtime;
        $data['visit2']=$curtime;
        $data['oid']=0;
        $data['type']='forum';
        $this->db->insert_ignore(DB_prefix.'last_visit',$data);
        $data['oid']=$this->forum['id'];
        $this->db->insert_ignore(DB_prefix.'last_visit',$data);
        if (!empty($this->topic)) {
          $data['oid']=$this->topic['id'];
          $data['type']='topic';
          $this->db->insert_ignore(DB_prefix.'last_visit',$data);
        }
      }
    }
  }

  /** Выборка данных о подразделах, если их показ включен в настройках **/
  function get_subforums() {
    if ($this->get_opt('enable_subforums')) {
      $forumlib=$this->load_lib('forums',true);
      /* @var $forumlib Library_forums */
      $cond['parent']=$this->forum['id'];
      $cond['start']=true;
      $cond['extdata']=true;
      $cond['lastpost']=true;
      if (!$this->is_guest()) {
        $sql='SELECT visit2 FROM ' . DB_prefix . 'last_visit lv WHERE lv.oid=0 AND lv.type=\'forum\' AND lv.uid=' . intval($this->get_uid());
        $last_visit=$this->db->select_int($sql);

        $visits_mode=$this->get_opt('visits_mode'); // получаем режим отслеживания времени последнего визитав: 0 -- на уровне форума, 1 -- на уровне раздела, 2 -- на уровне темы
        if ($visits_mode > 0) $cond['visits_user']=$this->get_uid();
      }
      $forums=$forumlib->list_forums($cond);
      $result = array();
      for ($i=0, $count=count($forums);$i<$count;$i++) if ($this->check_access('view',$forums[$i]['id'])) $result[]=$forums[$i];
      return $result;
    }
  }

  /** Данная функция определяет, поддерживает ли данный тип раздела приклеенные темы **/
  function has_sticky() {
    return false;
  }

  function set_lastmod() {
    parent::set_lastmod();
    if (isset($this->topic)) $this->lastmod = max($this->lastmod,$this->topic['lastmod']);
    else $this->lastmod = max($this->lastmod,$this->forum['lastmod']); // учитываем время последней модификации раздела при определнии времени последней модификации страницы
  }

  function check_modified() {
    $lastmod = isset($this->topic) ? $this->topic['lastmod'] : $this->forum['lastmod'];
    if (isset($_SESSION['starttime'])) $lastmod=max($lastmod,$_SESSION['starttime']);
    return (!$lastmod || $lastmod>$this->if_modified_time); // если время последнего обновления темы или раздела больше If-Modified-Since
  }

  function set_title() {
    $result='';
    $page = isset($this->out->pages['page']) ? $this->out->pages['page'] : false;
    if (!empty($this->topic)) {
      if ($page && $page>1) $result=$this->topic['title'].' (стр. '.$page.' из '.$this->out->pages['pages'].') | '.$this->forum['title']; //.' :: '.$this->get_opt('site_title');
      else $result=$this->topic['title'].' | '.$this->forum['title']; //.' :: '.$this->get_opt('site_title');
    }
     elseif (empty($this->topic) && $page>1) $result=$this->forum['title'].' (стр. '.$page.' из '.$this->out->pages['pages'].')'; // :: '.$this->get_opt('site_title');
    else $result=$this->forum['title'].' | '.$this->get_opt('site_title');
    if ($this->action==='reply') $result = 'Отправка сообщения в тему '.$result;
    if ($this->action==='newtopic') $result = 'Создание темы в разделе '.$result;
    if ($this->action==='tags') $result = 'Все теги раздела '.$result;
    if ($this->action==='tags') $result = 'Расширенные настройки раздела '.$result;

    return $result;
  }

  function set_opengraph() {
    if (!empty($this->topic)) {
      $this->meta('og:title',$this->topic['title'],true);
      $this->meta('og:url',$this->http($this->url($this->topic['full_hurl'])),true);
      $this->meta('og:description',$this->topic['descr'],true);
    }
    else {
      $this->meta('og:title',$this->forum['title'],true);
      $this->meta('og:url',$this->http($this->url($this->forum['hurl'])),true);
      $this->meta('og:description',$this->forum['descr'],true);
    }
    $this->meta('og:type','website',true);
    $this->meta('og:site_name',$this->get_opt('site_title'),true);
    $sitepic = $this->get_opt('site_picture');
    if ($sitepic && strpos($sitepic,'://')===false) $sitepic=$this->http($this->url($sitepic));
    if (!empty($sitepic)) $this->meta('og:image',$sitepic,true);
  }

  function set_location() {
    $result = parent::set_location();
    if ($this->forum['parent_id']!=0 && $this->get_opt('enable_subforums')) {
      $parents = array_reverse($this->get_parent_forums($this->forum['parent_id'],1));
      for($i=0, $count=count($parents); $i<$count; $i++) {
        array_push($result,array($parents[$i]['title'],$this->url($parents[$i]['hurl'].'/')));
      }
    }
    if ($this->action==='view_forum') {      
      if (isset($_REQUEST['tags'])) {
        array_push($result, array($this->forum['title'],$this->url($this->forum['hurl'].'/')));
        array_push($result,array('Записи с тегом „'.$_REQUEST['tags'].'”'));
      }
      else array_push($result, array($this->forum['title']));
    }
    else array_push($result,array($this->forum['title'],$this->url($this->forum['hurl'].'/')));
    if ($this->action==='view_topic') array_push($result,array($this->topic['title']));
    if (($this->action==='reply'  || $this->action==='edit') && !empty($this->topic)) {
      array_push($result,array($this->topic['title'],$this->url($this->topic['full_hurl'])));
      if ($this->action==='edit') array_push($result,array('Редактирование сообщения'));
      elseif (empty($_POST['preview'])) array_push($result,array('Отправка сообщения'));
      else array_push($result,array('Предварительный просмотр сообщения'));
    }    
    if ($this->action==='tags') array_push($result,array('Список тегов'));
    if ($this->action==='newtopic') array_push($result,array('Новая тема'));
    return $result;
  }

  function get_action_name() {
    if ($this->action==='view_forum') $result=sprintf('Просматривает список тем в разделе &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->forum['hurl'].'/'),$this->forum['title']);
    elseif ($this->action==='view_topic') $result=sprintf('Просматривает тему &laquo;<a href="%s">%s</a>&raquo; в разделе &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->topic['full_hurl']),htmlspecialchars($this->topic['title']),$this->url($this->forum['hurl'].'/'),htmlspecialchars($this->forum['title']));
    elseif ($this->action==='reply') $result=sprintf('Пишет сообщение в тему &laquo;<a href="%s">%s</a>&raquo; в разделе &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->topic['full_hurl']),htmlspecialchars($this->topic['title']),$this->url($this->forum['hurl'].'/'),htmlspecialchars($this->forum['title']));
    elseif ($this->action==='edit' && !empty($this->topic)) $result=sprintf('Редактирует сообщение в теме &laquo;<a href="%s">%s</a>&raquo; в разделе &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->topic['full_hurl']),htmlspecialchars($this->topic['title']),$this->url($this->forum['hurl'].'/'),htmlspecialchars($this->forum['title']));
    elseif ($this->action==='vote') $result=sprintf('Голосует в теме &laquo;<a href="%s">%s</a>&raquo; в разделе &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->topic['full_hurl']),$this->topic['title'],$this->url($this->forum['hurl'].'/'),htmlspecialchars($this->forum['title']));
    elseif ($this->action==='rate') $result=sprintf('Оценивает сообщение сообщение в теме &laquo;<a href="%s">%s</a>&raquo; в разделе &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->topic['full_hurl']),$this->topic['title'],$this->url($this->forum['hurl'].'/'),htmlspecialchars($this->forum['title']));
    elseif ($this->action==='newtopic') $result=sprintf('Создает новую тему в разделе &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->forum['hurl'].'/'),$this->forum['title']);
    elseif ($this->action==='tags') $result=sprintf('Просматривает список тегов в разделе &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->forum['hurl'].'/'),$this->forum['title']);
    elseif ($this->action==='owner_settings') $result=sprintf('Задает расширенные настройки раздела &laquo;<a href="%s">%s</a>&raquo;',$this->url($this->forum['hurl'].'/'),$this->forum['title']);
    else $result=parent::get_action_name();
    return $result;
  }

  function get_request_type() {
    if (isset($_REQUEST['ajax'])) return 1;
    elseif ($this->action==='rss') return 2;
    else return 0;
  }

  function get_topics_perpage($fid) {
    $perpage = false;
    if (isset($_SESSION['forum'.$fid]) && isset($_SESSION['forum'.$fid]['perpage'])) $perpage = intval($_SESSION['forum'.$fid]['perpage']);
    if (!$perpage) $perpage = $this->get_opt('topics_per_page','user'); // берем из настроек пользователя
    if (!$perpage) $perpage = $this->get_opt('topics_per_page');  // берем из настроек сайта в целом
    if (!$perpage) $perpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль
    return $perpage;
  }

  function get_posts_perpage($tid=false) {
    $tperpage = false;
    if (!$tperpage) $tperpage = $this->get_opt('posts_per_page','user'); // берем из настроек пользователя
    if (!$tperpage) $tperpage = $this->get_opt('post_per_page');  // берем из настроек сайта в целом
    if (!$tperpage) $tperpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль
    return $tperpage;
  }

  /** Увеличение счетчика просмотров текущих раздела и темы (если данные о теме загружены) на единицу **/
  function fix_view() {
    if ($this->bot_id==0) { // заходы в тему поисковых роботов не считаются
      $sql = 'UPDATE '.DB_prefix.'views SET views=views+1 WHERE (oid='.intval($this->forum['id']).' AND type=\'forum\')';
      if (!empty($this->topic)) {
        $sql .= ' OR (oid='.intval($this->topic['id']).' AND type=\'topic\')';
      }
      $this->db->query($sql);
      $affected = $this->db->affected_rows();
      if (($affected<2 && !empty($this->topic)) || $affected<1) {
        $data['oid']=$this->forum['id'];
        $data['type']='forum';
        $data['views']=1;
        $this->db->insert_ignore(DB_prefix.'views',$data);
        if (!empty($this->topic)) {
          $data['oid']=$this->topic['id'];
          $data['type']='topic';
          $this->db->insert_ignore(DB_prefix.'views',$data);
        }
      }
    }
  }

  /** Строит список должностных лиц раздела (экспертов и модераторов)
  * @param $fid integer Идентификатор форума
  * @return array Хеш-массив, ключи которого соответствуют ролям (т.е. moderator, expert, curator), а значения -- содержат массив данных о пользователе (id и display_name)
  **/
  function build_moderators_list($fid=false) {
    if (!$fid) $fid=$this->forum['id'];
    $result = $this->get_cached('Moderators_'.$fid); // сначала пытаемся прочитать данные о модераторах из кеша
    if ($result===NULL) {
      $fids = $this->get_parent_forums($fid);
      $sql = 'SELECT DISTINCT md.role, u.id, u.login, u.display_name FROM '.DB_prefix.'user u, '.DB_prefix.'moderator md '.
      'WHERE md.uid=u.id AND '.$this->db->array_to_sql($fids,'fid');
      $result=$this->db->select_super_hash($sql,'role');
      $this->set_cached('Moderators_'.$fid, $result);
    }
    if ($fid==$this->forum['id'] and $this->forum['selfmod']>0 && !empty($this->topic)) { // если кураторы включены и мы находимся в теме, получаем её куратора
      if ($this->topic['owner']>AUTH_SYSTEM_USERS) { // если куратор темы задан
        $result['curator']=$this->load_user($this->topic['owner'],0);
      }
    }
    return $result;
  }

  /** Проверка, имеет ли пользователь возможность модерации (по правам доступа или же за счет прав куратора, если они включены в разделе)
  * @param boolean $curators -- Если равен TRUE, то учитываются права кураторов тем (если это включено в настройках раздела), если FALSE -- то только реальные модераторские права
  * @return boolean TRUE -- если возможность модерации есть.
  **/
  function is_moderator($curators=true) {
    $result = $this->check_access('moderate');
    if ($curators && !empty($this->forum['selfmod']) && !empty($this->topic) && // проверка на кураторство
      $this->topic['owner']==$this->get_uid() && !$this->is_guest()) $result=true;
    return $result;
  }

  /** Получение расширенных данных текущего раздела **/
  function get_ext_data() {
    $buffer = $this->get_text($this->forum['id'], 3);
    if ($buffer) $result = unserialize($buffer);
    else $resul = array();
    return $result;
  }
  
  function action_update_extdata() {
    $this->update_extdata();
    $this->redirect($this->forum['hurl'].'/');
  }

  /** Обновление закешированных в extdata даных о последних темах или сообщениях.
  * В stdforum не используется, но нужна для унаследованных от него разделов, в частности, blog и microblog **/
  function update_extdata() {}
  

  /** Действие для редактирования настроек раздела, которые может задавать владелец**/
  function action_owner_settings() {
    $uid = $this->get_uid();
    if ($uid<=AUTH_SYSTEM_USERS) $this->output_403('Гости не могут редактировать настройки раздела!');
    if (!$this->check_access('owner',$this->forum['id'])) $this->output_403('Вы не являетесь владельцем этого раздела!');
    $module = $this->forum['module'];
    $filename = $module.'/owner_settings.tpl';
    if (!$this->valid_file($filename) || !is_readable(BASEDIR.'template/def/'.$filename)) $this->output_404('Данный тип раздела не поддерживает задаваемые владельцем настройки!');
    /** @var Library_forums **/
    $forumlib = $this->load_lib('forums',true);
    if ($this->is_post()) {
      $forumlib->update_forum($this->forum['id'],$_POST['extdata']);
      $this->message('Настройки форума обновлены!',1);
      $this->redirect($this->forum['hurl'].'/owner_settings.htm');
    }
    else {
      $fdata = $forumlib->get_forum($this->forum['id'],true);
      $this->out->extdata = $fdata['extdata'];
    }
    return $filename;
  }
  
  
}
