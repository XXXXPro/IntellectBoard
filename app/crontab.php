<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010-2012 4X_Pro, INTBPRO.RU
 *  http://intbpro.ru
 *  Скрипт выполнения задач по таймеру
 *  ================================ */


class Application_Crontab extends Application {
  function init() {
    $this->init_db();
    $this->time=time();
    Library::init($this);
    chdir(dirname(__FILE__).'/../www'); // так как при запуске через cron текущий каталог не выставляется автоматически, что может порождать ошибки
  }

  function main() {
        $start_time = microtime(true);
        $this->init();
        $this->db->lock_tables(DB_prefix.'crontab',true); // блокируем таблицу crontab полностью, чтобы исключить одновременное выполнение заданий двумя запросами

        $curtime=$this->time;
        $sql = 'SELECT id,library,proc,params,period FROM '.DB_prefix.'crontab WHERE period>0 AND nextrun<='.intval($curtime); // выбираем те задания, время которых ненулевое и уже в прошлом
        // пока таблицы заблокированы, выполняем только пересчет времени следующего выполнения
        // это сделано для того, чтобы не было двух одновременных попыток запустить "тяжелое" задание
        $jobs = $this->db->select_all($sql);
        for ($i=0,$count=count($jobs);$i<$count;$i++) {
          if ($jobs[$i]['period']>0) $jobs[$i]['nextrun']=$curtime+$jobs[$i]['period']*60; // если задание не одноразовое, задаем новое время выполнения
          else $jobs[$i]['nextrun']=0; // иначе прописываем ноль, чтобы в дальнейшем игнорировать это задание
          $this->db->update(DB_prefix.'crontab',$jobs[$i],'id='.intval($jobs[$i]['id']));
        }
        $this->db->unlock_tables(DB_prefix.'crontab');

        for ($i=0,$count=count($jobs);$i<$count;$i++) {
          if ($module=$this->load_lib($jobs[$i]['library'],false)) {
            if (method_exists($module,'cron_'.$jobs[$i]['proc'])) call_user_func(array($module,'cron_'.$jobs[$i]['proc']),$jobs[$i]['params']);
            else $this->log_entry('crontab',2,'modules/'.$jobs[$i]['library'].'.php','Не найдена процедура cron_'.$jobs[$i]['proc']);
          }
          else $this->log_entry('crontab',1,'crontab.php','Не найден модуль '.$jobs[$i]['module']);
        }

        // выполняем разовые «тяжёлые» задачи из таблицы task
        $max_time = php_sapi_name()=='cli' ? 60 : ini_get('max_execution_time'); // в случае запуска через cron макс. временем выполнения считаем минуту
        $runtime = $max_time*1000000 - (microtime(true) - $start_time); // оставшееся время для выполнения задач
        $max_tasks = floor($runtime/5000000.0); // исходим из того, что на каждую задачу отводится 5 секунд времени

        if ($max_tasks) { // если лимит времени на задачи есть
          $this->db->lock_tables(DB_prefix.'task',true); // блокируем таблицу crontab полностью, чтобы исключить одновременное выполнение заданий двумя запросами
          $sql = 'SELECT * FROM '.DB_prefix.'task WHERE nextrun<'.intval($this->time).' LIMIT '.intval($max_tasks);
          $tasks = $this->db->select_all($sql);
          if ($tasks) { // если невыполненные задачи есть, выполняем их «захват» — смещаем время следующей попытки выполнения на тот момент, при котором в норме задача будет уже завершена и удалена
            $task_ids = array();
            for ($i=0,$count=count($tasks);$i<$count;$i++) $task_ids[]=$tasks[$i]['id'];
            $sql = 'UPDATE '.DB_prefix.'task SET nextrun='.intval($this->time+6*$max_tasks).' WHERE '.$this->db->array_to_sql($task_ids,'id');
            $this->db->query($sql); // изменяем время nextrun на такое, чтобы было точно больше времени ожидаемого выполнения
          }
          $this->db->unlock_tables(DB_prefix.'task');
        }

        for ($i=0,$count=count($tasks);$i<$count;$i++) { // а теперь само выполнение
          $result = -1;          
          if (microtime(true) - $start_time < $max_time*1000000 - 5000000) { // если есть достаточный запас времени для выполнения
            try {
              if ($module=$this->load_lib($tasks[$i]['library'],false)) {
                if (method_exists($module,'task_'.$tasks[$i]['proc'])) {
                  $params = unserialize($tasks[$i]['params']);
                  $result = call_user_func(array($module,'task_'.$tasks[$i]['proc']),$params);
                }
                else $this->log_entry('tasks',2,'modules/'.$tasks[$i]['library'].'.php','Не найдена процедура task_'.$tasks[$i]['proc']);
              }
              else $this->log_entry('tasks',1,'crontab.php','Не найден модуль '.$task[$i]['module']);
            }
            catch (Exception $e) {
              $result=-2;
              $this->log_entry('tasks',1,$tasks[$i]['library'],'Процедура task_'.$tasks[$i]['proc'].' выбросила исключение: '.$e->getMessage());
            }
          }
          if ($result==0 || $tasks[$i]['errors']>=127) { // если в процессе выполнения не произошло ошибок, задача возвращает 0 или false. Считаем её выполненной и удаляем. Если количество ошибок превышает 127, то 
            $sql = 'DELETE FROM '.DB_prefix.'task WHERE id=?';
            $this->db->query($sql,true,array($tasks[$i]['id']));
          }
          else {
            $sql = 'UPDATE '.DB_prefix.'task SET nextrun=?, errors=errors+1 WHERE id=?';
            $this->db->query($sql,true,array($this->time+60*($tasks[$i]['errors']+1),$tasks[$i]['id']));
          }

        }

        $this->shutdown();

        $this->output('');
        $this->process_mail(); // отправляем почту в случае необходимости
  }

  function output($template) {
    if (php_sapi_name()!='cli' && !isset($_GET['debug'])) { // если вызов идет из броузера, а не через CLI (то есть не через системнй cron)
       header('Content-Type: image/gif');
      echo base64_decode('R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='); // выдаем прозрачный пиксель. Его код встроим прямо сюда, чтобы не читать лишний раз файл с диска
    }
    if (isset($_GET['debug']) && isset($GLOBALS['IntBF_debug'])) {
      echo $GLOBALS['IntBF_debug'];
    }
  }
}
