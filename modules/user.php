<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.02
 *  @copyright 2007,2009-2011, 2018 4X_Pro, INTBPRO.RU
 *  http://intbpro.ru
 *  Модуль профиля пользователя (регистрация/редактирование/вход/активация и т.п.)
 *  ================================ */

class user extends Application {
  function action_register() {
    if ($ext_lib_name = $this->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
       $ext_lib = $this->load_lib($ext_lib_name,false); // загружаем ее
       if ($ext_lib && !$ext_lib->allow_register()) $this->output_403('Прямая регистрация через форум запрещена. Зарегистрируйтесь через основной сайт.');
    }
    if (!isset($_REQUEST['accepted']) || $_REQUEST['accepted']!=1) { // если пользователь не принял правила, выводим их ему
      $this->out->rules = $this->get_text(0, 0); // получаем текст общих правил форума
      $this->out->referer = $this->referer();
      return 'user/rules.tpl';
    }
    $userlib = $this->load_lib('userlib',true);
    if ($this->get_opt('captcha')) $antibot = $this->load_lib('antibot');
    if ($this->is_post()) {
      $data=$_POST;
      if (empty($data['basic']['display_name'])) $data['basic']['display_name']=$data['basic']['login'];
      $errors=array();
      if ($data['basic']['password']!=$_POST['password_confirm']) $errors[]=array('text'=>'Пароль не совпадает с подтверждением!','level'=>3);
      if ($antibot && !$antibot->captcha_check()) $errors[]=array('text'=>'Неправильно введен проверочный код!','level'=>3);
      // проверка ответа на контрольный вопрос для регистрации, если таковой задан
      $question = $this->get_opt('userlib_reg_question');
      if ($question) { // если вопрос
        $answers = $this->get_opt('userlib_reg_answers');
        $answers = explode(',',$answers);
        for ($i=0,$count=count($answers);$i<$count;$i++) $answers[$i]=trim(strtolower($answers[$i]));
        if (count($answers)>0 && !in_array(trim(strtolower($_POST['answer'])), $answers)) $errors[]=array('text'=>'Неправильный ответ на проверочный вопрос!','level'=>3);
      }
      if (!empty($errors)) $this->message($errors);
      else {
        $result=$userlib->register_user($data['basic'],$data['settings']);
        if ($result) {
          $activate=$this->get_opt('userlib_activation');
          if ($activate==0) {
            $userlib->do_login($data['basic']['login'],$_POST['password_confirm']); // если активации пользователя не требуется, входим на форум сразу
            $this->output_msg($this->referer(), 'Вы успешно зарегистрированы!','Вернуться к предыдущей странице');
          }
          elseif ($activate==1) {
            $this->message('Вы зарегистрировались на форуме, но для дальнейших действий вам необходимо активировать вашу учетную запись. На указанный почтовый ящик выслано письмо со ссылкой для активации. <br />Если в течение 10 минут вы не получите письма, попробуйте <a href="change_email.htm">указать другой адрес EMail</a>.');
            $this->output_msg(false, 'Вы успешно зарегистрированы!');
          }
          elseif ($activate==2) {
            $this->message('Вы зарегистрировались на форуме, но вам необходимо дождаться, когда администратор активирует вашу учетную запись. Когда это произойдет, вам будет выслано уведомление на EMail.');
            $this->output_msg(false, 'Вы успешно зарегистрированы!');
          }
        }
      }
    }
    else {
      $data=$this->load_user(3,2); // загрузка профиля NewUser со всеми данными, включая настройки
      $data['basic']['login']='';
      $data['basic']['password']='';
      $data['basic']['email']='';
      $data['password_confirm']='';
    }
    if ($this->get_opt('captcha')) $antibot->captcha_generate();
    $this->out->referer = $this->referer();
    $this->out->timezones = Library_userlib::$timezones;
    $this->out->formdata = $data;
  }

  function action_login() {
    if ($this->is_post()) {
      $userlib = $this->load_lib('userlib',true);
      $long = isset($_POST['long']) ? 90 : 0; // если пользователь выбрал "запомнить", запоминаем его на 90 дней
      if ($userlib->do_login($_POST['login'],$_POST['password'],$long)) {
        $referer = $this->referer();
        if (isset($_POST['referer']) && empty($_POST['referer'])) $referer=$this->url('/'); // если на шаге перед логином REFERER был пуст и не сохранился в форме, отправим пользователя на главную
        $this->output_msg($referer,'Вы успешно вошли на форум!','Вернуться к предыдущей странице');
      }
      else $this->message('Ошибка: неправильный логин или пароль',3);
    }
    $this->out->referer = $this->referer();
  }

  function action_social_login() {
    $libname = $this->get_opt('site_social_lib');
    if (!$libname) $this->output_403('На данном сайте выключен или не настроен вход через социальные сети',true);
    $soclib = $this->load_lib($libname,true);
    $this->lastmod=$this->time;
    $userdata = $soclib->social_login();
    if (empty($userdata) || !empty($userdata['error']) || empty($userdata['contact_type'])) {
      if (empty($userdata)) $this->message('Непредвиденная ошибка авторизации через социальную сеть!',2);
      elseif (empty($userdata['contact_type'])) $this->message('Не определен тип контакта!',2);
      else $this->message(htmlspecialchars($userdata['error']),3);
      $this->redirect($this->referer());
    }
    $sql = 'SELECT uc.uid FROM '.DB_prefix.'user_contact uc, '.DB_prefix.'user_contact_type uct '.
    'WHERE uc.value=\''.$this->db->slashes($userdata['contact_id']).'\' AND uc.cid=uct.cid AND uct.c_name=\''.$this->db->slashes($userdata['contact_type']).'\'';
    $uid = $this->db->select_int($sql);
    if (!$uid) { // если пользователь не найден, начинаем процедуру регистрации
      $cid = false;
      if (!empty($userdata['contact_type'])) {
        $sql = 'SELECT cid FROM '.DB_prefix.'user_contact_type uct '.
        'WHERE uct.c_name=\''.$this->db->slashes($userdata['contact_type']).'\'';
        $cid = $this->db->select_int($sql);
      }
      if (!$cid) $this->output_403('К сожалению, данный провайдер авторизации не поддерживается настройками форума! Попробуйте воспользоваться другим или зарегистрироваться обычным образом.',true);
      // если соответствующий тип контактов поддерживается форумом (прописан в user_contact_type), отправляем пользователя на форму регистрации
      $userdata['contacts'][1]=array('cid'=>$cid,'value'=>$userdata['contact_id']);
      $this->session();
      $_SESSION['social_data']=$userdata;
      $this->redirect($this->http($this->url('user/social_register.htm?referer='.urlencode($this->referer()))));
    }
    else {
      $userlib = $this->load_lib('userlib',true);
      $userlib->force_login($uid);
      $this->message('Вы успешно вошли на форум!',1);
      $this->redirect($this->referer());
    }
  }

  function action_social_register() {
    $this->session();
    if ($ext_lib_name = $this->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
      $ext_lib = $this->load_lib($ext_lib_name,false); // загружаем ее
    }
    else $ext_lib=false;
    if ($ext_lib && !$ext_lib->allow_register()) $this->output_403('Прямая регистрация через форум запрещена. Зарегистрируйтесь через основной сайт.');
    if (empty($_SESSION['social_data'])) $this->output_403('Отсутствуют данные социальной авторизации! Попробуйте авторизоваться еще раз или воспользуйтесь обычной регистрацией.',true);

    $userlib = $this->load_lib('userlib',true);
    $errors = $userlib->validate_user($_SESSION['social_data']['basic'],true); // true означает валидацию с учетом специфики социальных сетей, в частности, отсутствие проверки на пустой пароль
    if (!empty($errors)) {
      $this->message($errors);
      $this->output_403('При авторизации возникли ошибки, воспользуйтесь другим провайдером авторизации или зарегистрируйтесь обычным образом');
    }

    if (!empty($_REQUEST['accepted'])) {
      $result = $userlib->register_user($_SESSION['social_data']['basic'],array(),$_SESSION['social_data']['contacts'],true,$_SESSION['social_data']['confirmed']);
      if ($result) {
        // подгрузка аватара
        $misclib = $this->load_lib('misc',false);
        $imglib = $this->load_lib('image',false);
        if ($_SESSION['social_data']['avatar_url'] && $misclib && $imglib) {
          $avatar_local = BASEDIR.'www/f/av/'.$_SESSION['social_data']['basic']['id'];
          $test = $misclib->download_file($_SESSION['social_data']['avatar_url'],$avatar_local.'.tmp');
          $avatar = $imglib->load($avatar_local.'.tmp');
          if (!empty($avatar)) {
           // TODO: возможно, сделать в userlib отдельную функцию по обработке аватара, чтобы не приходилось дублировать код
            $max_av_x = $this->get_opt('userlib_avatar_x');
            if (!$max_av_x) $max_av_x = 120;
            $max_av_y = $this->get_opt('userlib_avatar_y');
            if (!$max_av_y) $max_av_y = 120;
            if ($avatar['type']==IMAGETYPE_BMP) $type=IMAGETYPE_PNG;
            else $type=$avatar['type'];
            if ($type==IMAGETYPE_JPEG) { // если загрузили JPEG, то обрабатываем его с качеством, заданным в настройках
              $options = $this->get_opt('userlib_avatar_jpeg_qty');
              if (!$options) $options=90;
            }
            $ext = $imglib->get_extension($type);
            $up_avatar = $imglib->save_fit_to($avatar,$max_av_x,$max_av_y,$avatar_local.'.'.$ext,$type,$options);
            if ($up_avatar) {
              $userlib->update_avatar($_SESSION['social_data']['basic']['id'],$ext);
            }
          }
          unlink($avatar_local.'.tmp');
        }

        $activate=$this->get_opt('userlib_activation');
        if ($activate==0 || ($activate==1 && $_SESSION['social_data']['confirmed'])) {
          $userlib->force_login($_SESSION['social_data']['basic']['id']);
          $this->message('Вы успешно зарегистрированы!',1);
          $this->redirect($this->referer());
        }
        elseif ($activate==1) {
          $this->message('Вы зарегистрировались на форуме, но для дальнейших действий вам необходимо активировать вашу учетную запись. На указанный почтовый ящик выслано письмо со ссылкой для активации. <br />Если в течение 10 минут вы не получите письма, попробуйте <a href="change_email.htm">указать другой адрес EMail</a>.');
          $this->output_msg(false, 'Вы успешно зарегистрированы!');
        }
        elseif ($activate==2) {
          $this->message('Вы зарегистрировались на форуме, но вам необходимо дождаться, когда администратор активирует вашу учетную запись. Когда это произойдет, вам будет выслано уведомление на EMail.');
          $this->output_msg(false, 'Вы успешно зарегистрированы!');
        }
        unset($_SESSION['social_data']); // удаляем данные соцсети из сессии, так как они больше не нужны
      }
    }
    else {
      $this->out->rules = $this->get_text(0, 0); // получаем текст общих правил форума
      $this->out->social_login = $_SESSION['social_data']['basic']['login'];
      $this->out->social_name = $_SESSION['social_data']['basic']['display_name'];
      $this->out->social_email = $_SESSION['social_data']['basic']['email'];
      $this->out->referer = $this->referer();
      return 'user/rules.tpl';
    }
  }

  function action_logout() {
    $userlib = $this->load_lib('userlib');
    $userlib->do_logout();
    $this->output_msg($this->referer(),'Вы вышли с форума!','Вернуться к предыдущей странице');
  }

  function action_update() {
    if ($this->get_uid()<=AUTH_SYSTEM_USERS) $this->output_403('Нельзя редактировать профили гостя или служебных пользователей!',true);
    if ($ext_lib_name = $this->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
      $ext_lib = $this->load_lib($ext_lib_name,false); // загружаем ее
      if ($ext_lib && !$ext_lib->allow_update()) $this->output_403('Прямое редактирование профиля через форум запрещено!');
    }
    $userlib = $this->load_lib('userlib');
    $this->out->allow_template = $this->get_opt('userlib_allow_template');
    if ($this->out->allow_template) {
      $templatelib = $this->load_lib('template');
      /* @var $templatelib Library_template */
      if ($templatelib) $this->out->user_templates = array(''=>'Стиль сайта по умолчанию')+$templatelib->get_list($this->is_admin()); // если пользователь -- админ, он может выбрать любой шаблон, иначе -- только незаблокированные
    }
    // список вкладок профиля
    $profile_tabs = preg_replace('|\s+,|',',',$this->get_opt('userlib_profile_tabs')); //
    if (empty($profile_tabs)) $profile_tabs = 'basic,signature,avatar,bio,settings,notify,contacts';
    $this->out->profile_tabs = explode(',',$profile_tabs);

    if ($this->is_post()) {
      $data=$_POST;
      if (!$this->is_admin() || empty($data['basic']['login'])) $data['basic']['login']=$this->get_userlogin(); // если пользователь не админ, менять логин он не может
      if (empty($data['basic']['display_name'])) $data['basic']['display_name']=$this->get_username();
      if (empty($data['basic']['email'])) {
        $olddata = $this->load_user($this->get_uid(),0);
        $data['basic']['email']=$olddata['email'];
      }
      $errors=array();
      if ($data['basic']['password']!=$_POST['password_confirm']) $errors[]=array('text'=>'Пароль не совпадает с подтверждением!','level'=>3);
      if (!empty($errors)) $this->message($errors);
      else {
        $settings = isset($data['settings']) ? $data['settings'] : false;
        $contacts = isset($data['contacts']) ? $data['contacts'] : false;
        $interests = isset($data['interests_str']) ? $data['interests_str'] : false;
        $result=$userlib->update_user($data['basic'],$settings,$contacts,$interests); // валидация делается внутри процедуры update
        $this->set_user($this->load_user($this->get_uid(),1),isset($_COOKIE[CONFIG_session.'_long'])); // обновляем данные в сессии
        $_SESSION['starttime']=$this->time; // обновляем время создания сессии, чтобы пользователю не выдавались кешированные данные
        if ($result) {
          $this->output_msg($this->referer(), 'Ваш профиль отредактирован!','Перейти к предыдущей странице');
        }
      }
    }
    else {
      $data=$this->load_user($this->get_uid(),2); // загрузка профиля пользователя со всеми данными, включая настройки
      $data['basic']['password']='';
      $data['password_confirm']='';
      $data['interests_str']=is_array($data['interests']) ? join(', ',$data['interests']) : '';
      for ($i=0; $i<3; $i++) $data['contacts'][]=array('cid'=>0,'value'=>'');
    }
    $sql = 'SELECT cid,c_title FROM '.DB_prefix.'user_contact_type ORDER BY c_sort';
    $this->out->contact_types = $this->db->select_simple_hash($sql);
    $this->out->contact_types=array('0'=>'Нет')+$this->out->contact_types; // добавляем элемент "Нет", чтобы можно было удалить ненужный контакт
    $this->out->formdata = $data;
    $this->out->timezones = Library_userlib::$timezones;
    $this->out->is_admin = $this->is_admin();
    $this->out->referer = $this->referer();
  }

/** Активация пользователя по ссылке, отправленной на EMail **/
  function action_activate() {
    $userlib = $this->load_lib('userlib');
    if (!isset($_REQUEST['authkey'])) {
      $this->output_403('Смена пароля может осуществляться только по ключу, присланному в письме!');
    }
    $activate_mode=$this->get_opt('userlib_activation');
    if ($activate_mode>1) {
      $this->output_403('На данном форуме активация осуществляется администратором! Вы не можете сделать ее самостоятельно');
    }

    $uid = $this->get_uid();
    if ($uid <= AUTH_SYSTEM_USERS) { // проверяем, что номер пользователя больше максимального номера системного пользователя
      $this->output_403('Нельзя активировать системные учетные записи');
    }

    $data=$this->load_user($uid,0); // если ошибок не возникло, загружаем профиль пользователя
    if ($data['status']!=1) {
      $this->output_403('Пользователь уже активирован или забанен. Повторная активация невозможна!');
    }
    $data['status']=0; // и меняем ему статус на "активирован"
    unset($data['password']); // чтобы не сбрасывался пароль пользователя при включенном шифровании
    $result=$userlib->update_user($data,false);
    if ($result) {
      if ($this->get_opt('userlib_logs')>1) $this->log_entry ('user', 3, 'user.php', 'Профиль пользователя '.$data['login'].' активирован.');
      $this->output_msg('login.htm', 'Пользователь активирован! Теперь вы можете войти на форум.','Перейти на страницу входа');
    }
    else $this->output_msg(false, 'Ошибка активации профиля!');
  }

  function action_forgot() {
    $userlib = $this->load_lib('userlib');
    $antibot = $this->load_lib('antibot');
    if ($this->is_post()) {
      if ($antibot && $antibot->captcha_check()) { // если проверка CAPTCHA прошла нормально
      if ($_POST['login']) $uid = $userlib->get_uid_by_name($_POST['login'],false);
      elseif ($_POST['email']) $uid = $userlib->get_uid_by_email($_POST['email']);
      else $uid = false;

      if ($uid==false) {
        $this->message('Не найден пользователь с указанным логином или адресом EMail', 3);
      }
      else {
        $userdata = $this->load_user($uid,0);
        $newpasskey = $this->gen_auth_key($uid, 'change'); // генерируем ссылку-ключ, по которой пользователь сможет сменить пароль при выполнении действия change
        $mdata['keylink'] = $this->http(dirname($_SERVER['REQUEST_URI']).'/change.htm?authkey='.$newpasskey);
        $mdata['change_login'] = $userdata['login'];
        $mdata['ip']=$_SERVER['REMOTE_ADDR'];

        $maildata['subj']='Восстановление забытого пароля';
        $maildata['to']=$userdata['email'];
        $maildata['to_name']=$userdata['login'];
        $maildata['template']='user/mail_forgot.tpl';
        $maildata['data']=$mdata;
        $maildata['html']=true;
        $this->mail($maildata);
        $this->message('Ссылка для сброса пароля отправлена на EMail, указанный в профиле пользователя.');
        if ($this->get_opt('userlib_logs')>1) $this->log_entry ('user', 4, 'user.php', 'Пользователь '.$_POST['login'].' запросил высылку пароля на Email.');
        $this->output_msg(false,'Ссылка отправлена!');
      }
      }
      else {
        $this->message('Неверно введен проверочный код!', 3);
      }
    }
    if ($antibot) $antibot->captcha_generate();
  }

/** Действие по изменению пароля по ссылке в письме, отправляемом при действии "забытый пароль"
 * */
  function action_change() {
    $userlib = $this->load_lib('userlib');

    if (!isset($_REQUEST['authkey'])) {
      $this->output_403('Смена пароля может осуществляться только по ключу, присланному в письме!');
    }
    $uid=$this->get_uid();
    if ($uid<=AUTH_SYSTEM_USERS) $this->output_403('Нельзя сменить пароль системному пользователю!');

    if ($this->is_post()) {
      $data=$this->load_user($uid,0); // берем данные о пользователе
      $data['password']=$_POST['password']; // и заменяем в них пароль
      $errors=array();
      if ($data['password']!=$_POST['password_confirm']) $errors[]=array('text'=>'Пароль не совпадает с подтверждением!','level'=>3);
      if (!empty($errors)) $this->message($errors);
      else {
        $result=$userlib->update_user($data,false); // теперь изменяем эти данные, false во втором параметре приведет к тому, что настройки в таблице user_settings изменяться не будут\
        if ($result && $this->get_opt('userlib_logs')>1) $this->log_entry ('user', 5, 'user.php', 'Пользователь '.$data['login'].' сменил пароль по присланной в письме ссылке.');
        if ($result) {
          $userlib->do_login($data['login'],$_POST['password']);
          $this->output_msg('login.htm', 'Пароль успешно изменен!','Перейти на страницу входа');
        }
      }
    }
    $this->out->authkey=$_REQUEST['authkey'];
  }

  function action_change_email() {
    $userlib = $this->load_lib('userlib');
    if ($this->get_opt('captcha')) $antibot = $this->load_lib('antibot');
    else $antibot = false;

    if ($this->is_post()) {
      $errors=array();
      if ($antibot && !$antibot->captcha_check()) $errors[]=array('text'=>'Неправильно введен проверочный код!','level'=>3);
      $uid = $userlib->get_uid_by_name($_POST['login']);
      $data = $this->load_user($uid,0); // не самый лучший вариант с точки зрения производительности, но делать отдельный режим в load_user только для этого действия не вижу смысла
      if (!$data) $errors[] = array('text'=>'Пользователь с таким логином не найден!','level'=>3);
      else {
        if (!$userlib->check_password($data['id'],$_POST['password'])) {
          $errors[] = array('text'=>'Неправильный логин или пароль!','level'=>3);
        }
        if ($data['status']!=1) {
          $errors[] = array('text'=>'Данный пользователь уже активирован! Вы можете сменить Email через редактирование профиля.','level'=>3);
        }
      }
      if (!empty($errors)) $this->message($errors);
      else {
        $data['email']=$_POST['email'];
        unset($data['password']); // чтобы не сбрасывался пароль пользователя при включенном шифровании
        $result=$userlib->update_user($data,false,false,false,true); // теперь изменяем эти данные, false во втором параметре приведет к тому, что настройки в таблице user_settings изменяться не будут\
        if ($result) {
          if ($this->get_opt('userlib_logs')>1) $this->log_entry ('user', 6, 'user.php', 'Пользователь '.$data['login'].' изменил Email для отправки письма активации.');
          $this->message('Email пользователя изменен, на новый адрес было отправлено письмо со ссылкой для активации профиля.');
          $this->output_msg(false, 'Email пользователя изменен!');
        }
      }
    }
    if ($antibot) $antibot->captcha_generate();
//    $this->out->authkey=$_REQUEST['authkey'];
  }

  /** Вывод алфавитного списка и формы поиска пользователей */
  function action_view() {
    /** @var Library_userlib $userlib */
    $userlib = $this->load_lib('userlib',true);
    $this->out->letters = $userlib->get_letters();
    $this->out->groups = $userlib->list_groups(array('usercount'=>true,'nonzero'=>true));
    $this->out->last_users = $userlib->list_users(array('order'=>'reg_date','ext_data'=>true,'perpage'=>20,'sort'=>'DESC'));
    // TODO: сделать отдельную процедуру подсчета пользователей.
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'user WHERE id>'.AUTH_SYSTEM_USERS.' AND status=0';
     $this->out->total_users = $this->db->select_int($sql);

    $taglib = $this->load_lib('tags',false);
    if ($taglib) {
      $this->out->tags = $taglib->get_all_tags(1,20);
      $max=0; // считаем максимальный вес для тега для расчета коэффициента
      for ($i=0, $count=count($this->out->tags); $i<$count; $i++) $max = max($max,$this->out->tags[$i]['count']);
      $this->out->max_tag = $max;
    }
  }

  function action_all_tags() {
    $taglib = $this->load_lib('tags',false);
    if ($taglib) {
      $this->out->tags = $taglib->get_all_tags(1);
      $max=0; // считаем максимальный вес для тега для расчета коэффициента
      for ($i=0, $count=count($this->out->tags); $i<$count; $i++) $max = max($max,$this->out->tags[$i]['count']);
      $this->out->max_tag = $max;
    }
  }

  /** Вывод профиля конкретного пользователя **/
  function action_view_user() {
    $userlib = $this->load_lib('userlib',true);
    if (!isset($_REQUEST['uid']) || !is_numeric($_REQUEST['uid'])) $this->output_404('Некорректно указан идентификатор пользователя!');
    $uid = intval($_REQUEST['uid']);

    $udata = $this->load_user($uid,2);
    if (empty($udata) || empty($udata['basic']) || $udata['basic']['status']==1) $this->output_404('Пользователя с таким идентификатором не существует или он не активирован!');

    if (!$udata['settings']['hidden'] || $this->is_admin()) { // если пользователь не скрытый (или его просматривает не админ), получаем дату его последнего визита
      $sql = 'SELECT MAX(visit1) FROM '.DB_prefix.'last_visit WHERE uid='.intval($uid);
      $this->out->lastvisit = $this->db->select_int($sql);
    }
    for ($i=0, $count=count($udata['contacts']);$i<$count;$i++) {
      if (substr($udata['contacts'][$i]['link'],0,2)=='%s' && // защищемся от ссылок без http:// в начале, если в
        strpos($udata['contacts'][$i]['value'],'http://')===false &&
        strpos($udata['contacts'][$i]['value'],'https://')===false &&
        strpos($udata['contacts'][$i]['value'],'ftp://')===false) $udata['contacts'][$i]['value']='http://'.$udata['contacts'][$i]['value'];
    }
    $this->out->userdata=$udata;

    // подготовка даты рождения
    if ($udata['settings']['show_birthdate']==2) {
      $this->out->user_age=(intval(date('Y'))-intval(substr($udata['basic']['birthdate'],0,5))); // если показывать только возраст
    }
    elseif ($udata['settings']['show_birthdate']==1) { // если показывать только дату
      $tmpdate = date('Y').substr($udata['basic']['birthdate'],4);
      $this->out->birthdate = @strftime('%d %B',strtotime($tmpdate));
    }
    else $this->out->birthdate = $udata['basic']['birthdate'];


    if (!$this->is_guest()) { // если пользователь не гость, выясняем его отношение к просматриваемому пользователю и наоборот, отношение просматриваемого к нему
      $sql = 'SELECT ur.type FROM '.DB_prefix.'relation ur WHERE "from_"='.intval($this->get_uid()).' AND "to_"='.intval($uid);
      $this->out->relation_to = $this->db->select_str($sql);

      $sql = 'SELECT ur.type FROM '.DB_prefix.'relation ur WHERE "from_"='.intval($uid).' AND "to_"='.intval($this->get_uid());
      $this->out->relation_from = $this->db->select_str($sql);

      $this->out->friend_list = $userlib->list_users(array('gender'=>true,'friends_list'=>$uid));
      $this->out->add_key=$this->gen_auth_key(false,'add',$this->url('address_book/'));
    }
    $bbcode = $this->load_lib('bbcode',false);
    if ($bbcode) $this->out->userdata['basic']['signature']=$bbcode->parse_sig($udata['basic']['signature'],$udata['ext_data']['links_mode']);

    $forums = $this->get_forum_list('read');
    $sql = 'SELECT COUNT(*) AS count, SUM(CASE WHEN p.value=\'1\' THEN 1 ELSE 0 END) AS valued, SUM(CASE WHEN p.value=\'-1\' THEN 1 ELSE 0 END) AS flood, f.id, f.hurl, f.title, f.is_stats '.
       'FROM '.DB_prefix.'forum f, '.DB_prefix.'topic t, '.DB_prefix.'post p '.
       'WHERE p.uid='.intval($uid).' AND p.tid=t.id AND t.fid=f.id AND p.status=\'0\' '.
       'GROUP BY f.id, f.hurl, f.title,f.is_stats '.
       'ORDER BY count DESC';
    $this->out->forum_posts = $this->db->select_all($sql);
    $this->out->valued_count = 0;
    $this->out->flood_posts = 0;
    for ($i=0, $count=count($this->out->forum_posts); $i<$count; $i++) {
      $this->out->valued_count+=$this->out->forum_posts[$i]['valued'];
      $this->out->flood_posts+=$this->out->forum_posts[$i]['flood'];
    }

    // TODO: доделать сохранение даты редактирования профиля в базе
    //$this->lastmod=max($this->lastmod,$udata['user_ext']['']
    $this->lastmod=$this->time; // TODO: возможно, в будущем выводить в lastmod время последней модификации профиля
    $this->out->can_warn = $this->check_access('moderate',0);
  }

  function action_search() {
    $cond = false;
    if (!empty($_REQUEST['letter'])) $cond['letter']=$_REQUEST['letter'];
    if (!empty($_REQUEST['name'])) $cond['login']=$_REQUEST['name'];
    if (!empty($_REQUEST['group'])) $cond['group']=$_REQUEST['group'];
    if (!empty($_REQUEST['location'])) $cond['location']=$_REQUEST['location'];
    if (!empty($_REQUEST['tag'])) $cond['tag']=$_REQUEST['tag'];

    if ($cond===false) $this->output_403('Не задан критерий для поиска пользователей');
    $cond['ext_data']=true;
    $cond['all_data']=true;
    $cond['last_visit']=true;
    $cond['contacts']=true;

    $userlib = $this->load_lib('userlib',true);
    /* @var $userlib Library_userlib */
    $total = $userlib->count_users($cond);
    $perpage = $this->get_opt('posts_per_page','user');
    if (!$perpage) $perpage = $this->get_opt('posts_per_page');
    if (!$perpage) $perpage = 10;
    $pages['total']=$total;
    $pages['perpage']=$perpage;
    $pages['page']=isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
    $pages=$this->get_pages($pages);
    $this->out->pagedata = $pages;
    $cond['perpage']=$pages['perpage'];
    $cond['start']=$pages['start'];
    $this->out->users = $userlib->list_users($cond);

    $online = $this->get_opt('online_time');
    if (!$online) $online = 15;
    $this->out->lasttime  = $this->time - $online*60; // определяем момент времени, после которого пользователь считается еще находящимся онлайн
    $this->lastmod=$this->time; // TODO: подумать, возомжно, имеет смысл переделать на время последней модификации какого-либо профиля (но тогда будет некорректно считаться нахождение онлайн)
  }

  /** Вынесение предупреждения пользователю без привязки к конкретному сообщению **/
  function action_warn() {
    if (!$this->check_access('moderate',0)) $this->output_403('У вас нет прав для выноса предупреждения. Это могут делать только администраторы или модераторы всего форума. Если вы являетесь модератором раздела, вы можете вынести предупреждение при редактировании конкретного сообщения в этом разделе!');
    if (empty($_REQUEST['id'])) $this->output_403('Не задан идентификатор пользователя для выноса предупреждения');
    $uid=intval($_REQUEST['id']);
    if ($uid==$this->get_uid()) $this->output_403('Вы не можете вынести предупреждение самому себе.');
    $udata = $this->load_user($uid);
    if (empty($udata)) $this->output_404('Пользователь не найден!');
    $this->out->username = $udata['display_name'];
    if ($this->is_post()) {
      $data = $_POST['warn'];
      $warnlib = $this->load_lib('warning',true);
      /* @var $warnlib Library_warning */
      if ($warnlib->make_warning($uid, $_POST['warn'])) {
        $this->message('Автору сообщения вынесено предупреждение!',2);
        $pmlib = $this->load_lib('privmsg',false);
        /* @var $pmlib Library_privmsg */
        if ($pmlib) {
          $pmdata['thread']['title']='Предупреждение за нарушение правил форума';
          $pmdata['uids']=array($uid,$this->get_uid());
          $pmdata['post']['text']=$_POST['warn']['descr'];
          list($pm_thread,$pm_id)=$pmlib->save_message($pmdata);
          $pmdata['thread']['id']=$pm_thread;
          $pmdata['post']['id']=$pm_id;
          $notify_lib = $this->load_lib('notify',false);
          if ($notify_lib) {
            $userdata = $this->load_user($this->get_uid(),0);
            $notify_lib->new_pm($pmdata['thread'],$pmdata['post'],$pmdata['post']['text'],$this->get_username(),$userdata['email']);
          }
          $this->redirect($this->http($this->url(sprintf($this->get_opt('user_hurl'),$uid))));
        }
      }
    }
    $this->out->uid = $uid;
  }

  /** Отписка от массовой рассылки из Центра Администрирования. **/
  function action_unsubscribe_mass() {
    if (!isset($_REQUEST['authkey'])) $this->output_403('Быстрая отписка может осуществляться только по ключу, присланному в письме!');
    $uid=$this->get_uid();
    if ($uid<=AUTH_SYSTEM_USERS) $this->output_403('Быстрая отписка невозможна для гостей или системных пользователей. Отредактируйте профиль пользователя!');
    $userlib = $this->load_lib('userlib',true);
    $userlib->unsubscribe($uid,1);
    $this->output_msg(false,'Вы отписаны!','Вы отписаны от администраторской рассылки форума.');
  }

  /** Отписка от всех рассылок вообще. **/
  function action_unsubscribe_all() {
    if (!isset($_REQUEST['authkey'])) $this->output_403('Быстрая отписка может осуществляться только по ключу, присланному в письме!');
    $uid=$this->get_uid();
    if ($uid<=AUTH_SYSTEM_USERS) $this->output_403('Быстрая отписка невозможна для гостей или системных пользователей. Отредактируйте профиль пользователя!');
    $userlib = $this->load_lib('userlib',true);
    $userlib->unsubscribe($uid,0);
    $this->output_msg(false,'Вы отписаны!','Вы отписаны от всех рассылок форума.');
  }

  /** Функция для проверки допустимости логина через AJAX **/
  function action_check_login() {
    $userlib = $this->load_lib('userlib',true);
    /* @var $userlib Library_userlib */
    if (empty($_GET['login'])) $this->output_403('Не указан логин для проверки!');
    $data['id']=false; // проверка вызывается только при регистрации новых пользователей
    $data['login']=$_GET['login'];
    $data['display_name']=$_GET['login'];
    $res=$userlib->validate_login($data,false);
    if (empty($res)) {
      $result['result']='done';
      $result['message']='Логин корректен и свободен';
    }
    else {
      $result['result']='error';
      $result['message']='';
      for ($i=0, $count=count($res);$i<$count;$i++) $result['message'].=$res[$i]['text'].' ';
    }
    $this->output_json($result);
  }

  /** Вспомогательная функция для поиска по данным, вводимым через форму (имя и место),
   * нужна для редиректа с адресов вида search/?location=место на адреса /location-место/ **/
  function action_search_redir() {
    if (!empty($_GET['name'])) $this->redirect($this->http($this->url('users/search/name-'.urlencode($_GET['name']).'/')),true);
    elseif (!empty($_GET['location'])) $this->redirect($this->http($this->url('users/search/location-'.urlencode($_GET['location']).'/')),true);
    else $this->output_404('Не заданы необходимые для работы функции параметры');
  }

  function  set_title() {
    if ($this->action==='register' || $this->action==='social_register') $result='Регистрация нового пользователя ';
    elseif ($this->action==='login' || $this->action==='social_login') $result='Вход на сайт';
    elseif ($this->action==='change') $result='Смена пароля';
    elseif ($this->action==='change_email') $result='Смена адреса Email';
    elseif ($this->action==='update') $result='Редактирование профиля пользователя';
    elseif ($this->action==='forgot') $result='Восстановление забытого пароля';
    elseif ($this->action==='view') $result='Участники форума';
    elseif ($this->action==='search' || $this->action==='all_tags') $result='Поиск участников форума';
    elseif ($this->action==='view_user') $result='Профиль пользователя '.$this->out->userdata['basic']['display_name'];
    elseif ($this->action==='warn') $result='Вынесение предупреждения участнику форума';
    return $result;
  }

  function set_location() {
    $result=parent::set_location();
    if ($this->action==='register' || $this->action==='social_register') $result[1]=array('Регистрация');
    elseif ($this->action==='login' || $this->action==='social_login') $result[1]=array('Вход на сайт');
    elseif ($this->action==='change') $result[1]=array('Смена пароля');
    elseif ($this->action==='change_email') $result[1]=array('Смена адреса Email');
    elseif ($this->action==='update') $result[1]=array('Редактирование профиля');
    elseif ($this->action==='forgot') $result[1]=array('Восстановление пароля');
    elseif ($this->action==='view') $result[1]=array('Участники форума');
    elseif ($this->action==='search' || $this->action==='all_tags') { $result[1]=array('Участники форума',$this->url('users/')); $result[2]=array('Поиск пользователей'); }
    elseif ($this->action==='view_user')  { $result[1]=array('Участники форума',$this->url('users/')); $result[2]=array('Профиль пользователя '.$this->out->userdata['basic']['display_name']); }
    elseif ($this->action==='warn') {
      $result[1]=array('Пользователь '.$this->out->username,sprintf($this->get_opt('user_hurl'),$this->out->uid));
      $result[2]=array('Вынесение предупреждения');
    }
    return $result;
  }

  function get_action_name() {
    if ($this->action==='register' || $this->action==='social_register') $result='Регистрируется на сайте';
    elseif ($this->action==='login' || $this->action==='social_login') $result='Входит на сайт как пользователь';
    elseif ($this->action==='update') $result='Редактирует свой профиль';
    elseif ($this->action==='forgot' && $this->action=='change') $result='Восстанавливает забытый пароль';
    elseif ($this->action==='view' || $this->action==='search' || $this->action==='all_tags') $result='Просматривает список участников форума';
    elseif ($this->action==='view_user') $result='Просматривает профили участников форума';
    else $result=parent::get_action_name();
    return $result;
  }
}

?>
