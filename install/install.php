<?php
/** ================================
*  @package IntBPro
*  @author 4X_Pro <me@4xpro.ru>
*  @version 3.05
*  @copyright 2015, 4X_Pro, INTBPRO.RU
*  @url https://intbpro.ru
*  Инсталлятор Intellect Board 3 Pro
*  ================================ */

define('IntB_db_version',104); // Номер версии структуры базы данных для устанавливаемой версии. Будет использоваться при обновлениях.

class Application {
  private $step;
  private $mode;
  public $db;
  private $skip_errors = false;
  /* @var $db Database */
  private $allow_next = true;
  private $allow_prev = true;


  function init() {
    $this->step = empty($_REQUEST['step']) ? 1 : $_REQUEST['step'];
    $this->mode = empty($_REQUEST['mode']) ? false : $_REQUEST['mode'];
    define('BASEDIR','../');
    ini_set('display_errors',true); // для упрощения выявления проблем при установке
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

    $pos=strrpos($_SERVER['PHP_SELF'],'www/'); // последний www в пути будет каталогом www в дистрибутиве IntB
    $this->sitepath=($pos) ? substr($_SERVER['PHP_SELF'],0,$pos) : dirname($_SERVER['PHP_SELF']);
    if ($this->sitepath=='\\') $this->sitepath='/'; // для Windows
    if (substr($this->sitepath,-1,1)!=='/') $this->sitepath=$this->sitepath.'/';

    if ($this->step>1) {
      ob_start();
    }
  }

  // Данный init_db вызывается не из init, а из action'ов по мере необходимости, т.к. соединение с базой нужно только на некоторых шагах
  function init_db($params) {
    $db_driver = $params['DB_driver'];
    $result = include_once(BASEDIR.'db/database.php');
    echo tag('div','Подключение общего класса баз данных (файл db/database.php): '.cond($result,'Ok','Ошибка!'));
    if (!$result || !class_exists('Database')) {
      echo tag('div','Невозможно создать класс для работы с базой данных, работа не может быть продолжена!',-1);
      $this->allow_next=false;
      return;
    }
    $result = include_once(BASEDIR.'db/'.$db_driver.'.php');
    echo tag('div','Подключение драйвера '.$db_driver.' (файл db/'.$db_driver.'): '.cond($result,'Ok','Ошибка!'));
    if (!$result || !class_exists('Database_'.$db_driver)) {
      echo tag('div','Невозможно создать класс для работы с базой данных, работа не может быть продолжена!',-1);
      $this->allow_next=false;
      return;
    }
    $classname = 'Database_'.$db_driver;
    $this->db = new $classname;
    $this->db->connect($params);
  }

  function main() {
    $this->init();
    $this->process();
    $this->output();
    $this->shutdown();
  }

  function process() {
    if (!method_exists($this, 'action_step'.$this->step)) trigger_error('Не найден метод, отвечающий за нужный шаг инсталляции!',E_USER_ERROR);
    // в отличие от основных модулей IntB, вызов главной процедуры будет происходить
    // не здесь, а в output: это менее громоздко и делает код более понятным.
  }


  function output() {
    header('HTTP/1.0 200 Ok');
    header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html><head>
<meta charset="utf-8">
<title>Установка Intellect Board, шаг <?php echo $this->step ?> из 6</title>
<style type="text/css">
html { height: 100% }
body { height: 100%; padding: 0; margin: 0; font-size: 62.5%; font-family: 'Liberation Sans','Nimbus Sans','Open Sans', Tahoma, Verdana, Arial, sans-serif }
form { padding: 0; margin: 0 }
fieldset { border: 0 }
legend { display: none }
#ib_all { margin: auto; height: 100%; padding: 0 50px; font-size: 1.25em; width: 992px; position: relative }
#header, #content, #footer { position: absolute; width: 992px; padding: 0 10px }
#header { height: 6%;  }
#content { height: 85%;  top: 10%; border: #D8EAFF 1px solid; }
#content form { line-height : 180% }
#content li { line-height: normal }
#content ul { margin: 0 }
#content h4 { padding: 0.1em 0 0 0; margin: 0 }
#footer { height: 5%; position: absolute; top: 95% }
#ib_all address.copyright { text-align: center; font-size: 90%; font-style: normal; margin: 10px }

#ib_all h1 { font-size: 180%; margin: 1em 0 0.2em 0; color: #0101C4; padding: 0; font-weight: bold; font-family: 'Constantia', 'Times New Roman', serif  }
#ib_all h2 { padding: 0.2em 0 0.5em 0; margin: 0; color: #333; }
#header { background: #E5F1FF; border-left: #D8EAFF 1px solid;
border-right: #D8EAFF 1px solid; position: relative; font-family: 'Constantia', 'Times New Roman', serif; padding-top: 1% }
#header .site_title { font-size: 2.5em; text-align: center; color: #2524b0; line-height: 100%; vertical-align: middle; }

#back { position: absolute; left: 20%; bottom: 8%; display: block; font-size: 140%; padding: 4px 40px }
#forward { position: absolute; right: 24%; bottom: 8%; font-size: 140%; padding: 4px 40px }
#output {  position: absolute; height: 74%; top: 10%; overflow: auto; width: 98% }
span.fd { display: inline-block; width: 25% }

div.msg_error, #ib_all div.msg_warn, #ib_all div.msg_ok { padding: 4px 0px; margin: 2px 0 }
.msg_error { color: #800; font-weight: bold }
.msg_warn { color: #993; font-weight: bold }
.msg_ok { color: #080; font-weight: bold }
</style>
</head>
<body>
<div id="ib_all">
<div id="header">
<div class="site_title">Установка Intellect Board Pro</div>
</div>

<div id="content">
<form action="" method="post" id="mainform"><fieldset><legend></legend>
<h1>Шаг <?php echo $this->step; ?> из 6</h1>
<div id="output">
<?php call_user_func(array($this,'action_step'.$this->step)); ?>
</div>
<?php if ($this->step!=1) :?><button id="back" type="button" onclick="window.history.back(-1)">&lt; Назад</button><?php endif; ?>
<input type="hidden" name="step" value="<?php echo $this->step+1; ?>" />
<?php if ($this->mode) : ?><input type="hidden" name="mode" value="<?php echo $this->mode; ?>" /><?php endif; ?>
<button id="forward" type="submit">Дальше &gt;</button>
</fieldset></form></div>

<div id="footer">
<address class="copyright">Программа установки <a href="https://intbpro.ru">Intellect Board Pro</a>, © 2013-2023, 4X_Pro.</address>
</div>

</div>
</body></html>
<?php
    ob_end_flush();
  }

  function action_step1() {
    $this->allow_prev = false; // на первом шаге вернуться назад нельзя

    echo tag('h2','Проверка системных требований');
    $version = version_compare(PHP_VERSION, '5.2.4','>=');
    $version2 = version_compare(PHP_VERSION, '7.0.0','>=');
    echo tag('p','Проверяем версию PHP: '.tag('span',PHP_VERSION,($version && $version2) ? 1 : (($version && !$version2) ? 2 : -1)));
    if (!$version) echo $this->tag('div', 'Версия PHP ниже требуемой! Корректная работа Intellect Board при такой версии невозможна!',-1);
    if (!$version2) echo $this->tag('div', 'Версия PHP ниже рекомендуемой! Хотя работа Intellect Board на этой версии возможна, отдельные функции могут быть недоступны. Рекомендуем обновиться.',1);

    $disabled = explode(',',ini_get('disable_functions'));
    echo tag('h4','Проверяем необходимые для работы Intellect Board функции:');
    $check_call = !in_array('call_user_func', $disabled);
    echo tag('li','Поддержка вызова произвольной функции (функция call_user_func): '.cond($check_call,'Ok','Не доступна!'));
    $check_ob = function_exists('ob_start') && !in_array('ob_start', $disabled);
    echo tag('li','Поддержка буферизации вывода (функция ob_start): '.cond($check_ob,'Ok','Не доступна!'));
    $check_session=function_exists('session_start') && !in_array('session_start', $disabled);
    echo tag('li','Поддержка сессий (функция session_start): '.cond($check_session,'Ok','Не доступна!'));
    $check_preg = function_exists('preg_match') && !in_array('preg_match', $disabled);
    echo tag('li','Поддержка регулярных выражений (функция preg_match): '.cond($check_preg,'Ok','Не доступна!'));
    $check_hash = function_exists('hash') && !in_array('hash', $disabled);
    echo tag('li','Поддержка хеша (функция hash): '.cond($check_hash,'Ok','Не доступна!')); 

    echo tag('h4','Проверяем функции для дополнительных опций Intellect Board');
    $check_gzip = function_exists('ob_gzhandler') && !in_array('ob_gzhandler', $disabled);
    echo tag('li','Сжатие страниц с помощью GZIP (функция gz_handler): '.cond($check_gzip,'Ok','Не доступна!'));
    $check_gd = function_exists('gd_info') && !in_array('gd_info', $disabled);
    echo tag('li','Работа с изображениями с помощью библиотеки GD (функция gd_info): '.cond($check_gd,'Ok','Не доступна!'));
    $check_gd = function_exists('exif_read_data') && !in_array('exif_read_data', $disabled);
    echo tag('li', 'Работа с EXIF-данными изображений (функция exif_read_data): ' . cond($check_gd, 'Ok', 'Не доступна!'));

    $check_mail = function_exists('mail') && !in_array('mail', $disabled);
    echo tag('li','Отправка почты средствами PHP (функция mail): '.cond($check_mail,'Ok','Не доступна!'));
    $check_mb = function_exists('mb_strlen') && !in_array('mb_strlen', $disabled);
    echo tag('li','Работа со строками Unicode (функция mb_strlen): '.cond($check_mb,'Ok','Не доступна!'));

    echo tag('h4','Расширения для работы с СУБД');
    $check_mysqli = function_exists('mysqli_connect') && !in_array('mysqli_connect', $disabled);
    echo tag('li','MySQLi: '.cond($check_mysqli,'Ok','Не доступно!'));
    if (version_compare(PHP_VERSION,'7.0.0','<')) {
      $check_mysql = function_exists('mysql_connect') && !in_array('mysql_connect', $disabled);
      echo tag('li','MySQL: '.cond($check_mysql,'Ok','Не доступно!'));
    }
    else $check_mysql = false;

    echo tag('h4','Права доступа и место на диске');
    $check_etc = is_writable(BASEDIR.'etc');
    echo tag('li','Каталог etc/: '.cond($check_etc,'доступен для записи','Не доступен для записи!'));
    $check_logs = is_writable(BASEDIR.'logs');
    echo tag('li','Каталог logs/: '.cond($check_logs,'доступен для записи','Не доступен для записи!'));
    $check_tmp = is_writable(BASEDIR.'tmp');
    echo tag('li','Каталог tmp/: '.cond($check_tmp,'доступен для записи','Не доступен для записи!'));
    $free = floor(disk_free_space(BASEDIR)/(1024*1024));
    echo tag('li','Свободно на диске: '.tag('span',$free.' Мб ',($free>20) ? 1 : (($free>1) ? 2 : -1)));

    if (!empty($_SERVER['REDIRECT_URL'])) echo tag('div','Перенаправление запросов происходит через корневой .htaccess. Это не мешает работе форума, но несколько снижает производительность.<br />Попробуйте настроить DocumentRoot так, чтобы он указывал на подкаталог www/ или использовать директиву Alias.');

    $all_base = $check_call && $check_ob && $check_session && $check_preg && $version && $check_hash && $free>5;
    if (!$all_base) {
      $this->allow_next = false;
      echo tag('p','Некоторые из базовых требований к системе не выполнены!',-1);
    }
    $all_access = $check_etc && $check_logs && $check_tmp && $free>1;
    if (!$all_access) {
      $this->allow_next = false;
      echo tag('p','b','Некорректно выставлены права к файлам или слишком мало места на диске!',-1);
    }
    $all_db = $check_mysql || $check_mysqli;
    if (!$all_db) {
      $this->allow_next = false;
      echo tag('p','Не найдено ни одно из поддерживаемых расширений для СУБД!',-1);
    }

    $all_options = $check_gzip && $check_gd && $check_mail && $check_mb;
    $update=false;
    if ($this->allow_next) {
      if (!$all_options && !$version2) {
        echo tag('div',tag('b','Установка возможна, но некоторые возмжности Intellect Board будут недоступны.'),2);
      }
      else echo tag('div','Все необходимые условия для установки выполнены!',1);
      echo '<h4>Выберите желаемое действие:</h4>';
      $prev_version=file_exists(BASEDIR.'etc/ib_config.php');
      if ($prev_version) {
        @include(BASEDIR.'etc/ib_config.php');
        if (defined('DB_structure_version') && DB_structure_version<IntB_db_version) $update=true;
      }
      echo '<div><label><input type="radio" name="mode" value="1" '.(!$prev_version ? 'checked="checked"' : '').' required="required" /> Провести начальную настройку Intellect Board с нуля</label></div>';
      if ($update) echo '<div><label><input type="radio" name="mode" value="3" '.($update ? 'checked="checked"' : '').' required="required" /> Обновить предыдущую версию Intellect Board</label></div>';
      if ($prev_version) echo '<div><label><input type="radio" name="mode" value="2" '.($prev_version && !$update ? 'checked="checked"' : '').' required="required" /> Изменить настройки уже установленной версии Intellect Board</label></div>';      
    }
    else echo tag('div','Продолжение установки невозможно. Исправьте указанные выше проблемы и попробуйте еще раз!',-1);
  }

  function action_step2() {
    if ($this->mode==2) {
      $this->goto_step(3);
    }
    if ($this->mode==3) {
      $this->goto_step(4);
    }    
    if ($this->mode==1) {
      echo tag('h2','Лицензионное соглашение');
      $license = @file_get_contents(BASEDIR.'doc/license.txt');
      if (!$license) {
        echo tag('div','Не найден текст лицензионного соглашения! Установка невомзожна',-1);
        $this->allow_next=false;
      }
      else {
        echo '<textarea readonly="readonly" rows="25" cols="60" style="width: 98%">'.htmlspecialchars($license,NULL,'utf-8').'</textarea>';
        echo '<br /><label><input type="checkbox" name="accept" value="1" required="required">Я принимаю условния данного Лицензионного Согласения.</label>';
      }
    }
  }

  function action_step3() {
    if ($this->mode==1 && empty($_REQUEST['accept'])) {
      echo tag('div','Без принятия лицензионного соглашения установка невозможна!',-1);
      $this->allow_next = false;
    }
    else {
      if ($this->mode==1) {
        $db_host = ini_get('mysql.default_host') ?: 'localhost';
        $db_port = ini_get('mysql.default_port');
        $db_username = ini_get('mysql.default_user');
        $db_password = ini_get('mysql.default_password');
        $db_name = '';
        $db_socket = ini_get('mysql.default_socket');
        $db_charset = true;
        $db_persist = false;
        $db_prefix = 'intb_';
        $db_schema = 'public';
      }
      elseif ($this->mode==2) {
        $result = @include(BASEDIR.'etc/ib_config.php');
        echo tag('div','Подключение файла конфигурации etc/ib_config.php: '.cond($result,'Ok','Файл содержит ошибки'));
        $db_host = defined('DB_host') ? DB_host : '';
        $db_port = defined('DB_port') ? DB_port : '';
        $db_username = defined('DB_username') ? DB_username : '';
        $db_name = defined('DB_name') ? DB_name : '';
        $db_password = defined('DB_password') ? DB_password : '';
        $db_socket = defined('DB_socket') ? DB_socket : '';
        $db_charset = defined('DB_charset') ? DB_charset : '1';
        $db_persist = defined('DB_persist') ? DB_persist : '';
        $db_prefix = defined('DB_prefix') ? DB_prefix : 'ib_';
        $db_schema = defined('DB_prefix') ? DB_prefix : 'public';
      }
      echo tag('h2','Параметры подключения к базе данных');
      $disabled = explode(',',ini_get('disable_functions'));
      $disabled_classes = explode(',',ini_get('disable_classes'));
      $check_mysqli = function_exists('mysqli_connect') && !in_array('mysqli_connect', $disabled);
      $check_mysql = function_exists('mysql_connect') && !in_array('mysql_connect', $disabled);
      $check_postgres = function_exists('pg_connect') && !in_array('pg_connect', $disabled);
      $check_sqlite = class_exists('SQLite3') && !in_array('SQLite3', $disabled_classes);
      echo tag('h4','Выберите СУБД и расшриние PHP для работы с ней:');
      if ($check_mysqli) echo '<div><label><input type="radio" name="params[DB_driver]" value="mysqli" checked="checked" onclick="choose_db();"/>MySQL с подключением через расширение mysqli (рекомендуется)</label></div>';
      if ($check_mysql) echo '<div><label><input type="radio" name="params[DB_driver]" value="mysql5"  onclick="choose_db();"/>MySQL 5.x с подключением через расширение mysql (устарело, не рекомендуется)</label></div>';
      if ($check_postgres) echo '<div><label><input type="radio" name="params[DB_driver]" value="postgres"  onclick="choose_db();"/>PostgreSQL</label></div>';
      if ($check_sqlite) echo '<div><label><input type="radio" name="params[DB_driver]" value="sqlite"  onclick="choose_db();"/>SQLite3</label></div>';
      echo tag('h4','Укажите настройки для подключения к базе:');
      echo '<div id="connect_parameters1">';
      echo '<div><label><span class="fd">Хост и порт:</span> <input type="text" name="params[DB_host]" required="required" id="DB_host" value="'.htmlspecialchars($db_host).'"></label>:<input type="text" size="5" name="params[DB_port]" value="'.$db_port.'""> (оставьте пустым, чтобы использовать порт по умолчанию)</div>';
      echo '</div>';
      echo '<div><label><span class="fd">Название базы данных:</span>  <input type="text" name="params[DB_name]" required="required" value="' . htmlspecialchars($db_name) . '"></label></div>';
      if ($check_sqlite) echo '<div id="connect_sqlite_warning" style="display: none">База данных будет размещена в файле db/&lt;имя_базы&gt;.db. Убедитесь, что он недоступен для скачивания по HTTP!</div>';
      echo '<div id="connect_parameters2">';
      echo '<div><label><span class="fd">Имя пользователя:</span>  <input type="text" name="params[DB_username]" required="required" id="DB_username" value="'.htmlspecialchars($db_username).'"></label></div>';
      echo '<div><label><span class="fd">Пароль:</span>  <input type="password" name="params[DB_password]" required="required" id="DB_password" value="'.htmlspecialchars($db_password).'"></label></div>';
      echo '<div><label><span class="fd">Подключаться через Unix-сокет:</span>  <input type="text" name="params[DB_socket]" value="'.htmlspecialchars($db_socket).'"></label> (по возможности используйте именно socket-соединения)</div>';
      echo '</div>';
      echo '<div><label><span class="fd">Префикс таблиц:</span>  <input type="text" name="params[DB_prefix]" size="3" required="required" pattern="[a-zA-Z_]\w*" value="'.htmlspecialchars($db_prefix).'"></label></div>';
      echo '<div id="connect_parameters3">';
      if ($check_postgres) {
        echo '<div id="connect_schema"><label><span class="fd">Схема:</span>  <input type="text" name="params[DB_schema]" pattern="[a-zA-Z_]\w*" value="'.htmlspecialchars($db_schema).'"></label></div>';
      }
      echo '<div><label><input type="checkbox" name="params[DB_persist]" '.($db_persist ? 'checked="checked"' : '').' value="1">Использовать постоянные соединения (если не уверены, зачем это нужно, не включайте эту опцию)</label></div>';
      echo '<div><label><input type="checkbox" name="params[DB_charset]" '.($db_charset ? 'checked="checked"' : '').' value="1">Принудительно выставлять кодировку UTF-8 (снимите флажок, если utf-8 выставлена на сервере как кодировка по умолчанию, это даст небольшой прирост производительности)</label></div>';
      echo '</div>';
      echo '<div id="user_creation">';
      echo tag('h4','Создание пользователя и пароля');
      echo tag('div','Если у вас есть права администратора сервера MySQL или Postgres, можно создать указанную выше базу и пользователя прямо в процессе установки.');
      echo '<div><label><input type="checkbox" name="create_user" id="create_user" value="1">Создать пользователя и базу</label></div>';
      echo '<div><label><span class="fd">Имя администратора:</span>  <input type="text" name="DB_root" value="root" onchange="document.getElementById(\'create_user\').checked=true"></label></div>';
      echo '<div><label><span class="fd">Пароль:</span>  <input type="password" name="DB_root_password" value=""  onchange="document.getElementById(\'create_user\').checked=true"> (пароль администратора в настройках не сохраняется)</label></div>';
      echo '</div>';

      echo <<<ESCRIPT
      <script>
      function choose_db() {
        var db_selected = document.getElementById('mainform').elements['params[DB_driver]'].value;
        if (db_selected=='mysqli' || db_selected=='mysql5') {
          document.getElementById('connect_parameters1').style.display="block"; 
          document.getElementById('connect_parameters2').style.display="block";
          document.getElementById('connect_parameters3').style.display="block";
          document.getElementById('user_creation').style.display="block";
          document.getElementById('connect_schema').style.display="none";
          document.getElementById('connect_sqlite_warning').style.display="none";        
          document.getElementById('DB_username').required=true;
          document.getElementById('DB_password').required=true;
          document.getElementById('DB_host').required=true;        
          return false;
        }
        if (db_selected=='postgres') {
          document.getElementById('connect_parameters1').style.display="block"; 
          document.getElementById('connect_parameters2').style.display="block";
          document.getElementById('connect_parameters3').style.display="block";
          document.getElementById('user_creation').style.display="block";
          document.getElementById('connect_schema').style.display="block";
          document.getElementById('connect_sqlite_warning').style.display="none";        
          document.getElementById('DB_username').required=true;
          document.getElementById('DB_password').required=true;
          document.getElementById('DB_host').required=true;
          return false;
        }
        if (db_selected=='sqlite') {
          document.getElementById('connect_parameters1').style.display="none"; 
          document.getElementById('connect_parameters2').style.display="none";
          document.getElementById('connect_parameters3').style.display="none";
          document.getElementById('user_creation').style.display="none";
          document.getElementById('connect_schema').style.display="none";
          document.getElementById('connect_sqlite_warning').style.display="block";
          document.getElementById('DB_username').required=false;
          document.getElementById('DB_password').required=false;
          document.getElementById('DB_host').required=false;
          return false;
        }
      }
      choose_db();
      </script>
ESCRIPT;
      if ($this->mode==1) echo tag('div','Внимание! Если в базе данных присутствуют таблицы, имена которых совпадают с именами таблиц Intellect Board, они будут уничтожены (а в случае SQLite будет перезаписана вся база целиком)! Будьте внимательны!',2);
    }
  }

  function action_step4() {
    echo tag('h2','Проверка подключения к базе данных');
    if ($this->mode!=3 && !preg_match('|^[a-zA-z_]\w*$|',$_POST['params']['DB_prefix'])) {
      echo tag('div','Некорректный префикс таблиц! В нем допустимы только латинские буквы, прочерк и цифры',-1);
      $this->allow_next=false;
    }

    if ($this->mode!=3) echo tag('div','Прорверка префикса таблиц: '.tag('span','Ok',1));

    if ($this->mode>1) @include(BASEDIR.'etc/ib_config.php'); // если у нас режим настройки или обновления, используем рабочий файл конфигурации
    elseif ($this->mode==1) @include(BASEDIR.'etc/ib_config.def'); // иначе — файл с настройками по умолчанию

    if ($this->mode!=3) {
    $params=$_POST['params'];         
    if ($this->mode==1) $params['DB_structure_version']=IntB_db_version; // если мы делаем новую установку, то берем номер версии базы из инсталлятора
    }
    else {
      @include(BASEDIR.'etc/ib_config.php');
      $params = array('DB_host'=>DB_host,'DB_name'=>DB_name,'DB_username'=>DB_username,'DB_password'=>DB_password);
      if (defined('DB_socket')) $params['DB_socket']=DB_socket;
      if (defined('DB_persist')) $params['DB_persist']=DB_persist;
      if (defined('DB_charset')) $params['DB_charset']=DB_charset;
      if (defined('DB_port')) $params['DB_port']=DB_port;
      if (defined('DB_schema')) $params['DB_schema']=DB_schema;
      $params['DB_prefix']=DB_prefix; // нужно для пересохранения параметров
      $params['DB_driver']=DB_driver; // нужно для пересохранения параметров
      $params['DB_structure_version']=defined('DB_structure_version') ? DB_structure_version : 100; // иначе — берем старый номер версии из файла настроек — он нам потребуется для того, чтобы понять, какие файлы обновлений применить. Из-за бага в ранних версиях не сохранялась версия структуры, в этом случае применяем все обновления
    }
    $tmp_params = $params;
    if (!empty($_POST['create_user'])) {
      $tmp_params['DB_username']=$_POST['DB_root'];
      $tmp_params['DB_password']=$_POST['DB_root_password'];
      unset($tmp_params['DB_name']);
    }
    if ($this->mode==1 && $params['DB_driver']==='sqlite') {
      echo tag('div','Проверяем, что каталог db доступен для записи: '.cond(is_writable(BASEDIR.'/db/'),'Ok','Не доступен!'));
      if (file_exists(BASEDIR.'/db/'.$params['DB_name'].'.db')) echo tag('div','Удаляем старую базу: '.cond(unlink(BASEDIR.'/db/'.$params['DB_name'].'.db'),'Ok','Невозможно удалить файл!'));
    }

    $this->init_db($tmp_params);

    $version = $this->db->select_str('SELECT VERSION()');
    echo tag('div','Подключение к базе данных произведено, версия СУБД: '.tag('span',$version,1));
    
     $updated = false;
     if ($this->mode==1) {
       // далее идет СУБД-специфичный код
       if (!empty($_POST['create_user'])) {
          if ($params['DB_driver']==='mysql5' || $params['DB_driver']==='mysqli') {
            $sql = 'CREATE DATABASE IF NOT EXISTS '.$params['DB_name'];
            $result=$this->db->query($sql);
            echo tag('div','Создание базы данных: '.cond($result,'Ok',$this->db->error_str()));
            $sql = 'USE '.$params['DB_name'];
            $result=$this->db->query($sql);
            echo tag('div','Подключение к созданной базе данных: '.cond($result,'Ok',$this->db->error_str()));
            $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE, LOCK TABLES, ALTER ON '.addslashes($params['DB_name']).'.* '.
            'TO "'.$params['DB_username'].'"@';
            if ($params['DB_host']=='localhost' || $params['DB_host']=='127.0.0.1') $sql.='"'.$params['DB_host'].'"';
            else $sql.='"%"';
            $sql.=' IDENTIFIED BY \''.addslashes($params['DB_password']).'\'';
            $result=$this->db->query($sql);
            echo tag('div','Создание пользователя: '.cond($result,'Ok',$this->db->error_str()));
          }
          if ($params['DB_driver']==='postgres') {
            $sql = 'CREATE DATABASE '.$params['DB_name'].' LOCALE \'ru_RU.UTF-8\' TEMPLATE template0;';
            $result=$this->db->query($sql);
            echo tag('div','Создание базы данных: '.cond($result,'Ok',$this->db->error_str()));

            $this->db->close();
            $tmp_params['DB_name']=$params['DB_name'];
            $this->init_db($tmp_params);

            $sql = 'CREATE USER '.$params['DB_username'].' PASSWORD \''.addslashes($params['DB_password']).'\'';
            $result=$this->db->query($sql);
            echo tag('div','Создание пользователя: '.cond($result,'Ok',$this->db->error_str()));

            $sql = 'CREATE SCHEMA '.addslashes($params['DB_schema']).' AUTHORIZATION "'.addslashes($params['DB_username']).'"';
            $result=$this->db->query($sql);
            echo tag('div','Создание схемы: '.cond($result,'Ok',$this->db->error_str()));

            $sql = 'GRANT CONNECT ON DATABASE "'.addslashes($params['DB_name']).'" '.
            'TO "'.$params['DB_username'].'"';
            $result=$this->db->query($sql);

            if ($result) {
              $sql = 'ALTER DEFAULT PRIVILEGES IN SCHEMA "'.addslashes($params['DB_schema']).'" GRANT SELECT, INSERT, UPDATE, DELETE, TRUNCATE ON TABLES '.
              'TO "'.$params['DB_username'].'"';
              $result=$this->db->query($sql);
            }

            if ($result) {
              $sql = 'ALTER DEFAULT PRIVILEGES IN SCHEMA "'.addslashes($params['DB_schema']).'" GRANT USAGE, SELECT ON SEQUENCES TO "'.$params['DB_username'].'"';
              $result=$this->db->query($sql);
            }
            echo tag('div','Установка прав пользователя: '.cond($result,'Ok',$this->db->error_str()));
        }
       }

       echo tag('div','Начинаем импорт базы данных...');
       list($ok,$errors)=$this->load_dump(BASEDIR.'db/sql/'.$params['DB_driver'].'_new.sql',$params['DB_prefix'],$params['DB_driver'],$params['DB_schema']);
       if ($ok==0) {
         echo tag('div','Импорт базы не произведен!',2);
         $this->allow_next=false;
         return;
       }
       echo tag('div','Импорт завершен! Операций выполнено: '.$ok.', ошибок: '.$errors.'.',$errors==0 ? 1 : 2);
       $params['DB_structure_version']=IntB_db_version; // сохраняем номер текущей версии БД для последующих обновлений

       if (!file_exists(BASEDIR.'www/.htaccess')) {
         $buffer = file_get_contents(BASEDIR.'www/htaccess.def');
         $buffer = str_replace('### RewriteBase /','RewriteBase '.$this->sitepath,$buffer);
         $result=file_put_contents(BASEDIR.'www/.htaccess',$buffer);
         echo tag('div','Копирование файла перенаправления запросов www/htaccess.def в www/.htaccess: '.cond($result,'Ok','Ошибка!'));
       }

       // потеряло актуальность после версии 3.05
       /*if (!file_exists(BASEDIR.'etc/htaccess.txt')) {
         $buffer = file_get_contents(BASEDIR.'etc/htaccess.def');
         $buffer = str_replace('### RewriteBase /','RewriteBase '.$this->sitepath,$buffer);
         file_put_contents(BASEDIR.'etc/htaccess.txt',$buffer);
         echo tag('div','Копирование шаблона файла перенарпавления запросов etc/htaccess.def в etc/htaccess.txt: '.cond($result,'Ok','Ошибка!'));
       }
       else echo tag('div','Файл etc/htaccess.txt уже существует, оставляем без изменений.');*/

       if (!file_exists(BASEDIR.'etc/routes.txt')) {
         $buffer = file_get_contents(BASEDIR.'etc/routes.def');
         $result = file_put_contents(BASEDIR.'etc/routes.txt',$buffer);
         echo tag('div','Копирование файла перенаправления запросов etc/routes.def в etc/routes.txt: '.cond($result,'Ok','Ошибка!'));
       }       
       else echo tag('div','Файл etc/routes.txt уже существует, оставляем без изменений.');
       if (!file_exists(BASEDIR.'etc/routes.cfg')) {
         $buffer = file_get_contents(BASEDIR.'etc/routes.def');
         $buffer = str_replace('<<<index_route>>>', '^$ mainpage.php',$buffer); // прописываем правило для главной по умолчанию
         $buffer.= '

^about/((\w+)\.htm)?$ statpage.php?f=1&a=$2
^moderate/about/edit_foreword.htm$ statpage.php?f=1&a=edit';
         $result=file_put_contents(BASEDIR.'etc/routes.cfg',$buffer);
         echo tag('div','Копирование файла перенаправления запросов etc/routes.def в etc/routes.cfg: '.cond($result,'Ok','Ошибка!'));
       }
       else echo tag('div','Файл etc/routes.cfg уже существует, оставляем без изменений.'); 

       if (!file_exists(BASEDIR.'www/robots.txt')) {
         $result=copy(BASEDIR.'www/robots.def',BASEDIR.'www/robots.txt');
         echo tag('div','Копирование файла www/robots.def в www/robots.txt: '.cond($result,'Ok','Ошибка!'));
       }
       else echo tag('div','Файл www/robots.txt уже существует, оставляем без изменений.');
     }
     elseif ($this->mode==2) {
       @include(BASEDIR.'etc/ib_config.def');
       if (!empty($_POST['create_user'])) {
         $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON '.addslashes($params['DB_name']).'.* '.
         'TO "'.$params['DB_username'].'"@';
         if ($params['DB_host']=='localhost' || $params['DB_host']=='127.0.0.1') $sql.='"'.$params['DB_host'].'"';
         else $sql.='"%"';
         $sql.=' IDENTIFIED BY \''.addslashes($params['DB_password']).'\'';
         $result=$this->db->query($sql);
         echo tag('div','Создание пользователя: '.cond($result,'Ok',$this->db->error_str()));
       }
     }
     elseif ($this->mode==3) {
       if (!file_exists(BASEDIR.'etc/routes.txt')) {
         $buffer = file_get_contents(BASEDIR.'etc/routes.def');
         file_put_contents(BASEDIR.'etc/routes.txt',$buffer);
         echo tag('div','Копирование файла перенаправления запросов etc/routes.def в etc/.routes.txt: '.cond($result,'Ok','Ошибка!'));
       }
       else echo tag('div','Файл etc/routes.txt уже существует, оставляем без изменений.');      
       
       if (empty($params['DB_structure_version'])) $params['DB_structure_version']=100;
       $version = $params['DB_structure_version']+1;
       if ($params['DB_structure_version']==IntB_db_version) {
         echo tag('div','Обновлений структуры базы данных не требуется.',1);
         return true;
       }
       echo tag('div','Обновление структуры БД с версии '.$params['DB_structure_version'].' до '.IntB_db_version.'.');
       $this->skip_errors=true; // чтобы скрипт не завершался в случае ошибок обновления базы      
       // далее идет СУБД-специфичный код для отключения фатальности ошибок при обновлени структуры базы
       if (DB_driver=='mysqli') mysqli_report(MYSQLI_REPORT_ERROR);
       $updated=true;       
       while ($version<IntB_db_version+1) {
         $filename=BASEDIR.'db/sql/update/'.$params['DB_driver'].'_'.$version.'.sql';
         if (file_exists($filename)) {
           list($ok,$errors)=$this->load_dump($filename,DB_prefix,DB_driver,DB_schema);
           echo tag('div','Обновление структуры до версии '.$version.' проведено! Операций выполнено: '.$ok.', ошибок: '.$errors.'.',$errors==0 ? 1 : 2);
           $params['DB_structure_version']=$version;
         }
         else {
           echo tag('div','Нет файла для обновления до версии '.$version.', пропускаем!',2);
           $updated=false;
         }
         $version++;
       }
     }
     if ($updated) $params['DB_structure_version']=$version-1; // вычитаем единицу, так как нужно сохранить номер той версии, до которой обновились, а не следующей
     else {      
      $params['DB_structure_version']=IntB_db_version;
     }

     $config = $this->build_data();
     $result=$this->save_config($config, $params);
     if ($result) {
       echo tag('div','Файл конфигурации сохранен!',1);
     }
     else {
       echo tag('div','Ошибка при сохранении файла конфигурации etc/ib_config.php!',-1);
       $this->allow_next=false;
     }
  }

  function action_step5() {
    if ($this->mode==3) {
      $this->goto_step(7);
    }
    echo tag('h2','Данные администратора форума');
    echo '<p>Логин не может содержать символы ";\', и совпадать с именами специальных пользователей: Guest, System, New User</p>';
    echo '<div><label><span class="fd">Логин:</span><input type="text" name="user[login]" required="required" /></label></div>';
    echo '<div><label><span class="fd">Пароль:</span><input type="password" name="user[password]" required="required" min="6" /></label></div>';
    echo '<div><label><span class="fd">Потдверждение пароля:</span><input type="password" name="password_confirm" required="required" min="6" /></label></div>';
    echo '<div><label><span class="fd">Email:</span><input type="email" name="user[email]" required="required" /></label></div>';
  }

  function action_step6() {
    echo tag('h2','Регистрация администратора форума');
    @include(BASEDIR.'etc/ib_config.php');
    $params = array('DB_host'=>DB_host,'DB_name'=>DB_name,'DB_username'=>DB_username,'DB_password'=>DB_password);
    if (defined('DB_socket')) $params['DB_socket']=DB_socket;
    if (defined('DB_persist')) $params['DB_persist']=DB_persist;
    if (defined('DB_charset')) $params['DB_charset']=DB_charset;
    if (defined('DB_port')) $params['DB_port']=DB_port;
    if (defined('DB_schema')) $params['DB_schema']=DB_schema;
    $params['DB_prefix']=DB_prefix; // нужно для пересохранения параметров
    $params['DB_driver']=DB_driver; // нужно для пересохранения параметров
    $params['DB_structure_version']=DB_structure_version; // нужно для пересохранения параметров
    $this->init_db($params);

   if ($this->mode==1) {
    $userdata = $_POST['user'];
    if (in_array($userdata['login'],array('Guest','System','New User')) || preg_match('|[,;\'"]|', $userdata['login'])) {
      echo tag('div','Недопустимый логин!',-1);
      $this->allow_next=false;
      return;
    }
    $userdata['display_name']=$userdata['login'];
    $userdata['pass_crypt']=5;
    $userdata['rnd']=mt_rand(0,0x7FFFFFFF);
    $userdata['password']=hash('sha256',$userdata['password'].$userdata['rnd']);
    $userdata['canonical']=$this->canonize_name($userdata['display_name']);
    $userdata['location']='';
    $userdata['gender']='U';
    $userdata['signature']='';
    $userdata['status']='0';
    $userdata['title']='';
    $this->db->insert(DB_prefix.'user',$userdata);
    $uid = $this->db->insert_id();
    $userdata['id']=$uid;
    echo tag('div','Регистрация пользователя '.$userdata['login'].': '.cond($uid>0,'Ok','Ошибка: '.$this->db->error_str()));
    if (!$uid) { $this->allow_next=false; return; }

    $sql = 'SELECT * FROM '.DB_prefix.'user_ext WHERE id=3';
    $extdata = $this->db->select_row($sql);
    $extdata['id']=$uid;
    $extdata['group_id']=1024;
    $extdata['reg_date']=time();
    $result1=$this->db->insert(DB_prefix.'user_ext',$extdata);

    $sql = 'SELECT * FROM '.DB_prefix.'user_settings WHERE id=3';
    $sdata = $this->db->select_row($sql);
    $sdata['id']=$uid;
    $result2=$this->db->insert(DB_prefix.'user_settings',$sdata);

    $sql = 'INSERT INTO '.DB_prefix.'mark_all (uid,fid,mark_time) VALUES ('.intval($uid).',0,'.time().')';
    $result3=$this->db->query($sql);

    echo tag('div','Установка начальных настроек пользователя: '.cond($result1 && $result2 && $result3,'Ok','Ошибка: '.$this->db->error_str()));

    $settings = $this->build_data();
    $settings['CONFIG_email']=$userdata['email'];
    $settings['CONFIG_email_from']=$userdata['email'];
    $settings['CONFIG_email_return']=$userdata['email'];
    $settings['CONFIG_site_secret']=substr(hash('sha256',mt_rand(1,mt_getrandmax())),0,16);
    $this->save_config($settings, $params);
    echo tag('div','Сохранение начальных настроек форума: Ok');
   }
   elseif ($this->mode==2) {
     $sql = 'SELECT * FROM '.DB_prefix.'user WHERE login="'.$this->db->slashes($_POST['user']['login']).'"';
     $userdata = $this->db->select_row($sql);
     if (!$userdata) {
       echo tag('div','Пользователь с таким логином не найден!',-1);
       $this->allow_next=false;
       return;
     }
     $new_rnd=mt_rand(0,0xFFFFFFFF);
     $new_pass=hash('sha256',$_POST['user']['password'].$new_rnd);    
     $sql = 'UPDATE '.DB_prefix.'user SET password="'.$this->db->slashes($new_pass).'", rnd='.intval($new_rnd).', '.
     'pass_crypt="5", email="'.$this->db->slashes($_POST['user']['email']).'" WHERE id='.intval($userdata['id']);
     $result1=$this->db->query($sql);
     echo tag('div','Изменение пароля и EMail: '.cond($result1,'Ok','Ошибка: '.$this->db->error_str()));

     $sql = 'UPDATE '.DB_prefix.'user_ext SET group_id=1024 WHERE id='.intval($userdata['id']);
     $result2=$this->db->query($sql);
     echo tag('div','Восстановление прав администратора: '.cond($result2,'Ok','Ошибка: '.$this->db->error_str()));
   }
   $period = time() + 15 * 60; // изначально устанавливаем долгий ключ авторизации всего на 15 минут
   $key = $this->gen_long_key($userdata);

   $sitepath = $this->sitepath;
   setcookie(CONFIG_session,'',1,$sitepath, false, isset($_SERVER['HTTPS']),true); // сбрасываем сессионную cookie, чтобы не было конфликтов
   setcookie(CONFIG_session.'_long', $key, $period, $sitepath, false, isset($_SERVER['HTTPS']),true);
   $admin_key = $this->gen_admin_cookie($userdata);
   setcookie(CONFIG_session.'_a',$admin_key,false,$sitepath.'admin/',false,isset($_SERVER['HTTPS']),true);

   echo tag('div','Настройка форума завершена! Теперь вы можете перейти в Центр Администрирования форума!',1);
  }

  function action_step7() {
    ob_clean();
    header('HTTP/1.0 302 Moved temporary');
    header('Location: '.$this->http($this->sitepath.'admin/settings/view.htm'));
    $this->shutdown();
    exit();
  }

  /** Генерация ключа для долгосрочной идентификации * */
  function gen_long_key($userdata, $session_name=false) {
    if (!$session_name)  $session_name = CONFIG_session;
    // TODO: возможно, доделать добавку очищенного User Agent
    return $userdata['id'].'-'.md5($userdata['id'].$userdata['password'].$userdata['rnd'].$userdata['pass_crypt'].$session_name);
  }

  function gen_admin_cookie($udata) {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $agent = preg_replace('|\d+|', '', $agent);
    return md5($udata['id'].$udata['password'].$udata['rnd'].$agent.$_SERVER['REMOTE_ADDR']);
  }

  /** Приведение имени пользователя к "каноническому" виду (замена похожих по начертанию букв и цифры) в целях недопущения регистрации пользователей с похожими именами **/
  function canonize_name($name) {
    $name = str_replace(' ','',$name);
    $name = str_replace(array('Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э','Я','Ч','С','М','И','Т','Ь','Б','Ю'),
        array('й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в','а','п','р','о','л','д','ж','э','я','ч','с','м','и','т','ь','б','ю'),$name);
    $name = str_replace(array('0','1','6','а','в','е','з','и','к','о','р','с','у','х','ь','i','н','п','м','т'),
        array('o','l','b','a','b','e','3','u','k','o','p','c','y','x','b','l','h','n','m','t'),$name);
    $name = str_replace("ю","lo",$name);
    $name = str_replace("ы","bl",$name);
    return $name;
  }

  function build_data() {
    $data = array();
    $const = get_defined_constants(true);
    foreach ($const['user'] as $item=>$oldvalue) {
      if (strpos($item,'CONFIG_')===0) $data[$item]=$oldvalue;
    }
    return $data;
  }

  function save_config($data,$params) {
    $buffer="<?php\n";
    foreach ($params as $item=>$value) {
      $buffer.="define('".addslashes($item)."','".addslashes($value)."');\n";
    }
    if (!empty($data)) foreach ($data as $item=>$value) {
      $buffer.="define('".addslashes($item)."','".addslashes($value)."');\n";
    }
    $buffer.="define('INTB_LAST_CONFIG_TIME',".time().");\n";
    return file_put_contents(BASEDIR.'etc/ib_config.php', $buffer);
  }

  function load_dump($filename,$prefix,$mode,$schema) {
    $buffer = file_get_contents($filename);
    $buffer = str_replace("\r", "\n", $buffer);
    $sqls = explode(";\n",$buffer);
    $ok=0; $errors=0;
    for ($i=0, $count=count($sqls);$i<$count;$i++) {
      if (trim($sqls[$i])) {
        if ($mode==='mysqli' || $mode==='mysql5') $sqls[$i]=preg_replace('|`ib_(\w+)`|',$prefix.'$1',$sqls[$i]);
        elseif ($mode==='postgres') $sqls[$i]=preg_replace('|(\W)ib_current.ib_(\w+)|','$1'.$schema.'.'.$prefix.'$2',$sqls[$i]);
        elseif ($mode==='sqlite') $sqls[$i]=preg_replace('|`ib_(\w+)`|',$prefix.'$1',preg_replace('|"ib_(\w+)"|',$prefix.'$1',$sqls[$i]));
        if ($mode==='postgres') $sqls[$i]=str_replace('CREATE OR REPLACE FUNCTION ib_current.instr ',"CREATE OR REPLACE FUNCTION $schema.instr ",$sqls[$i]);
        $result=$this->db->query($sqls[$i],false);
        if ($result) $ok++;
        else {
          $errors++;
          echo tag('li',$sqls[$i].'<br />Ошибка: '.$this->db->error_str());
        }
      }
    }
    return array($ok,$errors);
  }

  function goto_step($step) {
    ob_clean();
    header('HTTP/1.0 302 Moved temporary');
    header('Location: '.$this->http($_SERVER['PHP_SELF'].'?step='.$step.'&mode='.$this->mode));
    exit();
  }

  function http($path) {
    $result = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
    $result.= $_SERVER['HTTP_HOST'].$path;
    return $result;
  }


  function shutdown() {
    if (is_object($this->db)) $this->db->close();
  }

  function error_handler($errno, $errstr, $errfile, $errline) {
    if ($errno==E_ERROR || $errno==E_USER_ERROR) {
      echo 'Произошла ошибка в строке '.$errline.': '.$errstr;
      if (!$this->skip_errors) {
        $this->shutdown();      
        exit();
      }
    }
  }
}

/** Глобальная функция для вывода HTML-тегов **/
function tag($tagname,$text,$class=0) {
  static $prevtag;
  $buffer='';
  if ($tagname=='li' && $prevtag!='li') $buffer.='<ul>';
  elseif ($tagname!='li' && $tagname!='span' && $prevtag=='li') $buffer.='</ul>';
  $buffer .= "<$tagname";
  if ($class==-1) $buffer.=" class='msg_error'";
  elseif ($class==1) $buffer.=" class='msg_ok'";
  elseif ($class==2) $buffer.=" class='msg_warn'";
  $buffer.='>'.$text.'</'.$tagname.'>';
  if ($tagname!='span') $prevtag = $tagname;
  return $buffer;
}

/** Вывод span по условию: если оно истинно, выводим $text1 с классом ok, если нет $text2 с классом error **/
function cond($exp,$text1,$text2) {
  if ($exp) $result=tag('span',$text1,1);
  else $result=tag('span',$text2,-1);
  return $result;
}

function _dbg() {}

$app = new Application();
$app->main();
