<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Скрипт выполнения различных служебных операций по поддержанию работоспособности форума:
 *  ротации логов, периодической оптимизации баз и т.п. 
 *  ================================ */
 
 class Library_maintain extends Library {
    /** Ротация логов.
    * @param $count integer Количество файлов логов, которые требуется сохранять. Файлы будут получать имена имя_лога.001, имя_лога.002, и т.д.
    **/
    function cron_log_rotate($count) {
       $dir=BASEDIR.'logs/';
       $logs = glob($dir.'*.csv',GLOB_NOSORT); // сортировка файлов нам не нужна
       foreach ($logs as $log) {
          $basename = str_replace('.csv','',$log);
          $newname=sprintf('%s.%03d',$basename,$count);
       if (file_exists($dir.$newname)) unlink($dir.$newname); // удаляем самый старый лог
          for ($i=$count; $i>1; $i--) {
             $oldname=sprintf('%s.%03d',$basename,$i-1);
             if (file_exists($dir.$oldname)) rename($dir.$oldname,$dir.$newname);
             $newname=$oldname; // небольшой трюк, чтобы лишний раз не вызывать sprintf: на каждой следующей итерации новым именем буде то, которое на предыдущем шаге было старым              
          }
          // TODO: сделать компрессию файла с логом, если соответствующий модуль установлен
          if (file_exists($dir.$log)) rename($dir.$log,$dir.$newname); //  переименовываем самый последний файл лога, с расширением CSV
       }
    }
    
    /** Очистка старых результатов поиска
     * 
     * @param integer $period Количество дней, которое прошло после поискового запроса **/
    function cron_search_results_clear($period) {
      $period=intval($period);
      if (!$period) return false;
      $timelimit = Library::$app->time-$period*24*60*60;
      $sql = 'SELECT id FROM '.DB_prefix.'search WHERE time<'.intval($timelimit);
      $ids = Library::$app->db->select_all_numbers($sql);
      $sql = 'DELETE FROM '.DB_prefix.'search_result WHERE '.Library::$app->db->array_to_sql($ids, 'sid');
      Library::$app->db->query($sql);
      $sql = 'DELETE FROM '.DB_prefix.'search WHERE time<'.intval($timelimit);
      Library::$app->db->query($sql);
    }
    
    /** Очистка логов с действиями модераторов
     *
     * @param integer $period Количество дней, которое прошло после поискового запроса **/
    function cron_mod_logs_clear($period) {
      $period=intval($period);
      if (!$period) return false;
      $timelimit = Library::$app->time-$period*24*60*60;
      $sql = 'DELETE FROM '.DB_prefix.'log_action WHERE time<'.intval($timelimit);
      Library::$app->db->query($sql);
    }
    
    /** Очистка таблицы online
     *
     * @param integer $period Количество дней, которое прошло после последнего обновления таблицы **/
    function cron_online_clear($period=2) {
      $period=intval($period);
      if (!$period) return false;
      $timelimit = Library::$app->time-$period*24*60*60;
      $sql = 'DELETE FROM '.DB_prefix.'online WHERE visittime<'.intval($timelimit);
      Library::$app->db->query($sql);
    }    

    /** Малая оптимизация: только основные таблицы **/
    function cron_light_optimize($empty) {
      require_once BASEDIR.'db/'.DB_driver.'.ext.php';
      $classname = DB_driver.'_extender';
      $extdb = new $classname(Library::$app->db);
      /* @var $extdb mysqli_extender */
      $extdb->optimize(array(DB_prefix.'text',DB_prefix.'topic',DB_prefix.'post',DB_prefix.'privmsg_post')); 
    }
    
    function cron_heavy_optimize($empty) {
      require_once BASEDIR.'db/'.DB_driver.'.ext.php';
      $classname = DB_driver.'_extender';
      $extdb = new $classname(Library::$app->db);
      /* @var $extdb mysqli_extender */
      $tables = $extdb->get_tables();
      $extdb->optimize($tables);       
    }
    
    /** Обновление времени последнего действия "Отметить все как прочитанное" для пользователей,
     * которые выполняли его более указанного времени назад. 
     * Нужно для того, чтобы у пользователей не возникало слишком много непрочитанных тем после 
     * долгого отсутствия, и чтобы не происходило перегрузки таблицы last_visit 
     * @param integer $period Количество дней, после которого все темы считаются как прочитанные
     **/
    function cron_update_mark_all($period) {
      $period=intval($period);
      if (!$period) return false;
      $timelimit = Library::$app->time-$period*24*60*60;
      $sql = 'UPDATE '.DB_prefix.'mark_all SET mark_time='.intval($timelimit).' WHERE fid=0 AND mark_time<'.intval($timelimit);
      Library::$app->db->query($sql);
      $sql = 'DELETE FROM '.DB_prefix.'mark_all WHERE mark_time<'.intval($timelimit);
      Library::$app->db->query($sql);
      $sql = 'DELETE FROM '.DB_prefix.'last_visit WHERE visit2<'.intval($timelimit).' AND oid!=0 AND type=\'forum\' AND bookmark=\'0\' AND subscribe=\'0\' ';
      Library::$app->db->query($sql);
    }
 }
