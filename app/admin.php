<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.05
 *  @copyright 2014, 2021-2023 4X_Pro, INTBPRO.RU
 *  @url https://intbpro.ru
 *  Общий класс Центра Администрирования  Intellect Board 3 Pro
 *  ================================ */

class Application_Admin extends Application {
  const LONG_COOKIE_TIME = 14*24*60*60; // срок, на который выставляется администраторское cookie, если включена опция «запомнить меня»
  function init_last_visti() {} // заглушка, в АЦ последний визит не фиксируется
  function init_check_bot() {} // заглушка, боты в АЦ не попадут в любом случае  

  function set_lastmod($newtime=false) {
    $this->lastmod = $this->time; // в АЦ всегда выдаем текущее время в качестве Last-Modified, чтобы избежать проблем с кешированием
  }

  function gen_admin_cookie($udata,$long=false) {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $agent = preg_replace('|\d+|', '', $agent);
    $result=hash('sha256',$udata['id'].$udata['password'].$udata['rnd'].$agent.$_SERVER['REMOTE_ADDR']);
    if ($long) $result='!'.$result;
    return $result;
  }

  /** Переопределяем функцию для дополнительной проверки админского cookie **/
  function test_admin_cookie() {
    $result = parent::is_admin();
    if (!$result) return false;
    $cookie_name=(defined('CONFIG_session')) ?  CONFIG_session.'_a' : 'ib_sid_a';
    if (empty($_COOKIE[$cookie_name])) return false;
    $udata = $this->load_user($this->get_uid(),0);
    $long = ($_COOKIE[$cookie_name][0]==='!');
    if ($_COOKIE[$cookie_name]!==$this->gen_admin_cookie($udata,$long)) return false;
    if ($long) {
      $cookie_time = $this->time + $this::LONG_COOKIE_TIME;
      setcookie($cookie_name, $_COOKIE[$cookie_name], $cookie_time, $this->sitepath.'admin/', false, !empty($_SERVER['HTTPS']), true);
    }
    return true;
  }

  /** Переопределяем fix_online, чтобы заносить в лог администраторские действия. **/
  function fix_online() {
    if ($this->get_opt('enable_log_admins')) { // если фиксация действий администратора включена
      $data['uid']=$this->get_uid();
      $data['text']=$this->get_action_name();
      $data['ip'] = $this->get_ip();
      $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; // определяем параметры, которые нужны, чтобы уникально идентифицировать пользователя
      $connection = isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '';
      $enc = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
      $lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
      $charset = isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : '';
      $data['hash'] = isset($_COOKIE['IntB_uh']) ? $_COOKIE['IntB_uh'] : hash('sha256',$data['ip'].$agent.$connection.$enc.$lang.$charset); // если у пользователя в cookie стоит идентификатор, используем его, иначе генерируем новый
      $this->log_user($data);
    }
  }

  function process() {
    if (!$this->is_post() && file_exists(BASEDIR.'www/install.php')) {
      @unlink(BASEDIR.'www/install.php');
      if (file_exists(BASEDIR.'www/install.php')) $this->message('Для безопасной работы форума необходимо удалить файл www/install.php!',3);
    }
    if (!$this->test_admin_cookie()) $this->action='login';
    if ($this->get_opt('IntB_demo') && isset($_REQUEST['authkey'])) {
      $this->message('Центр Администрирования работает в демо-режиме. Какие-либо изменения в настройках невозможны!',2);
      return 'admin/main.tpl';
    }
    $this->out->authkey=$this->gen_auth_key();
    if ($this->is_post() && $this->action!=='login' &&
      $_POST['authkey']!=$this->out->authkey) $this->output_403('Неправильный ключ аутентификации'); // если некорректно введен ключ аутентификации,
    $result = parent::process();
    return 'admin/'.$result; // все шаблоны хранятся в admin
  }

  function subactions() {
    $this->out->admin_menu = $this->get_menu_items(2); // меню 2 -- администраторское
  }

  function action_login() {
    $uid=$this->get_uid();
    if ($uid<= AUTH_SYSTEM_USERS)  $this->output_403('Для доступа в Центр Администрирования необходимо сначала войти на форум обычным образом!',true);
    if ($this->is_post()) {
      $reg_timeout=$this->get_opt('userlib_login_timeout');
      $antibot=$this->load_lib('antibot',false);
      $result=true;
      if ($reg_timeout && $antibot) { // проверяем, что предыдущая регистрация с этого IP была не менее указанного времени назад, причем делаем это только в том случае, если не было ошибок при валидации пользователя
        if (!$antibot->timeout_check('userlib_login', $reg_timeout)) {
          $this->message('Предыдущая попытка входа была менее чем '.$reg_timeout.' секунд назад',3);
          $result = false;
        }
      }

      if ($result) {
        $userdata=$this->load_user($uid,1); // пользователь должен уже был быть залогинен на форуме
        if (!$userdata['admin']) {
          $this->log_entry('user', 7, 'admin/login.php', 'Попытка входа пользователя ' . $login . ' в Центр Администрирования без администраторских прав!' );
          $this->output_403('Вы не являетесь администратором форума!');
        }

        $crypted_password = $this->crypt_password($_POST['password'],$userdata['pass_crypt'],$userdata['rnd']);
        if ($userdata['password']!=$crypted_password) {
          if ($this->get_opt('userlib_logs')>4)  $this->log_entry('user', 7, 'admin/login.php', 'Неудачная попытка входа пользователя ' . $login . ' в Центр Администрирования!' );
          $this->message('Неправильный пароль!',3);
          $result = false;
        }
        else {
          $cookie_name=(defined('CONFIG_session')) ?  CONFIG_session.'_a' : 'ib_sid_a';
          $cookie_time = !empty($_POST['long']) ? $this->time+$this::LONG_COOKIE_TIME : false;

          $path = $this->sitepath;
          setcookie($cookie_name,$this->gen_admin_cookie($userdata,!empty($_POST['long'])),$cookie_time,$path.'admin/',false,!empty($_SERVER['HTTPS']),true);
          $this->redirect(substr($_SERVER['REQUEST_URI'],strlen($path)));
        }
      }
    }
    $this->out->referer = $this->referer();
    return 'login.tpl';
  }

  function action_logout() {
    $path = $this->sitepath;
    $cookie_name=(defined('CONFIG_session')) ?  CONFIG_session.'_a' : 'ib_sid_a';
    setcookie($cookie_name,'',-1,$path.'admin/',false,!empty($_SERVER['HTTPS']),true);
    $this->redirect('/');
  }

  /** Сброс данных, закешированных в пользовательской сессии.
  * Необходимо производить при изменении прав доступа, чтобы права вступали в силу сразу, без необходимости перелогиниваться **/
  function reset_session_cache() {
    // Код сброса кеша вынесен в библиотеку warning, т.к. он также требуется и при вынесении предупреждений пользователям
    $warnlib = $this->load_lib('warning',true);
    /* @var $warnlib Library_warning */
    $warnlib->reset_session_cache();
  }

  /** Редирект через тег META. Используется для длительных операций типа администраторской рассылки **/
  function meta_redirect($url,$message) {
    $this->out->meta_redirect = $url;
    $this->out->meta_messsage = $message;
  }

  /** Список полей доступа **/
  function get_access_fields() {
    return array('view','read','post','attach','topic','poll','html','vote','rate','edit','nopremod');
  }
}
