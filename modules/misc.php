<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2009-2011, 2013, 2020 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Модуль вывода вспомогательной информации (справка, правила, и т.п.)
 *  ================================ */

class misc extends Application {
  /** Вывод справочной информации.
  * Файлы справки должны храниться в каталоге Help
  **/
  protected $help_filename;
  protected $help_title;
  public $forum;

  function action_help() {
    $this->help_filename = !empty($_GET['help']) ? $_GET['help'] :'index.htm';
    if (!$this->valid_file($this->help_filename)) $this->output_404('Такого файла справки не существует!'); // проверка на то, чтобы пользователь не подсунул неправильное имя файла
    $fullname= BASEDIR.'help/'.$this->help_filename;
    if (!file_exists($fullname)) $this->output('Такого файла справки не существует!');
    $this->out->help = file_get_contents($fullname);
    if ($this->help_filename!=='index.htm' && file_exists(BASEDIR.'help/index.txt')) { 
      $helptopics = file(BASEDIR.'help/index.txt');
      $this->help_title = $this->help_filename;
      for ($i=0, $count=count($helptopics); $i<$count; $i++) if (trim($helptopics[$i])) {
        list($fn,$tl) = explode('|',$helptopics[$i]);
        if ($fn===$this->help_filename) $this->help_title=$tl;
      }
    }
  }

  function action_smiles() {
    $bbcode = $this->load_lib('bbcode');
    if ($bbcode) $this->out->smiles = $bbcode->load_smiles_hash();
  }
  
  function get_forum_id() {
    if (isset($_GET['forum'])) {
      $sql = 'SELECT id, title, hurl FROM '.DB_prefix.'forum WHERE hurl=\''.$this->db->slashes($_GET['forum']).'\'';
      $this->forum = $this->db->select_row($sql);
      if (empty($this->forum)) $this->output_404('Раздела с таким номером не существует!');
      if (!$this->check_access('view',$this->forum['id'])) $this->output_404('Раздела с таким номером не существует!');
      $forum_id = $this->forum['id']; 
    }
    else $forum_id=0;
    return $forum_id;
  }
  
  /** Вывод правил форума или раздела с проверкой прав доступа **/
  function action_rules() {
    $rules_id = $this->get_forum_id();
    $this->out->rules = $this->get_text($rules_id,0);
    $this->out->onlytext = !empty($_GET['onlytext']);
    if (empty($this->out->rules)) $this->output_404('Для данного раздела не предусмотрено отдельных правил!');
  }
  
  /** Функция отметки всех сообщений в разделе как прочитанных.
  * TODO: подумать, возможно, переместить действие в bookmark.php или еще какой-то модуль.
  **/
  function action_mark_all() {
    $fid = $this->get_forum_id();
    $uid = $this->get_uid();
    $sql = 'DELETE FROM '.DB_prefix.'mark_all WHERE uid='.intval($uid);
    if ($fid!=0) $sql.=' AND fid='.intval($fid); // если отмечаем как прочитанное вообще все, то удаляем все старые пометки для подразделов
    $this->db->query($sql);
    $data['uid']=$uid; $data['fid']=$fid; $data['mark_time']=$this->time;
    $this->db->insert(DB_prefix.'mark_all',$data);
    $this->message('Сообщения отмечены как прочитанные!',1);
    $this->redirect($this->http($this->url($this->forum['hurl'].'/')));
  }  
  
  /** Вывод списка команды форума с указанием прав доступа **/
  function action_team() {
     $sql = 'SELECT CASE WHEN u.title!=\'\' THEN u.title ELSE g.name END AS user_title, g.level, g.admin, '.
     'u.id AS uid, u.display_name, u.avatar, u.photo, ue.reg_date, tx.data as text, u.location  '.
     'FROM '.DB_prefix.'group g, '.DB_prefix.'user_ext ue '.
     'LEFT JOIN '.DB_prefix.'user u ON (ue.id=u.id) '.
     'LEFT JOIN '.DB_prefix.'text tx ON (tx.id=u.id AND tx.type=33) '.
     'WHERE g.team=\'1\' AND g.level=ue.group_id '.
     'ORDER BY ue.reg_date ASC';
     $this->out->team_users = $this->db->select_hash($sql,'uid');
     $sql = 'SELECT f.id AS fid, f.title AS title, f.hurl AS hurl, ue.id AS uid, m.role '.
     'FROM '.DB_prefix.'group g, '.DB_prefix.'user_ext ue, '.DB_prefix.'moderator m '.
     'LEFT JOIN '.DB_prefix.'forum f ON (f.id=m.fid) '.
     'WHERE g.team=\'1\'  AND g.level=ue.group_id AND ue.id=m.uid '.
     'ORDER BY ue.id,f.id';
     $roles = $this->db->select_super_hash($sql,'uid');
     if (is_array($this->out->team_users)) foreach ($this->out->team_users as  $uid=>$curitem) {
        $g_mod = false;
        $g_exp = false;
        $buffer='';
        if ($curitem['admin']==1) {
          $buffer.='администратор форума';
          $g_exp = true;
          $g_mod= true;
        }
        else {                
         foreach ($roles[$curitem['uid']] as $currole) {
           if ($buffer) $buffer.=', ';
           elseif (intval($currole['fid'])===0 && $currole['role']==='expert') {
              $buffer.='глобальный эксперт';
              $g_exp = true;
           }
           elseif (intval($currole['fid'])===0 && $currole['role']==='moderator') {
              $buffer.='глобальный модератор';
              $g_mod= true;              
           }
           elseif ($currole['role']==='expert' && !$g_exp && $this->check_access('view',$currole['fid'])) { // если пользователь -- эксперт раздела и не глобальный эксперт
              $buffer.='эксперт раздела &laquo;<a href="'.$this->url($currole['hurl']).'">'.htmlspecialchars($currole['title']).'</a>&raquo;';
           }
           elseif ($currole['role']==='moderator' && !$g_mod && $this->check_access('view',$currole['fid'])) { // если пользователь -- эксперт раздела и не глобальный эксперт
              $buffer.='модератор раздела &laquo;<a href="'.$this->url($currole['hurl']).'">'.htmlspecialchars($currole['title']).'</a>&raquo;';
           }
         }
        }
        $this->out->team_users[$uid]['roles']=$buffer;
     }     
  }
  
  function action_levels() {
    $sql = 'SELECT * FROM '.DB_prefix.'group WHERE level>0 ORDER BY level';
    $this->out->groups = $this->db->select_all($sql);
  }


  function set_title() {
    $result=false;
    if ($this->action==='help') {
      if ($this->help_filename==='index.htm')  $result='Справка Intellect Board :: '.$this->get_opt('site_title');
      else $result=$this->help_title.' :: Справка :: '.$this->get_opt('site_title');
    }
    elseif ($this->action==='rules') {
      $result='Правила форума &laquo;'.$this->get_opt('site_title').'&raquo;';
    }
    elseif ($this->action==='team') {
      $result='Команда форума &laquo;'.$this->get_opt('site_title').'&raquo;';
    }
    elseif ($this->action==='levels') {
      $result='Уровни доступа участников';
    }
    elseif ($this->action==='smiles') {
      $result='Используемые на форуме смайлики';
    }
    return $result;
  }
  
  function set_location() {
    $result = parent::set_location();
    if ($this->action==='help') {
      if ($this->help_filename==='index.htm') $result[]=array('Справка Intellect Board Pro');
      else {
        $result[]=array('Справка Intellect Board Pro','./');
        $result[]=array($this->help_title);
      }
    }
    elseif ($this->action==='rules') {
      if (isset($this->forum)) {
        $result[]=array($this->forum['title'],$this->url($this->forum['hurl'].'/'));
        $result[]=array('Правила раздела');
      }
      else $result[]=array('Правила форума');
    }
    elseif ($this->action==='team') $result[]=array('Наша команда');
    elseif ($this->action==='levels') $result[]=array('Уровни доступа участников');
    elseif ($this->action==='smiles') $result[]=array('Смайлики');
    return $result;
  }
  
  function get_action_name() {
    if ($this->action==='help') $result='Читает справку форума';
    elseif ($this->action==='rules') $result='Читает правила форума';
    elseif ($this->action==='team') $result='Просматривает страницу команды форума';    
    elseif ($this->action==='levels') $result='Изучает уровни доступа';    
    elseif ($this->action==='smiles') $result='Изучает список смайликов';    
    else $result=parent::get_action_name();
     return $result;
  }  
  
}
