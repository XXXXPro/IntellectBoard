<?php
/**
 * ================================
 *
 * @package IntBPro
 * @author 4X_Pro <admin@openproj.ru>
 * @version 3.0
 * @copyright 2018,  4X_Pro, INTBPRO .RU
 * http://intbpro.ru
 * Модуль Twitter-подобного микроблога
 * ================================
 */
require_once(BASEDIR.'app/forum.php');
require_once(BASEDIR.'modules/stdforum.php');

class micro extends stdforum {
  function action_view() {
    $fid = $this->forum['id'];
    list($cond,$need_count,$perpage,$tperpage)=$this->view_forum_build_cond($fid); // формируем массив $cond с параметрами для выборки темы
    unset($cond['sticky']); // sticky-темы в микроблоге никак не выделяются, поэтому сбрасываем этот признак, чтобы вывести все
//    $cond['first']=true; // извлекаем первое сообщение каждой темы, чтобы сразу вывести
    // $cond['sort']='first_post_date'; // сортируем по времени сообщений

    list($this->out->pages,$cond)=$this->view_forum_pagedata($perpage, $cond,$need_count);
    $this->out->topics=$this->view_forum_get_topics($cond,$tperpage);

    /** @var Library_topic **/
    $tlib = $this->load_lib('topic',true);
    $this->out->topics = $tlib->get_first_posts($this->out->topics,array('ratings'=>$this->forum['rate'],'attach'=>true)); // получаем тексты сообщений для всех выводимых тем
    for ($i=0, $count=count($this->out->topics); $i<$count; $i++) {
      $this->out->topics[$i]['post']['norate']=$this->check_rateable($this->out->topics[$i]['post']); // проверяем, можно ли рейтинговать сообщение
      $this->out->topics[$i]['post']['editable']=$this->check_editable($this->out->topics[$i]['post']); // проверяем, можно ли редактировать сообщение
    }

    $this->view_forum_misc();
    if (!empty($this->out->moderator)) $this->view_forum_moderator();
    $this->fix_view(); // фиксируем просмотр раздела
    $this->out->form_params = $this->set_form_fields($this->out->perms,'view',true);
    $this->out->editpost['post'] = $tlib->set_new_post($this->out->perms);
    $this->out->editpost['action']='newtopic.htm';
    $this->out->delete_key=$this->gen_auth_key($this->get_uid(),'delete_post',$this->url('moderate/'.$this->forum['hurl'].'/'));
    $this->out->mod_no_marks = true; // запрещаем помечать сообщения при просмотре в разделе
    $this->out->authkey=$this->gen_auth_key($this->get_uid(),'newtopic');
  }

  function action_newtopic($anonym=false) {
    if ($this->is_post()) {
      $title=substr($_POST['post']['text'],0,80);
      $pos=strrpos($title,' ');
      if ($pos!==false) $title=substr($title,0,$pos).'…';
      else $title=substr($title,0,79).'…';
      $_POST['topic']['title']=$title;
    }
    parent::action_newtopic();
    $this->out->form_params = $this->set_form_fields($this->out->perms,'newtopic',true);
    if ($this->is_post()) $this->update_extdata();
  }

  function action_post() {
    $this->output_404('Для микроблогов невозможен просмотр отдельных сообщений!');
  }

  function action_view_topic() {
    // list($cond,$need_count,$perpage,$tperpage)=$this->view_forum_build_cond($fid); // формируем массив $cond с параметрами для выборки темы
    $this->output_404('Для микроблогов невозможен просмотр отдельных тем!');
  }

   function action_rss() {
    $tlib = $this->load_lib('topic',true);
    $cond['topics']=true;
    $cond['noflood']=true;
    $cond['sort']='DESC';
    $cond['first']=true;

    $period = 10;
    $cond['after_time']=max(intval($this->if_modified_time),$this->time-$period*24*60*60);

    $limit = $this->get_opt('rss_max_items');
    if (!$limit) $limit=250;
    $cond['offset']=0;
    $cond['perpage']=$limit; //

    /* @var Library_bbcode */
    $bbcode = $this->load_lib('bbcode');

    $this->out->intb->link=$this->http($this->url($this->forum['hurl'].'/'));
    $this->out->intb->descr=$this->forum['descr'];

    $cond['order']='first_post_date';
    $cond['fid']=$this->forum['id'];

    $data = $tlib->list_topics($cond);
    $data = $tlib->get_first_posts($data);

    if (empty($data) && $this->if_modified_time) $this->output_304();
    $count=count($data);
    $tperpage = $this->get_opt('topics_per_page','user'); // берем из настроек пользователя
    if (!$tperpage) $tperpage = $this->get_opt('topics_per_page');  // берем из настроек сайта в целом
    if (!$tperpage) $tperpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль    
    for ($i=0; $i<$count; $i++) {
      $data[$i]['text']=$bbcode->parse_msg($data[$i]['post']);
      $data[$i]['text']=html_entity_decode($data[$i]['text']);
      $data[$i]['title']=strip_tags($data[$i]['text']);
      $data[$i]['text'] = preg_replace('|<a\W[^>]*?href=[\'"]([^>]+?)[\'"][^>]*?>(.*?)</a>|u','$2 ($1)',$data[$i]['text']);
      $data[$i]['link']=$this->http($this->url($this->forum['hurl'].'/'));
      if ($i>$tperpage) $data[$i]['link'].=ceil($i/$tperpage).'.htm';
      $data[$i]['link'].='#p'.$data[$i]['id'];
      $data[$i]['postdate']=$data[$i]['post']['postdate'];
      $data[$i]['author']=$data[$i]['post']['author'];
    }
    $this->out->items=$data;
  }

  /** В этой функции делается редирект после создания новой темы. Для микроблога это редирект на главную страницу **/
  function newtopic_redirect($hurl,$post_id) {
    $this->redirect($this->forum['hurl'].'/');
  }

  /** Обновление расширенных данных о разделе, где, в частности, кешируется несколько последних сообщений  **/
  function update_extdata() {
     $tlib = $this->load_lib('topic',false);
     if (!$tlib) return false; // если библиотеку тем загрузить не удалось, выходим, не отображая ничего

     $cond['fid']=$this->forum['id'];
     $cond['first']=true;
     $cond['perpage']=(!empty($this->forum['extdata']['mainpage_posts'])) ? $this->forum['extdata']['mainpage_posts'] : 3; // число сообщений для вывода, по умолчанию три
     $cond['order']='first_post_date';
     $topics = $tlib->list_topics($cond);

     $topics = $tlib->get_first_posts($topics,array('ratings'=>false,'attach'=>true)); // получаем тексты сообщений для всех выводимых тем
     $flib = $this->load_lib('forums',false);
     if ($flib) $flib->update_forum($this->forum['id'],array('last_topics'=>$topics));
  }

  /** Предварительная проверка сообщения. Выполняются все те же проверки, что и в stdforum, но еще нужно добавить проверку на длину сообщения, установленную конкретно для этого блога.**/
  function post_pre_check($post,$perms,$parsed,&$premod,$timeout_check=true) {
    $result = parent::post_pre_check($post,$perms,$parsed,$premod,$timeout_check);
    $test_str = strip_tags($parsed);
    if (function_exists('mb_strlen')) $len = mb_strlen($test_str);
    else $len = strlen($test_str);
    $maxlen = (!empty($this->forum['extdata']['max_post_length'])) ? $this->forum['extdata']['max_post_length'] : 280; // если максимальная длина не задана, делаем такую же, как в Twitter
    if ($len>$maxlen) $result[]=array('text'=>$this->incline($maxlen,'Запись превышает допустимую длину в %d символ!','Запись превышает допустимую длину в %d символа!','Запись превышает допустимую длину в %d символов!'),'level'=>3);
    return $result;
  }

  function set_form_fields($perms,$action,$topic=false) {
    if ($this->is_guest()) $result['username']=true;
    if ($action==='view') $result['form_class']='miniform';
    $result['topic_block']=false; // нужно ли выводить блок «параметры темы» и поле topic_title в нем
    $result['topic_descr']=false;
    $result['topic_hurl']=false;
    $result['area_class']='bbcode'; // класс (или классы) для вывода основного блока textarea
    $result['area_rows']=5; // высота основного блока textarea в строках
    $result['attach']=$perms['attach']; // если есть права, выводим блок прикрепления файлов
    $result['allowed']=false; // список разрешенного: HTML, BBCode и т.п.

    if ($action==='edit') { // если тему/сообщение не редактируем, а создаем новое
      $result['delete']=$perms['delete']; // если редактируем сообщение и есть достаточно прав, выводим checkbox для удаления
      $result['warning']=$this->is_moderator();
      $result['value']=$perms['value']; // ценность сообщения разрешено задавать только при редактировании
      $result['lock_post']=$perms['lock']; // закрыть сообщение можно только при редактировании
    }
    $result['sticky']=false;
    $result['sticky_post']=false;
    $result['poll']=false;

    $result['tags']=$perms['tags'] && $topic; // теги можно редактировать только для тем
    $result['postdate']=true;  // для микроблога возможны backdated entries

    return $result;
  }

}
