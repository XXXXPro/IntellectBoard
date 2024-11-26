<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2009-2011,2013 4X_Pro, INTBLITE.RU
 *  http://intblite.ru
 *  Модуль анонимного раздела
 *  ================================ */

require_once (BASEDIR.'modules/stdforum.php');

class anon extends stdforum {

  function init_object() {
    parent::init_object();
    $this->forum['sticky_post']=3;
  }

  function view_forum_misc() {
    $pids = array();
    $tlib = $this->load_lib('topic',true);
    $this->out->topics = $tlib->get_first_posts($this->out->topics,array('ratings'=>false,'attach'=>true)); // получаем первые сообщения (вопросы) для всех выводимых тем
    $this->out->start_text = $this->get_text($this->forum['id'],2);  // текст с типом 2 -- вводный
    $this->out->mod_no_marks = true; // запрещаем помечать сообщения при просмотре в разделе

    $this->out->perms = $tlib->get_permissions();
    if ($this->is_post()) $this->out->editpost=$_POST;
    else $this->out->editpost['post']=$tlib->set_new_post($this->out->perms);
    $this->out->editpost['action']='newtopic.htm';
    $this->out->editpost['edittopic']=true;
    $this->out->editpost['topic']['title']='Тема '.$this->long_date($this->time);

    if ($this->is_guest() && $this->get_opt('captcha')) {
      $antibot = $this->load_lib('antibot');
      /* @var $antibot Library_antibot */
      if ($antibot) $antibot->captcha_generate();
    }

    $this->out->form_params = $this->set_form_fields($this->out->perms,'view_forum',true);
    if ($this->is_moderator()) $this->view_forum_moderator();
    $this->out->editpost = $tlib->set_new_post($this->out->perms);
    $this->out->editpost['action']='newtopic.htm';
    $this->out->editpost['topmsg']='Задать вопрос';
    $this->out->authkey = $this->gen_auth_key(false,'newtopic'); // аутентификационный ключ нужен для того, чтобы если пользователя разлогинит по таймауту, его сообщение все равно бы отправилось
  }

  function action_newtopic($anonym=false) {
      if ($this->is_post()) {
         if (strlen($_POST['post']['text'])<80) $_POST['topic']['title']=$_POST['post']['text'];
         else {
           $_POST['topic']['title']=substr($_POST['post']['text'],0,80);
           $pos = strrpos($_POST['topic']['title'], ' ');
           if ($pos>0) $_POST['topic']['title']=substr($_POST['post']['text'],0,$pos);
         }
      }
      $template = parent::action_newtopic(!empty($_POST['anonymous']));
      $this->out->editpost['topmsg']='Ваш вопрос';
      return $template;
  }

  /** Построение массива условий для выборки тем в разделе на основе данных, получаемых из forum_params **/
  function view_forum_build_cond($fid) {
    $need_count = false;
    $perpage = $this->get_topics_perpage($fid);
    $tperpage = $this->get_posts_perpage();
    $cond['fid']=$this->forum['id'];
    $cond['last']=true;
    $cond['first']=true;
    $cond['sort']='DESC';
    $cond['views']=true;
    $cond['order']='first_post_id'; // сортируем всегда по id первого сообщения, чтобы темы выводились в том порядке, в котором они созданы

    return array($cond,$need_count,$perpage,$tperpage);
  }

  function view_topic_misc($fid) {
    $this->out->del_key= $this->gen_auth_key(false,'delete_topic',$this->url('moderate/'.$this->out->topic['full_hurl']));
    $this->out->del_post_key= $this->gen_auth_key(false,'delete_post',$this->url('moderate/'.$this->out->topic['full_hurl']));
  }

  function action_reply($anonym=false) {
    $old_uid = $this->get_uid();
    parent::action_reply(!empty($_POST['anonymous']));
    return 'anon/newtopic.tpl';
  }

  function set_title() {
    if ($this->action==='view_topic') return 'Вопрос №'.$this->topic['id'].' :: '.$this->topic['title'];
    else return parent::set_title();
  }
  
  function set_form_fields($perms,$action,$topic=false) {
    if ($this->is_guest()) $result['username']=true;
    if ($topic) {
      $result['topic_block']=false; // нужно ли выводить блок «параметры темы» и поле topic_title в нем
      $result['topic_descr']=false;
      $result['topic_hurl']=false;
    }
    $result['area_class']='bbcode'; // класс (или классы) для вывода основного блока textarea
    $result['area_rows']=($action=='viw_forum') ? 4 : 10; // высота основного блока textarea в строках
    $result['attach']=$perms['attach']; // если есть права, выводим блок прикрепления файлов
    $result['allowed']=false; // список разрешенного: HTML, BBCode и т.п.
    if ($action==='view_forum') $result['form_class']='miniform'; // в разделе выводим минималистичную форму 
    
    if ($action!=='edit') { // если тему/сообщение не редактируем, а создаем новое
      if (!$this->is_guest()) { // зарегистрированным пользователям показываем checkboxes для добавления в закладки и подписки
        $result['subscribe']=true;
        $result['bookmark']=false; 
      }      
    }
    else {
      $result['delete']=$perms['delete']; // если редактируем сообщение и есть достаточно прав, выводим checkbox для удаления
      $result['warning']=$this->is_moderator();
      $result['value']=$perms['value']; // ценность сообщения разрешено задавать только при редактировании
      $result['lock_post']=$perms['lock']; // закрыть сообщение можно только при редактировании
      $result['tags']=$perms['tags'] && $topic; // теги можно редактировать только для тем
      if ($topic) $result['topic_block']=true; // если редактируем пост с вопросом, показываем поле с темой
    }
    if ($topic) {
      $result['sticky']=false;
      $result['sticky_post']=false;      
    }
    $result['lock']=$perms['lock']; // возможность закрыть тему определяется соответствующими правами
    $result['poll']=false;
    
    $result['postdate']=false;  // для стандартных форумов менять дату сообщения нельзя в любом случае
    
    return $result;    
  }  
}
