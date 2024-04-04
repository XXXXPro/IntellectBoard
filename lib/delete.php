<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2014 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Библиотека для окончательного удаления тем, сообщений, пользователей
 *  ================================ */

class Library_delete extends Library {
 /** Удаление сообщений вместе с приложенными к ним файлами.
  * Какой-либо корректировки статистики не выполняется, так как предполагается, что сообщениям до вызова этой функции уже выставили статус 2 **/
   function delete_posts($pids) {
     if (empty($pids)) return; // если удалять нечего, сразу выходим во избежание ошибок
     $sql = 'SELECT fkey FROM '.DB_prefix.'file WHERE type=\'1\' AND '.Library::$app->db->array_to_sql($pids,'oid');
     $keys = Library::$app->db->select_all_numbers($sql);

     for ($i=0, $count=count($keys); $i<$count; $i++) {
       $filename=intval($type).'/'.$oid.'-'.$keys[$i].'.dat';
       if (Library::$app->valid_file($filename)) unlink(BASEDIR.'/www/f/up/'.$filename);
       $dirs = glob(BASEDIR.'/www/f/up/'.intval($type).'/pr/*');
       for ($j=0,$count2=count($dirs);$j<$count2;$j++) if ($dirs[$j]!='.' && $dirs[$j]!='..') {
         array_map('unlink',glob($dirs[$j].'/'.$oid.'-'.$keys[$i].'*'));
       }
     }

     $sql = 'DELETE FROM '.DB_prefix.'file WHERE type=\'1\' AND '.Library::$app->db->array_to_sql($pids,'oid');
     Library::$app->db->query($sql);

     // TODO: подумать, нужно ли удалять рейтинги удаленных сообщений, или их следует оставить для подсчета статистики
     // $sql = 'DELETE FROM '.DB_prefix.'rating WHERE '.Library::$app->db->array_to_sql($pids,'id');
     // Library::$app->db->query($sql);

     $sql = 'DELETE FROM '.DB_prefix.'text WHERE type=\'16\' AND '.Library::$app->db->array_to_sql($pids,'id');
     Library::$app->db->query($sql);

     $sql = 'DELETE FROM '.DB_prefix.'post WHERE '.Library::$app->db->array_to_sql($pids,'id');
     Library::$app->db->query($sql);
   }

   /** Удаление всех сообщений, помеченных к удалению ранее указанного периода **/
   function delete_older_posts($time) {
     $sql = 'SELECT p.id FROM '.DB_prefix.'post p, '.DB_prefix.'text tx '.
         'WHERE status=\'2\' AND p.id=tx.id AND tx.type=16 AND tx.tx_lastmod<'.intval($time);
     $pids = Library::$app->db->select_all_numbers($sql);
     $this->delete_posts($pids);
   }

   /** Удаление тем, а также информации об их просмотрах и голосований **/
   function delete_topics($tids) {
     if (empty($tids)) return;
     $sql = 'SELECT id FROM '.DB_prefix.'post WHERE '.Library::$app->db->array_to_sql($tids,'tid'); // извлекаем список сообщений в теме, чтобы удалить сначала их
     $pids = Library::$app->db->select_all_numbers($sql);
     $this->delete_posts($pids);

     // удаление данных о просмотре, подписке и т.п.
     $sql = 'DELETE FROM '.DB_prefix.'last_visit WHERE type=\'topic\' AND '.Library::$app->db->array_to_sql($tids,'oid');
     Library::$app->db->query($sql);
     $sql = 'DELETE FROM '.DB_prefix.'views WHERE type=\'topic\' AND '.Library::$app->db->array_to_sql($tids,'oid');
     Library::$app->db->query($sql);

     // удаление опросов и вариантов к ним
     $sql = 'DELETE FROM '.DB_prefix.'vote WHERE  '.Library::$app->db->array_to_sql($tids,'tid');
     Library::$app->db->query($sql);
     $sql = 'DELETE FROM '.DB_prefix.'poll_variant WHERE  '.Library::$app->db->array_to_sql($tids,'tid');
     Library::$app->db->query($sql);
     $sql = 'DELETE FROM '.DB_prefix.'poll WHERE  '.Library::$app->db->array_to_sql($tids,'id');
     Library::$app->db->query($sql);
     
     // удаление тегов для тем
     $sql = 'DELETE FROM '.DB_prefix.'tagentry WHERE '.Library::$app->db->array_to_sql($tids,'item_id');
     Library::$app->db->query($sql);

     $sql = 'DELETE FROM '.DB_prefix.'topic WHERE '.Library::$app->db->array_to_sql($tids,'id');
     Library::$app->db->query($sql);
   }

   /** Удаление всех сообщений, помеченных к удалению ранее указанного периода **/
   function delete_older_topics($time) {
     $sql = 'SELECT t.id FROM '.DB_prefix.'topic t '.
         'WHERE status=\'2\' AND t.lastmod<'.intval($time);
     $tids = Library::$app->db->select_all_numbers($sql);
     $this->delete_topics($tids);
   }

   /** Удаление пользователей и всех их данных **/
   function delete_users($uids) {
     if (empty($uids)) return;

/*     $sql = 'SELECT id, display_name FROM '.DB_prefix.'user WHERE '.Library::$app->db->array_to_sql($uids,'uid');
     $names = Library::$app->db->select_simple_hash($sql);
     foreach ($names as $uid=>$name) {
       $sql = 'UPDATE '.DB_prefix.'post SET author="'.Library::$app->db->slashes($name).'" WHERE uid='.intval($uid);
       Library::$app->db->query
     }*/
     // переписываем все отправленные пользователями сообщения на гостя
     $sql = 'UPDATE '.DB_prefix.'post SET uid=1 WHERE '.Library::$app->db->array_to_sql($uids,'uid');
     Library::$app->db->query($sql);

     // отписываем пользователя от всех ЛС
     $pmlib = Library::$app->load_lib('privmsg',false);
     if ($pmlib) {
       $sql = 'SELECT pm_thread, uid FROM '.DB_prefix.'privmsg_thread_user WHERE '.Library::$app->db->array_to_sql($uids,'uid');
       $threads = Library::$app->db->select_all($sql);
       for ($i=0, $count=count($threads);$i<$count;$i++) $pmlib->unsubscribe($threads[$i]['pm_thread'],$threads[$i]['uid']);
     }

     // TODO: подумать об удалении рейтингов сообщений
//     $sql = 'DELETE FROM '.DB_prefix.'rating WHERE '.Library::$app->db->array_to_sql($uids,'uid');
//     Library::$app->db->query($sql);
     // удаление данных о просмотре, подписке и т.п.
     $sql = 'DELETE FROM '.DB_prefix.'last_visit WHERE '.Library::$app->db->array_to_sql($uids,'uid');
     Library::$app->db->query($sql);
     $sql = 'DELETE FROM '.DB_prefix.'mark_all WHERE '.Library::$app->db->array_to_sql($uids,'uid');
     Library::$app->db->query($sql);

     // удаление контактов
     $sql = 'DELETE FROM '.DB_prefix.'user_contact WHERE '.Library::$app->db->array_to_sql($uids,'uid');
     Library::$app->db->query($sql);

     // TODO: подумать об удалении результатов голосования

     // TODO: подумать, что делать с личными разделами
     // TODO: удаление данных о пользователе
     $sql = 'DELETE FROM '.DB_prefix.'user_ext WHERE '.Library::$app->db->array_to_sql($uids,'id');
     Library::$app->db->query($sql);
     $sql = 'DELETE FROM '.DB_prefix.'user_settings WHERE '.Library::$app->db->array_to_sql($uids,'id');
     Library::$app->db->query($sql);
     $sql = 'DELETE FROM '.DB_prefix.'user_warning WHERE '.Library::$app->db->array_to_sql($uids,'uid');
     Library::$app->db->query($sql);
     $sql = 'DELETE FROM '.DB_prefix.'user WHERE '.Library::$app->db->array_to_sql($uids,'id');
     Library::$app->db->query($sql);


     if ($ext_lib_name = Library::$app->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
       $ext_lib = Library::$app->load_lib($ext_lib_name,false); // загружаем ее
       if ($ext_lib) {
         foreach ($uids as $uid) {
           $ext_lib->on_delete($uid);
         }
       }
     }
   }

   /** Удаление разделов. При этом содержащиеся в них темы не удаляются сразу, а только помечаются к удалению.
    * А вот прочая служебная информация удаляется сразу же. **/
   function delete_forums($ids) {
     if (!is_array($ids)) $ids=array($ids); // если передан один раздел, преобразуем его в массив
     $sql = 'UPDATE '.DB_prefix.'topic SET status=\'2\', lastmod='.intval(Library::$app->time).
       ' WHERE '.Library::$app->db->array_to_sql($ids,'fid');
     Library::$app->db->query($sql); // помечаем все темы раздела к удалению (но не удаляем их сразу)
     $sql = 'DELETE FROM '.DB_prefix.'forum WHERE '.Library::$app->db->array_to_sql($ids, 'id');
     Library::$app->db->query($sql);
     // удаление вступительного слова, правил, объявления
     $sql = 'DELETE FROM '.DB_prefix.'text WHERE (type=0 OR type=1 OR type=2 OR type=3) AND '.Library::$app->db->array_to_sql($ids, 'id');
     Library::$app->db->query($sql);
     // удаление данных о заходах в раздел
     $sql = 'DELETE FROM '.DB_prefix.'last_visit WHERE type=\'forum\' AND '.Library::$app->db->array_to_sql($ids, 'oid');
     Library::$app->db->query($sql);
     // удаление данных об отметке раздела как прочитанного
     $sql = 'DELETE FROM '.DB_prefix.'mark_all WHERE '.Library::$app->db->array_to_sql($ids, 'fid');
     Library::$app->db->query($sql);
     // удаление количества просмотров
     $sql = 'DELETE FROM '.DB_prefix.'views WHERE type=\'forum\' AND '.Library::$app->db->array_to_sql($ids, 'oid');
     Library::$app->db->query($sql);
   }

   /** Удаление всех неактивных пользователей, со времени регистрации которых прошло более чем укаанное количество дней **/
   function cron_inactive_users_clear($period) {
     $period=intval($period);
     if (!$period) return false;
     $timelimit = Library::$app->time-$period*24*60*60;
     $sql = 'SELECT u.id FROM '.DB_prefix.'user u, '.DB_prefix.'user_ext ue WHERE u.id=ue.id AND u.status=\'1\' AND ue.reg_date<'.intval($timelimit);
     $uids=Library::$app->db->select_all_numbers($sql);
     $this->delete_users($uids);
     return true;
   }
}
