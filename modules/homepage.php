<?php /** ================================
 *  @package IntBPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.02
 *  @copyright 2007,2010-2012,2014 4X_Pro, INTBPRO.RU
 *  http://intbpro.ru
 *  Модуль вывода личных страниц пользователя с подразделами
 *  ================================ */

 require_once(BASEDIR.'app/forum.php');

 class homepage extends Application_Forum {

  function action_view() {
    $this->out->static_text = $this->get_text($this->forum['id'],2); // получаем текст статической страницы (для большинства разделов 2 -- это код вводного текста, но для статического/контейнера -- основного)
    $this->out->mod_link = $this->is_moderator();
    $this->meta('description',$this->forum['descr']);

    $this->out->subforums = $this->get_subforums();
  }

  function action_edit() {
    if (!$this->is_moderator()) $this->output_403('У вас нет прав для редактирования этого раздела!');
    if ($this->is_post()) {
      $misclib = $this->load_lib('misc',true);
      $text = $_POST['text'];
      if (trim(strip_tags($text))==='') $text='';
      $misclib->save_text($text,$this->forum['id'],2);
      $this->out->static_text = $text;
    }
    else {
      $this->out->static_text = $this->get_text($this->forum['id'],2); // получаем текст статической страницы (для большинства разделов 2 -- это код вводного текста, но для статического/контейнера -- основного)
    }
    $this->out->authkey=$this->gen_auth_key();
  }

  function  get_action_name() {
    if ($this->action=='view') $result='Просматривает страницу &laquo;%s&raquo;';
    else $result=parent::get_action_name();
    return $result;
  }

  function set_location() {
    $result = parent::set_location();
    if ($this->action==='view') $result[1]=array($this->forum['title']);
    else {
      $result[1]=array($this->forum['title'],$this->url($this->forum['hurl'].'/'));
    }
    if ($this->action==='edit') {
      $result[2]=array('Редактирование текста раздела');
    }
    return $result;
  }
}
