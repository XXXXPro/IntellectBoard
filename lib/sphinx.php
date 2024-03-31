<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  Intellect Board Pro
 *  Библиотека для работы с локальной поисковой системой Sphinx
 *  ================================ */

class Library_sphinx extends Library {
  function search($text,$fids,$cond,$mode) {
    $server=Library::$app->get_opt('search_sphinx_server');
    if (!$server) $server = '127.0.0.1';
    $port = Library::$app->get_opt('search_sphinx_port');
    if (!$port) $port=9306;
    $sphinx = mysqli_connect($server,"root","","",intval($port));
    $post_index = Library::$app->get_opt('search_sphinx_post_index');
    if (!$post_index) $post_index = 'idx_intb_post';
    $topic_index = Library::$app->get_opt('search_sphinx_topic_index');
    if (!$topic_index) $topic_index = 'idx_intb_topic';

    if ($sphinx===false || (!is_resource($sphinx) && get_class($sphinx)!='mysqli')) trigger_error('Не получилось подключиться к Sphinx! Если вы администратор сайта, проверьте его настройки!',E_USER_ERROR);

    if ($mode==0) $sql = "SELECT id AS oid, WEIGHT() AS relevancy FROM $post_index WHERE MATCH('".mysqli_escape_string($sphinx,$text)."')";
    else $sql = "SELECT id AS oid, WEIGHT() AS relevancy FROM $topic_index WHERE MATCH('".mysqli_escape_string($sphinx,$text)."')";
    if (!empty($fids) && is_array($fids)) $sql.=' AND fid IN ('.join(',',$fids).')';
    if (!empty($cond['start_date'])) $sql.= ' AND postdate>='.intval($cond['start_date']);
    if (!empty($cond['end_date'])) $sql .= ' AND postdate<=' . intval($cond['end_date']);
    if (!empty($cond['value'])) $sql .=' AND value IN ('.join(',',$cond['value']).')';
    $res = mysqli_query($sphinx,$sql);

    $result = array();
    while ($data = mysqli_fetch_assoc($res)) {
      $result[]=$data;
    }
    mysqli_free_result($res);
    return $result;
  }
  
  function search_topics($text,$fids,$cond) {
    $server=Library::$app->get_opt('search_sphinx_server');
    if (!$server) $server = '127.0.0.1';
    $port = Library::$app->get_opt('search_sphinx_port');
    $sphinx = mysqli_connect($server, "root", "", "", 9306 );

    if (!is_resource($sphinx) && get_class($sphinx)!='mysqli') trigger_error('Не получилось подключиться к Sphinx! Проверьте настройки!',E_USER_ERROR);

    $sql = "SELECT id AS oid, WEIGHT() AS relevancy FROM idx_intb_topic WHERE MATCH('".mysqli_escape_string($sphinx,$text)."')";
    if (!empty($fids) && is_array($fids)) $sql .= ' AND fid IN (' . join(',', $fids) . ')';
    $res = mysqli_query($sphinx,$sql);

    $result = array();
    while ($data = mysqli_fetch_assoc($res)) {
      $result[]=$data;
    }
    mysqli_free_result($res);
    return $result;
  }  
}