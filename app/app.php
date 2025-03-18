<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.05
 *  @copyright 2007, 2010, 2012-2024 4X_Pro, INTBPRO.RU
 *  @url https://intbpro.ru
 *  Основной модуль сайтового движка Intellect Board 3 Pro
 *  ================================ */
define('AUTH_SYSTEM_USERS', 3);
define('INTB_VERSION', '3.05');

class Application {

  public $lastmod = INTB_LAST_CONFIG_TIME;/** время последней модификации страницы, которое будет отдано в заголовке Last-Modified * */

  /** Объект для взаимодействия с базой данных
   * @var Database $db  * */
  public $db;

  /** хеш с данными пользователя * */
  private $userdata;

  /** путь к корню движка * */
  protected $sitepath;

  /** сообщения для отправки Email * */
  private $mail = array();

  /** Время начала выполнения скрипта * */
  private $start_time = 0;

  /** Время, переданное в заголовке If-Modified-Since, используется для проверки наличия изменений в выводимой странице по сравнению с прошлым обращением * */
  public $if_modified_time = 0;

  /** Запрошенное пользователем действие * */
  public $action;

  /** Стиль, используемый для отображения сайта, по умолчанию используется стиль def. * */
  public $template = 'def';

  /** Заголовок страницы * */
  public $title = '';

  /** Идентификатор поискового бота, если пользователь опознан как бот * */
  public $bot_id = 0;

  /** В этой переменной будут храниться экземпляры классов-библиотек * */
  private $libs = array();

  /** Имя библиотеки с помощью которой будет генерироваться выводимый код * */
  public $template_lib = 'twig';

  /** Объект для хранения всех данных, предназначенных для вывода * */
  public $out;

  /** Здесь хранится время начала выполнения запроса. Рекомендуется брать время отсюда, чтобы избежать достаточно долгих вызвов функции time или рассинхронизации * */
  public $time;

  /** Объект для работы с серверным кешем * */
  public $server_cache = false;

  /** Данные для вывода мета-тегов */
  public $meta = array();

  /** Главная функция, из нее вызывается все остальное
   * Действия:
   * Вызов процедуры инициализации
   * Подключение модуля, который обрабатывает объекты запрошенного типа
   * Вызов процедуры, выполняющей запрошенное действие
   * Выполнение subactions -- всопмогательных действий (показа главного меню, указателя текущего местоположения, числа непрочитанных сообщений в ЛС и т.п.)
   * Проверка, были ли изменения по сравнению с предыдущим запросом (при наличии соответствующих заголовков)
   * Вызов процедуры вывода данных
   * */
  function main() {
    try {
      $this->init();

      $this->checkpoint('Конец инициализации');

      if ($this->get_request_type() === 0)
        $this->fix_online(); // присутствие пользователя нужно фиксировать до выполнения действия
      $this->checkpoint('Фиксация действия пользователя выполнена');

      /*if ($this->is_guest() && !$this->get_opt('nocache') && !$this->check_modified()) { // проверка. были ли изменения с момента последнго обращения к странице, если нет, выдаем 304 и завершаем работу сразу
        $this->output_304();
      }*/

      if ($this->get_request_type() <= 1) { // выводим информацию о настройках лимитов на загрузку файлов, если запрос обычный или AJAXовый
        $this->out->upload_max_filesize = $this->return_bytes(ini_get('upload_max_filesize')) ?: 2 * 1024 * 1024;
        $this->out->post_max_size = $this->return_bytes(ini_get('post_max_size')) ?: 2 * 1024 * 1024;
        $this->out->max_file_uploads = ini_get('max_file_uploads');
      }
      $template = $this->process();

      $this->set_lastmod(); // выставляем время последней модификации
      $this->checkpoint('Основное действие выполнено');
      $rqt = $this->get_request_type(); // получаем тип запроса (обычный, AJAX или RSS)
      if ($rqt !== 1) { // если выводим страницу не AJAXом, а в обычном режиме, формируем вспомогательные элементы
        $this->out->intb->title = $this->set_title(); // формируем заголовок страницы для тега TITLE
        if ($this->get_opt('oauth_server_enable')) $this->link($this->http($this->url('.oauth/authorization_endpoint')),'authorization_endpoint');
        if ($rqt != 2) { // для RSS не имеет смысла формировать главное меню, ссылки на RSS и тому подобное, поэтому пропускаем
          $this->subactions();
          if ($this->get_opt('opengraph')) $this->set_opengraph();
        }
      }

      // После того как все действия по подготовке данных выполнены, освобождаем те ресурсы, которые для вывода не нужны.
      if (isset($_SESSION['messages'])) { // если с предыдущей страницы остались невыведенные сообщения (из-за редиректа)
        if (empty($this->out->intb->messages))
          $this->out->intb->messages = array();
        $this->out->intb->messages = $this->out->intb->messages + $_SESSION['messages']; // то добавляем их к сообщениям этой страницы
        unset($_SESSION['messages']); // и удаляем из сессии
      }
      if (session_id() != false)
        session_write_close(); // сессию закрываем здесь, так как дальше закрывается соединение с БД и нужно успеть ее сохранить (если вдруг будем хранить сессии в БД)
      if (is_object($this->db))
      $this->db->close(); // закрываем соединение с БД как можно раньше -- до осуществления вывода и отправки почты
      $this->db = false; // уничтожаем объект БД, чтобы освободить память
      $this->libs = array(); // очищаем кеш библиотек, так как в большинстве случаев они уже тоже не потребуются
      // Если оказалось, что данные так с прошлого раза и не менялись, то выдаем 304 и завершаем работу без генерации HTML
      if (!defined('CONFIG_nocache') || !CONFIG_nocache) { // проверка, что кеширование не запрещено
        if ($this->lastmod > 0 && $this->lastmod <= $this->if_modified_time)
          $this->output_304();
      }

      if (!headers_sent() && $this->get_request_type()!==4) header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
      if (!defined('CONFIG_nocache') || !CONFIG_nocache) { // если кеширование не запрещено в отладочных целях в настройках, выдаем управляющие заголовки
        if ($this->is_guest())
          header('Cache-Control: no-cache'); // данные для гостей можно кешировать и на клиенте, и на proxy, но при каждом запросе делать проверку обновления
        else
          header('Cache-Control: no-cache, private'); // а вот пользовательские -- кешировать только на клиенте, но ен на proxy
      }
      else { // а если запрещено, то выдаём заголовки полного запрета на кеширование вообще
        if ($_SERVER['SERVER_PROTOCOL']==='HTTP/1.0') header('Pragma: no-cache'); // HTTP 1.0 не поддерживает Cache-Control, поэтому выдаём Pragma
        else header('Cache-Control: no-cache, no-store, must-revalidate');
      }
      header('Content-Type: '.$this->get_mime());
      if (!$this->lastmod) $this->lastmod = time();    
      header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $this->lastmod));    
      $udata = $this->userdata;
      unset($udata['password']);
      unset($udata['pass_crypt']);
      $this->out->user = $udata; // выводим данные о пользователе

      if ($this->get_request_type()!==4) $this->output($template);
      else echo $template;

      header('Content-Length: '.ob_get_length());
      ob_end_flush();
  //          $this->checkpoint('Вывод выполнен', true);
      $this->process_mail(); // отправляем почту в случае необходимости
  //          $this->checkpoint('Почта отправлена', true);
    }
    catch (Exception $e) {
      trigger_error("Exception: ".$e->getFile().", line ".$e->getLine().': '.$e->getMessage(),E_USER_ERROR);
    }
  }

  /** Функция разбирает запрошенный URL и возвращает имя класса, необходимое для его обработки.
   *  В качестве побочного эффекта также выставляются элементы глобального массива $_REQUEST, необходимые для обработки (например, a — название действия и f — HURL форума).
   *  Данные для обработки берутся из etc/routes.txt, а в случае его отсутствия — из etc/routes.def.
   *  Формат данных: регулярное_выражение класс.php?парам1=значение1&парам2=значение2 (по аналогии с правилами RewriteRule из .htaccess, но без самой директивы и )
   *
   *  @return string Имя класса, который будет выполнять обработку запроса
   *  **/
  static function do_routing() {
    $filename = is_readable(BASEDIR . 'etc/routes.cfg') ? BASEDIR . 'etc/routes.cfg' : BASEDIR . 'etc/routes.def';
    $routes = file($filename);
    $result = false;
    $start_url = preg_replace('|/+|', '/', $_SERVER['REQUEST_URI']);
    $base_url = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], 'www/'));
    if ($base_url!=='/') $url = str_replace($base_url, '', $start_url);
    elseif ($base_url!=='/') $url = substr($start_url, 1);
    if (($pos = strpos($url, '?')) !== false) $url = substr($url, 0, $pos);

    for ($i = 0, $count = count($routes); $i < $count && !$result; $i++) { // поиск ведется до первого срабатывания рег. выражения
      $data = trim($routes[$i]);
      if (empty($data)) continue;
      list($regexp, $params) = explode(' ', $data, 2);
      $regexp = str_replace('/', '\\/', $regexp);
      print "Regexp $regexp, trying to match $url<br />";
      if (preg_match('/' . $regexp . '/u', $url, $matches)) { // если выражение сработало, определяем параметры запроса
        if (strpos($params, '?') === false) $result = $params;
        else {
          list($result, $query_str) = explode('?', $params, 2);
          $items = explode('&', $query_str);
          foreach ($items as $item) if (!empty($item)) {
            list($key, $value) = explode('=', $item, 2);
            for ($j = 1; $j <= 9; $j++) {
              if (!isset($matches[$j])) $matches[$j] = '';
              $key = str_replace('$'.$j,$matches[$j],$key);
              $value = str_replace('$'.$j,$matches[$j],$value);
            }
            $_REQUEST[urldecode($key)] = urldecode($value);
            if ($_SERVER['REQUEST_METHOD']!=='POST') $_GET[urldecode($key)] = urldecode($value);
          }
        }
        $result = str_replace('.php','',$result);
      }
    }
    return $result;
  }

  /** Функция общей инициализации: подключение обработчиков ошибок, буферизации и т.п. * */
  function init() {
    $this->init_basic(); // основная часть инициализиации (буфер вывода и т.п.)
    $this->init_db(); // инициализация подключения к БД
//          $this->init_config(); // подключение файла конфигурации

    ob_implicit_flush(false);

    $this->action = (empty($_REQUEST['a'])) ? 'view' : $_REQUEST['a'];

    $this->init_user(); // инициализация пользователя (подгрузка его данных и настроек из базы или сессии)
    $this->init_object(); // подгрузка данных об обекте (например, разделе или теме) и проврка базовых прав доступа (на просмотр и чтение)
    $this->init_last_visit();
    $this->init_style(); // определяем стиль, который будет использоваться для отображения сайта
  }

  /** Общая часть инициализации: установка локали, часового пояса, обработчиков ошибок, буферизации данных * */
  function init_basic() {
    // gc_disable(); // поэкспериментировать, будет ли от этого эффект
    $this->start_time = microtime(true); // фиксируется время запуска скрипта;
    $this->time = time();
    error_reporting(E_ALL);

    Library::init($this);

    $this->out = new stdClass;/** Данные для вывода * */
    $this->out->intb = new stdClass;/** Для вывода общей информации, например, названия форума * */
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
      $this->if_modified_time = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);

    $GLOBALS['IntBF_debug'] = '';
    setlocale(LC_ALL, array('ru_RU.UTF-8', 'ru_RU.utf-8', 'Russian_Russia.65001'));

    set_error_handler(array($this, 'error_handler')); // вешаем собственный обработчик ошибок для фиксации их в логах и выдачи дружественных сообщений
    register_shutdown_function(array($this, 'shutdown')); // обработчик для корректного закрытия соединения с БД
    date_default_timezone_set('UTC'); // выставляем временную зону в UTC, чтобы для вывода даты было достаточно приплюсовать смещение, заданное в настройках пользователя

    if (defined('CONFIG_gzip') && CONFIG_gzip)
      ob_start('ob_gzhandler');
    else
      ob_start(); // если выключена поддержка GZIP, используем простую буферизацию
    if (defined('CONFIG_debug') && CONFIG_debug>0) ini_set('display_errors',true); // если задан отладочный режим, включаем вывод ошибок для упрощения отладки

// определяем sitepath и парсим URL
    $pos = strrpos($_SERVER['PHP_SELF'], 'www/'); // последний www в пути будет каталогом www в дистрибутиве IntB
    $this->sitepath = ($pos) ? substr($_SERVER['PHP_SELF'], 0, $pos) : dirname($_SERVER['PHP_SELF']);
    if ($this->sitepath == '\\')
      $this->sitepath = '/'; // для Windows
    if (substr($this->sitepath, -1, 1) !== '/')
      $this->sitepath = $this->sitepath.'/';

    // подключаем серверное кеширование, если оно задано в настройках
    $cachename = $this->get_opt('site_cache_lib');
    if ($cachename) {
      $this->server_cache = $this->load_lib($cachename, false); // отсутствие кеш-библиотеки фатальным не является, если ее не будет, просто будем брать все из базы
      if (!is_object($this->server_cache) || !($this->server_cache instanceof iCache))
        $this->server_cache = false; // если вдруг библиотека не загрузилась или не подходит по интерфейсу, отключаем ее использование
    }
  }

  /** Инициализация базы данных * */
  function init_db() {
    require BASEDIR.'db/database.php'; // общий модуль базы данных

    if (!defined('DB_driver') || !include(BASEDIR.'db/'.DB_driver.'.php')) {
      header($_SERVER['SERVER_PROTOCOL'].' 503 Service Unavailable');
      echo 'Ошибка: Некорректно указан тип используемой БД или модуль работы с БД поврежден!'.BASEDIR.'db/'.DB_driver.'.php';
      exit();
    }

    $classname = 'Database_'.DB_driver;
    $this->db = new $classname;
    if (!($this->db instanceof iDBDriver)) {
      header($_SERVER['SERVER_PROTOCOL'].' 503 Service Unavailable');
      echo 'Ошибка: класс '.$classname.' не является классом базы данных (не реализует интерфейс iDBDriver)!';
      exit();
    }
    $params = array('DB_host'=>DB_host, 'DB_name'=>DB_name, 'DB_username'=>DB_username, 'DB_password'=>DB_password);
    foreach (array('DB_socket','DB_presist','DB_charset','DB_port','DB_schema') as $item) if (defined($item)) $params[$item]=constant($item);
    $this->db->connect($params);
  }

  /** Парсинг URL и подгрузка данных о разделе или объекте * */
  function init_object() {
//                $this->forum = false;
  }

  /** Подключение файла конфигурации * */
  /*        function init_config() {

    } */

  /** Восстановление сессии пользователя * */
  function init_user() {
    $banned = false;
    $time = time();
    $session_name = CONFIG_session;
    $ip = ip2long($_SERVER['REMOTE_ADDR']);
    if (isset($_COOKIE['ibbc'])) { // если у пользователя установлено cookie с признаком "забанен", блокируем его IP на два часа
      $bandata = array('start'=>$ip, 'end'=>$ip, 'till'=>($time + 2 * 60 * 60));
      $this->db->insert_ignore($bandata, DB_prefix.'banned_ip');
      $banned = true;
    }
    else {
      if (strpos($_SERVER['REMOTE_ADDR'],':')===false) { // TODO: сделать со временем проверку IPv6, а пока проверяем только IPv4
        $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'banned_ip WHERE ('.intval($ip).' BETWEEN "start" AND "end") AND till>='.$time;
        $banned_ip=$this->db->select_int($sql);
      }
      else $banned_ip=false; // если у нас IPv6, то пока придется обойтись без проверки
      if ($banned_ip > 0)
        $banned = true; // если из базы удалось что-то извлечь, то IP попадает в число забаненных
      else { // иначе проверяем дальше
        $auth_done = false;
        if (isset($_REQUEST['authkey'])) { // сначала проверяем аутентификацию по ключу, она имеет наибольший приоритет
          list($uid, $key) = explode('-', $_REQUEST['authkey']);
          $rightkey = $this->gen_auth_key($uid); // генерируем правильный аутентификационый ключ для указанного uid и текущего (запрошенного) HURL и действия
          if ($uid > AUTH_SYSTEM_USERS && $_REQUEST['authkey'] == $rightkey)
            $this->userdata = $this->load_user($uid, 1); // если ключ правильный и пользователь не специальный, загружаем информацию о данном пользователе
// ВАЖНО! Поскольку authkey должен срабатывать только для текущего действия, вместо использования функции set_user, которая создала бы сессию и длинный ключ, устанавливаем данные непосредственно в массив $user
          else {
            $this->load_guest(); // если ключи не совпали, пользователь будет гостем
            $this->message('Неправильный ключ аутентификации!', 2);
          }
          $auth_done = true;
        }
        elseif (!empty($_REQUEST[$session_name]) || !empty($_COOKIE[$session_name])) { // если в запросе есть идентификатор сессии, используем данные оттуда
          $this->session(); // вызываем процедуру создания сессии, чтобы установить свой обработчик и проверить необходимость перезагрузки данных
          if ($ext_lib_name = $this->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
            $ext_lib = $this->load_lib($ext_lib_name, false); // загружаем ее
            if ($ext_lib) {
              $user_id = $ext_lib->get_user_by_session(); // и получаем ID пользователя (в нумерации IntB), которого будем считать авторизованным. Если библиотека возвращает false, то пользователь не подгружается
              if ($user_id > AUTH_SYSTEM_USERS) {
                $userdata = $this->load_user($user_id, 1);
                $this->set_user($userdata);
                $_SESSION['starttime'] = $this->time;
              }
              elseif ($user_id == 1) { // если библиотека возвращает единицу, это значит, что пользователя нужно разлогинить принудительно
                $this->set_user($this->load_guest());
                $_SESSION['starttime'] = $this->time;
              }
            }
            else
              _dbg('Ошибка подключения внешней библиотеки!', E_USER_WARNING);
          }
          if (isset($_SESSION['IntB_auth'])) { // если нет необходимости обновить данные пользователя, а можно взять их из сессии
            if (!$this->check_cache_expired()) {
              if (!$this->get_opt('check_user_agent') || $_SESSION['IntB_user_agent'] === preg_replace('|\d+|', '', $_SERVER['HTTP_USER_AGENT'])) // если проверка User Agent выключена или же он совпадает с тем, что был при создании сессии
                $this->userdata = $_SESSION['IntB_user']; // если сессия была инициализирована правильно (т.е. не произошло ее истечения или повреждения)
              else {
                $this->load_guest();
                _dbg('Ошибка безопасности: не прошла проверка по User Agent!');
              }
            }
            else
              $this->set_user($this->load_user($_SESSION['IntB_user']['id'], 1)); // если необходимость обновить данные возникла (поменялись настройки разделов, права доступа или еще что-то подобное), загружаем их заново
            $auth_done = true;
          }
          else
            $auth_done = false; // если данные из сессии взять не удалось, сбрасываем переменную, чтобы потом попробовать авторизоваться через long cookie
        }
        if (!$auth_done && isset($_COOKIE[$session_name.'_long'])) { // если в запросе есть cookie с длинным ключом, проверяем его корректность и устанавливаем данные пользователя
          list($uid, $key) = explode('-', $_COOKIE[$session_name.'_long']);
          $userdata = $this->load_user($uid, 1);
          $rightkey = $this->gen_long_key($userdata, $session_name); // генерируем правильный длинный ключ для данного пользователя
          if ($_COOKIE[$session_name.'_long'] == $rightkey && !empty($userata))  // вторая проверка нужна на случай удаления пользователя за время его отсутствия на форуме
            $this->set_user($userdata, 14); // если ключ правильный, выставляем данные о пользователе и создаем его сессию, а так же обновляем cookie еще на 14 дней
// хотя в принципе длинного ключа достаточно для аутентификации, но взятие данных из сессии позволит выполнять аутентификацию быстрее, поэтому мы ее и создаем
          else
            $this->load_guest(); // если ключи не совпали, пользователь будет гостем
        }
        elseif (!$auth_done) { // если ни один из способов авторизации пользователя не сработал, считаем его гостем и загружаем соответствующие данные
          $this->load_guest();
        }
        if ($this->userdata['status'] == 2 || $this->userdata['banned_till'] > $this->time)
          $banned = true; // проверка бана пользователя по статусу и времени истечения предупреждений
      }
    }
    if ($banned)
      $this->output_403('Вы изгнаны с этого форума. Вы можете обратиться за разъяснениями к администрации по адресу <a href="mailto:'.CONFIG_email.'">'.CONFIG_email.'</a>');
    if ($this->userdata['status'] == 1 && !isset($_REQUEST['authkey'])) {
      $this->set_user($this->load_guest());
      $this->output_403('Ваша учетная запись не активирована.');
    }
    $this->init_check_bot();
    if ($this->bot_id != 0 && !$this->is_guest()) { // защита на случай, если пользователь выложит куда-то ссылку со своим идентификатором сессии
      $this->output_403('Во избежание доступа к личной информации поисковым роботам запрещено действовать под логинами зарегистрированных пользователей!');
    }
    if ($this->get_opt('check_referer') && $this->is_post()) { // если пришел POST-запрос и включена проверка REFERER, выполняем ее
      $result = true;
      $referer = strtolower($_SERVER['HTTP_REFERER']);
      if (strpos($referer, 'http://') === 0 || strpos($referer, 'https://') === 0) { // если Referer начинается с http:// или https://, значит, он корректный, то есть не заблокирован всякими firewallами, поэтому проверку выполняем
        if (strpos($referer, 'http://'.$_SERVER['HTTP_HOST'].'/') === false && strpos($referer, 'https://'.$_SERVER['HTTP_HOST'].'/') === false)
          $result = false; // если Referer коррктен, но содержит другой сайт
      }
      // если же referer пуст или содержит что-то некорректное, то придется пользователя не пустить
      // исключение — ситуации входа из социальных сетей (действие social_login из модуля user)
      if (!$result && $this->action!=='social_login' && $this->userdata['id']>AUTH_SYSTEM_USERS)
        $this->output_403('Обнаружена попытка выполнения POST-запроса со стороннего сайта. По соображениям безопасности такие действия запрещены.');
    }
  }

  /**
   * Проверка на то, что пользователь является поисковым роботом *
   */
  function init_check_bot() {
    $mode = $this->get_opt('check_bots');
    if ($mode == 0)
      return false; // если режим проверки ботов выключен, выходим сразу
    $agent = isset($_SERVER ['HTTP_USER_AGENT']) ? $_SERVER ['HTTP_USER_AGENT'] : false;
    if (empty($agent))
      return false; // если строка заголовка UserAgent пуста или отсутствует, определить, что это робот, нет возможности
    $sql = 'SELECT id FROM '.DB_prefix.'bots WHERE INSTR(\''.$this->db->slashes($agent).'\',user_agent)>0 ';
    $this->bot_id = $this->db->select_int($sql);
    if ($this->bot_id && $mode == 2) {
      $sql = 'UPDATE '.DB_prefix.'bots SET last_visit='.intval($this->time).' WHERE id='.intval($this->bot_id);
      $this->db->query($sql);
    }
    return !empty($this->bot_id);
  }

  /**
   * Отслеживание и обновление времени последнего визита *
   */
  function init_last_visit() {
    if (!$this->is_guest()) {
      if (!empty($this->forum))
        $forum_id = $this->forum ['id'];
      else
        $forum_id = 0;
      $curtime = $this->time;
      $inactive = $this->get_opt('online_time');
      if (!$inactive)
        $inactive = 15;
      $lasttime = $curtime - $inactive * 60; // если последнее действие пользователя было до этого времени, переносим его в visit2 (время последнего прошлого визита), иначе оставляем без изменений
      $sql = 'UPDATE '.DB_prefix.'last_visit SET visit2= CASE WHEN visit1<'.$lasttime.' THEN visit1 ELSE visit2 END, visit1='.$curtime.' WHERE uid='.intval($this->get_uid()).' AND oid=0 AND type=\'forum\'';
      $this->db->query($sql);
      if ($this->db->affected_rows() == 0) {
        $data ['uid'] = $this->get_uid();
        $data ['visit1'] = $curtime;
        $data ['visit2'] = $curtime;
        $data ['oid'] = 0;
        $data ['type'] = "forum";
        $this->db->insert_ignore(DB_prefix.'last_visit', $data);
      }
    }
  }

  /** Определение шаблона для отображения сайта. Вынесено в отдельную процедуру, чтобы можно было легко переопределить в объектах-наследниках.
   *
   */
  function init_style() {
    if ($this->get_opt('userlib_allow_template') && $this->get_opt('template', 'user'))
      $this->template = $this->userdata['template'];
    else
      $this->template = $this->get_opt('site_template');
    if (!$this->template)
      $this->template = 'def';

    // Задаем библиотеку-обработчик для генерации HTML или RSS
    if ($this->get_request_type() != 2)
      $this->template_lib = $this->get_opt('template_lib'); // если запрашиваем не RSS, то используем тот обработчик, который задан в настройках
    else
      $this->template_lib = 'rss'; // иначе -- специализированный для RSS
    if (!$this->template_lib)
      $this->template_lib = 'twig';
  }

  /* * Выполнение запрошенного пользователем действия. Действие может вернуть имя шаблона,
    * с помощью которого отображаются результаты. Если нет, берется имя шаблона по умолчанию в виде модуль/действие.htm.
    * Сами данные, полученные в ходе выполнения действия, должны сохраняться в $this->out. * */
  function process() {
    $action = $this->action;
    if (!is_string($action))
      $this->output_403('Некорректное запрошенное действие!');
    if (method_exists($this, 'action_'.$action))
      $template = call_user_func(array($this, 'action_'.$action));
    else
      $this->output_404('Запрашиваемое действие '.$action.' не поддерживается!');
    if (!$template && $template!=='') // пустую строку могут возвращать запросы с типом 4, если не требуется ничего выводить
      $template = get_class($this).'/'.$action.'.tpl';
    return $template;
  }

  /** Точка контроля производительности движка
   *
   * @param string $name Имя контрольной точки
   * @param bool $output Способ вывода: false -- с помощью dbg, true -- напрямую, с помощью echo.
   */
  function checkpoint($name) {
    if (defined('CONFIG_debug') && CONFIG_debug >= 4 && !$this->bot_id) {
      $tpass = microtime(true) - $this->start_time;
      $msg = 'Контрольная точка "'.$name.'". Время выполнения: '.sprintf('%.3f', $tpass);
      if (is_object($this->db)) {
        $msg.='. Запросов: '.$this->db->query_count.', время запроса: '.sprintf('%.3f (%.2f)%%', $this->db->query_time, ($this->db->query_time / $tpass) * 100);
      }
      if (function_exists('memory_get_usage')) {
        $msg.='. Памяти использовано: '.memory_get_usage().' байтов';
      }
      _dbg($msg);
    }
  }

  /** Выполнение вспомогательных действий, таких как пстроение главного меню, выдача объявления и т.п.
   * Вынесены в отедльную процедуру для того, чтобы было легче дополнять их: достаточно
   * сделать класс наследник и переопределить в нем этот метод, при необходимости вызвав parent::subactions * */
  function subactions() {
    $this->out->intb->rss = $this->set_rss(); // определяем ссылки на RSS, если они есть
    $this->out->intb->mainmenu = $this->get_menu_items(1); // главное меню хранится в базе под номером 1 всегда
    $this->out->intb->location = $this->set_location(); // формируем указатель текущего местоположения на форуме

    $modules = '\'*\',\''.$this->db->slashes(get_class($this)).'\''; //
    if (is_subclass_of($this,'Application_Forum')) $modules.=',\'*forum\''; // для разделов-форумов всех типов
    $actions = '\'*\',\''.$this->db->slashes($this->action).'\'';
    $sql = 'SELECT * FROM '.DB_prefix.'subaction WHERE module IN ('.$modules.') AND action IN ('.$actions.') AND active=\'1\'';
    if (!empty($this->forum)) $sql.=' AND fid IN (0,'.intval($this->forum['id']).')';
    if (!empty($this->topic)) $sql.=' AND tid IN (0,'.intval($this->topic['id']).')';
    $sql.= ' ORDER BY priority';
    $subactions = $this->db->select_all($sql);
    for ($i=0, $count=count($subactions); $i<$count; $i++) {
      if ($module=$this->load_lib($subactions[$i]['library'],false)) {
        if (method_exists($module,$subactions[$i]['proc'])) {
          $result = call_user_func(array($module,$subactions[$i]['proc']),$subactions[$i]['params']);
          if ($result && is_array($result)) $this->out->IntB_subactions[$subactions[$i]['block']][$result[0]]=$result[1]; // результаты сохраняются в IntB_subactions[имя_блока][имя_шаблона_без_tpl]
        }
        else _dbg('Не найдена функция '.$subactions[$i]['proc'].' в файле lib/'.$subactions[$i]['library'].'.php!');
      }
      else _dbg('Не найден модуль lib/'.$subactions[$i]['library'].'.php!');
    }
    
    $this->checkpoint('Вспомогательные действия выполнены');
  }

  /** Сохранение сообщения для последующего вывода * */
  function message($message, $level = 1) {
    if (empty($this->out->intb->messages))
      $this->out->intb->messages = array();
    if (is_string($message))
      $this->out->intb->messages[] = array('text'=>$message, 'level'=>$level);
    elseif (is_array($message) && isset($message['text']))
      $this->out->intb->intb_messages[] = $message;
    else
      $this->out->intb->messages = array_merge($this->out->intb->messages, $message);
  }

  /** Выборка из хеш-массива $data только тех элементов, ключи которых есть в массиве $keys.
   * Используется для фильтрации данных, переданных из формы или перед сохранением в БД.
   * Обработка провощится нерекурсивно.
   *
   * @param array $data Исходные данные
   * @param array $keys Список допустимых ключей для массива исходных данных
   * @return array Отфильтрованный масссив
   */
  function filter($data, $keys) {
    $result = array();
    if (is_array($keys)) {
      for ($i = 0, $count = count($keys); $i < $count; $i++)
        if (isset($data[$keys[$i]]) && !is_array($data[$keys[$i]]))
          $result[$keys[$i]] = $data[$keys[$i]];
    }
    return $result;
  }

  /** Получение значения переменной из настроек * */
  function get_opt($name, $type = 'global', $id = 0) {
    $result = false;
    if ($type == 'global') {
      if (defined('CONFIG_'.$name))
        $result = constant('CONFIG_'.$name);
    }
    elseif ($type == 'user') {
      if ($id == 0 || $id == $this->get_uid()) {
        if (isset($this->userdata[$name]))
          $result = $this->userdata[$name];
      }
      else
        trigger_error('Пока не реализовано!', E_USER_WARNING);
      // TODO: доделать вариант получения настроек другого пользователя (если это вообще когда-нибудь будет нужно)
    }
    elseif ($type == 'group') {
      if (isset($this->userdata[$name]))
        return $this->userdata[$name];
      else
        return false;
    }
    return $result;
  }

  /** Получение текста из таблицы текстов
   *
   * @param integer $id Идентификатор объекта, к которому привязан текст. 0 -- тексты общефорумского значения
   * @param integer $type Тип текста:
   *   0 -- правила форума или раздела
   *   1 -- объявление форума или раздела
   *   2 -- текст статического раздела, вводный текст для главной (для id=0) или обычного раздела
   *   Полный перечень см. в doc/text.txt
   *   Также обновляет значение $this->lastmod в соответствии с временем последнего изменения текста
   * @return string Полученный из базы текст
   */
  function get_text($id, $type) {
    if ($type < 3) { // такие тексты как правила, объявления и вводные слова пытаемся взять из кеша, чтобы лишний раз не обращаться к БД
      $result = $this->get_cached('Text'.$type.'_'.$id);
      if ($result != NULL)
        return $result;
    }
    $sql = 'SELECT data, tx_lastmod FROM '.DB_prefix.'text WHERE id='.intval($id).' AND type='.intval($type);
    $result = $this->db->select_row($sql);
    if (empty($result) || $result===null) return ''; // если ничего извлечь не удалось, возвращаем пустую строку
    $this->lastmod = max($this->lastmod, $result['tx_lastmod']);
    if ($type <= 3)
      $this->set_cached('Text'.$type.'_'.$id, $result['data']); // если запрашиваемый текст относится к кешируемым, сохраняем его в кеш
    return $result['data'];
  }

  /** Получение IP-адреса в виде строки (нужно для логов, сообщений и т.п.).
   * То, из каких ключей в $_SERVER берутся значения для IP-адреса, можно задать в настройке ip_address_source.
   * Это позволяет работать в конфигурациях, где есть reverse proxy, и параметр передается не в REMOTE_ADDR,
   * а в каких-то других переменных, например, в X_REAL_IP.
   * 
   *
   * @return int
   */
  function get_ip() {
    $ip_source = $this->get_opt('ip_address_source');
    if (!$ip_source) $ip_source="REMOTE_ADDR,HTTP_X_FORWARDED_FOR"; // на случай, если в настройках ничего не указано
    $ip_parts = explode(",",$ip_source);
    $result = array();
    foreach ($ip_parts as $part) if (isset($_SERVER[trim($part)])) $result[]=$_SERVER[trim($part)];
    return join(',',$result);
  }

  /** Возвращает ID текущего пользователя * */
  function get_uid() {
    $result = $this->userdata['id'];
    if (!$result)
      $result = 1; // если данных о пользователе нет, возвращаем идентификатор гостя
    return $result;
  }

  /** Возвращает ID текущего пользователя без влияния имперсонализации, если она когда-либо будет сделана * */
  function get_effective_uid() {
    return $this->get_uid(); // TODO: пока имперсонализации нет, возвращаем просто UID
  }

  /** Загрузка модуля с указанным именем * */
  function load_lib($name, $fatal = false) {
//    if (isset($this->libs[$name])) return $this->libs[$name];
//    else {
    $filename = BASEDIR.'lib/'.$name.'.php';
    $result = false;
    if ($this->valid_file($name) && file_exists($filename)) {
      $classname = 'Library_'.$name;
      if (!class_exists($classname))
        if (!include($filename))
          trigger_error('При подгрузке класса '.$name.' произошла ошибка!', ($fatal) ? E_USER_ERROR : E_USER_WARNING);
      if (!class_exists($classname))
        trigger_error('В файле '.$filename.' класс '.$classname.' не найден!', ($fatal) ? E_USER_ERROR : E_USER_WARNING);
      else
        $result = new $classname($this);
//         $this->libs[$name]=$result; // сохраняем экземпляр библиотеки в кеш
      return $result;
    }
    elseif ($fatal) {
      trigger_error('Не удается найти класса '.$name.' или его имя содержит некорректные символы', E_USER_ERROR);
    }
    return $result;
//    }
  }

  /** Расчет смещения от стартового элемента по номеру страницы.
   * @param $pagedata array Хеш с исходными данными для расчета. Должен содержать следующие ключи:
   * total -- общее количество имеющихся элементов (тем, сообщений и т.п.)
   * page -- номер текущей страницы (если не задан, равен единице)
   * perpage -- количество элементов на странице
   * @param $direction boolean TRUE -- если страницы будут выдаваться в обратном порядке
   * @param $links boolean Если TRUE, то будут также сгенерированы (с помощью функции link) теги link со ссылками на предыдущую, следующую, первую и последнюю страницы
   * @return array Хеш, аналогичный $pagedata, но содержащий также ключи pages -- количество страницы и start -- смещение первого элемента для указанной страницы (его можно испольовать в качестве offset1 при вызове SQL-функций)
   * */
  function get_pages($pagedata, $direction = false, $links = false) {
    if (empty($pagedata['page']))
      $pagedata['page'] = 1;
    $result['perpage'] = $pagedata['perpage'];
    $result['total'] = $pagedata['total'];
    $result['pages'] = ($result['total'] > 0) ? ceil($result['total'] / $result['perpage']) : 1; // если даже элементов нет, то все же одна страница с сообщением об этом быть должна
    $result['page'] = $pagedata['page']; // TODO: доделать обработку all
    $result['direction'] = $direction;
    if (($result['page'] - 1) * $result['perpage'] > $result['total'] || $result['page'] < 1)
      $result = false; // неправильный номер страницы, в разделах будет обрабатываться как ошибка
    else
      $result['start'] = ($result['page'] - 1) * $result['perpage']; // TODO: уточнить насчет обратной сортировки, возможно, там будет другая формула
    if ($result === false) { // если в результате расчета окзаалось, что пытаемся обратиться к несуществующей странице
      $this->output_404('Страницы с таким номером не существует!');
    }
    if ($links) {
      if ($result['page'] != 1)
        $this->link('./', 'first', 'nav_link_first_page');
      if ($result['page'] > 1)
        $this->link(($result['page'] - 1).'.htm', 'prev', 'nav_link_prev_page');
      if ($result['page'] < $result['pages'])
        $this->link(($result['page'] + 1).'.htm', 'next', 'nav_link_next_page');
      if ($result['page'] != $result['pages'])
        $this->link($result['pages'].'.htm', 'last', 'nav_link_last_page');
    }
    return $result;
  }

  /** Показывает меню с задаваемыми пунктами (описанными в таблице menu_item) * */
  function get_menu_items($id) {
    // Для гостей и простых пользователей берем меню из кеша (причем кеш разный для тех и других), для админов -- не кешируем
    if ($this->is_guest())
      $cache_id = ('Menu_guest_'.$id);
    elseif (!$this->is_admin())
      $cache_id = ('Menu_user_'.$id);
    if (!$this->is_admin())
      $menuitems = $this->get_cached($cache_id);
    else
      $menuitems = NULL; // для админов меню не кешируем
    if ($menuitems === NULL) {
      $sql = 'SELECT title,url,hurl_mode FROM '.DB_prefix.'menu_item WHERE mid='.intval($id);
      if ($this->is_admin()) {
        $sql.=' AND show_admins=\'1\'';
      }
      elseif ($this->is_guest()) {
        $sql.=' AND show_guests=\'1\'';
      }
      else {
        $sql.=' AND show_users=\'1\'';
      }
      $sql.=' ORDER BY sortfield';
      $menuitems = $this->db->select_all($sql);
      for ($i = 0, $count = count($menuitems); $i < $count; $i++)
        if ($menuitems[$i]['hurl_mode'])
          $menuitems[$i]['url'] = $this->url($menuitems[$i]['url']);
      if (!$this->is_admin())
        $this->set_cached($cache_id, $menuitems); // запоминаем полученное в кеш
    }
    return $menuitems;
  }

  /** Вывод итогов выполнения скрипта в обычном режиме
   * @param $template string Имя файла (относительное) с шаблоном для вывода. Данный шаблон будет вставлен внутрь основного шаблона main.htm */
  function output($template) {
    /* Выводим номер версии, если его показ включен в настройках сайта */
    $this->out->intb->intb_version = $this->get_opt('site_version') ? INTB_VERSION : '';
    // переменная intb.is_ajax в шаблонизаторе используется для выбора родительского шаблона (main.htm или ajax.htm), а также для проверок внутри других шаблонов в случае необходимости
    $this->out->intb->is_ajax = ($this->get_request_type() == 1);
    $this->out->intb->is_admin = $this->is_admin();
    $this->out->intb->action = $this->action;
    $this->out->now = $this->time;

    /* Название правильного обработчика должно быть выставлено заранее в переменной template_lib.
      Предполгается, что это делается в функции init_style, но возможна и модификация где-то еще * */
    $outlib = $this->load_lib($this->template_lib, true); // отсутствие парсера должно вызывать фатальный шаблон, поэтому ставим true
    if (!($outlib instanceof iParser))
      trigger_error('Библиотека '.$this->template_lib.' не является парсером!', E_USER_ERROR);
    $outlib->set_template($template);
    $outlib->set_style($this->template);
    $html = $outlib->generate_html($this->out,false,$this->get_opt('minify_html'));
    $this->checkpoint('После срабатывания шаблонизатора.');

    if ($this->get_opt('debug') > 0 && $GLOBALS['IntBF_debug'])
      $dbg_output = '<!--noindex--><div style="border: #080 1px solid; line-height: 0.8em; font-size: 80%; background: #EFE; overflow: auto" class="debug noprint">'.$GLOBALS['IntBF_debug'].'</div><!--/noindex-->';
    else
      $dbg_output = '';
    $html = str_replace('<!--##DEBUG#-->', $dbg_output, $html);

    echo $html;
  }

  function output_404($message) {
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    if ($this->get_request_type() != 1)
      $this->output_error($message, '404');
    else
      $this->output_json(array('result'=>'error', 'message'=>$message)); // иначе -- отдаем JSON-объект с сообщением об ошибке
    $this->process_mail(); // отправляем почту в случае необходимости
    exit();
  }

  function output_400($message,$code) {
    header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
    header('Content-Type: application/json;charset=UTF-8');
    header('Cache-Control: no-store');
    if ($_SERVER['SERVER_PROTOCOL']=='HTTP/1.0') header('Pragma: no-cache');
    $result['error']=$code;
    $result['error_description']=$message;
    print (json_encode($result));
    $this->process_mail(); // отправляем почту в случае необходимости
    exit();
  }

  function output_403($message, $need_login = false) {
    $this->message($message);
    if ($need_login && $this->is_guest()) {
      $this->redirect($this->http($this->url('user/login.htm?referer='.$this->http($_SERVER['REQUEST_URI']))));
    }
    header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
    if ($this->get_request_type() != 1)
      $this->output_error($message, '403'); // если сделали запрос не AJAXом, то выводим страницу ошибки
    else {

      $this->output_json(array('result'=>'error', 'message'=>$message)); // иначе -- отдаем JSON-объект с сообщением об ошибке
    }
    $this->process_mail(); // отправляем почту в случае необходимости
    exit();
  }

  /** Выдача файла с сообщением об ошибке (для ошибок 404 и 403) * */
  function output_error($message, $file) {
    $buffer = @file_get_contents(BASEDIR.'www/'.$file.'.htm');
    if (!$buffer)
      $buffer = 'Произошла ошибка и не найден файл для отображения сообщения о ней!<br />Текст ошибки: '.htmlspecialchars($message);
    $buffer = str_replace('<!--##MESSAGES#-->', $message, $buffer);
    $buffer = str_replace('<!--##DEBUG#-->', $GLOBALS['IntBF_debug'], $buffer);
    echo $buffer;
    ob_end_flush();
  }

  /** Выдача заголовка 304 в случае, если данные не поменялись * */
  function output_304() {
//      if ($GLOBALS['IntBF_debug']!=='' && $this->get_opt('debug')) trigger_error('Статус 304 при не пустом отладочном буфере!',E_USER_ERROR); // для упрощения отладки
    header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
    if ($this->is_guest())
      header('Cache-Control: nocache');
    else
      header('Cache-Control: nocache, private');
    ob_clean();
    exit();
  }

  /** Выдача страницы с сообщением с возможностью редиректа через META-тег
   *
   * @param string $url Адрес, на который делается редирект.
   *   Адрес может быть относительным, в этом случае он считается относи.
   *   Если адрес равен false, то редиректа не происходит, а только выводится сообщение.
   * @param string $title Заголовок страницы с сообщением
   * @param string $message Дополнительная часть сообщения
   */
  function output_msg($url, $title, $link_text = false) {
    if ($this->get_opt('debug') && !empty($GLOBALS['IntBF_debug'])) $this->out->noredirect = 1; // если есть откладочные сообщения, то блокируем редирект
    $this->out->location = $url;
    $this->out->location_text = ($link_text) ? $link_text : 'Нажмите сюда, если редирект не произошел автоматически.';
    $this->out->intb->title = $title;
    header('Content-Type: text/html; charset=utf-8');
    $this->output('message.tpl');
    $this->process_mail(); // отправляем почту в случае необходимости
    exit();
  }

  /** Вывод указанных данных в JSON-формате.
   * В отличие от всех остальных output-функций, берет данные не из $this->out,
   * а только из параметра $data, чтобы в JSON-объект не уходили ненужные данные
   *  * */
  function output_json($data) {
    echo json_encode($data);
    header('Content-Type: application/json'); // для JSON Content-type фиксированный
    header('Content-Length: '.ob_get_length());
    //      header('ETag: W/"'.$hash.'"');
    if (!$this->lastmod)
      $this->lastmod = time();
    header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $this->lastmod));
    header('Cache-Control: no-cache, no-store');
    ob_end_flush();
    $this->process_mail(); // отправляем почту в случае необходимости
    exit();
  }

  /** Функция редиректа без показа сообщения
   *
   * @param       string $url URL, на который следует отправить пользователя. Может быть относительным (в этом случае преобразуется в абсолютный с помощью функций url и http).
   * @param mixed $permanent Если TRUE, то редирект со статусом 301, иначе -- 302.
   */
  function redirect($url, $permanent = false) {
    if (!empty($this->out->intb->messages)) { // если есть невыведенные сообщения, сохраняем их в сессию, выведем на следующей странице
      $this->session();
      $_SESSION['messages'] = $this->out->intb->messages;
    }
    if ($permanent)
      header($_SERVER['SERVER_PROTOCOL'].' 301 Moved permanently');
    elseif (!$this->is_post())
      header($_SERVER['SERVER_PROTOCOL'].' 302 Moved temporary');
    else
      header($_SERVER['SERVER_PROTOCOL'].' 303 Moved temporary');
    if (strpos($url, '://') === false)
      $url = $this->http($this->url($url)); // если URL не абсолютный, преобразуем его в таковой
    header('Location: '.$url);
    $this->process_mail(); // отправляем почту в случае необходимости
    exit();
  }

  /** Создание пользовательской сессии **/
  function session() {
    if (!session_id()) { 
      if (!defined('CONFIG_session')) define('CONFIG_session', 'ib_sid');    
      session_name(CONFIG_session);
      if (defined('CONFIG_session_path') && CONFIG_session_path) session_save_path(CONFIG_session_path); // если в настройках выставлено сохранение сессий в отдельный путь

  // TODO: возможно, вынести часть параметров сессии в конфиг
      session_set_cookie_params(false, $this->url('/'), false, !empty($_SERVER['HTTPS']), true); // последний параметр повышает безопасность cookies, делая их недоступными для JavaScript, если броузер пользователя такое поддерживает
      session_start();
    }
    if (!isset($_SESSION['starttime'])) $_SESSION['starttime'] = $this->time;
  }

  /** Проверка, не возникла ли необходимость обновить пользовательские данные, закешированные в сесии
   * Возвращает TRUE если сбро необходим
   * * */
  function check_cache_expired() {
    $reset_time = $this->get_cached('Session_Reset'); // сначала проверим значение в серверном кеше, чтобы не открывать лишний раз файл
    if ($reset_time === NULL) { // если не найдено, проверяем файл на диске
      $filename = BASEDIR.'tmp/reset.txt'; // в этом файле записывается время выполнения последнего действия, потребовавшего сброса кеша
      if (!file_exists($filename))
        return false;
      $reset_time = intval(file_get_contents($filename));
      $this->set_cached('Session_Reset', $reset_time);
    }
    $result = !isset($_SESSION['starttime']) || $_SESSION['starttime'] < intval($reset_time); // сброс необходим, если время старта сессии не зафиксировано вообще или оно раньше времени принудительного сброса
    if ($result)
      $_SESSION['starttime'] = $reset_time; // если обнаружилось, что кеш нужно сбросить, обновляем время начала сессии, чтобы во-первых, избежать сброса при каждом обращении, во-вторых, обновилось время последней модификации страницы
    return $result;
  }

  /** Загрузка данных о пользователе. Может производится в двух режимах: для сессии пользователя
   *  (грузится часть базовых данных пользователя
   *
   * @param integer $uid Идентфикатор пользователя
   * @param integer $for_session Режим загрузки:
   *   0 -- только базовые данные,
   *   1 -- данные для сессии (часть базовых, группа, настройки пользователя, права доступа)
   *   2 -- базовые данные, настройки и контакты (для редактирования/просмотра профиля пользователя)
   *   3 -- загрузка данных по логину, а не по uid
   *   4 -- загрузка базовых данных и данных из ext_data
   * @return array
   */
  function load_user($uid, $mode = 0, $login = false) {
    $params = array();
    if ($mode == 1 || $mode == 3) {
      $sql = 'SELECT u.id, login, password, pass_crypt, email, status, rnd, display_name, avatar,'.
              'g.*, us.*, ue.banned_till, ue.post_count, ue.reg_date FROM '.DB_prefix.'user u '.
              'LEFT JOIN '.DB_prefix.'user_settings us ON (u.id=us.id) '.
              'LEFT JOIN '.DB_prefix.'user_ext ue ON (u.id=ue.id) '.
              'LEFT JOIN '.DB_prefix.'group g ON (ue.group_id=g.level) ';
      if ($mode == 1) {
        $sql.='WHERE u.id=?';
        $params[]=$uid;
      }
      else {
        $sql.='WHERE u.login=?';
        $params[]=$login;
      }

      $result = $this->db->select_row($sql,$params);
      if (empty($result) && $uid!=1) {
        return $this->load_user(1,1); // если пользователь не найден, заружаем данные гостя
      }
      $uid = $result['id'];
      $sql = 'SELECT f.id AS id, f.title, f.is_flood, f.parent_id, f.hurl, ac.*, md.role=\'moderator\' AS moderate FROM '.DB_prefix.'forum f '.
              'LEFT JOIN '.DB_prefix.'access ac ON (f.id=ac.fid AND ac.gid='.intval($result['level']).')'.
              'LEFT JOIN '.DB_prefix.'moderator md ON (f.id=md.fid AND md.uid='.intval($uid).' AND role=\'moderator\')';
      $result['access'] = $this->db->select_hash($sql, 'id');
      $sql = 'SELECT ac.* FROM '.DB_prefix.'access ac WHERE ac.gid='.intval($result['level']);
      $result['access'][0] = $this->db->select_row($sql);
      $sql = 'SELECT md.role=\'moderator\' FROM '.DB_prefix.'moderator md WHERE md.fid=0 AND md.uid='.intval($uid);
      $result['access'][0]['moderate'] = $this->db->select_int($sql);
    }
    elseif ($mode == 2 || $mode == 4) {
      $sql = 'SELECT u.* FROM '.DB_prefix.'user u '.
              'WHERE u.id=?';
      $result['basic'] = $this->db->select_row($sql,array($uid));
      if (empty($result['basic']) && $uid!=1) {
        return $this->load_user(1,$mode); // если пользователь не найден, заружаем данные гостя 
      }


      if ($mode == 2) {
        $sql = 'SELECT us.* FROM '.DB_prefix.'user_settings us  '.
                'WHERE us.id='.intval($uid);
        $result['settings'] = $this->db->select_row($sql);
      }
      $sql = 'SELECT ue.*, g.* FROM '.DB_prefix.'user_ext ue '.
              'LEFT JOIN '.DB_prefix.'group g ON (ue.group_id=g.level) '.
              'WHERE ue.id='.intval($uid);
      $result['ext_data'] = $this->db->select_row($sql);
      if ($mode == 2) {
        $sql = 'SELECT uc.cid,value,c_title, icon, link, c_permission FROM '.DB_prefix.'user_contact uc, '.DB_prefix.'user_contact_type uct '.
                'WHERE uid='.intval($uid).' AND uc.cid=uct.cid '.
                'ORDER BY c_sort';
        $result['contacts'] = $this->db->select_all($sql);
        $taglib = $this->load_lib('tags', false);
        if ($taglib)
          $result['interests'] = $taglib->get_tags($result['basic']['id'], 1);
        else
          $result['interests'] = false;
      }
    } else {
      $sql = 'SELECT u.* FROM '.DB_prefix.'user u '.
              'WHERE u.id='.intval($uid);
      $result = $this->db->select_row($sql);
    }
    if (!$result && $uid != 1) { // если данные не удалось загрузить, и пользователь не гость (защита от зацикливания)
      $this->load_guest();
    }
    return $result;
  }

  function load_guest() {
    $this->userdata = $this->get_cached('Guest');
    if ($this->userdata === NULL) {
      $this->userdata = $this->load_user(1, 1);
      $this->set_cached('Guest', $this->userdata);
    }
    return $this->userdata; // TODO: проверить, а нужно ли вообще это возвращать?
  }

  /** Установка данных пользователя в $this->userdata и сессию.
   * Именно с этого момента все действия рассматриваются как действия этого
   * пользователя с соответствующими правами доступа * */
  function set_user($userdata, $long = 0) {
    $this->session(); // создаем сессию, если это необходимо
    if ($long) {
      $key = $this->gen_long_key($userdata);
      $period = $this->time + $long * 24 * 60 * 60;
      setcookie(CONFIG_session.'_long', $key, $period, $this->url('/'), false, !empty($_SERVER['HTTPS']), true);
    }
//      unset($userdata['password']);
    $_SESSION['IntB_auth'] = 1; // признак того, что сессия корректно инициализирована
    $_SESSION['IntB_user'] = $userdata; // устанавливаем данные о пользователе
    $_SESSION['IntB_user_agent'] = preg_replace('|\d+|', '', $_SERVER['HTTP_USER_AGENT']);
    $this->userdata = $userdata;
    $this->lastmod = $this->time;
  }

  /**  Устанавливает дату последней модификации страницы, которая будет выдана в  заголовке Last-Modified.
   *   Дата обновляется только в том случае, если вычисленная дата модификации получается новее.
   *   В родительском объекте проверяем только время создания сесии, в наследуемых смотрим также другие параметры (например lastmod форума или темы)
   *   При необходимости можно обновлять lastmod и в других местах.
   *   TODO: подумать, может лучше отказаться от этой процедуры вовсе, а проверки распределить по коду.
   *  */
  function set_lastmod() {
    if (isset($_SESSION['starttime']))
      $this->lastmod = max($this->lastmod, $_SESSION['starttime']);
  }

  /** Предварительная проверка на наличие изменений по сравнению с последним временем запроса страницы.
   * Вполняется до вызова process и может использоваться для быстрого завершения скрипта с минимальной нагрузкой на базу, если новых данных не появилось.
   * */
  function check_modified() {
    return true;
  }

  /** Устанавливает META-теги, необходимые для вывода разметки OpenGraph
   **/
  function set_opengraph() {
  }

  /** Определение заголовка выводимой страницы, если она будет выведена обычным образом (через output). * */
  function set_title() {
    $this->set_opengraph();
    return $this->get_opt('site_title'); // по умолчанию выводим только название сайта, в наследуемых объектах это будет переопределено.
  }

  /** Формирование указателя текущего местоположения. Возврат false означает, что его вообще выводить не надо.
   * Предполагается переопределение в классах-наследниках
   * * */
  function set_location() {
    $start_name = $this->get_opt('site_start');
    if (!$start_name)
      $start_name = $this->get_opt('site_title');
    $result[0] = array($start_name, $this->url('/'));
    return $result;
  }

  /** Определение ссылок на RSS. Возврат false означает, что их нет, иначе можно возвращать массивом
   *
   * @return array Массив ссылок на RSS-ленты для данной страницы.
   */
  function set_rss() {
    return false;
  }

  /** Фиксирует пользователя и его местоположение в списке присутствующих на форуме */
  function fix_online() {
    if ($this->get_opt('online_time') > 0) { // если время присутствия установлено в ноль, фиксация присутствующих на сайте не ведется
      $data['ip'] = $this->get_ip();
      $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; // определяем параметры, которые нужны, чтобы уникально идентифицировать пользователя
      $connection = isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '';
      $enc = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
      $lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
      $charset = isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : '';
      $hash = isset($_COOKIE['IntB_uh']) ? $_COOKIE['IntB_uh'] : md5($data['ip'].$agent.$connection.$enc.$lang.$charset); // если у пользователя в cookie стоит идентификатор, используем его, иначе генерируем новый
      $data['fid'] = isset($this->forum) ? intval($this->forum['id']) : 0; // если загружены данные о текущем разделе, то фиксируем его номер
      $data['tid'] = isset($this->topic) ? intval($this->topic['id']) : 0; // если загружены данные о текущей теме, то фиксируем ее тоже
      $data['visittime'] = $this->time;
      $data['uid'] = $this->get_uid();
      $data['text'] = $this->get_action_name();

      if (!$this->is_guest()) { // если пользователь не гость, то проверяем, является ли он администратором или членом группы, которая входит в команду форума
        if ($this->is_admin())
          $data['type'] = -3; // администратор
        if ($this->get_opt('team', 'group'))
          $data['type'] = -2; // член команды
        else
          $data['type'] = -1; // просто пользователь
        if ($this->get_opt('hidden', 'user'))
          $data['type'] = -128; // если у пользователя включена настройка "скрыть мое присутствие на форуме", то не показывать его
      }
      elseif ($this->bot_id != 0)
        $data['type'] = $this->bot_id; // если пользователя определили как поискового бота, фиксируем его номер
      else
        $data['type'] = 0; // если ничего не помогло, считаем пользователя гостем

      $sql = 'DELETE FROM '.DB_prefix.'online WHERE (hash=\''.$this->db->slashes($hash).'\' AND uid=1)'; // для гостей удаляем тех, у кого совпадает хеш с текущим
      if (!$this->is_guest()) $sql.=' OR uid='.intval($data['uid']); // для залогиненных пользователей удаляем все предыдущие записи вне зависимости от хеша
      $this->db->query($sql);

      $data['hash'] = $hash;
      $this->db->insert_ignore(DB_prefix.'online', $data);

      if (($this->is_guest() && $this->get_opt('enable_log_guests')) ||
              (!$this->is_guest() && $this->get_opt('enable_log_users')))
        $this->log_user($data); //
    }
  }

  /** Получение человекочитаемого описания действия, совершаемого пользователем
   *  По умолчанию возвращает "неописуемое действие", должна переопределяться в наследуемых модулях.
   *  В описании допускается использовать %s для обозначения мест, куда надо вставить ссылку на раздел или тему.
   *  Длина описания не должна превышать 255 символов для корректного сохранения в базе.
   *
   * @param string $action_name Название действия, передаваемое в параметре a скрипту
   * @return string Человекочитаемое описание действия
   */
  function get_action_name() {
    return 'Совершает неописуемое действие';
  }

  /** Шфирование пароля. Будет поддерживаться несколько методов для возможности легкого переноса с других движков.
   * В частности:
   * 1 -- простой хеш MD5 только от пароля
   * 2 -- MD5-хеш от соли+пароля 
   * 3 -- MD5-хеш от пароля+соли
   * 4 -- SHA-2 256 bit от соли+пароля
   * 5 -- SHA-2 256 bit от пароля+соли
   * 6 -- SHA-2 512 bit от соли+пароля
   * 7 -- SHA-2 512 bit от пароля+соли
   * 8 -- использование функции crypt
   * */
  function crypt_password($password, $method, $salt='') {    
    if ($method == 1) return md5($password);
    elseif ($method==2) return md5($salt.$password);
    elseif ($method==3) return md5($password.$salt);
    elseif ($method==4) return hash('sha256',$salt.$password);
    elseif ($method==5) return hash('sha256',$password.$salt);
    elseif ($method==6) return hash('sha512',$salt.$password);
    elseif ($method==7) return hash('sha512',$password.$salt);
    elseif ($method==8) return crypt($password,$salt);
    else
      return $password;
  }

  /** Генерация аутентификационного ключа с привязкой к URL и действию
   * @param $uid integer Идентификатор пользоватля, для которого генерируется ключ * */
  function gen_auth_key($uid=false, $action=false, $url=false) {
    if (!$url)
      $url = substr($_SERVER['REQUEST_URI'], -1, 1) == '/' ? $_SERVER['REQUEST_URI'] : dirname($_SERVER['REQUEST_URI']).'/'; // если url не указан явно, берем его из адреса текущего запроса, отрезая хвостовую часть (в ней хранится действие)
    if (!$action)
      $action = $this->action;
    if (!$uid)
      $uid = $this->get_uid();
    if ($uid <= AUTH_SYSTEM_USERS)
      return '1-='; // для специальных пользователей аутентификация по ключу невозможна
    if (!empty($this->userdata) && $this->userdata['id'] == $uid)
      $userdata = $this->userdata;
    else
      $userdata = $this->load_user($uid, 0);
    $url = str_replace('/./', '/', $url); // для случаев, если раздел является корневым и имеет HURL в виде точки
    $secret = $this->get_opt('site_secret'); // секретный ключ сайта, хранимый в настройках
    return $uid.'-'.md5($uid.$action.$secret.$userdata['rnd'].$url.$userdata['password'].$userdata['pass_crypt'].$userdata['email']);
  }

  /** Генерация ключа для долгосрочной идентификации * */
  function gen_long_key($userdata, $session_name = false) {
    if (!$session_name)
      $session_name = CONFIG_session;
    // TODO: возможно, доделать добавку очищенного User Agent
    return $userdata['id'].'-'.md5($userdata['id'].$userdata['password'].$userdata['rnd'].$userdata['pass_crypt'].$session_name);
  }

  /** Проверка, является ли пользователь гостем.
   * @return boolean TRUE, если пользователь -- гость.
   */
  function is_guest() {
    if (!isset($this->userdata['id']) || $this->userdata['id'] == 1)
      return true;
    else
      return false;
  }

  /** Проверка, есть ли у пользователя права администратора  или founderа* */
  function is_admin($founder = false) {
    $varname = $founder ? 'founder' : 'admin';
    return (!empty($this->userdata[$varname]));
  }

  /** Получение отображаемого имени текущего пользователя
   * @return string Логин пользователя * */
  function get_username() {
    return $this->userdata['display_name'];
  }

  /** Получение логина текущего пользователя
   * @return string Логин пользователя * */
  function get_userlogin() {
    return $this->userdata['login'];
  }

  /** Ссылка на профиль пользователя
   * @param $uid integer — идентификатор пользователя. Если равен нулю, формируется ссылка для текущего пользователя
   */
  function get_user_url($uid=false,$login=false) {
    $current_uid = $this->get_uid();
    if (!$uid) $uid = $current_uid;
    if (!$login && $uid==$current_uid) $login = $this->get_userlogin();
    elseif (!$login) { // если логин не указан, придётся его подгружать
      $userdata=$this->load_user($uid,0);
      if (!$userdata) return false;
      $login = $userdata['basic']['login'];
    }
    if ($uid<=AUTH_SYSTEM_USERS) return false; // у системных пользователей профилей нет
    return $this->http($this->url(sprintf($this->get_opt('user_hurl'),$uid))); // TODO: добавить обработку логина
  }

  /** Проверка наличия прав доступа на выполнение того или иного действия.
   * Проверка производится иерархически, сначала для текущего раздела, потом для родительского, и так до корня форума.
   * Если запрошено действие, которое не предусмотрено в таблице прав доступа, то результат всегда будет FALSE.
   * На данный момент поддерживаются следующие действия: view, read, post, attach, topic, poll, html, vote, rate, nopremod
   * @param $action string Название действия
   * @param $forum integer Идентификатор раздела, для которого проверяются права. Если равен FALSE, то в качестве такового берется текущий раздел
   * @return TRUE, если права на действие имеются.
   * */
  function check_access($action, $forum = false) {
    if ($forum === false)
      $forum = isset($this->forum['id']) ? $this->forum['id'] : 0;
    if ($action !== 'super' && $this->userdata['admin'])
      return true; // если пользователь -- админ, то ему можно все, кроме действий суперпользователя
    if (!empty($this->forum) && $this->forum['id']==$forum && !empty($this->forum['owner']) && $this->forum['owner']==$this->get_uid() && $action!='super' && $action!='html')  // если у раздела есть владелец и этот владелец -- текущий пользователь, то действие разрешаем, если оно не требует прав суперпользователя
      return true;
    if (isset($this->userdata['access'][$forum]) && isset($this->userdata['access'][$forum][$action])) {
      $result = ($this->userdata['access'][$forum][$action] === '1') ? true : false;
    }
    else {
      if ($forum == 0)
        $result = false; // если поднялись до самого корня (общесайтовые права), и нигде данный вид прав не определен, то считаем, что действие запрещено
      else {
        if (isset($this->userdata['access'][$forum]) && isset($this->userdata['access'][$forum]['parent_id'])) // если удалось определить родительский форум, то проверяем права доступа к нему
          $result = $this->check_access($action, $this->userdata['access'][$forum]['parent_id']); // иначе проверяем права для сайта в целом
        else
          $result = $this->check_access($action, 0);
      }
    }
    return $result;
  }

  /** Получение списка разделов, для которых есть права на то или иное действие.
   * Если действие не указано, то под ним подразумевается действие view.
   * Если указано какое-то другое действие, то все равно производится проверка и на наличие прав на view во избежание раскрытия скрытых разделов.
   * @param $access string Название действия
   * @param $mode integer То, в каком режиме выдавать данные о разделах:
   *    0 -- только массив из идентификаторов (для использования в SQL-запросах)
   *    1 -- хеш вида id=>название_раздела (для выдачи selectов)
   *    2 -- хеш вида hurl=>название_раздела (для организации быстрого межфорумного перехода)
   * @param $noflood boolean Выбирать только разделы с признаком noflood
   * @return array Массив идентификаторов разделов, для которых оно разрешено.
   * */
  function get_forum_list($action = false, $mode = 0, $noflood = false) {
    $result = array();
    foreach ($this->userdata['access'] as $curid=> $curforum)
      if ($curid != 0 && (!$noflood || !$curforum['is_flood'])) {
        if ($this->check_access('view', $curid) && ($action == false || $this->check_access($action, $curid))) {
          if ($mode == 1)
            $result[$curid] = $curforum['title'];
          elseif ($mode == 2)
            $result[$this->url($curforum['hurl'])] = $curforum['title'];
          else
            $result[] = $curid;
        }
      }
    return $result;
  }

  // Задел на будущее для перевода
  function lang($text) {
    return call_user_func_array('sprintf',func_get_args());
  }

  /** Преобразует путь относительно корня движка в путь относитльно корня сайта
   *
   * @param string $rel_path Часть пути относительно корня
   * @param boolean $fullpath
   */
  function url($rel_path) {
    /*          if ($this->sitepath==='/') $site_path=''; // чтобы избежать двойного / в пути к корню.
      else $site_path = $this->sitepath;
      if ($rel_path==='/') $rel_path='';
      return $site_path.'/'.$rel_path; */
    if ($rel_path === '/')
      $rel_path = ''; // чтобы не было двойных // в адресе
    return $this->sitepath.$rel_path; // после дорабтки алгоритма в $this->sitepath URL всегда кончается на /
  }

  /** Возвращает полную ссылку вида http://имя_сайта/путь или https://имя_сайта/путь из ссылки относительно корня сайта
   * При включенной опции force_https всегда будет возвращать URL с https
   *
   * @param string $path Путь относительно корня сайта
   * @return string Полная ссылка
   */
  function http($path) {
    $result = $this->get_opt('force_https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
    $result.= $_SERVER['HTTP_HOST'].$path;
    return $result;
  }

  /** Если события включены, возвращает PHP-код, который должен быть выполнен с помощью eval при наступлении этого события.
   *
   * @param string $event Имя события
   * @return string PHP-код настраиваемых обработчиков события
   *
   * Закомментировано, т.к. на данный момент от использования событий через eval решено отказаться в пользу задаваемых библиотек
   */
  /*        function event($event) {
    if (defined('CONFIG_events') && CONFIG_events) {
    $sql = 'SELECT code FROM '.DB_prefix.'event WHERE name="'.$this->db->slashes($event).'" '.
    'AND active="1" ORDER BY sortfield';
    $event_codes = $this->db->select_all_strings($sql);
    $result = join("\n",$event_codes);
    }
    else $result='';
    return $result;
    } */

  /** Получение данных из серверного кеша * */
  function get_cached($cache_id) {
    if (is_object($this->server_cache))
      $result = $this->server_cache->get($cache_id);
    else
      $result = NULL;
    return $result;
  }

  /** Сохранение данных в серверный кеш * */
  function set_cached($cache_id, $data) {
    if (is_object($this->server_cache))
      $this->server_cache->set($cache_id, $data);
  }

  function clear_cached($cache_id) {
    if (is_object($this->server_cache))
      $this->server_cache->clear($cache_id);
  }

  /** Прием сообщения для отправки по Email
   * (сообщение кладется в буфер, реальная отправка происходит только перед самым завершением работы скрипта)
   * Поля структуры maildata:
   *   to -- адрес получателя
   *   from -- адрес отправителя
   *   from_name -- имя отправителя
   *   to_name -- имя получателя
   *   subj -- тема письма
   *   template -- файл шаблона письма, будет включен внутрь общего почтового шаблона template/mail.php
   *   unsubscribe -- ссылка для отписки
   *   data -- данные для обработки письма шаблонизатором
   *   reply -- адрес отправки ответа
   * Используемые настройки (функция get_opt):
   *   email_enabled -- почтовые функции включены
   *   email_from -- адрес отправки по умолчанию
   *   site_title -- название отправителя соответствует названию сайта
   *   email_return -- адрес возврата писем с ошибками
   */
  function mail($maildata) {
    if ($this->get_opt('email_enabled')) {
      if (empty($maildata['from'])) {
        $maildata['from'] = $this->get_opt('email_from');
        $maildata['from_name'] = $this->get_opt('site_title');
      }
      if (empty($maildata['to']))
        trigger_error('Попытка отправки почты без указания адреса получателя!', E_USER_WARNING);
      elseif (empty($maildata['template']))
        trigger_error('Попытка отправки почты без указания шаблона!', E_USER_WARNING);
      else
        $this->mail[] = $maildata;
    }
  }

  /** В случае, если требуется отправка почты (почтовый буфер не пуст), подключает модуль mail и передает ему буфер с почтовыми сообщениями для отправки * */
  function process_mail() {
    if (count($this->mail) > 0) {
      $mailsender = $this->load_lib('mail');
      if (!$mailsender)
        trigger_error('Не удалось загрузить модуль отправки почты! Рассылка не будет произведена', E_USER_WARNING);
      else {
        $outlib = $this->load_lib($this->template_lib, true); // отсутствие парсера должно вызывать фатальный шаблон, поэтому ставим true
        if (!($outlib instanceof iParser))
          trigger_error('Библиотека '.$this->template_lib.' не является парсером!', E_USER_WARNING);
        else {
          $outlib->set_style($this->style);
          for ($i = 0, $count = count($this->mail); $i < $count; $i++) {
            $outlib->set_template($this->mail[$i]['template']);
            $this->mail[$i]['text'] = $outlib->generate_html($this->mail[$i]['data'], true);
          }
          $mailsender->process_mail($this->mail, $this->get_opt('CONFIG_email_return'));
        }
      }
    }
  }

  /** Делает запись в лог с сохранением всех параметров, которые в дальнейшем могут потребоваться для отладки
   *
   * @param string $log -- название лога
   * @param int $code -- код записи
   * @param string $file -- имя файла, в котором произошло логгируемое событие
   * @param string $text -- текст сообщения
   */
  function log_entry($log, $code, $file, $text, $line = '') {
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($referer, 'authkey=') !== false)
      $referer = preg_replace('/authkey=\d+-[0-9a-f]+/', '', $referer); // чистим REFERER и адрес запроса, чтобы в них не попали ключи аутентификации (на случай, если логи окажутся доступными для просмотра)
    if (strpos($uri, 'authkey=') !== false)
      $uri = preg_replace('/authkey=\d+-[0-9a-f]+/', '', $uri);
    $buffer = date('Y-m-d H:i').','.$code.','.str_replace(',', '\\,', $uri).','.
            str_replace(',', '\\,', $referer).','.$file.','.$line.','.
            $_SERVER['REMOTE_ADDR'].','.str_replace(',', '\\,', isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '').
            ','.str_replace(',', '\\,', $text)."\n";
    $filename = BASEDIR.'logs/'.$log.'.csv';
    if ($this->valid_file($log)) {
      $fh = fopen($filename, 'a+');
      if ($fh) {
        fputs($fh, $buffer);
        fclose($fh);
      }
      else
        _dbg('Не удалось сохранить событие в лог '.$log.'.csv!');
    }
    else
      _dbg('Некорректное имя лог-файла '.$log.'.csv!');
  }

  /** Запись в лог информации о действии пользователя в файл logs/visits/ГГГГ-ММ-ДД.csv
   *  Формат лога: время|URL|запрошенное действие|ID пользователя|IP|User Agent|идентифицирующий cookie|HTTP Referer|описание действия
   *  @param array $data -- массив с данными о пользователе, по формату соответствует таблице prefix_online
   * */
  function log_user($data) {
    if ($this->get_opt('enable_log_useragent'))
      $agent = isset($_SERVER['HTTP_USER_AGENT']) ? '"'.str_replace('"', '""', $_SERVER['HTTP_USER_AGENT']).'"' : '';
    else
      $agent = '';
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    if (isset($_SERVER['HTTP_FORWARDED_FOR']))
      $ip.=', '.$_SERVER['HTTP_FORWARDED_FOR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
      $ip.=', '.$_SERVER['HTTP_X_FORWARDED_FOR'];
    $buffer = date('G:i:s,', $this->time + $this->get_opt('timezone_correction'));
    $buffer.=$_SERVER['REQUEST_URI'].','.$this->action.','.$this->get_username().','.$ip.','.$agent.',';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $data['hash'] = substr($data['hash'], 0, 12); // 12 символов будет достаточно для идентификации пользователя, а эффект
    $buffer.=$data['hash'];
    if ($this->get_opt('enable_user_cookies')) {
      setcookie('IntB_uh', $data['hash'], 180 * 60 * 60 * 24, $this->url('/'));
    }
    $buffer.=','.$referer.',';
    if ($this->get_opt('enable_log_action'))
      $buffer.='"'.str_replace('"', '""', $data['text']).'"';
    $buffer.="\r\n";
    $filename = BASEDIR.'logs/visits/'.date('Y-m-d', $this->time + $this->get_opt('timezone_correction')).'.csv';
    file_put_contents($filename, $buffer, FILE_APPEND | LOCK_EX); // во избежание затирания лога пытаемся записать его в эксклюзивном режиме
  }

  /** Проверка, работаем ли мы в режиме Центра Администрирования (нужно для модулей администрирования, которые работают только оттуда).
   * Для основного приложения это утверждение всегда неверно, поэтому возвращаем false.
   * Для собственно модуля администрирования здесь будет дополнительная аутентификация.
   */
  /*        function is_admin_mode() { // закомментировано по причине того, что для обычной работы достаточно is_admin, а в админке будет своя проверка
    return false;
    } */

  /** Добавление META-тега в буфер meta или meta_properties. (Буфер meta выводится в виде тегов с <meta name="">, буфер meta_properties -- в виде <meta property="..">. **/
  function meta($name, $value, $property=false) {
    if ($property) $this->out->meta_properties[$name]=$value;
    else $this->out->meta[$name]=$value;
  }

  /** Добавление тега LINK в буфер meta * */
  function link($href, $rel, $id = false, $type=false) {
    $tag = array('href'=>$href, 'rel'=>$rel);
    if ($type) $tag['type']=$type;
    if ($id) $tag['id'] = $id;
    $this->out->link[] = $tag;
  }
  
  /** Добавление тега SCRIPT в буфер meta * */
  /*        function script($src, $defer=false) {
    $tag['type'] = 'script';
    $tag['src'] = $src;
    if ($defer)  $tag['defer'] = 'defer';
    $this->meta[] = $tag;
    } */

  // TODO: добавить функцию noindex

  /** Проверка, что выполняемый запрос отправлен POST-методом * */
  function is_post() {
    return ($_SERVER['REQUEST_METHOD'] === 'POST');
  }

  /** Определение типа запроса. Возможные варианты:
   * 0 -- типовой запрос, обабатывается обычным шаблонизатором с генерацией вспомогательной информации (главного меню, списка присутствующих и т.п.)
   * 1 -- AJAX-запрос, обабатывается обычным шаблонизатором выдается только внутренняя часть страницы, вспомогательная информация не генерируется
   * 2 -- RSS-поток, вместо шаблонизатора обрабатывается генератором RSS
   * 3 -- зарезервировано для ATOM
   * 4 -- вывод результата как есть, без какой-либо шаблонизации
   * 5-127 -- зарезервированы для будущих версий IntB
   * 128-255 -- могут использоваться сторонними разработчиками для своих нужд
   * */
  function get_request_type() {
    if (isset($_REQUEST['ajax']))
      return 1;
    else
      return 0;
  }

  function get_mime() {
    if ($this->get_request_type() == 2)
      return 'application/xml; charset=utf-8';
    else
      return 'text/html; charset=utf-8';
  }

  /** Определение страницы, на которую следует вернуть пользователя после выполнения следующего шага.
   * Выполняется следующим образом: если в параметрах запроса есть поле referer, адрес берется из него.
   * Если нет, берем поле HTTP_REFERER и проверяем на наличие правильного URL (начинающегося с http:// или https://) и не содержащего символов <,>,",'.
   * Если такие символы отсутствуют (например, некоторые firewallы там пишут BLOCKED BY...), то помещаем туда главную страницу движка.
   */
  function referer() {
    if (isset($_REQUEST['referer']))
      $result = $_REQUEST['referer'];
    elseif (isset($_SERVER['HTTP_REFERER']))
      $result = $_SERVER['HTTP_REFERER']; // если указана явно страница возврата, возвращаемся на нее, иначе -- туда, откуда зашли на текующую страницу
    else
      $result = false;
    $parsed = parse_url($result);
    if (empty($parsed['host'])) $parsed['host']=$_SERVER['HTTP_HOST'];
    if (empty($parsed['scheme'])) $parsed['scheme']='http';
    if ($parsed['scheme']!=='http' && $parsed['scheme']!=='https') $result=false; // если URL не http или https, это может быть попыткой атаки
    if ($parsed['host']!==$_SERVER['HTTP_HOST']) $result=false;
    if (strpos($result, '>') !== false && strpos($result, '<') !== false && strpos($result, '"') !== false && strpos($result, '\'') !== false) $result=false; // если в referer нет подозрительных символов, то используем его, если есть, отправляем пользователя в корень движка во избежание XSS-атак
    if (!$result) $result = $this->http($this->url('')); // если result пуст, генерируем ссылку на главную
    return $result;
  }

  /** Форматирование даты и времени. Ввиду глюков стандартных функций под Windows названия месяцев и дней недели формируются вручную.* */
  function format_date($date, $format,$short=false,$relative=true) {
    $timezone = $this->get_opt('timezone', 'user');
    $date = $date + $timezone;
    $time = $this->time;
    if ($this->get_opt('date_today') && $relative) { // если в настройках включен вывод "вчера" и "сегодня"
      if (gmdate('d m Y', $time + $timezone) === gmdate('d m Y', $date)) { // если выводимая дата -- сегодня
        $format = preg_replace('/^%e|d/', 'Сегодня', $format); // если день выводится в начале строки, то пишем "Сегодня" с заглавной буквы
        $format = preg_replace('/(?<!%)((?:%%)*)%e|d/', 'cегодня', $format); // иначе -- со строчной
        $format = preg_replace('/(?<!%)((?:%%)*)%[dBbYyehmgG]/', '', $format); // блокируем вывод дней, месяцев, года
      }
      if (gmdate('d m Y', $time + $timezone - 24 * 60 * 60) === gmdate('d m Y', $date)) { // если выводимая дата -- вчера
        $format = preg_replace('/^%e|d/', 'Вчера', $format); // если день выводится в начале строки, то пишем "Вчера" с заглавной буквы
        $format = preg_replace('/(?<!%)((?:%%)*)%e|d/', 'вчера', $format); // иначе -- со строчной
        $format = preg_replace('/(?<!%)((?:%%)*)%[dBbYyehmgG]/', '', $format); // блокируем вывод дней, месяцев, года
      }
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
      $format = preg_replace('/(?<!%)((?:%%)*)%e/', '\1%#d', $format); // замена %e на %d# под Windows
    if (strpos($format, '%B') !== false) {
      $month = intval(date('m', $date));
      if ($month == 1)
        $month = 'января';
      elseif ($month == 2)
        $month = 'февраля';
      elseif ($month == 3)
        $month = 'марта';
      elseif ($month == 4)
        $month = 'апреля';
      elseif ($month == 5)
        $month = 'мая';
      elseif ($month == 6)
        $month = 'июня';
      elseif ($month == 7)
        $month = 'июля';
      elseif ($month == 8)
        $month = 'августа';
      elseif ($month == 9)
        $month = 'сентября';
      elseif ($month == 10)
        $month = 'октября';
      elseif ($month == 11)
        $month = 'ноября';
      elseif ($month == 12)
        $month = 'декабря';
      $format = str_replace('%B', $month, $format);
    }
    if (strpos($format, '%b') !== false) {
      $month = intval(date('m', $date));
      if ($month == 1)
        $month = 'янв';
      elseif ($month == 2)
        $month = 'фев';
      elseif ($month == 3)
        $month = 'мар';
      elseif ($month == 4)
        $month = 'апр';
      elseif ($month == 5)
        $month = 'мая';
      elseif ($month == 6)
        $month = 'июн';
      elseif ($month == 7)
        $month = 'июл';
      elseif ($month == 8)
        $month = 'авг';
      elseif ($month == 9)
        $month = 'сен';
      elseif ($month == 10)
        $month = 'окт';
      elseif ($month == 11)
        $month = 'ноя';
      elseif ($month == 12)
        $month = 'дек';
      $format = str_replace('%b', $month, $format);
    }
    if (strpos($format, '%a') !== false) {
      $wday = intval(date('w', $date));
      if ($wday == 1)
        $wday = 'пн';
      elseif ($wday == 2)
        $wday = 'вт';
      elseif ($wday == 3)
        $wday = 'ср';
      elseif ($wday == 4)
        $wday = 'чт';
      elseif ($wday == 5)
        $wday = 'пт';
      elseif ($wday == 6)
        $wday = 'сб';
      elseif ($wday == 0)
        $wday = 'вс';
      $format = str_replace('%a', $wday, $format);
    }
    if (strpos($format, '%A') !== false) {
      $wday = intval(date('w', $date));
      if ($wday == 1)
        $wday = 'понедельник';
      elseif ($wday == 2)
        $wday = 'вторник';
      elseif ($wday == 3)
        $wday = 'среда';
      elseif ($wday == 4)
        $wday = 'четверг';
      elseif ($wday == 5)
        $wday = 'пятница';
      elseif ($wday == 6)
        $wday = 'суббота';
      elseif ($wday == 0)
        $wday = 'воскресенье';
      $format = str_replace('%A', $wday, $format);
    }
    if (class_exists('IntlDateFormatter')) { // проверка нужна для сохранения совместимости со старыми версиями PHP или на случай, если модуля intl нет
      if ($short) {
        $intl_fmt = IntlDateFormatter::MEDIUM;
        if (defined('IntlDateFormatter::RELATIVE_MEDIUM') && $this->get_opt('date_today') && $relative) $intl_fmt = IntlDateFormatter::RELATIVE_MEDIUM;
      }
      else {
        $intl_fmt = IntlDateFormatter::LONG;
        if (defined('IntlDateFormatter::RELATIVE_LONG') && $this->get_opt('date_today') && $relative) $intl_fmt = IntlDateFormatter::RELATIVE_LONG; 
      }
      $formatter = new IntlDateFormatter('ru_RU',$intl_fmt,IntlDateFormatter::SHORT);
      $result = $formatter->format($date); 
    }
    else $result = @strftime($format, $date); 
    $result = preg_replace('|\s+, |', ', ', $result); // выпрявляем запятые, если дата имеет вид "сегодня, 11:40"
    return $result;
  }

  function long_date($date) {
    if (isset($this->userdata['settings']['date_long_format']) && $this->userdata['settings']['date_long_format'])
      $format = $this->userdata['settings']['date_format'];
    else
      $format = "%e %B %Y, %H:%M";
    return $this->format_date($date, $format);
  }

  function short_date($date) {
    if (isset($this->userdata['settings']['date_short_format']) && $this->userdata['settings']['date_short_format'])
      $format = $this->userdata['settings']['date_format'];
    else
      $format = "%e %b %Y, %H:%M";
    return $this->format_date($date, $format,true); // так как пользователь может задавать формат даты, экранирем ее на всякий случай
  }

  /** Преобразует значения типа 8G, 4M в количество байтов (полезно для работы с настройками PHP)
   * @param string $val Строковое значение настройки
   * @return integer Значение настройки в виде количества байтов
  */
  function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = intval($val);
    switch ($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
  }

  /** Функция для склонения числительных. Может принимать произвольное число аргументов,
   * для русского языка требуется три формы склонения. Результат обрабатывается функцией sprintf, числовое значение вставляется вместо %d.
   *
   * @param integer $value Число, для которого требуется подобрать форму склонения
   * @return string Выбранная форма склонения
   */
  function incline($value) {
    if (!$value)
      $value = 0;
    if (($value % 10) == 1 && ($value % 100) != 11)
      $result = func_get_arg(1);
    elseif ($value % 10 > 1 && $value % 10 < 5 && ($value % 100 < 10 || $value % 100 > 20))
      $result = func_get_arg(2);
    else
      $result = func_get_arg(3);
    return sprintf($result, $value);
  }

  /** Получение информации о текущем и всех родительских форумах
   *
   * @param integer $fid Идентификатор форума
   * @param integer $mode Режим получения информации:
   *   1 -- получить все доступные данные о разделе
   *   другое значение -- получить только массив идентификаторов родительских разделов
   * @return array Массив с идентификаторами форума
   * * */
  function get_parent_forums($fid, $mode = 0) {
    if ($mode == 1)
      $fids = array();
    else
      $fids = array($fid);
    while ($fid != 0) {
      if ($mode == 1) {
        $tmp = $this->userdata['access'][$fid];
        $fids[] = $tmp;
        $fid = $tmp['parent_id'];
      }
      else {
        $fid = $this->userdata['access'][$fid]['parent_id'];
        $fids[] = $fid;
      }
    }
    return $fids;
  }

  /** Проверяет, не внесён ли указанный домен в чёрный список
   * @param string $host Имя домена (будет проверяться как с www., так и без) 
   * @return boolean TRUE если домен в чёрном списке
   */
  function is_domain_blacklisted($host) {
    return false; // TODO: доделать!
  }

  /** Функция проверяет имя файла на наличие недопустимых символов и, при необходимости, существование
  *  При $debug==true информация об отсутствии файла сохраняется в отладочную информацию. * */
  function valid_file($filename, $exist = false, $debug = false) {
    $result = (substr($filename, 0, 1) != '/' && substr($filename, 0, 1) != '\\' && substr($filename, 0, 1) != '~');
    if ($result) {
      $test = array('..', '://', '`', '\'', '"', ':', ';', ',', '&', '>', '<');
      for ($i = 0, $count = count($test); $i < $count && $result; $i++)
        $result = (strpos($filename, $test[$i]) === false);
    }
    if ($result && $exist) {
      $result = is_readable($filename);
      if (!$result && $debug)
        _dbg('Файл '.$filename.' не найден или недоступен для чтения!');
    }
    return $result;
  }

  /** Экранирование кавычек в SQL-запросах. * */
  /*    function slashes($sql) {
    return $this->db->slashes($sql); //TODO: сделать использование средств БД для экранирования
    } */

  function shutdown() {
    if (is_object($this->db))
      $this->db->close();
  }

  /** Обработчик ошибок. Выдает статус 500 и выводит дружественное сообщение об ошибке * */
  function error_handler($errno, $errstr, $errfile, $errline) {
    static $callnumber;
    global $app;

    $dbg_on = defined('CONFIG_debug') ? CONFIG_debug : false;
    $debug = debug_backtrace();
    $errstr = str_replace("\t", ' ', $errstr);
    $filemsg = $errno."\t".$errstr."\t".$errline."\t";
    $errmsg = '<p>'.$errstr.' (строка '.$errline.', '.$errfile.', ошибка: '.$errno.')'.'</p><ul style="font-size: 0.9em; color: #600">';

    if ($dbg_on >= 3)
      for ($i = 1, $count = count($debug); $i < $count; $i++) {
        $errmsg.='<li>'.$debug[$i]['function'].'()'.
                ' &mdash; '.((isset($debug[$i]['file'])) ? $debug[$i]['file'] : 'неизвестный файл').', '.
                'строка '.((isset($debug[$i]['line'])) ? $debug[$i]['line'] : 'неизвестна').'</li>'; //.var_dump($debug[$i]['args'])
      }
    $filemsg.=$debug[1]['function'].' '.
            (isset($debug[1]) && (isset($debug[1]['line'])) ? $debug[1]['line'] : 'unknown')."\t".
            (isset($debug[1]) && (isset($debug[1]['file'])) ? $debug[1]['file'] : 'unknown')."\t";
    $filemsg.='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\t".date('r')."\t".$_SERVER['REMOTE_ADDR']."\t";
    $errmsg.='</ul>';

    if (($errno & E_ERROR) || ($errno & E_USER_ERROR)) {
      if (!headers_sent() && $callnumber == 0) {
        $this->log_entry('error', $errno, $errfile, $errstr);
        header($_SERVER['SERVER_PROTOCOL'].' 503 Temporary Unavailable');
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE HTML>
                            <head>
                            <title>Ошибка сайта</title>
                            <meta charset="utf-8"/>
                            </head><body>';
      }
      $callnumber++;

      if (!isset($app->action))
        $app->action = '';
      if (!isset($_SERVER['REQUEST_URI']))
        $_SERVER['REQUEST_URI'] = '';

      $email = CONFIG_email; // EMail для отправки сообщений об ошибке храним в том же конфиг-файле, что и настройки БД, чтобы он был доступен даже в ситуации, когда база не работает
      echo '<div style="font-size: 1em; padding: 4px; font-weight: bold; color: #C44; border: #C00 1px solid; margin: 4px">
                    <p>На сайте произошла ошибка. Попробуйте повторить ваше действие через пару минут. Если ошибка не исчезнет, сообщите о ней администратору сайта по адресу <a href="mailto:'.$email.'">'.$email.'</a></p>
                    <p>В сообщении опишите, в каких случаях возникает ошибка и укажите следующие данные:
                        <ul><li>URL запроса: '.htmlspecialchars($_SERVER['REQUEST_URI']).'</li>
                        <li>Тип запроса: '.htmlspecialchars($_SERVER['REQUEST_METHOD']).'</li>
                        <li>Строка запроса: ?'.htmlspecialchars($_SERVER['QUERY_STRING']).'</li>
                        <li>Запрошенное действие: '.htmlspecialchars($app->action).'</li>';
      if (($errno & E_USER_ERROR) && !$dbg_on) {
        echo '<li>Текст ошибки: '.htmlspecialchars($errmsg).'</li>';
      }
      echo '</ul></p>';
      if ($dbg_on) {
        echo '<p>'.$errmsg.'</p>';
        echo 'Вспомогательная отладочная информация: '.$GLOBALS['IntBF_debug'];
      }
      echo '</div></body></html>';
      exit();
    }
    else {
      if ($dbg_on)
        $this->log_entry('warn', $errno, $errfile, $errstr);
      _dbg(str_replace('<p>', '', str_replace('</p>', '', $errmsg)));
      //echo $errmsg;
    }
  }

}

/** Помещение информации в отладочный массив * */
function _dbg() {
  $dbg_on = defined('CONFIG_debug') ? CONFIG_debug : false;
  if ($dbg_on) {
    if (!isset($GLOBALS['IntBF_debug']))
      $GLOBALS['IntBF_debug'] = '';
    $GLOBALS['IntBF_debug'].='<p>';
    foreach (func_get_args() as $name=> $value) {
      if (is_array($value) || is_object($value))
        $GLOBALS['IntBF_debug'].=$name.': '.nl2br(str_replace('  ', '&nbsp;', htmlspecialchars(print_r($value, true), 0, 'utf-8')));
      else
        $GLOBALS['IntBF_debug'].=$name.': '.htmlspecialchars($value, 0, 'utf-8').' ';
    }
    $GLOBALS['IntBF_debug'].="</p>\n";
  }
}

/* === Интерфейсы замеянемых вспомогательных модулей === */

/** Интерфейс обработчика выходных данных для генерации HTML-кода * */
interface iParser {

  function set_style($stylename);

  function set_template($tmpl);

  function generate_html($data);

  function clear_cache();
}

/** Интерфейс серверной системы кеширования данных * */
interface iCache {

  function get($id);

  function set($id, $data);

  function clear($id);

  function clear_all();
}

/** Интерфейс модуля отправки уведомлений о новых темах, сообщениях и ЛС * */
interface iNotifier {

  function new_post($post, $topic, $forum, $parsed);

  function new_topic($post, $topic, $forum, $parsed);

  function new_pm($thread, $pm, $parsed, $sender, $reply_mail);

  function new_user($udata, $activate_mode);
}

/** Интерфейс модуля входа через социальные сети * */
interface iSocial {

  /** Функция должна возвращать хеш, содержащий следующие поля:
   *  login -- логин или идентификатор пользователя в соцсети (обязательно)
   *  display_name --  обязательно
   *  avatar_url -- ссылка на аватар пользователя
   *  gender -- пол пользователя
   *  birthdate -- дата рождения
   */
  function social_login();
}

/** Интерфейс библиотеки авторизации пользователей через внешние данные (сессию или логин/пароль)
 * Библиотека должна проверять наличие пользователя в базе IntB и, в случае необходимости, создавать его самостоятельно * */
interface iExternalAuth {

  function get_user_by_session(); // получение user_id по данным внешней сессии

  function get_user_by_login($login, $password); // получение user_id по внешнему логину/паролю

  function on_logout(); // обработка выхода с форума (при необходимости также завершает сессию на внешнем сайте)

  function on_register($data, $settings); // обработка регистрации нового пользователя

  function on_profile_update($data, $settings); // обработка редактирования профиля

  function on_profile_delete($uid); // обработка удаления пользователя

  function allow_register(); // проверка, можно ли регистрироваться на форуме напрямую или только через внешний сайт

  function allow_update(); // проверка, можно ли редактировать профиль
}

/** Общий предок для всех классов-библиотек, содержит только общее статическое поле -- ссылку на приложение * */
class Library {

/** @var Application $app **/
  protected static $app;

  static function init(Application $applink) {
    self::$app = $applink;
  }

}

// interface iUser


/** Получение значения из хеша или значения по умолчанию, если в хеше такого ключа не имеется. **/
/* function _dfn($var,$name,$default) {
  return isset($var[$name]) ? $var[$name] : $default;
}*/
