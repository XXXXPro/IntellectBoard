<?php 

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.10
 *  @copyright 2021, 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Модуль работы с протоколом OAuth
 *  ================================ */

 class oauth extends Application {

  /** Убирает www из начала имени хоста, если нужно
   * @param $host string Имя хоста
   * @return string Имя хоста без www
   */
  function cut_www($host) {
    if (substr($host,0,4)==='www.') return substr($host,4);
    else return $host;
  }

  function get_request_type() {
    if ($this->action=='authorization_endpoint' && isset($_POST['code'])) return 4;
    elseif ($this->action=='token_endpoint') return 4;
    else return parent::get_request_type();
  }

  function get_mime() {
    if ($this->action=='authorization_endpoint' && isset($_POST['code'])) return strpos($_SERVER['HTTP_ACCEPT'],'application/json')!==false ? 'application/json' : 'application/x-www-form-urlencoded';
    elseif ($this->action=='tokem_endpoint') return strpos($_SERVER['HTTP_ACCEPT'],'application/json')!==false ? 'application/json' : 'application/x-www-form-urlencoded';
    else return parent::get_mime();
  }

  function action_authorization_endpoint() {
    if (!$this->get_opt('oauth_server_enable')) $this->output_403('Использование сервера авторизации запрещено настройками форума.');
    if (isset($_REQUEST['redirect_uri']) && isset($_REQUEST['client_id']) && ((isset($_REQUEST['me']) && isset($_REQUEST['state'])) || isset($_POST['code']))) {
      /** @var Library_userlib **/
      $userlib = $this->load_lib('userlib',true);
      $client_id=strtolower($_REQUEST['client_id']); // приводим все URL к нижнему регистру
      $redirect_uri = strtolower($_REQUEST['redirect_uri']);
      if (!isset($_POST['code'])) {
        if ($this->is_guest()) $this->output_403($this->lang('Вам необходимо войти на сайт, чтобы использовать его для авторизации на сторонних ресурсах.'),true);
        $this->out->homepage = $userlib->get_homepage(); // проверяем, есть ли у пользователя раздел с домашней страницей
        if (!$this->out->homepage && $this->get_opt('oauth_server_enable')==2) $this->output_403($this->lang('Использовать данный сайт для авторизации могут только пользователи с разделом типа «Домашняя страница»!'));
        $client_host = parse_url($client_id,PHP_URL_HOST);
        $redirect_host = parse_url($redirect_uri,PHP_URL_HOST);
        if ($client_host!==$redirect_host) {
          // TODO: добавить запрос страницы для проверки допустимости URL
          $this->output_400($this->lang('Адрес редиректа находится не на хосте клиента! Авторизация невозможна.'),'invalid_client');
        }
        $me = strtolower($_REQUEST['me']);
        if (!$me || !empty($me['user']) || !empty($me['pass']) || !empty($me['fragment'])) $this->output_400($this->lang('Некорректный URL пользователя.'),'invalid_client');
        $me_data = parse_url($me);
        $my_host = $this->cut_www($_SERVER['HTTP_HOST']);
        if ($this->cut_www($me_data['host'])!==$my_host) $this->output_400($this->lang('Некорректный адрес сайта!'),'unauthorized_client');
      }
      if ($this->is_post() && isset($_POST['code'])) { // POST-запрос от клиента для верификации кода
        $sql = 'SELECT me FROM '.DB_prefix.'oauth_code WHERE code=? AND redirect_uri = ? AND client_id = ? AND expires>= ?';
        $result = $this->db->select_str($sql,array($_POST['code'],$redirect_uri,$client_id,$this->time));
        if ($result) {
          $data['me']=$result;
          $sql = 'DELETE FROM '.DB_prefix.'oauth_code WHERE code=? AND redirect_uri = ? AND client_id = ?';
          $this->db->query($sql,false,array($_POST['code'],$redirect_uri,$client_id,$this->time));
          return json_encode($data);
        }
        else $this->output_400($this->lang('Недействительный код авторизации'),'invalid_code');
      }
      elseif ($this->is_post() && isset($_POST['confirm'])) { // пользователь подтвердил вход, делаем редирект на redirect_uri
        $this->session();
        if ($_SESSION['oauth']['redirect_uri']!=$redirect_uri || $_SESSION['oauth']['state']!=$_POST['state']) $this->output_400($this->lang('Попытка исказить параметры авторизации!'),'server_error');
        if ($_SESSION['oauth']['csrf']!=$_POST['csrf']) $this->output_403($this->lang('Неверный CSRF-токен!'));
        $code = hash('sha256',mt_rand().$client_id.mt_rand().$_POST['state'].mt_rand().$this->get_opt('site_secret').mt_rand().strrev('site_secret').mt_rand(),false); // авторизационный код будет в hex
        $data['code']=$code;
        $data['client_id']=$client_id;
        $data['uid']=$this->get_uid();
        $data['redirect_uri']=$redirect_uri;
        $data['expires']=$this->time+3600; // рекомендуемое время жизни кода
        $data['me'] = $_SESSION['oauth']['me']; // URL, на который делается авторизация
        $this->db->insert_ignore(DB_prefix.'oauth_code',$data);
        $location=$redirect_uri.'?state='.urlencode($_SESSION['oauth']['state']).'&code='.$code;
        unset($_SESSION['oauth']); // очищаем данные сессии, связанные с OAuth
        $this->redirect($location,false);
      }
      else { // GET-запрос для получения кода, показываем пользователю страницу подтверждения и запоминаем данные в сессию
        $this->out->oauth['client_id']=$client_id;
        $this->out->oauth['redirect_uri']=$redirect_uri;
        $this->out->oauth['state']=$_GET['state'];

        $this->session();
        $_SESSION['oauth']['redirect_uri']=$redirect_uri;
        $_SESSION['oauth']['state']=$_GET['state'];
        $path = str_replace($this->sitepath,'',$me_data['path']); // удаляем начальную часть URL
        if (substr($path,-1,1)=='/') $path=substr($path,0,-1);
        if ($path=='') $path='/'; // на случай, если корневым разделом форума является личный раздел
        $sql = 'SELECT owner FROM '.DB_prefix.'forum WHERE hurl=?';
        $owner = $this->db->select_int($sql,array($path));
        if ($owner && $owner==$this->get_uid()) { // если URL указанного раздела существует и его владельцем является текущий пользователь, авторизуем с запрошенным адресом
          $me = $this->http($this->url($path));
          if ($path!='/') $me.='/'; // добавляем / в конец, если URL — не корень сайта
          $this->out->mode=2;
        }
        else { // если нет, то используем профиль пользователя для его авторизации
          if ($this->get_opt('oauth_server_enable')==2) $this->output_403($this->lang('Использовать данный сайт для авторизации могут только пользователи, у которых есть личные разделы!'));
          $me = $this->get_user_url(); 
          $this->out->mode=1;
        }
        $_SESSION['oauth']['me']=$me;
        $this->out->me=$me;
        $this->out->scope = isset($_GET['scope']) ? $_GET['scope'] : '';
        $_SESSION['oauth']['csrf']=substr(hash('sha256',mt_rand().mt_rand().$this->get_opt('site_secret').mt_rand(),false),0,24); // false — возвращать данные в виде hex
        $this->out->oauth['csrf'] = $_SESSION['oauth']['csrf'];
      }
    }
    else {
      $this->output_400($this->lang('Некорректно составлен запрос, отсутствуют ключевые параметры: me, redirect_uri, client_id, state'),'parameter_absent');
    }
  }

  function action_token_endpoint() {
    file_put_contents(BASEDIR.'tmp/token.log',print_r($_REQUEST,true));
    if (!$this->get_opt('oauth_server_enable')) $this->output_403('Использование сервера авторизации запрещено настройками форума.');
    if (isset($_POST['code']) && isset($_POST['client_id']) && isset($_POST['redirect_uri'])) {
      /** @var Library_userlib **/
      $userlib = $this->load_lib('userlib',true);
      
      $client_id=strtolower($_POST['client_id']); // приводим все URL к нижнему регистру
      $redirect_uri = strtolower($_POST['redirect_uri']);
      
      // необходимые проверки (подумать, нужны ли они повторно, если уже были сделаны на предыдущем шаге)
      $client_host = parse_url($client_id,PHP_URL_HOST);
      $redirect_host = parse_url($redirect_uri,PHP_URL_HOST);
      if ($client_host!==$redirect_host) {
        // TODO: добавить запрос страницы для проверки допустимости URL
        $this->output_400($this->lang('Адрес редиректа находится не на хосте клиента! Авторизация невозможна.'),'invalid_client');
      }

      // проверяем код и извлекаем из базы данные о пользователе и scope, на которые тот был выдан
      $sql = 'SELECT uid, scope, me FROM '.DB_prefix.'oauth_code WHERE code=? AND redirect_uri = ? AND client_id = ? AND expires>= ?';
      $result = $this->db->select_row($sql,array($_POST['code'],$redirect_uri,$client_id,$this->time));
      
      if (!empty($result)) {
        $token = str_replace('=','',base64_encode(hash('sha256',mt_rand().$client_id.mt_rand().$this->get_opt('site_secret').mt_rand().strrev('site_secret').mt_rand(),true))); // авторизационный код будет в hex

        // TODO: сохранение tokenа
        $data['token']=$token;
        $data['scope']=$result['scope'];
        $data['uid']=$result['uid'];
        $data['client_id']=$client_id;
        $data['expires']=$this->time+365*24*3600; // токен живёт целый год

        $this->db->insert_ignore(DB_prefix.'oauth_token',$data);
        $sql = 'DELETE FROM '.DB_prefix.'oauth_code WHERE code=?';
        $this->db->query($sql,false,array($_POST['code']));
        if (!$result['scope']) $result['scope']='profile topic';
        
        $output_array = array('access_token'=>$token,'me'=>$result['me'],"scope"=>$result['scope']);
        if (strpos($_SERVER['HTTP_ACCEPT'],'application/json')!==false) $output = json_encode($output_array);
        $output = http_build_query($output_array);
        return $output;
      }
      else $this->output_400($this->lang('Недействительный код авторизации!'),'invalid_code'); // если 
    }
    else {
      $this->output_400($this->lang('Некорректно составлен запрос, отсутствуют ключевые параметры: redirect_uri, client_id, state'),'parameter_absent');
    }
  }

  function set_title() {
    if ($this->action=='authorization_endpoint') return $this->lang('Авторизация на сайте %s',$_REQUEST['client_id']);
    else return parent::set_title();
  }  

  function set_location() {
    $result[0] = array($this->get_opt('site_title'), $this->url('/'));
    $result[1] = array($this->lang('Подтверждение авторизации'));
    return $result;
  }
 }