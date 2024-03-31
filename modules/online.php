<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010-2012 4X_Pro, INTBLITE.RU
 *  http://intblite.ru
 *  Модуль вывода развернутого списка присутствующих онлайн
 *  ================================ */

class online extends Application {

  function action_view() {
    $this->out->online_time = $this->get_opt('online_time');
    if ($this->out->online_time) {
      $limit_time = time() - $this->out->online_time*60;
      $sql = 'SELECT uid, visittime, type, fid, tid, text, ip, '.
      'u.login, u.display_name, f.title AS f_title, f.hurl AS f_hurl, '.
      'bot_name '.
      'FROM '.DB_prefix.'online o '.
      'LEFT JOIN '.DB_prefix.'user u ON (o.uid=u.id) '.
      'LEFT JOIN '.DB_prefix.'bots b ON (o.type=b.id AND o.type>0) '.
      'LEFT JOIN '.DB_prefix.'forum f ON (o.fid=f.id AND o.fid>0) '.
      'WHERE visittime>'.intval($limit_time).' '.
      'ORDER BY visittime DESC'; // TODO: добавить получение темы и имени бота
      $online = $this->db->select_all($sql);
      
      $this->out->online_users=array();
      $is_admin = $this->is_admin();
      $this->out->is_admin = $is_admin;
      foreach ($online as $curitem) {
         if ($curitem['type']!=-128 || $is_admin) { // показываем пользователя, если он не скрытный или текущий пользователь является админом
           if ($curitem['fid']!=0) { // если пользователь просматривает какой-то раздел форума, то нужно выполнить доп. проверки и сформировать ссылку
             if ($this->check_access('view',$curitem['fid'])) { // проверяем, что текущий пользователь имеет право знать о существовании этого раздела
               $forum_link = '<a href="'.$this->url($curitem['f_hurl'].'/').'">'.$curitem['f_title'].'</a>';
               $curitem['text']=sprintf($curitem['text'],$forum_link); // TODO: потом то же самое и для темы
             }
             else $curitem['text']=parent::get_action_name('view'); // если нет, то выдаем сообщение о действии по умолчанию
           }
           $this->out->online_users[]=$curitem;
         }
      }    
    }
  }

  function set_title() {
    return 'Список присутствующих участников :: '.$this->get_opt('site_title');
  }

  function set_location() {
    $result=parent::set_location();
    $result[1]=array('Присутствующие участники');
    return $result;
  }

  function  set_lastmod() {
    $this->lastmod=$this->time;
  }

  function  get_action_name() {
    if ($this->action=='view') $result='Просматривает список присутствующих участников';
    else $result=parent::get_action_name();
    return $result;
  }
}
