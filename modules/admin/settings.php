<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2014-2015, 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Основной модуль Центра Администрирования  Intellect Board 3 Pro
 *  Отвечает за показ статуса форума и общие настройки
 *  ================================ */

class settings extends Application_Admin {
/** Выдача различных параметров сервера **/  
  function action_view() {
    // часть кода заимствована из TextCMS
    $data['basedir']=realpath(BASEDIR);
    $data['CMS_VERSION']=INTB_VERSION;
    $data['DB_DRIVER']=DB_driver;
    $data['DB_PERSIST']=defined('DB_persist') && DB_persist;
    $data['CMS_DEBUG']=$this->get_opt('debug');
    $data['SQL_DEBUG']=$this->get_opt('sql_debug');
    $data['CMS_NOCACHE']=$this->get_opt('nocache');
    $data['PARSER']=$this->get_opt('site_template_lib');
    $data['SESSION_PATH']=$this->get_opt('session_path');
    if (!$data['SESSION_PATH']) $data['SESSION_PATH']=ini_get('session.save_path');
    $data['EMAIL']=$this->get_opt('email_enabled');
    
    $data['SERVER_SOFT']=$_SERVER['SERVER_SOFTWARE'];
    $data['SERVER_IP']= isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
    $data['PHP_VERSION']=phpversion();
    if (function_exists('gd_info')) {
        $gdinfo = gd_info();
        $data['GD_VERSION'] = $gdinfo['GD Version'];
        if (!$gdinfo['JPEG Support']) $data['GD_VERSION'] .= ', без JPEG';
        if (!$gdinfo['GIF Create Support']) $data['GD_VERSION'] .= ', без GIF';
        if (!$gdinfo['PNG Support']) $data['GD_VERSION'] .= ', без PNG';
    }
    else $data['GD_VERSION']='GD не установлена';
    
    $data['LIMIT_TIME']=ini_get('max_execution_time');
    $data['LIMIT_MEM']=ini_get('memory_limit');
    $data['LIMIT_SIZE']=ini_get('upload_max_filesize').', '.ini_get('post_max_size');
    $data['LIMIT_MEM']=ini_get('memory_limit');
    $data['LIMIT_DISK']=floor(diskfreespace('./')/(1024*1024)).' M';
    
    $data['SCRIPT_ERRORS']=(ini_get('display_errors')) ? '<span class="ok">Вкл.</span>' : '<span class="warn">Выкл.</span>';
    $data['PHP_UPLOAD']=(ini_get('file_uploads')) ? '<span class="ok">Вкл.</span>' : '<span class="warn">Выкл.</span>';
//    $data['PHP_GLOBALS']=(ini_get('register_globals')) ? '<span class="warn">Вкл.</span>' : '<span class="ok">Выкл.</span>';
//    $data['PHP_QUOTES']=(ini_get('rmagic_quotes_gpc')) ? '<span class="warn">Вкл.</span>' : '<span class="ok">Выкл.</span>';
    $data['SCRIPT_OWNER_NAME']=get_current_user();
    $data['SCRIPT_OWNER_UID']=getmyuid();
    if (function_exists('posix_geteuid')) {
      $euid=posix_geteuid();
      $name=posix_getpwuid($euid);
      $data['SCRIPT_RUN_NAME']=$name['name'];
      $data['SCRIPT_RUN_UID']=$euid;
    }
    else $data['SCRIPT_RUN']='Неизвестно';
    $data['CMS_PATH']=dirname($_SERVER['SCRIPT_FILENAME']);
    $data['SERVER_TEMP']=ini_get('upload_tmp_dir');
    $data['USER_ACTIVATE']=$this->get_opt('userlib_activation');
    
    $data['WRITABLE_config']=is_writable(BASEDIR.'etc/ib_config.php');
    $data['WRITABLE_logs']=is_writable(BASEDIR.'logs');
    $data['WRITABLE_av']=is_writable(BASEDIR.'www/f/av');
    $data['WRITABLE_up']=is_writable(BASEDIR.'www/f/up/1');
    $data['WRITABLE_cap']=is_writable(BASEDIR.'www/f/cap');
    $data['WRITABLE_ph']=is_writable(BASEDIR.'www/f/ph');
    
    $this->out->db_version = $this->db->select_str('SELECT VERSION()');
        
    $this->out->status=$data;
    
    $tlib = $this->load_lib('topic',false);
    if ($tlib) {
      $this->out->post_active = $tlib->count_posts(array('all'=>false));
      $this->out->post_premod = $tlib->count_posts(array('premod'=>true));
      $this->out->post_valued = $tlib->count_posts(array('valued'=>true));

      $this->out->topic_active = $tlib->count_topics(array('all'=>false));
      $this->out->topic_premod = $tlib->count_topics(array('premod'=>true));      
    }
    
    $userlib = $this->load_lib('userlib',false);    
    if ($userlib) {
      $this->out->users_active = $userlib->count_users(array('status'=>0));
      $this->out->users_inactive = $userlib->count_users(array('status'=>1));
      $this->out->users_banned = $userlib->count_users(array('status'=>2));
      $this->out->admins = $userlib->get_admins();
    }
    
    $sql = 'SELECT bot_name, last_visit FROM '.DB_prefix.'bots';
    $this->out->bots = $this->db->select_all($sql);
    
    $sql = 'SELECT MIN(reg_date) FROM '.DB_prefix.'user u, '.DB_prefix.'user_ext ue '.
        'WHERE u.id>'.AUTH_SYSTEM_USERS.' AND u.id=ue.id AND u.status=\'0\'';
    $minreg = $this->db->select_int($sql);
    if ($minreg) {
      $this->out->forum_exists = floor(($this->time-$minreg)/(24*60*60));
    }

    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'task';
    $this->out->tasks_count = $this->db->select_int($sql);
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'task WHERE errors>0';
    $this->out->tasks_errors = $this->db->select_int($sql);
  }
  
  function action_edit_rules() {
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);
      $misclib -> save_text($_POST['text'],0,0); // текст 0 типа для объекта с номером 0 -- это правила форума 
      $this->message('Правила сохранены!',1);
    }
    $this->out->rules = $this->get_text(0, 0);
  }
  
  function action_edit_foreword() {
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);
      $misclib -> save_text($_POST['text'],0,2); // текст 1 типа для объекта с номером 0 -- это вводное слово форума целиком
      $this->message('Вводный текст сохранен!',1);
    }
    $this->out->rules = $this->get_text(0, 2);
  }  
  
  function action_announce() {
    $fid = isset($_REQUEST['fid']) ? intval($_REQUEST['fid']) : 0;
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);      
      if (!empty($_POST['text'])) {
        $misclib->save_text($_POST['text'],$fid,1); // текст 0 типа для объекта с номером 0 -- это правила форума
        $this->message('Текст объявления сохранен!',1);
      }
      else {
        $misclib->delete_text($fid,1); // текст 0 типа для объекта с номером 0 -- это правила форума
        $this->message('Текст объявления удален!',1);        
      }
    }
    $this->out->announce = $this->get_text($fid,1);
    $this->out->fid = $fid;
    $this->out->forum_list = array('0'=>'Главная страница форума')+$this->get_forum_list('read',1);
  }
  
  function action_settings() {
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);
      /** @var Library_misc $misclib **/
      $misclib->save_config($_POST['config']);
      $this->message('Настройки сохранены',1);
      $this->redirect($this->http($_SERVER['REQUEST_URI']));
    }
    $const = get_defined_constants(true);
    foreach ($const['user'] as $item=>$value) if (strpos($item,'CONFIG_')===0) $data[substr($item,7)]=constant($item);
    $this->out->config = $data;
    // получаем список доступных шаблонов
    $templatelib = $this->load_lib('template',false);
    /* @var $templatelib Library_template */
    $this->out->templates = $templatelib->get_list();
  }
  
  /** Страница настройки задаваемых библиотек и некоторых других критичных настроек форума **/
  function action_libs() {
    if (!$this->is_admin(true)) { // менять наиболее критичные настройки могут только Основатели форума
      $this->message('Только Основатели форума могут менять эти настройки',2);
      return 'main.tpl';
    }
    $this->action_settings();
  }
  
  function action_crontab() {
    if ($this->is_post()) {
      if (!empty($_POST['cron'])) foreach ($_POST['cron'] as $id=>$data) {
        $result=$this->db->update(DB_prefix.'crontab',$data,'id='.intval($id));
      }
      $this->message('Изменения сохранены!',1);      
    }
    $sql = 'SELECT * FROM '.DB_prefix.'crontab';
    $this->out->cron = $this->db->select_all($sql);
  }
  
  function action_subactions() {
    $sql = 'SELECT * FROM '.DB_prefix.'subaction ORDER BY block, priority';    
    $this->out->subactions = $this->db->select_all($sql);
    foreach ($this->out->subactions as &$subaction) {
      if (empty($subaction['name'])) {
        $subaction['name']='Вызов '.$subaction['proc'].' из библиотеки '.$subaction['library'].'.';
      }
      if ($subaction['tid']) $subaction['name'].=' для темы №'.$subaction['tid'];
      if ($subaction['fid']) $subaction['name'].=' в разделе №'.$subaction['fid'];
      $subaction['descr2']=' Вызывается ';
      if ($subaction['action']!="*" || $subaction['module']!="*") $subaction['descr2'].=', если'; else $subaction['descr2'].=' для любых действий';
      if ($subaction['module']!="*") $subaction['descr2'].='если модуль — '.$subaction['module'];
      if ($subaction['action']!="*" && $subaction['module']!="*") $subaction['descr2'].=' и ';
      if ($subaction['action']!="*") $subaction['descr2'].='если действие равно '.$subaction['action'];
    }
    $this->out->toggle_key = $this->gen_auth_key(false,'subaction_change');
    $this->out->read_only=!$this->is_admin(true) && !$this->get_opt('enable_admin_subactions');
  }
  
  function subaction_validate($data) {
    $fields = array('module','action','tid','fid','library','proc','block');
    foreach ($fields as $field) if (!isset($data[$field]) || $data[$field]==="") return false;
    return true;
  }
  
  function action_subaction_new() {
    if (!$this->is_admin(true) && !$this->get_opt('enable_admin_subactions')) { // менять наиболее критичные настройки могут только Основатели форума
      $this->message('Только Основатели форума могут менять эти настройки',2);
      return 'main.tpl';
    }    
    if ($this->is_post()) {
      $data = $_POST['subaction'];
      $data['active']=!empty($_POST['subaction']['active']);
      if (!$this->subaction_validate($data)) $this->message('Все поля, кроме параметров, обязательны для заполнения!',3);
      else {
        $this->db->insert(DB_prefix.'subaction',$data);
        $this->message('Вспомогательное действие создано!',1);
        $this->redirect($this->http($this->url('admin/settings/subactions.htm')));
      }
      $this->out->subaction = $data;
    }
    else {
      $this->out->subaction = array('module'=>'*','action'=>'*','fid'=>0,'tid'=>0,'active'=>1,'priority'=>1);      
    }
    return 'settings/subaction.tpl';
  }
  
  function action_subaction_edit() {
    if (empty($_REQUEST['id'])) $this->output_403('Не указан идентификатор вспомогательного действия!');
    if (!$this->is_admin(true) && !$this->get_opt('enable_admin_subactions')) { // менять наиболее критичные настройки могут только Основатели форума
      $this->message('Только Основатели форума могут менять эти настройки',2);
      return 'main.tpl';
    }
    $id = $_REQUEST['id'];
    if ($this->is_post()) {
      $data = $_POST['subaction'];
      $data['active']=!empty($_POST['subaction']['active']);
      if (!$this->subaction_validate($data)) $this->message('Все поля, кроме параметров, обязательны для заполнения!',3);
      else {
        if (empty($_REQUEST['delete'])) {
          $this->db->update(DB_prefix.'subaction',$data,'id='.intval($id));
          $this->message('Вспомогательный блок изменен!',1);
        }
        else {
          $result=$this->db->query('DELETE FROM '.DB_prefix.'subaction WHERE id='.intval($id));
          $this->message('Вспомогательный блок удален!',1);
        }
        // _dbg($_REQUEST['delete']);
        $this->redirect($this->http($this->url('admin/settings/subactions.htm')));
      }
      $this->out->subaction = $data;
    }
    else {
      $sql = 'SELECT * FROM '.DB_prefix.'subaction WHERE id='.intval($id);
      $this->out->subaction = $this->db->select_row($sql);
    }
    return 'settings/subaction.tpl';    
  }
  
  function action_subaction_change() {    
    if (empty($_REQUEST['authkey'])) $this->output_403('Не указан аутентификационный ключ!');
    if (empty($_REQUEST['id'])) $this->output_403('Не указан id вспомогательного действия!');
    if (!isset($_REQUEST['enable'])) $this->output_403('Не указано, что нужно сделать: включить или отключить!');
    $result=$this->db->update(DB_prefix.'subaction',array('active'=>intval($_REQUEST['enable'])),'id='.intval($_REQUEST['id']));
    if ($result) {
      $this->message($_REQUEST['enable'] ? 'Вспомогательное действие включено.' : 'Вспомогательное действие выключено.' ,1);
      $this->redirect($this->http($this->url('admin/settings/subactions.htm')));
    }
  }
    
  /** Выбор меню для редактирования (главное или меню админки, также возможно создание своих меню) **/
  function action_menu() {
    $sql = 'SELECT * FROM '.DB_prefix.'menu';
    $this->out->menus = $this->db->select_all($sql);
  }
  
  /** Редактирование конкретного меню **/
  function action_menu_edit() {
    $menu_id = intval($_REQUEST['id']);
    $checkboxes = array('show_guests','show_users','show_admins','hurl_mode');
    if (empty($menu_id)) $this->output_403('Не указан идентификатор меню!');
    if ($this->is_post()) {
      if (!empty($_POST['items'])) foreach ($_POST['items'] as $id=>$data) {
        foreach ($checkboxes as $box) if (empty($data[$box])) $data[$box]='0';
        if (!empty($data['url']))  $this->db->update(DB_prefix.'menu_item',$data,'id='.intval($id));
        else {
          $sql = 'DELETE FROM '.DB_prefix.'menu_item WHERE id='.intval($id);
          $this->db->query($sql);
        }
      }
      if (!empty($_POST['newitems'])) foreach($_POST['newitems'] as $data) if (!empty($data['url'])) {
        foreach ($checkboxes as $box) if (empty($data[$box])) $data[$box]='0';
        $data['mid']=$menu_id;
        $this->db->insert(DB_prefix.'menu_item',$data);
      }
      $this->message('Настройки меню сохранены!',1);
      // очищаем кеш, чтобы изменения отобразились сразу же
      $this->clear_cached('Menu_user_'.$menu_id);
      $this->clear_cached('Menu_guest_'.$menu_id);
    }
    $sql = 'SELECT * FROM '.DB_prefix.'menu_item WHERE mid='.intval($menu_id).
      ' ORDER BY sortfield';
    $this->out->menu_items = $this->db->select_all($sql);
  }
  
  /** Редактор файлов стиля **/
  function action_edit_style() {
    if (!$this->is_admin(true)) { // если нет прав founder, то создавать группу пользователь не имеет права
      $this->message('Вы не можете редактировать стили, так как не имеете прав основателя!',3);
      return 'admin/main.tpl';
    }    
    $style = isset($_REQUEST['style']) ? $_REQUEST['style'] : $this->get_opt('template','user');
    if (!$style) $style=$this->get_opt('site_template'); // если у пользователя стоит стиль по умолчанию, берем его из настроек форума
    $filename = isset($_REQUEST['filename']) ? $_REQUEST['filename'] : false;
    $mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : false;
    $dirname = BASEDIR.'www/s/'.$style;
    if (!$this->valid_file($style) || !is_dir($dirname)) $this->output_403('Некорректное имя стиля!');
    if ($style==='def') $this->message('Нельзя редактировать файлы стиля def! Создайте новый стиль и скопируйте в него те файлы, которые вы хотите изменитьь!',2); 
    
    if ($this->is_post()) {
      if ($style!=='def') {
        $fullname = BASEDIR.'/'.($mode=='css' ? 'www/s' : 'template/').'/'.$style.'/'.$filename;
        file_put_contents($fullname, $_POST['data']);
        $this->message('Изменения в файле '.htmlspecialchars($filename).' сохранены!',1);
      }
      $this->redirect($this->http($_SERVER['REQUEST_URI']));
    }
    else {
      $this->out->css_files = $this->get_files($dirname,'\.css$');
      $this->out->tpl_files = $this->get_files(BASEDIR.'template/'.$style,'\.tpl$');     
      if (empty($this->out->css_files) && empty($this->out->tpl_files)) $this->message('В данном стиле пока нет ни одного файла!',2);
      else {
        if (!$filename) {
          if (!empty($this->out->css_files)) {
            $mode = 'css';
            $filename = $this->out->css_files[0]; 
          }
          else {
            $mode = 'tpl';
            $filename = $this->out->tpl_files[0];
          }
        }
        $fullname = BASEDIR.'/'.($mode=='css' ? 'www/s' : 'template/').'/'.$style.'/'.$filename; 
        $this->out->data = file_get_contents($fullname);
      }
      $this->out->filename = $filename;
      $this->out->mode = $mode;
      $this->out->style = $style;
      $this->out->locked_style = file_exists($dirname.'/locked.txt');
      
      $templatelib = $this->load_lib('template');
      /* @var $templatelib Library_template */
      if ($templatelib) $this->out->templates=$templatelib->get_list(true); // получаем список всех стилей, включая скрытые
      
    }
  }
  
  function get_files($base,$mask,$dir='') {
    $dh = opendir($base.'/'.$dir);
    $result = array();
    while ($item = readdir($dh)) {
      if (is_dir($base.$dir.'/'.$item) && $item!='.' && $item!='..') $result = array_merge($result,$this->get_files($base, $mask,$item.'/'));
      elseif (preg_match('|'.$mask.'|', $item) && !is_dir($base.$dir.'/'.$item)) $result[]=$dir.$item;
    }
    sort($result);
    return $result;
  }
  
  /** Создать новый стиль **/
  function action_create_style() {
    if (!$this->is_admin(true)) { // если нет прав founder, то создавать группу пользователь не имеет права
      $this->message('Вы не можете создать стиль, так как не имеете прав основателя!',3);
      return 'admin/main.tpl';
    }
    if ($this->is_post()) {
      $style = $_POST['style']['filename'];
      if (!$this->valid_file($style)) $this->message('Имя стиля недопустимо с точки зрения безопасности!',3);
      elseif (!preg_match('|^[a-zA-z\-_0-9]+$|',$style)) $this->message('Имя стиля содержит недопустимые символы!',3);
      elseif (file_exists(BASEDIR.'www/s/'.$style)) $this->message('Стиль с таким именем уже существует!',3);
      elseif (!is_writable(BASEDIR.'www/s/')) $this->message('Невозможно создать стиль! Каталог www/ недоступен для записи!',3);
      else {
        mkdir(BASEDIR.'www/s/'.$style); // создаем каталог для статики
        mkdir(BASEDIR.'template/'.$style); // создаем каталог для шаблонов
        if (!empty($_POST['style']['name'])) file_put_contents(BASEDIR.'www/s/'.$style.'/name.txt', $_POST['style']['name']); // сохраняем имя стиля
        $this->message('Стиль создан! Теперь вы можете скопировать файлы, которые хотите изменить, из стиля по умолчанию.');
        $this->redirect($this->http(str_replace('create_style.htm','copy_file.htm',$_SERVER['REQUEST_URI']).'?style='.$style));
        // создаем .htaccess, который будет брать недостающие файлы из стиля по умолчанию
        $htaccess = 'RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ ../def/$1 [L,QSA]';
        file_put_contents(BASEDIR.'www/s/'.$style.'/.htaccess',$htaccess);
        if (!empty($_POST['style']['locked'])) file_put_contents(BASEDIR.'www/s/'.$style.'/locked.txt','');
      }
    }    
  }
  
  /** Скопировать файлы из стиля def **/
  function action_copy_file() {
    if (!$this->is_admin(true)) { // если нет прав founder, то создавать группу пользователь не имеет права
      $this->message('Вы не можете копировать файлы стиля, так как не имеете прав основателя!',3);
      return 'admin/main.tpl';
    }    
    $style=$_REQUEST['style'];
    if (!$this->valid_file($style) || !$style) $this->output_403('Имя стиля недопустимо с точки зрения безопасности!');
    elseif (!file_exists(BASEDIR.'www/s/'.$style) || !is_dir(BASEDIR.'www/s/'.$style)) $this->output_403('Нет такого стиля!'); 
    if ($style=='def') $this->output_403('Нельзя копировать файлы в стиль по умолчанию!');
    if ($this->is_post()) {
      $count = 0;
      if (!empty($_POST['tpl'])) foreach ($_POST['tpl'] as $file=>$value) {
        $dirname = dirname(BASEDIR.'template/'.$style.'/'.$file);
        if (!is_dir($dirname)) mkdir($dirname,0777,true); // если нет соответствующего каталога, создаем его
        if ($this->valid_file($file)) copy(BASEDIR.'template/def/'.$file,BASEDIR.'template/'.$style.'/'.$file);
        $count++;
      }     
      if (!empty($_POST['css'])) foreach ($_POST['css'] as $file=>$value) {
        $dirname = dirname(BASEDIR.'www/s/'.$style.'/'.$file);
        if (!is_dir($dirname)) mkdir($dirname,0777,true); // если нет соответствующего каталога, создаем его        
        if ($this->valid_file($file)) copy(BASEDIR.'www/s/def/'.$file,BASEDIR.'www/s/'.$style.'/'.$file);
        $count++;
      }
      $this->message('Скопировано файлов: '.$count);
      $this->redirect($this->http(str_replace('copy_file.htm','edit_style.htm',$_SERVER['REQUEST_URI'])));
    }
    else {
      $this->out->css_files = $this->get_files(BASEDIR.'www/s/def','.*');
      $this->out->tpl_files = $this->get_files(BASEDIR.'template/def','\.tpl$');
      $this->out->style=$style;      
    }
  }
  
  function action_toggle_style() {
    if (!$this->is_admin(true)) { // если нет прав founder, то создавать группу пользователь не имеет права
      $this->message('Вы не можете копировать файлы стиля, так как не имеете прав основателя!',3);
      return 'admin/main.tpl';
    }
    $style=$_REQUEST['style'];
    if (!$this->valid_file($style) || !$style) $this->output_403('Имя стиля недопустимо с точки зрения безопасности!');
    elseif (!file_exists(BASEDIR.'www/s/'.$style) || !is_dir(BASEDIR.'www/s/'.$style)) $this->output_403('Нет такого стиля!');
    if ($style=='def') $this->output_403('Нельзя копировать файлы в стиль по умолчанию!');
    
    $filename = BASEDIR.'www/s/'.$style.'/locked.txt';
    if ($_REQUEST['switch']=='close') file_put_contents($filename,'');
    elseif (file_exists($filename)) unlink($filename);
    $this->redirect($this->http(str_replace('toggle_style.htm','edit_style.htm',$_SERVER['REQUEST_URI'])));
  }
}