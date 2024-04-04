<?php
/**
 * ================================
 *
 * @package IntBPro
 * @author 4X_Pro <admin@openproj.ru>
 * @version 3.0
 * @copyright 2007,2009-2011 4X_Pro, INTBLITE.RU
 * http://intbpro.ru
 * Модуль вывода главной страницы или категорий
 * ================================
 */
class mainpage extends Application {
  function action_view() {
    $category=isset($_REQUEST['cat']) ? intval($_REQUEST['cat']) : 0; // если задана конкретная категория, будем выводить разделы только из нее, иначе -- считаем категорию равной нулю и выводим все разделы

    $forumlib=$this->load_lib('forums',true);
    /* @var $forumlib Library_forums */
    $this->out->cat_list=$forumlib->list_categories($category);

    /*
     * if ($this->is_guest()) { $sql = 'SELECT f.id, category_id, title, descr, hurl, module, lastmod, post_count, topic_count, icon_new, icon_nonew, p.uid, p.author, p.postdate, views ';
     * $sql.='FROM '.DB_prefix.'forum f '. 'LEFT JOIN '.DB_prefix.'views v ON (f.id=v.oid AND v.type="forum") '.
     * 'LEFT JOIN '.DB_prefix.'post p ON (p.id=f.last_post_id) ';
     * $sql.='WHERE is_start="1"'; if ($category) $sql.='AND category_id='.$category;
     * $sql.=' AND parent_id=0 '. 'ORDER BY sortfield'; $forums = $this->db->select_all($sql); } else { // получаем время последнего визита на форум в целом $sql = 'SELECT visit2 FROM '.DB_prefix.'last_visit lv WHERE lv.oid=0 AND lv.type="forum" AND lv.uid='.intval($this->get_uid()); $last_visit = $this->db->select_int($sql); $visits_mode = $this->get_opt('visits_mode'); // получаем режим отслеживания времени последнего визитав: 0 -- на уровне форума, 1 -- на уровне раздела, 2 -- на уровне темы $sql = 'SELECT f.id, category_id, title, descr, hurl, module, lastmod, post_count, topic_count, icon_new, icon_nonew, p.uid, p.author, p.postdate, views '; if ($visits_mode>0) $sql.=', visit2 '; $sql.='FROM '.DB_prefix.'forum f '. 'LEFT JOIN '.DB_prefix.'views v ON (f.id=v.oid AND v.type="forum") '. 'LEFT JOIN '.DB_prefix.'post p ON (p.id=f.last_post_id) '; if ($visits_mode>0) $sql.='LEFT JOIN '.DB_prefix.'last_visit lv ON (lv.uid='.$this->get_uid().' AND lv.oid=f.id AND lv.type="forum") '; $sql.='WHERE is_start="1"'; if ($category) $sql.='AND category_id='.$category; $sql.=' AND parent_id=0 '. 'ORDER BY sortfield'; $forums = $this->db->select_all($sql);
     */
    $cond['parent']=0;
    $cond['start']=true;
    $cond['extdata']=true;
    if ($category) $cond['category']=$category;
    $cond['lastpost']=true;
    if (!$this->is_guest()) {
      $sql='SELECT visit2 FROM ' . DB_prefix . 'last_visit lv WHERE lv.oid=0 AND lv.type=\'forum\' AND lv.uid=' . intval($this->get_uid());
      $last_visit=$this->db->select_int($sql);

      $sql='SELECT mark_time FROM ' . DB_prefix . 'mark_all ma WHERE ma.fid=0 AND ma.uid=' . intval($this->get_uid());
      $mark_all=$this->db->select_int($sql);

      $visits_mode=$this->get_opt('visits_mode'); // получаем режим отслеживания времени последнего визитав: 0 -- на уровне форума, 1 -- на уровне раздела, 2 -- на уровне темы
      //if ($visits_mode > 0) $cond['visits_user']=$this->get_uid();
    }

    $forums=$forumlib->list_forums($cond);

    if (!$this->is_guest()) if ($this->get_opt('visits_mode')==0) { // проверяем новые темы в режиме ослеживания последнего визита по разделу
      foreach ($forums as &$curforum) {
        if (!empty($curforum['visit2'])) { // если время последнего визита для раздела определено и не равно нулю, то наличие новых сообщений определяем по нему
          $curforum['is_new']=$curforum['visit2'] < $curforum['postdate'] && $curforum['mark_time'] < $curforum['postdate'] && $mark_all < $curforum['postdate'];
        }
        else {
          $curforum['is_new']=$last_visit < $curforum['postdate'] && $mark_all < $curforum['postdate'];
        }
      }
    }
    else { // проверяем новые темы в режиме ослеживания последнего визита по теме
      $sql = 'SELECT t.fid, COUNT(*) AS unread_count FROM  '.DB_prefix.'topic t '.
             'LEFT JOIN '.DB_prefix.'last_visit lv1 ON (t.id=lv1.oid AND lv1.type=\'topic\' AND lv1.uid='.intval($this->get_uid()).') '.
             'LEFT JOIN '.DB_prefix.'mark_all ma ON (ma.fid=t.fid AND ma.uid='.intval($this->get_uid()).') '.
             'WHERE t.last_post_time>'.intval($mark_all).' AND (t.last_post_time>ma.mark_time OR ma.mark_time IS NULL) AND (lv1.visit1<t.last_post_time OR lv1.visit1 IS NULL)'.
             'GROUP BY t.fid';
      $visitdata = $this->db->select_simple_hash($sql);
      foreach ($forums as &$curforum) $curforum['is_new']=isset($visitdata[$curforum['id']]) ? $visitdata[$curforum['id']] > 0 : false;
    }

    $this->out->total_topics=0;
    $this->out->total_posts=0;

    foreach ($forums as &$curforum ) {
      if ($this->check_access('view',$curforum['id'])) { // если пользователю разрешено видеть раздел
        // if (!isset($curforum['is_new'])) $curforum['is_new']=false;
        $this->out->total_topics+=$curforum['topic_count'];
        $this->out->total_posts+=$curforum['post_count'];
        $this->lastmod=max($this->lastmod,$curforum['lastmod']);
        $this->out->cat_list[$curforum['category_id']]['forums'][]=$curforum; // добавляем в список форумов данной категории для вывода
      }
    }

    if ($category == 0) {
      // получаем число пользователей и имя последнего зарегистрированного
      $sql='SELECT id, login, display_name FROM ' . DB_prefix . 'user WHERE status=0 ORDER BY id DESC';
      $this->out->last_user=$this->db->select_row($sql);

      $sql='SELECT COUNT(*) FROM ' . DB_prefix . 'user WHERE id>' . AUTH_SYSTEM_USERS . ' AND status=0';
      $this->out->total_users=$this->db->select_int($sql);

      $this->meta('description',$this->get_opt('site_description') . ' :: ' . $this->incline($this->out->total_topics,'%d тема','%d темы','%d тем') . ', ' . $this->incline($this->out->total_posts,'%d сообщение','%d сообщения','%d сообщений') . ', ' . $this->incline($this->out->total_users,'%d участник','%d участника','%d участников'));
    }
    else {
      $this->out->category_name=$this->out->cat_list[$category]['title'];
      if (!$this->out->cat_list[$category]['collapsed']) $this->meta('robots','noindex');
    }

/*    $online_mode=$this->get_opt('online_list'); // режим вывода списка присутствующих: 0 - выключен, 1 -- только на главной, прочие режимы -- будем выводить на главной и на странице категорий
    if ($online_mode > 0 && ($category == 0 || $online_mode > 1)) {
      $online_lib=$this->load_lib('online');
      /** @var $online_lib Library_online ** /
      if ($online_lib) $this->out->online_users=$online_lib->get_online_users(0,0); // получаем список пользователей онлайн для всего форума
    } */

    $this->out->start_text=$this->get_text(0,2); // текст с типом 2 -- вводный
    $this->out->allow_share=true;
  }

  function set_title() {
    if (isset($this->category_name)) $result=$this->category_name;
    else {
      $result=$this->get_opt('mainpage_title');
      if (!$result) $result='Главная страница';
    }
    // на главной странице название форума в TITLE не выводится. Если нужно, можно продубировать его в опции mainpage_title.
    return $result;
  }

  function set_location() {
    if (isset($this->category_name)) {
      $start_name=$this->get_opt('site_start');
      if (!$start_name) $start_name=$this->get_opt('site_title');
      $result[0]=array($start_name, $this->url('/'));
      $result[1]=array($this->category_name);
    }
    else
      $result=false;
    return $result;
  }

  function get_action_name() {
    if ($this->action == 'view') $result='Просмаривает список разделов на главной странице.';
    else $result=parent::get_action_name();
    return $result;
  }

  function get_announce() {
    $mode=$this->get_opt('announce_mode');
    if ($mode > 0) { // режим 1 -- вывод объявления только на главной, режим 2 -- на всех страницах
      return $this->get_text(0,1);
    }
    else
      return false;
  }
}