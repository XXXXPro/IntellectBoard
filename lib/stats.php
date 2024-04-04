<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2014 4X_Pro, INTBPRO.RU
 *  @url http://intbpro.ru
 *  Библиотека для вывода статистики зарегистрированных пользователей и самых активных тем
 *  ================================ */

class Library_stats extends Library {
  function visits() {
    _dbg('It works!');
    return array('mainpage/stats.tpl',array('test1'=>'1','test2'=>4));
  }
}
