<?php

class Library_instantcms extends Library implements iExternalAuth {
  function get_user_by_session() {
    $offset = 0; // в IntB номера пользователей смещены на 3 из-за существования системных пользователей (Guest, System, New User)

//    _dbg('Called!');
    $intb_user = isset($_SESSION['IntB_auth']) && isset($_SESSION['IntB_user']) && isset($_SESSION['IntB_user']['id']) ? $_SESSION['IntB_user']['id'] : 1;
    if (empty($_SESSION['user']) && $intb_user==1) return false; // гость и там, и там
    if ($_SESSION['user']['id']==$intb_user+$offset && empty($_SESSION['profile_updated'])) return false; // уже авторизован в IntB, нет необходимости загружать данные еще раз
    if (empty($_SESSION['user'])) return 1; // если пользователь разлогинился в InstantCMS, то принудительно делаем его гостем в IntB
    else return $_SESSION['user']['id']+$offset; // иначе загружаем профиль пользователя
    return false;
  }

  function get_user_by_login($login,$password) {
    return false;
  }

  function on_logout() {
    if (!empty($_SESSION['user'])) unset($_SESSION['user']); // при выходе с форума обнуляем также пользователя и в основной сесиии
  }

  function on_register($data,$settings) {
  }

  function on_profile_update($data,$settings) {
  }

  function on_profile_delete($uid) {
  }


  function allow_register() {
    return false;
  }

  function allow_update() {
    return true;
  }


}
