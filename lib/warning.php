<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2015 4X_Pro, INTBPRO.RU
 *  http://intbpro.ru  
 *  Модуль работы с предупреждениями пользователей
 *  ================================ */

class Library_warning extends Library {
  /** Вывод списка вынесенных пользователю предупреждений
   * @param integer $uid Идентификатор пользователя
   * @param array $cond Массив условий выборки. Может содержать ключи: 
   *   active -- выборка только действующих на данный момент предупреждений
   *   limit -- ограничение количества предупреждений
   *   moderator -- извление имени и id модератора, вынесшего предупреждения
   *   links -- формирование ссылок на сообщения 
   * **/
  function list_warnings($uid,$cond=false) {
    $columns = 'uw.id, uw.uid, uw.warntime, uw.value, uw.warntill, uw.descr';
    if (!empty($cond['moderator'])) $columns.=', u.id AS moderator_id, u.display_name AS moderator';
    if (!empty($cond['links'])) $columns.=', CONCAT(f.hurl,\'/\', CASE WHEN t.hurl!=\'\' THEN t.hurl ELSE CAST(t.id AS CHAR(11)) END,\'/post-\',pid,\'.htm\') AS post_hurl';
    $where = 'uw.uid='.intval($uid);
    if (!empty($cond['active'])) $where.=' AND uw.till>='.Library::$app->time;
    
    $sql = 'SELECT '.$columns.' FROM '.DB_prefix.'user_warning uw ';
    if (!empty($cond['moderator'])) $sql.='LEFT JOIN '.DB_prefix.'user u ON (u.id=uw.moderator)';
    if (!empty($cond['links'])) $sql.=' LEFT JOIN '.DB_prefix.'post p ON (p.id=uw.pid AND p.status=\'0\') '.
        'LEFT JOIN '.DB_prefix.'topic t ON (t.id=p.tid AND t.status=\'0\') '.
        'LEFT JOIN '.DB_prefix.'forum f ON (f.id=t.fid)';
    $sql.=' WHERE '.$where.' ORDER BY warntime DESC';
    $cond['limit'] = isset($cond['limit']) ? $cond['limit'] : false;
    return Library::$app->db->select_all($sql,$cond['limit']);    
  }
  
  /** Вынесение предупреждения пользователю **/
  function make_warning($uid,$data,$override=false) {
    $warn['value']=$data['value'];
    $warn['descr']=$data['descr'];
    $warn['warntime']=Library::$app->time;
    $warn['warntill']=empty($data['limit']) ? 0xFFFFFFFF : Library::$app->time+$data['period']*24*60*60;
    $warn['uid']=$uid;
    $warn['moderator']=(!$override || isset($data['moderator'])) ? Library::$app->get_uid() : $data['moderator'];
    $warn['pid']=isset($data['pid']) ? $data['pid'] : 0;
    $result = Library::$app->db->insert(DB_prefix.'user_warning', $warn);
    if ($result) $this->resync_warnings($uid);
    return $result;
  }
  
  /** Удаление предупреждений, вынесенных пользователю
   * 
   * @param integer $uid Идентификатор пользователя 
   * @param array $ids Массив идентификаторов предупреждений
   */
  function delete_warnings($uid,$ids) {
    $sql = 'DELETE FROM '.DB_prefix.'user_warning WHERE uid='.intval($uid).' AND '.Library::$app->db->array_to_sql($ids, 'id');
    Library::$app->db->query($sql);
    $this->resync_warnings($uid);
  }
  
  /** Подсчет числа действующих предупреждений у пользователя **/
  function count_warnings($uid) {
    $sql = 'SELECT SUM(value) FROM '.DB_prefix.'user_warning WHERE uid='.intval($uid).' AND warntill>='.intval(Library::$app->time);
    return Library::$app->db->select_int($sql);
  }
  
  /** Пересчет количества штрафных баллов пользователя и обновление даты окончания бана, если это необходимо **/
  function resync_warnings($uid) {
    $sql = 'SELECT warntill, value FROM '.DB_prefix.'user_warning '.
    ' WHERE uid='.intval($uid).' AND warntill>='.intval(Library::$app->time).
    ' ORDER BY warntill';
    $warnings = Library::$app->db->select_all($sql);
    $summ=0;
    $count= count($warnings);
    for ($i=0;$i<$count;$i++) $summ+=$warnings[$i]['value'];
    $max_value = Library::$app->get_opt('user_max_warnings');   
    if ($summ==0 || $summ<$max_value) { // если сумма штрафных баллов изначально меньше максимально допустимой, то пользователя пока не баним 
      $maxdate=0;
    }
    else { // иначе рассчитываем срок окончания бана
      $maxdate = 0xFFFFFFFF;      
      $cursumm = $summ;
      for ($i=0;$i<$count && $cursumm>=$max_value;$i++) { // проходим цикл до тех пор, пока сумма оставшихся баллов остается больше или равной количеству баллов для бана
        $cursumm=$cursumm-$warnings[$i]['value'];
        $maxdate=$warnings[$i]['warntill'];
      }
      // после выполнения цикла в maxdate будет дата окончания предупреждения, при сгорании которого у пользователя сумма штрафных баллов станет меньше максимально-допустимой.       
    }
    $sql = 'UPDATE '.DB_prefix.'user_ext SET warnings='.intval($summ).', banned_till='.intval($maxdate).' WHERE id='.intval($uid);
    Library::$app->db->query($sql);
    $this->reset_session_cache();    
  }
  
  function reset_session_cache() {
    file_put_contents(BASEDIR.'tmp/reset.txt', Library::$app->time);
    Library::$app->set_cached('Session_Reset',Library::$app->time);    
  }
}