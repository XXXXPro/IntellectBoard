<?php
/** ================================
 *  Intellect Board Pro
 *  http://intbpro.ru
 *  Библиотека базовых операций над профилем пользователя (регистрация, удаление, обновление, проверка)
 *  ================================ */

class Library_userlib extends Library {
  static $user_fields = array('id','login','password','pass_crypt','email','title','gender','birthdate',
    'location','group_id','status','canonical','signature','rnd','display_name','avatar','photo','real_name');
  static $settings_fields = array('id','topics_per_page','posts_per_page','template','msg_order',
     'subscribe','timezone','signatures','avatars','smiles','pics','longposts','flood','show_birthdate',
     'subscribe_mode','email_fulltext','email_pm','email_message','email_broadcasts','hidden','flood_limit','topics_period','wysiwyg');
  static $timezones = array(
  '-43200'=>'(GMT -12:00) Эниветок, Кваджалейн',
  '-39600'=>'(GMT -11:00) Остров Мидуэй, Самоа',
  '-36000'=>'(GMT -10:00) Гавайи',
  '-32400'=>'(GMT -9:00) Аляска',
  '-28800'=>'(GMT -8:00) Тихоокеанское время (США и Канада)',
  '-25200'=>'(GMT -7:00) Горное время (США и Канада)',
  '-21600'=>'(GMT -6:00) Центральное время (США и Канада), Мехико',
  '-18000'=>'(GMT -5:00) Восточное время (США и Канада), Богота, Лима',
  '-14400'=>'(GMT -4:00) Атлантическое время (Канада), Каракас, Ла-Пас',
  '-12600'=>'(GMT -3:30) Ньюфаундленд',
  '-10800'=>'(GMT -3:00) Бразилия, Буэнос-Айрес, Джорджтаун',
  '-7200'=>'(GMT -2:00) Срединно-Атлантическое',
  '-3600'=>'(GMT -1:00 час) Азорские острова, острова Зеленого Мыса',
  '0'=>'(GMT) Время Западной Европы, Лондон, Лиссабон, Касабланка',
  '3600'=>'(GMT +1:00) Брюссель, Копенгаген, Мадрид, Париж',
  '7200'=>'(GMT +2:00) Калининград, Киев,  Минск, Южная Африка',
  '10800'=>'(GMT +3:00) Москва, Санкт-Петербург,Багдад, Эр-Рияд',
  '12600'=>'(GMT +3:30) Тегеран',
  '14400'=>'(GMT +4:00) Самара, Абу-Даби, Мускат, Баку, Тбилиси',
  '16200'=>'(GMT +4:30) Кабул',
  '18000'=>'(GMT +5:00) Екатеринбург, Исламабад, Карачи, Ташкент',
  '19800'=>'(GMT +5:30) Бомбей, Калькутта, Мадрас, Нью-Дели',
  '20700'=>'(GMT +5:45) Катманду',
  '21600'=>'(GMT +6:00) Омск, Алматы, Дакке, Коломбо',
  '25200'=>'(GMT +7:00) Красноярск, Бангкок, Ханой, Джакарта',
  '28800'=>'(GMT +8:00) Иркутск, Пекин, Перт, Сингапур, Гонконг',
  '32400'=>'(GMT +9:00) Якутск, Токио, Сеул, Осака, Саппоро',
  '34200'=>'(GMT +9:30) Аделаида, Дарвин',
  '36000'=>'(GMT +10:00) Владивосток, Восточная Австралия, Гуам',
  '39600'=>'(GMT +11:00) Соломоновы острова, Новая Каледония',
  '43200'=>'(GMT +12:00) Камчатка, Окленд, Веллингтон, Фиджи');

  function filter_user($data) {
    return Library::$app->filter($data,Library_userlib::$user_fields);
  }

  function filter_settings($data) {
    return Library::$app->filter($data,Library_userlib::$settings_fields);
  }

  /** Проверка пароля пользователя без входа в систему.
   *
   * @global Application $this->app
   * @param int $uid Идентификатор пользователя
   * @param string $password Пароль в открытом виде
   * @return boolean TRUE, если пароль верен
   */
  function check_password($uid,$password) {
    $userdata=Library::$app->load_user($uid);
    $crypted_password=Library::$app->crypt_password($password,$userdata['pass_crypt']);
    if ($userdata['password']!=$crypted_password) return false; //если шифрованный пароль не совпал с хранящимся в базе, выходим из процедуры
    else return true;
  }

  /** Осуществление входа на форум по логину/паролю, а не по сессии или ключу **/
  function do_login($login,$password,$long=false) {
    $reg_timeout=Library::$app->get_opt('userlib_login_timeout');
    $antibot = Library::$app->load_lib('antibot',false);
    if ($reg_timeout && $antibot) { // проверяем, что предыдущая регистрация с этого IP была не менее указанного времени назад, причем делаем это только в том случае, если не было ошибок при валидации пользователя
      if (!$antibot->timeout_check('userlib_login', $reg_timeout)) {
        Library::$app->message('Предыдущая попытка входа была менее чем '.$reg_timeout.' секунд назад',3);
        return false;
      }
    }

    $user_id=false;

    if ($ext_lib_name = Library::$app->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
      $ext_lib = Library::$app->load_lib($ext_lib_name,false); // загружаем ее
      if ($ext_lib) {
        $user_id = $ext_lib->get_user_by_login($login,$password);
      }
    }

    if (!$user_id) { // если из внешней библиотеки не пришло user_id
      $userdata=Library::$app->load_user(0,3,$login); // 3 -- загружаем данные о пользователе по его имени, а не по uid
      if ($userdata['id']<=AUTH_SYSTEM_USERS) return false;
      $crypted_password=Library::$app->crypt_password($password,$userdata['pass_crypt'],$userdata['rnd']);
    }
    else {
      if ($user_id<=AUTH_SYSTEM_USERS) return false;
      $userdata=Library::$app->load_user($user_id,1); // 1 -- загружаем данные о пользователе по его uid
      $crypted_password=$userdata['password']='*';
    }

    if ($userdata['password']!=$crypted_password) {
      if (Library::$app->get_opt('userlib_logs')>4) Library::$app->log_entry ('user', 7, 'userlib.php', 'Неудачная попытка входа пользователя '.$login);
      return false; //если шифрованный пароль не совпал с хранящимся в базе, выходим из процедуры
    }
    Library::$app->set_user($userdata,$long);
    $_SESSION['starttime']=Library::$app->time;
    if (Library::$app->get_opt('userlib_logs')>3) Library::$app->log_entry ('user', 8, 'userlib.php', 'Пользователь '.$login.' вошел на форум.');

    return true;
  }

  /** Принудительный вход под пользователем. Используется действиями социального входа. Использовать с осторожностью! **/
  function force_login($uid) {
    Library::$app->session();
    $udata = Library::$app->load_user($uid,1);
    if ($udata['id']<=AUTH_SYSTEM_USERS) trigger_error('Некорректный идентификатор пользователя при социальной авторизации',E_USER_ERROR);
    Library::$app->set_user($udata);
    $_SESSION['starttime']=Library::$app->time;
    if (Library::$app->get_opt('userlib_logs')>3) Library::$app->log_entry ('user', 8, 'userlib.php', 'Пользователь '.$udata['login'].' вошел на форум через социальную сеть '.$userdata['contact_type'].'.');
  }

  /** Выход с форума **/
  function do_logout() {
    if (isset($_COOKIE[CONFIG_session.'_long'])) setcookie(CONFIG_session.'_long','',1,Library::$app->url('/'),false,isset($_SERVER['HTTPS']),true);
    Library::$app->set_user(Library::$app->load_guest());
    if ($ext_lib_name = Library::$app->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
      $ext_lib = Library::$app->load_lib($ext_lib_name,false); // загружаем ее
      if ($ext_lib) $ext_lib->on_logout();
    }
    $_SESSION['starttime']=Library::$app->time;
  }

  /** Осуществляет занесение данных пользователя в БД (предварительно проведя валидацию)
   *
   * @global Application $this->app
   * @param array $data Хеш с данными о пользователе. Должен содержать два подмассива: basic (базовая информация, которая пойдет в модуль auth) и settings -- информация о настройках профиля
   * @return array Массив ошибок при валидации пользователя (или пустой при успешной регистрации)
   */
  function register_user(&$data,$settings,$contacts=false,$social=false,$novalidate=false) {
    if (isset($data['id'])) unset($data['id']); // сбрасываем идентификатор, если он вдруг установлен
    if (!isset($data['password'])) $data['password']=substr(sha1(rand()),rand(7,9)); //если пароль не установлен, генерируем случайный
    $activate_mode=Library::$app->get_opt('userlib_activation');
    $data['status']=($activate_mode==0) ? 0 : 1; // если активация пользователя требуется, то пользователь изначально неактивен
    if ($novalidate && $activate_mode==1) $data['status']=0;

    if (!isset($data['pass_crypt']))  $data['pass_crypt']=5; // если пользователь ничего не выбрал, пароль шифруем в SHA2 по формуле пароль+соль.

    $errors = $this->validate_user($data,$social);

    $reg_timeout=Library::$app->get_opt('userlib_register_timeout');
    $antibot = Library::$app->load_lib('antibot',false);
    if ($reg_timeout && $antibot && empty($errors)) { // проверяем, что предыдущая регистрация с этого IP была не менее указанного времени назад, причем делаем это только в том случае, если не было ошибок при валидации пользователя
      if (!$antibot->timeout_check('userlib_regiser', $reg_timeout)) $errors[]=array('text'=>'Предыдущая регистрация с данного адреса была менее чем '.$reg_timeout.' секунд назад','level'=>3);
    }
    $result = false;
    if (empty($errors)) { // если при валидации пользователя не произошло ошибок
      $data['rnd']=mt_rand(0,0x7FFFFFFF);      
      if (!$social) { // если обычная регистрация, шифруем пароль в соответствии с выбранными настройками
        $uncrypt_password=$data['password'];
        $data['password']=Library::$app->crypt_password($data['password'], $data['pass_crypt'],$data['rnd']);  //шифруем пароль
      }
      else { // если идет регистрация через социальную сеть, то прописываем в пароль звездочку и помечаем его как зашифрованный, чтобы нельзя было войти обычным образом до тех пор, пока пользователь не сменит пароль
        $data['password']='*';
        $data['pass_crypt']=1;
      }
      if (!isset($data['display_name']) || !$data['display_name']) $data['display_name']=$data['login']; // TODO: возможно, в а
      $data['canonical']=$this->canonize_name($data['display_name']);

      $def_settings = Library::$app->load_user(3, 2);
      unset($def_settings['basic']['id']);
      $data = $data+$def_settings['basic'];

//      @eval(Library::$app->event('userlib_before_register')); // Вызываем дополнительные обработчики

      $data = $this->filter_user($data);
      Library::$app->db->insert(DB_prefix.'user', $data);
      $data['id']=Library::$app->db->insert_id();

      if ($data['id']) { // если пользователя удалось зарегистрировать и ему дали идентификатор
//        @eval(Library::$app->event('userlib_after_register')); // Вызываем дополнительные обработчики
        if (!is_array($settings)) $settings = array();
        if (!is_array($def_settings['settings'])) $def_settings['settings'] = array();
        $settings = $settings + $def_settings['settings'];
        $settings['id']=$data['id'];

        $settings = $this->filter_settings($settings);
        Library::$app->db->insert(DB_prefix.'user_settings', $settings);

        $ext_data['id']=$data['id']; // создаем запись в таблице расширенных данных
        $ext_data['group_id']=$def_settings['ext_data']['group_id'];
        $ext_data['reg_date']=Library::$app->time;
        $ext_data['reg_ip']=Library::$app->get_ip();
        Library::$app->db->insert(DB_prefix.'user_ext',$ext_data);

        // помечаем все сообщения форума как прочитанные, чтобы если пользователь зайдет в "непрочитанные"
        $mark_data['uid']=$data['id'];
        $mark_data['fid']='0';
        $mark_data['mark_time']=Library::$app->time;
        Library::$app->db->insert(DB_prefix.'mark_all',$mark_data);

        if (!empty($contacts)) { // если при регистрации были указаны какие-то контакты, записываем их в базу
          $cdata['uid']=intval($data['id']);
          for ($i=1,$count=count($contacts);$i<=$count;$i++) {
            $cdata['cid']=intval($contacts[$i]['cid']); // тип контакта
            $cdata['value']=$contacts[$i]['value']; // значение контакта
            if ($cdata['cid']) Library::$app->db->insert(DB_prefix.'user_contact', $cdata);
          }
        }

        $result = true;

        $mdata['regdata'] = $data;
        if (Library::$app->get_opt('userlib_logs')>0) Library::$app->log_entry('user', 1, 'userlib.php', 'Пользователь '.$data['login'].' зарегистрирован'); // логгируем событие, если логгирование включено
        if ($activate_mode==1 && !$novalidate) { // если включена активация пользователя администратором и авторизуемся не через соцсеть с проверенными EMail
          $userkey = Library::$app->gen_auth_key($data['id'],'activate'); // эти данные будут использоваться в письме
          $mdata['keylink'] = Library::$app->http(Library::$app->url('user/activate.htm?authkey='.$userkey));
          $mdata['uncrypt_password']=$uncrypt_password;
          Library::$app->mail(array('to'=>$data['email'],'subj'=>'Подтверждение регистрации на сайте',
            'to_name'=>$data['login'],'template'=>'user/mail_register.tpl','data'=>$mdata,'html'=>1,'list-id'=>'Register <register.'.$_SERVER['HTTP_HOST'].'>'));
        }
        if (Library::$app->get_opt('userlib_newuser_mail')) {
          $notify_lib = Library::$app->load_lib('notify',false);
          if ($notify_lib) $notify_lib->new_user($data,$activate_mode);
        }
        if ($ext_lib_name = Library::$app->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
          $ext_lib = Library::$app->load_lib($ext_lib_name,false); // загружаем ее
          if ($ext_lib) $ext_lib->on_register($data,$settings);
        }
      }
    }
    else Library::$app->message($errors); // иначе -- выводим сообщения об ошибке

    return $result;
  }

  /** Осуществляет занесение данных пользователя в БД (предварительно проведя валидацию)
   *
   * Примечание: для расширенных данных (ext_data) будет использоваться отдельная таблица
   *
   * @global Application $this->app
   * @param array $data Хеш с данными о пользователе
   * @param array $settings Хеш с настройками пользователя (для таблицы user_settings)
   * @param array $contacts Данные о контактах пользователя
   * @param array $interests Строка с интересами пользователя
   * @param boolean $force Возможность изменять профиль пользователя, id которого отличается от залогиненного (например, при вызове этой функции из АЦ)
   * @return array Массив ошибок при валидации пользователя (или пустой при успешной регистрации)
   */
  function update_user($data,$settings,$contacts=false,$interests=false,$force=false) {
    if (Library::$app->get_uid()!=$data['id'] && $force==false) { // если не включен режим изменения чужого профиля, то идентфиикатор редактируемого пользователя должен совпадать с залогиненным
      Library::$app->message('Нельзя редактировать чужой профиль!',3);
      return false;
    }

    $olddata = Library::$app->load_user($data['id']);
    $change_email=($olddata['email']!=$data['email']);
    $change_login=($olddata['login']!=$data['login']);

    $log_level = Library::$app->get_opt('userlib_logs'); // уровень логгирования действий над профилем
    $need_log = $log_level > 2 ? true : false; // при уровне больше 2 все действия над профилем логгируются безусловно, при уровне 2 -- логгируются только изменения пароля и EMail, при более низких -- не логгируются вообще
    $log_string = ''; // в эту строку пишем, что конкретно изменили из критичных параметров

    // TODO: подумать, где сделать проверку старого пароля в том случае, если пользователь хочет менять логин или EMail: здесь или в user.php

    if ($change_email && !$force) {
      $activate_mode=Library::$app->get_opt('userlib_activation');
      $data['status']=($activate_mode==1) ? 1 : 0; // если включен режим активации пользователем, то деактивируем пользователя до момента, когда он подтвердит свой ящик
      if ($log_level>=2) $need_log = true;
      $log_string.='Изменен EMail. ';
    }

    if (isset($data['password']) && $data['password']) { // если новый пароль задан (проверка ввода подтверждения пароля будет делаться в user.php)
      $crypt_mode=5; // новый пароль шифруем всегда по методу 5 (SHA2 от пароля+соли), остальные методы нужны только для совместимости с другими движками
      $data['pass_crypt']=$crypt_mode;
      $data['password']=Library::$app->crypt_password($data['password'], $crypt_mode,$olddata['rnd']); //шифруем пароль
      $log_string.='Изменен пароль. ';
    }
    else { // если новый пароль пустой, сбрасываем поля password и pass_crypt, чтобы избежать изменения пароля
      unset($data['password']);
      unset($data['pass_crypt']);
    }

    if (!Library::$app->get_opt('custom_title','group')) $data['title']=Library::$app->get_opt('title','group'); // если в группе не разрешено задание собственного статуса, пишем в статус название группы

    $errors = $this->validate_user($data);
    $result = false;
    if (empty($errors)) { // если при валидации пользователя не произошло ошибок
      if (!$data['display_name']) $data['display_name']=$data['login'];
      $data['canonical']=$this->canonize_name($data['display_name']);

      $condition='id='.intval($data['id']);

      $up_avatar = isset($_FILES['avatar']) && is_uploaded_file($_FILES['avatar']['tmp_name']);
      $up_photo = isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']['tmp_name']);

      if (isset($data['avatar_delete']) && $data['avatar_delete']) { // если пользователь поставил галочку "удалить аватар", то удаляем, и сбрасываем признак загрузки
        $up_avatar = false;
        if ($olddata['avatar']!='none') { // если раньше был аватар и его формат отличается от текущего, удаляем старый файл с аватаром (если совпадает, то необходимости в этом нет, мы его просто перезаписали в функции save_fit_to)
            $filename = 'f/av/'.intval($data['id']).'.'.$olddata['avatar'];
            if (Library::$app->valid_file($filename)) unlink($filename);
        }
        $data['avatar']='none';
      }
      if (isset($data['photo_delete']) && $data['photo_delete']) {
        $up_photo = false;
        if ($olddata['photo']!='none') { // если раньше был аватар и его формат отличается от текущего, удаляем старый файл с аватаром (если совпадает, то необходимости в этом нет, мы его просто перезаписали в функции save_fit_to)
            $filename = 'f/ph/'.intval($data['id']).'.'.$olddata['photo'];
            if (Library::$app->valid_file($filename)) unlink($filename);
        }
        $data['photo']='none';
      }

      if ($up_avatar || $up_photo) { // если загрузили аватар или фото, нужно уменьшить их до нужного размера и положить в каталог
        $image = Library::$app->load_lib('image',false);
        if (!$image) Library::$app->message('Не удалось загрузить библиотеку обработки изображений',2);
        else {
          if ($up_avatar) {
            $max_av_x = Library::$app->get_opt('userlib_avatar_x');
            if (!$max_av_x) $max_av_x = 120;
            $max_av_y = Library::$app->get_opt('userlib_avatar_y');
            if (!$max_av_y) $max_av_y = 120;
            $avatar = $image->load($_FILES['avatar']['tmp_name']);
            if (!$avatar) Library::$app->message('Не удалось распознать графический файл аватара',2);
            else {
              if ($avatar['type']==IMAGETYPE_BMP) $type=IMAGETYPE_PNG;
              else $type=$avatar['type'];
              if ($type==IMAGETYPE_JPEG) { // если загрузили JPEG, то обрабатываем его с качеством, заданным в настройках
                $options = Library::$app->get_opt('userlib_avatar_jpeg_qty');
                if (!$options) $options=90;
              }
              else $options = false;
              $ext = $image->get_extension($type);
              $up_avatar = $image->save_fit_to($avatar,$max_av_x,$max_av_y,'f/av/'.intval($data['id']).'.'.$ext,$type,$options);
              if ($up_avatar) {
                if ($olddata['avatar']!='none' && $olddata['avatar']!=$ext) { // если раньше был аватар и его формат отличается от текущего, удаляем старый файл с аватаром (если совпадает, то необходимости в этом нет, мы его просто перезаписали в функции save_fit_to)
                  $filename = 'f/av/'.intval($data['id']).'.'.$olddata['avatar'];
                  if (Library::$app->valid_file($filename)) unlink($filename);
                }
                $data['avatar']=$ext;
              }
            }
          }
          if ($up_photo) {
            $max_ph_x = Library::$app->get_opt('userlib_photo_x');
            if (!$max_ph_x) $max_ph_x = 240;
            $max_ph_y = Library::$app->get_opt('userlib_photo_y');
            if (!$max_ph_y) $max_ph_y = 400;
            $photo = $image->load($_FILES['photo']['tmp_name']);
            if (!$photo) Library::$app->message('Не удалось распознать графический файл аватара',2);
            else {
              if ($photo['type']==IMAGETYPE_BMP) $type=IMAGETYPE_PNG;
              else $type=$photo['type'];
              if ($type==IMAGETYPE_JPEG) { // если загрузили JPEG, то обрабатываем его с качеством, заданным в настройках
                $options = Library::$app->get_opt('userlib_photo_jpeg_qty');
                if (!$options) $options=95;
              }
              else $options = false;
              $ext = $image->get_extension($type);
              $up_photo = $image->save_fit_to($photo,$max_ph_x,$max_ph_y,'f/ph/'.intval($data['id']).'.'.$ext,$type,$options);
              if ($up_photo) {
                if ($olddata['photo']!='none' && $olddata['photo']!=$ext) { // если раньше был аватар и его формат отличается от текущего, удаляем старый файл с аватаром (если совпадает, то необходимости в этом нет, мы его просто перезаписали в функции save_fit_to)
                  $filename = 'f/ph/'.intval($data['id']).'.'.$olddata['photo'];
                  if (Library::$app->valid_file($filename)) unlink($filename);
                }
                $data['photo']=$ext;
              }
            }
          }
        }
      }

      if ($interests!==false) {
        $taglib = Library::$app->load_lib('tags',false);
        if ($taglib) {
          $taglib->set_tags($interests,$data['id'],1); // сохраняем набор тегов
        }
      }

//      @eval(Library::$app->event('userlib_before_update')); // Вызываем дополнительные обработчики

      $data=$this->filter_user($data);
      $result=Library::$app->db->update(DB_prefix.'user', $data, $condition);

      if ($result) { // если изменения в профиль внесены успешно
//        @eval(Library::$app->event('userlib_after_update')); // Вызываем дополнительные обработчики

        if (is_array($settings)) {
          if (Library::$app->get_opt('userlib_allow_template') && isset($settings['template'])) {
            $templatelib = Library::$app->load_lib('template');
            if (!$templatelib || !$templatelib->is_valid($settings['template'])) unset($settings['template']); // если не удалось подгрузить библиотеку template или имя выбранного шаблона неверно, сбрасывем его и оставляем без изменений
          }
          $settings=$this->filter_settings($settings);
          Library::$app->db->update(DB_prefix.'user_settings',$settings,$condition);
        }
        if (is_array($contacts)) {
          $sql = 'DELETE FROM '.DB_prefix.'user_contact WHERE uid='.intval($data['id']);
          Library::$app->db->query($sql);
          $cdata['uid']=intval($data['id']);
          for ($i=1,$count=count($contacts);$i<=$count;$i++) {
            $cdata['cid']=intval($contacts[$i]['cid']); // тип контакта
            $cdata['value']=$contacts[$i]['value']; // значение контакта
            if ($cdata['cid']) Library::$app->db->insert(DB_prefix.'user_contact', $cdata);
          }
        }
        if ($need_log) {
          if (Library::$app->get_uid()==$data['id']) Library::$app->log_entry('user', 2, 'userlib.php', 'Пользователь '.$data['login'].' ('.$data['display_name'].') отредактировал свой профиль. '.$log_string);
          else Library::$app->log_entry('user', 2, 'userlib.php', 'Пользователь '.Library::$app->get_userlogin().' отредактировал профиль пользователя '.$data['login'].' ('.$data['display_name'].'). '.$log_string);
        }

        if ($change_email && $activate_mode==1 && !$force) { // если включена активация пользователя через его ящик EMail, и редактируем не через админку (
          $userkey = Library::$app->gen_auth_key($data['id'],'activate'); // эти данные будут использоваться в письме
          $mdata['keylink'] = Library::$app->http(dirname($_SERVER['REQUEST_URI']).'/activate.htm?authkey='.$userkey);
          $mdata['regdata'] = $data;
          Library::$app->mail(array('to'=>$data['email'],'subj'=>'Подтверждение изменения почтового адреса',
            'to_name'=>$data['login'],'template'=>'user/mail_update.tpl','data'=>$mdata));
        }

        if ($ext_lib_name = Library::$app->get_opt('user_external_lib')) { // если задана библиотека внешней авторизации в настройках
          $ext_lib = Library::$app->load_lib($ext_lib_name,false); // загружаем ее
          if ($ext_lib) $ext_lib->on_profile_update($data,$settings);
        }

        if (Library::$app->get_uid()==$data['id'])Library::$app->set_user(Library::$app->load_user($data['id'],1)); // загружаем обновленные данные пользователя в сессию
      }
    }
    else Library::$app->message($errors); // иначе -- выводим сообщения об ошибке

    return $result;
  }

  /** Обновление данных только об аватаре.
   * Используется при авторизации через социальные сети, в дальнейшем, возможно, пригодится еще где-то.
   **/
  function update_avatar($uid,$avatar) {
    $sql = 'UPDATE '.DB_prefix.'user SET avatar=\''.Library::$app->db->slashes($avatar).'\' WHERE id='.intval($uid);
    return Library::$app->db->query($sql);
  }


  /** Проверка валидности данных для регистрации/изменения профиля пользователя.
   *  **/
  function validate_user($data,$social=false) {
    if (!isset($data['id'])) $data['id']=false;

    // для начала проверим допустимост логина
    $result = $this->validate_login($data,$social);

    // проверка правильности Email
    if (!isset($data['email']) || $data['email']=='') {
      $result[]=array('text'=>'Адрес электронной почты не может быть пустым!','level'=>3);
    }
    elseif (!$this->valid_email($data['email'])) {
      $result[]=array('text'=>'Адрес электронной почты не является правильным!','level'=>3);
    }
    else {
      list($mailuser,$host)=explode('@',$data['email']);
      if (Library::$app->get_opt('userlib_check_mx') && function_exists('getmxrr')) {
        if ($host!='localhost' || $_SERVER['HTTP_HOST']!='localhost') {
          $dom_result = getmxrr($host,$mx);
          if (!$dom_result || !is_array($mx)) $result[]=array('text'=>'Указанный домен электронной почты не имеет MX-записи!','level'=>3);
        }
      }
/*      if (Library::$app->check_list('user_banned_mail',strtolower($host))) {
        $result[]=array('text'=>'Регистрация на адреса электронной почты, находящиеся в домене '.$host.', запрещена администратором форума!','level'=>3);
      }*/
    }

    $test_uid = $this->get_uid_by_email($data['email']); // проверка EMail на уникальность
    if ($test_uid && $test_uid!=$data['id']) $result[]=array('text'=>'Пользователь с таким адресом Email уже существует на форуме!','level'=>3);

//    @eval(Library::$app->event('userlib_validate')); // Вызов внешних обработчиков, если надо

    return $result;
  }

  /** Проверка правильности логина и отображаемого имени пользователя
   * @param array $data Данные пользователя для регистрации
   * @param boolead $social Режим прверки для социальных сетей: пропускается проверка допустимости отображаемого имени, оно берется из соцсети как есть
   * @return multitype:multitype:string number  multitype:number string
   *
   * Используемые глобальные настройки:
   *  user_name_mode -- допустимые символы в имени пользователя
   *        0 -- все, кроме точки с запятой (;), запятой (,), кавычек одинарных и двойных (",') и обратного апострофа (`)
   *        1 -- алфавитно-цифровые символы (латиница+кириллица),
   *        2 -- алфавитно-цифровые символы (только латиница),
   *        3 -- только буквы и пробелы (латиница+кириллица)
   *        4 -- правильный идентификатор с точки зрения программиста (латиница, вторая и след буквы могут быть цифрами или _).
   *        5 -- регулярное выражение
   * user_name_regexp -- регулярное выражение для проверки на допустимость имени в режиме №4

   */
  function validate_login($data,$social=false) {
    $result = array();
    $name_mode=Library::$app->get_opt('userlib_name_mode');
    if ($name_mode==0) $regexp='/^[^,;"\'`]+$/';
    elseif ($name_mode==1) $regexp='/^[A-Za-zА-Яа-яёЁ\d\-!?+\[\]\.*\(\)\/ ]+$/u';
    elseif ($name_mode==2) $regexp='/^[A-Za-zА-Яа-яёЁ\d ]+$/u';
    elseif ($name_mode==3) $regexp='/^[A-Za-z ]+$/u';
    elseif ($name_mode==4) $regexp='/^[A-Za-z][A-Za-z_\d]*$/u';
    elseif ($name_mode==5) $regexp=Library::$app->get_opt('userlib_name_regexp');
    if ($regexp=='') $regexp='/^[^,;"\'`]+$/';

    // проверяем имя пользователя
    if (!isset($data['login']) || $data['login']=='') {
      $result[]=array('text'=>'Имя пользователя не может быть пустым!','level'=>3);
    }
    elseif (preg_match('|[\x{0000}-\x{001F}\x{2000}-\x{200B} \x{202f}\x{205f}\x{2060}\x{3000}]|u', $data['display_name'])) {
      $result[]=array('text'=>'Спецсимволы с кодами меньше 32 или неразрывный пробел в отображаемом имени не допускаются!','level'=>3);
    }
    elseif (!$social && !preg_match($regexp,$data['login'])) { // проверку логина производим только в том случае, если регистрация идет не через соцсеть
      if ($name_mode==0) $text='Имя пользователя не должно содержать точки с запятой (;), запятой (,), кавычек одинарных и двойных (",\') и обратного апострофа (`).';
      elseif ($name_mode==1) $text='Имя пользователя может содержать только буквы кириллицы и латиницы, цифры и знаки +-[]*()./!?.';
      elseif ($name_mode==2) $text='Имя пользователя может содержать только буквы кириллицы и латиницы, цифры и пробелы.';
      elseif ($name_mode==3) $text='Имя пользователя может содержать только буквы и пробелы (кириллица и латиница).';
      elseif ($name_mode==4) $text='Имя пользователя должно быть правильным идентификатором (содержать только буквы и цифры и начинаться с буквы).';
      elseif ($name_mode==5) $text='Имя пользователя должно соответствовать регулярному выражению '.$regexp.'.';
      $result[]=array('text'=>$text,'level'=>3);
    }

    // приведение отображаемого имени к "каноническому" виду и проверка на уникальность
    $canonical_name = $this->canonize_name($data['display_name']);
    $test_uid=$this->get_uid_by_name($canonical_name,true);
    if ($test_uid && $test_uid!=$data['id']) $result[]=array('text'=>'Пользователь с таким (или очень похожим) отображаемым именем уже существует на форуме!','level'=>3);

    // проверка уникальности логина, логин проверяется по точному совпадению, приведение к каноническому виду не требуется
    $test_uid=$this->get_uid_by_name($data['login'],false);
    if ($test_uid && $test_uid!=$data['id']) $result[]=array('text'=>'Пользователь с таким логином уже существует на форуме!','level'=>3);
    return $result;
  }

  /** Получение иденфтикиатора пользователя по логину
   *
   * @global Application $this->app
   * @param string $name Логин пользователя (не отображаемое имя)
   * @param boolean $canonical Проверять не просто логин, а его канонизированное написание (поле canonical) для выявления пользователей с похожими именами
   * @return integer Идентификатор пользователя или false, если его найти не удалось.
   */
  function get_uid_by_name($name,$canonical=false) {
    if ($canonical) $fieldname='canonical';
    else $fieldname='login';

    $sql = 'SELECT id FROM '.DB_prefix.'user WHERE '.$fieldname.'=\''.Library::$app->db->slashes($name).'\'';
    $id = Library::$app->db->select_int($sql);
    return $id;
  }

  /** Получение иденфтикиатора пользователя по отображаемому имени
   *
   * @global Application $this->app
   * @param string $name Логин пользователя (не отображаемое имя)
   * @param boolean $canonical Проверять не просто логин, а его канонизированное написание (поле canonical) для выявления пользователей с похожими именами
   * @return integer Идентификатор пользователя или false, если его найти не удалось.
   */
  function get_uid_by_display_name($name) {
   $fieldname='display_name';

   $sql = 'SELECT id FROM '.DB_prefix.'user WHERE '.$fieldname.'=\''.Library::$app->db->slashes($name).'\'';
   $id = Library::$app->db->select_int($sql);
   return $id;
  }

  /** Получение иденфтикиатора пользователя по адресу EMail
   *
   * @global Application $this->app
   * @param string $email Логин пользователя (не отображаемое имя)
   * @return integer Идентификатор пользователя или false, если его найти не удалось.
   */
  function get_uid_by_email($email) {
    $sql = 'SELECT id FROM '.DB_prefix.'user WHERE email=\''.Library::$app->db->slashes($email).'\'';
    $id = Library::$app->db->select_int($sql);
    return $id;
  }

  /** Приведение имени пользователя к "каноническому" виду (замена похожих по начертанию букв и цифры) в целях недопущения регистрации пользователей с похожими именами **/
  function canonize_name($name) {
    $name = str_replace(' ','',$name);
    $name = str_replace('_','',$name);
    $name = str_replace('-','',$name);
    $name = str_replace(array('Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э','Я','Ч','С','М','И','Т','Ь','Б','Ю'),
            array('й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в','а','п','р','о','л','д','ж','э','я','ч','с','м','и','т','ь','б','ю'),$name);
    $name = str_replace(array('0','1','6','а','в','е','з','и','к','о','р','с','у','х','ь','i','н','п','м','т'),
            array('o','l','b','a','b','e','3','u','k','o','p','c','y','x','b','l','h','n','m','t'),$name);
    $name = str_replace("ю","lo",$name);
    $name = str_replace("ы","bl",$name);
    return $name;
  }

  /** Получение массива пользователей с правами администраторов.
   *
   * @global Application $this->app
   * @return array Хеш-массив с данными.
   */
  function get_admins() {
    $sql = 'SELECT u.id, u.login, u.email, u.display_name FROM '.DB_prefix.'user u, '.DB_prefix.'user_ext ue, '.DB_prefix.'group g '.
      'WHERE u.id=ue.id AND ue.group_id=g.level AND (g.admin=\'1\' OR g.founder=\'1\')';
    $admins=Library::$app->db->select_all($sql);
    return $admins;
  }

  /** Увеличение количества сообщений пользователя на единицу при отправке сообщения **/
  function increment_user($uid=false) {
   if (!$uid) $uid=Library::$app->get_uid();
   $sql = 'UPDATE '.DB_prefix.'user_ext SET post_count=post_count+1 WHERE id='.intval($uid);
   Library::$app->db->query($sql);

   if ($uid>AUTH_SYSTEM_USERS) { // гостям и служебным пользователям статус повышаться не должен
    // повышение статуса пользователя, если он достиг определенных показателей
    if ($uid==Library::$app->get_uid() && isset($_SESSION['user'])) { // если увеличили счетчик текущего пользователя и его сессия загружена, берем данные из нее, чтобы не делать лишнего запроса к БД
      $_SESSION['user']['post_count']++; // увеличиваем количество сообщений, запомненное в сессии (чтобы не лезть за ним в базу)
      $special = $_SESSION['user']['special'];
      $level = $_SESSION['user']['level'];
      $regdate = $_SESSION['user']['reg_date'];
      $posts = $_SESSION['user']['post_count'];
    }
    else { // если увеличиваем другому пользователю (например, при проходе сообщения через премодерацию), то придется загрузить его данные из базы
      $userdata = Library::$app->load_user($uid,1);
      $special = $userdata['special'];
      $level = $userdata['level'];
      $regdate = $userdata['reg_date'];
      $posts = $userdata['post_count'];
    }
    if (!$special) { // увеличиваем уровень пользователя только в том случае, если он не состоит ни в какой
      $days = floor((Library::$app->time - $regdate)/(24*60*60)); // высчитываем, сколько дней назад пользователь зарегистрировался
      $sql = 'SELECT level FROM '.DB_prefix.'group '.
         'WHERE level>'.intval($level).' AND special=\'0\' AND min_posts<='.intval($posts).' AND min_reg_time<='.intval($days).
       ' ORDER BY level DESC'; // выбираем все группы, по уровню более высокие, чем текущая, в которые можно вступить за счет сообщений (нет статуса "особая") и у которых И количество сообщений И время пребывания на форуме больше минимально требуемого
      $level = Library::$app->db->select_int($sql);
      if ($level) { // если группа найдена
        $sql = 'UPDATE '.DB_prefix.'user_ext SET group_id='.intval($level).' WHERE id='.intval($uid);
        Library::$app->db->query($sql);
        if ($uid==Library::$app->get_uid()) { // если обновили текущего пользователя, то загружаем его новые данные, так как могли поменяться права доступа
          Library::$app->set_user(Library::$app->load_user($uid,1));
        }
      }
    }
   }
  }

  /** Пересчет колчиества сообщений пользователя **/
  function user_resync($uid) {
    $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'post p, '.DB_prefix.'topic t, '.DB_prefix.'forum f '.
        'WHERE p.status=\'0\' AND p.tid=t.id AND t.fid=f.id AND f.is_stats=\'1\' AND p.uid='.intval($uid);
    // TODO: подумать, может быть, вынести это в библиотеку topic
    $count_posts = Library::$app->db->select_int($sql);
    $sql = 'UPDATE '.DB_prefix.'user_ext SET post_count='.intval($count_posts).' WHERE id='.intval($uid);
    // TODO: подумать, нужна ли тут проверка на изменение статуса
    return Library::$app->db->query($sql);
  }

  /** Получение списка букв для алфавитного поиска пользователей **/
  function get_letters($start=false,$all=false) {
    if ($start) {  $sqline='AND SUBSTR(u.display_name,1,1)=\''.Library::$app->db->slashes($start).'\' ';
      $pos=2;
    }
    else { $sqline ='';  $pos = 1;  }
    $sql = 'SELECT DISTINCT SUBSTR(u.display_name,'.intval($pos).',1) AS letter FROM '.DB_prefix.'user u '.
    'WHERE ';
    if ($all==false) $sql.='u.status=\'0\' AND ';
    $sql.='u.id>'.intval(AUTH_SYSTEM_USERS).' '.$sqline.
    'ORDER BY letter';
    return Library::$app->db->select_all_strings($sql);
  }

  /** Получение списка групп **/
  function list_groups($cond) {
    $columns = '';
    if (!empty($cond['usercount'])) $columns.=', COUNT(ue.id) AS usercount';
    $sql = 'SELECT g.level, g.name '.$columns.' FROM '.DB_prefix.'group g ';
    if (!empty($cond['usercount'])) $sql.='LEFT JOIN '.DB_prefix.'user_ext ue ON (ue.group_id=g.level AND ue.id>'.AUTH_SYSTEM_USERS.') LEFT JOIN '.DB_prefix.'user u ON (ue.id=u.id) ';
     if (!empty($cond['usercount']) && empty($cond['all'])) $sql.=' WHERE u.status=\'0\'';
    $sql.=' GROUP BY g.level, g.name ';
    if (!empty($cond['usercount']) && !empty($cond['nonzero'])) $sql.='HAVING COUNT(ue.id)>0 ';
    $sql.='ORDER BY g.level DESC';
    return Library::$app->db->select_all($sql);
  }

  /** Подсчет количества пользователей по определенным критериям
   * @param $cond array Хеш-массив с условиями выборки. Может содержать индексы:
   * status -- состояние пользователя (0=активен, 1=неактивен, 2=забанен). Если не задан, ищутся только активные пользователи
   * letter -- буква, на которую начинается имя пользователя
   * group -- группа, к которой принадлежит пользователь
   * tag -- пользователи, указавшие соответствующий тег в интересах
   *
   * **/
  function count_users($cond) {
    $where = 'u.id>'.intval(AUTH_SYSTEM_USERS);
    if (empty($cond['status'])) $where.=' AND u.status=\'0\''; // по умолчанию ищем только активных пользователей
    elseif ($cond['status']!=='all') $where.=' AND u.status=\''.intval($cond['status']).'\'';
    if (!empty($cond['letter'])) $where.=' AND display_name LIKE \''.Library::$app->db->slashes($cond['letter']).'%\'';
    if (!empty($cond['group'])) $where.=' AND g.level='.intval($cond['group']);
    if (!empty($cond['login']))
    if (is_array($cond['login'])) $where.=' AND '.Library::$app->db->array_to_sql($cond['login'],'display_name');
    else $where.=' AND display_name LIKE \''.Library::$app->db->slashes($cond['login']).'%\'';
    if (!empty($cond['location'])) $where.=' AND u.location LIKE \''.Library::$app->db->slashes($cond['location']).'%\'';
    if (!empty($cond['tag'])) $where.=' AND tn.tagname=\''.Library::$app->db->slashes($cond['tag']).'\'';

    $sql = 'SELECT COUNT(*) '.
    ' FROM '.DB_prefix.'user u ';
    if (!empty($cond['ext_data']) ||!empty($cond['group'])) $sql.='LEFT JOIN '.DB_prefix.'user_ext ue ON (u.id=ue.id) ';
    if (!empty($cond['group'])) $sql.='LEFT JOIN '.DB_prefix.'group g ON (ue.group_id=g.level) ';
    if (!empty($cond['tag'])) $sql.='LEFT JOIN '.DB_prefix.'tagentry te ON (u.id=te.item_id) '.
        'LEFT JOIN '.DB_prefix.'tagname tn ON (te.tag_id=tn.id AND tn.type=\'1\') ';
    $sql.='WHERE '.$where;
    return Library::$app->db->select_int($sql);
  }

  /** Выборка пользователей из базы.
  * @param $cond array Хеш-массив с условиями выборки. Может содержать индексы?
  *   status -- состояние пользователя (0=активен, 1=неактивен, 2=забанен). Если не задан, ищутся только активные пользователи
  *   login -- массив со списком отображаемых имен пользователей (Внимание: поиск ведется именно по отображаемому имени, а не логину!)
  *   friend_list -- список друзей пользователя, индетификатор которого передан как параметр
  *   relations  -- получение данных об отношениях текущего пользователя к извлекаемым (TODO: сделать передачу логина пользователя в параметре relations, сейчас берется из app->get_uid)
  *   ext_data -- извлечение дополнительных данных о пользователе из таблицы user_ext (кол-во сообщений, статус и т.п.)
  *   sort -- поле для сортировки
  *   letter -- буква, с которой должно начинаться имя пользователя
  *   last_visit -- извлекать время последнего визита на форум
  *   group -- выбирать пользоватлей, принадлежащих к указанной группе
  *   all_data -- извлечь все данные о пользователе
  *   tag -- выбрать пользователей, указавших соответствующий тег в интересах
  *   location -- пользователи, у которых поле "Место жительства" начитается с указанного значения
  *   contacts -- выбирать контактные данные о пользователях
  *   banned -- изгнанные как по штрафным баллам, так и по через АЦ безусловно
  **/
  function list_users($cond) {
    if (empty($cond)) trigger_error('Не заданы условия выборки пользователей. Прекращаем работу во избежание выгрузки базы целиком!',E_USER_ERROR);
    $where = 'u.id>'.intval(AUTH_SYSTEM_USERS);
    if (empty($cond['status']) && empty($cond['banned'])) $where.=' AND u.status=\'0\''; // по умолчанию ищем только активных пользователей
    elseif (!empty($cond['status']) && $cond['status']!=='all') $where.=' AND u.status=\''.intval($cond['status']).'\'';
    if (!empty($cond['banned'])) $where.=' AND (u.status=\'2\' OR ue.banned_till>'.intval(Library::$app->time).') '; // пользователь может быть изгнан двумя способами: по баллам и безусловно, выбираем его и в том, и в другом случае
    if (!empty($cond['login']))
      if (is_array($cond['login'])) $where.=' AND '.Library::$app->db->array_to_sql($cond['login'],'display_name');
      else $where.=' AND display_name LIKE \''.Library::$app->db->slashes($cond['login']).'%\'';
    if (!empty($cond['friends_list'])) $where .= ' AND (ur1.type=\'friend\' OR ur2.type=\'friend\')';
    if (!empty($cond['letter'])) $where.=' AND display_name LIKE \''.Library::$app->db->slashes($cond['letter']).'%\'';
    if (!empty($cond['group'])) $where.=(is_array($cond['group'])) ? ' AND '.Library::$app->db->array_to_sql($cond['group'],'g.level') : ' AND g.level='.intval($cond['group']);
    if (!empty($cond['location'])) $where.=' AND u.location LIKE \''.Library::$app->db->slashes($cond['location']).'%\'';
    if (!empty($cond['tag'])) $where.=' AND tn.tagname=\''.Library::$app->db->slashes($cond['tag']).'\'';

    if (!empty($cond['all_data'])) $columns = 'u.*';
    else $columns = 'u.id, u.display_name';
    if (!empty($cond['ext_data'])) $columns.=', ue.*';
    if (!empty($cond['relations'])) $columns .= ', ur.type';
    if (!empty($cond['friends_list'])) $columns .= ', CASE WHEN ur1.type=\'friend\' AND ur2.type=\'friend\' THEN \'mutual\' WHEN ur2.type=\'friend\' THEN \'from\' WHEN ur1."type"=\'friend\' THEN \'to\' ELSE \'\' END AS friend_type, u.login ';
    if (!empty($cond['gender'])) $columns.= ', u.gender';
    if (!empty($cond['last_visit'])) $columns.=', lv.visit1';
    if (!empty($cond['group_data'])) $columns.=', g.*';

    $sql = 'SELECT '.$columns.
    ' FROM '.DB_prefix.'user u ';
    if (!empty($cond['ext_data']) ||!empty($cond['group']) || !empty($cond['banned'])) $sql.='LEFT JOIN '.DB_prefix.'user_ext ue ON (u.id=ue.id) ';
    if (!empty($cond['tag'])) $sql.='LEFT JOIN '.DB_prefix.'tagentry te ON (u.id=te.item_id) '.
       'LEFT JOIN '.DB_prefix.'tagname tn ON (te.tag_id=tn.id AND tn.type=\'1\') ';
    if (!empty($cond['relations'])) $sql.='LEFT JOIN '.DB_prefix.'relation ur ON (u.id=ur.from_ AND ur.to_='.($cond['relations']).') ';
    if (!empty($cond['friends_list'])) {
      $sql.='LEFT JOIN '.DB_prefix.'relation ur1 ON (ur1.from_='.intval($cond['friends_list']).' AND ur1.to_=u.id) '.
         'LEFT JOIN '.DB_prefix.'relation ur2 ON (ur2.to_='.intval($cond['friends_list']).' AND ur2.from_=u.id) ';
    }
    if (!empty($cond['group']) || !empty($cond['group_data'])) $sql.='LEFT JOIN '.DB_prefix.'group g ON (ue.group_id=g.level) ';
    if (!empty($cond['last_visit'])) $sql.='LEFT JOIN '.DB_prefix.'last_visit lv ON (lv.uid=u.id AND lv.oid=0 AND lv.type=\'forum\') ';
    $order =!empty($cond['order']) ? $cond['order'] : 'display_name';
    $sql.='WHERE '.$where.' ORDER BY '.$order;
    if (!empty($cond['sort']) && $cond['sort']==='DESC') $sql.=' DESC';

    $offset = !empty($cond['start']) ? $cond['start']: false; // граничные условия для LIMIT
    $count = !empty($cond['perpage']) ? $cond['perpage'] : false;
    if ($count!==false && $offset===false) $offset=0;
    $result = Library::$app->db->select_all($sql,$offset,$count);

    if (!empty($cond['contacts'])) { // подгружаем контактные данные пользователя
      $uids = array();
      for ($i=0, $count=count($result); $i<$count; $i++) $uids[]=$result[$i]['id'];
      if (!empty($uids)) {
        $sql = 'SELECT uc.uid, uc.value, uct.icon, uct.c_title, uct.link '.
        'FROM '.DB_prefix.'user_contact uc, '.DB_prefix.'user_contact_type uct '.
        'WHERE '.Library::$app->db->array_to_sql($uids,'uc.uid').' AND uc.cid=uct.cid';
        $contacts = Library::$app->db->select_super_hash($sql,'uid');
        for ($i=0, $count=count($result); $i<$count; $i++) if (!empty($contacts[$result[$i]['id']])) $result[$i]['contacts']=$contacts[$result[$i]['id']];
      }
    }
    return $result;
  }

  /** Функция проверки корректности Email (по формату) * */
  function valid_email($email) {
   return preg_match('|^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\.\-]+$|', $email);
  }

  /** Отписка пользователя от уведомлений на EMail без изменения других настроек профиля
   * @param $uid integer Идентификатор пользователя
   * @param $mode integer Режим отписки:
   *   0 — отписать от всех уведомлений
   *   1 — отписать от администраторской рассылки
   * **/
  function unsubscribe($uid,$mode=0) {
    if ($mode==1) $sqldata = 'email_broadcasts=\'0\''; // отписка только от администраторской рассылки
    else $sqldata = 'subscribe_mode=0, email_pm=\'0\', email_message=\'0\', email_broadcasts=\'0\''; // отписка вообще от всего
    $sql = 'UPDATE '.DB_prefix.'user_settings SET '.$sqldata.' WHERE id='.intval($uid);
    return Library::$app->db->query($sql);
  }
  
  /** Проверка прав доступа на раздел для пользователя, который не является текущим
  * @param $fids — список разделов, где первым должен идти р
  **/
  function ext_check_access($fids,$gid,$action) {
    static $cache=array();
    $fids[]=0;    
    if (!isset($cache[$gid])) {
      $sql = 'SELECT fid, "view", "'.Library::$app->db->slashes($action).'" FROM '.DB_prefix.'access WHERE '.Library::$app->db->array_to_sql($fids,'fid').' AND gid='.intval($gid);
      $data = Library::$app->db->select_hash($sql,'fid');
      $cache[$gid]=$data;
    }
    else $data=$cache[$gid];
    
    foreach ($fids as $fid) {
      if (isset($data[$fid])) {
        if ($data[$fid]['view']=="1" && $data[$fid][$action]=="1") return true;
        else return false;
      }
    }
    return false;
  }

  /** Получение адреса раздела с домашней страницей пользователя (разделом типа homepage) 
   * @ @param int $uid ID пользователя, false -- получить для текущего пользователя
   * @return string URL раздела или false, если таковой отсутствует
   * **/
  function get_homepage($uid=false) {
    if (!$uid) $uid = Library::$app->get_uid();
    if ($uid<=AUTH_SYSTEM_USERS) return false; // у служебных пользователей не может быть домашней страницы
    $sql = 'SELECT hurl from '.DB_prefix.'forum WHERE module=\'homepage\' AND owner=?';
    $hurl = Library::$app->db->select_str($sql,array($uid));
    if (!$hurl) return false;
    else return Library::$app->http(Library::$app->url($hurl));
  }
}
