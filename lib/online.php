<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010-2012 4X_Pro, INTBPRO.RU
 *  http://intbpro.ru
 *
 *  ================================ */

class Library_online extends Library {
  private $online_time;

/** Получение списка присутствующих онлайн пользователей.
 *  Время присутствия берется из глобальной настройки online_time
 *
 * @param <type> $fid
 * @param <type> $tid
 * @return <type>
 */
  function get_online_users($mode=2) {
    $fid =  !empty(Library::$app->forum['id']) ? Library::$app->forum['id'] : false;
    $tid =  !empty(Library::$app->topic['id']) ? Library::$app->topic['id'] : false;
    $result = array('online'=>array('team'=>array(),'users'=>array(),'bots'=>array(),'guests'=>array(),'hidden'=>array()),
      'today'=>array('team'=>array(),'users'=>array(),'bots'=>array(),'guests'=>array(),'hidden'=>array()));
    if ($tid && $mode==2) $result['online_header']='Присутствующие в теме участники';
    elseif ($fid && $mode>=1) $result['online_header']='Присутствующие в разделе участники';
    else $result['online_header']='Присутствующие на форуме участники';    
    $this->online_time = Library::$app->get_opt('online_time'); // количество миут, в течение которых пользователь считается присутстующим на форуме
    $user_id = Library::$app->get_uid();
    if ($this->online_time) { // если время присутствия не равно нулю, значит, отслеживание пользователей включено
      $timezone=Library::$app->get_opt('timezone','user');
      $start_time = mktime(0,0,-$timezone); // время начала суток с поправкой на часовой пояс пользователя
      if (Library::$app->time-$start_time > 24*60*60) $start_time+=24*60*60;
      elseif ($start_time>Library::$app->time) $start_time-=24*60*60;
      $limit_time = Library::$app->time - $this->online_time*60;
      $sql = 'SELECT uid, visittime, type, b.bot_name, '.
      'u.login, u.display_name '.
      'FROM '.DB_prefix.'online o '.
      'LEFT JOIN '.DB_prefix.'bots b ON (o.type=b.id AND o.type>0) '.
      'LEFT JOIN '.DB_prefix.'user u ON (o.uid=u.id) '.
      'WHERE visittime>'.intval($start_time).' ';
      if ($tid && $mode==2) $sql.=' AND o.tid='.intval($tid);
      if ($fid && $mode>=1) $sql.=' AND o.fid='.intval($fid);
      $sql.=' ORDER BY visittime DESC'; // TODO: добавить получение темы и имени бота
      $online = Library::$app->db->select_all($sql);

      foreach ($online as $curitem) {
        if ($curitem['type']==-2) $result['today']['team'][]=$curitem;
        elseif ($curitem['type']==-1) $result['today']['users'][]=$curitem;
        elseif ($curitem['type']>0) $result['today']['bots'][]=$curitem;
        elseif ($curitem['type']==-128) $result['today']['hidden'][]=$curitem;
        else $result['today']['guests'][]=$curitem;
        if ($curitem['visittime']>$limit_time) {
          if ($curitem['type']==-2) $result['online']['team'][]=$curitem;
          elseif ($curitem['type']==-1) $result['online']['users'][]=$curitem;
          elseif ($curitem['type']>0) $result['online']['bots'][]=$curitem;
          elseif ($curitem['type']==-128) $result['online']['hidden'][]=$curitem;
          else $result['online']['guests'][]=$curitem;
        }
        if ($curitem['uid']!=$user_id || $user_id <= AUTH_SYSTEM_USERS) Library::$app->set_lastmod($curitem['visittime']); // меняем время последней модификации страницы в соответствии со временем визита пользователя, если этот пользователь — не мы
      }
    }
    $result['total'] = array_merge($result['today']['team'],$result['today']['users']);
    return array('online.tpl',$result);
  }
}
