<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.05
 *  @copyright 2021 4X_Pro, INTBPRO.RU
 *  @url https://intbpro.ru
 *  Модуль фотогалереи
 *  ================================ */

require_once(BASEDIR.'modules/blog.php');

class gallery extends blog {
  function action_view_forum()  {
    $this->out->gallery_x = $this->get_opt('gallery_preview_x') ?: $this->get_opt('posts_preview_x') ?: 392;
    $this->out->gallery_y = $this->get_opt('gallery_preview_y') ?: $this->get_opt('posts_preview_y') ?: 294;
    return parent::action_view_forum();
  }


  function action_view_topic()  {
    $this->out->gallery_x = $this->get_opt('gallery_preview_x') ?: $this->get_opt('posts_preview_x') ?: 392;
    $this->out->gallery_y = $this->get_opt('gallery_preview_y') ?: $this->get_opt('posts_preview_y') ?: 294;
    return parent::action_view_topic();
  }

  function view_forum_build_cond($fid) {
    $conds = parent::view_forum_build_cond($fid);
    $conds[0]['attach']=true; // добавляем условие, чтобы извлекать те приложенные файлы, которые помечены как главные
    $conds[0]['attach_count']=true; // извлекаем количество приложенных файлов к первому сообщению каждой темы
    return $conds;
  }

  // отображение всех фото на одной странице
  function action_full_photos() {
    $this->out->gallery_mode = 1;
    return $this->action_view_topic() ?: 'gallery/view_topic.tpl'; 
  }

  // отображение EXIF-данных (пришлось отказаться в связи с тем, что EXIF-данные теряются при уменьшении через GD)
  function action_exif() {
    $this->out->gallery_mode = 2;
    return $this->action_view_topic() ?: 'gallery/view_topic.tpl'; 
  }

  // переопределяем newtopic_redirect, чтобы сразу переходить к редактированию альбома
  function newtopic_redirect($hurl,$post_id) {
    $this->redirect($this->http($this->url($this->forum['hurl'].'/'.$hurl.'/edit.htm?id='.intval($post_id))));
  }

  // переопределяем post_redirect, чтобы была возможность возвращаться к редактированию альбома 
  function post_redirect($pid) {
    if ($this->action=='edit' && isset($_POST['continue'])) $this->redirect($this->http($this->url($this->topic['full_hurl']).'edit.htm?id='.intval($pid)));
    else parent::post_redirect($pid);
  }

  function action_newtopic($anonym=false) {
    if ($this->is_post()) { // если название альбома пустое, 
      if (empty($_POST['topic']['title'])) $_POST['topic']['title']='Безымянная серия, загруженная '.$this->format_date($this->time,"%d %B %Y",true,false);
      if (empty($_POST['post']['text'])) $_POST['post']['text']=' ';
    }
    parent::action_newtopic($anonym);
    return 'gallery/reply.tpl';
  }

  function action_edit() {
    if (empty($_POST['topic']['title'])) $_POST['topic']['title']=$this->topic['title'];
    if (empty($_POST['post']['text'])) $_POST['post']['text']=str_repeat(' ',$this->get_opt('post_minlength') ?: 1);
  
    $this->out->delete_key =  $this->gen_auth_key(false,'delete_photo');

    if (!empty($_POST['photo_descr'])) foreach ($_POST['photo_descr'] as $fkey=>$value) {
      if (empty($value)) $value=null;
      $this->db->update(DB_prefix.'file',array('descr'=>$value),'fkey=\''.$this->db->slashes($fkey).'\'');
    }

    if (!empty($_POST['cover'])) {
      $sql = 'UPDATE '.DB_prefix.'file SET is_main = CASE WHEN fkey=\''.$this->db->slashes($_POST['cover']).'\' THEN \'1\' ELSE \'0\' END WHERE oid='.intval($_POST['id']);
      $this->db->query($sql);
    }

    parent::action_edit();    
    return 'gallery/reply.tpl';
  }

  function action_delete_attach() {
    if (empty($_GET['authkey'])) $this->output_403('Неверно указан ключ аутентификации!');
    if (empty($_GET['fkey'])) $this->output_404('Не указан код удаляемого файла!');
    if (empty($_GET['id'])) $this->output_404('Не указан идентификатор сообщения!');

    /** @var Library_attach $atlib */
    $atlib = $this->load_lib('attach',true);
    $atlib->delete_uploads(array($_GET['fkey']),$_GET['id']);
    $atlib->check_main_attach($_GET['id']);    
    if ($this->get_request_type()!=1) {
      $this->redirect($this->http($this->url($this->forum['hurl'])));
    }
    else $this->output_json(array('result'=>'done'));
  }

  // Кешируем несколько последних тем, включая фото обложек, для показа на главной 
  function update_extdata() {
    /* @var Library_topic */
    $tlib = $this->load_lib('topic',false);
    if (!$tlib) return false; // если библиотеку тем загрузить не удалось, выходим, не отображая ничего

    $cond['fid']=$this->forum['id'];
    $cond['first']=true;
    $cond['attach']=true; // добавляем условие, чтобы извлекать те приложенные файлы, которые помечены как главные
    $forumlib = $this->load_lib('forums',false);
    if ($forumlib) $forum = $forumlib->get_forum($this->forum['id'],true);
    else $forum=$this->forum;
    
    $cond['perpage']=(!empty($forum['extdata']['mainpage_posts'])) ? $forum['extdata']['mainpage_posts'] : 3; // число сообщений для вывода, по умолчанию три
    $cond['order']='first_post_date';
    $topics = $tlib->list_topics($cond);

    $flib = $this->load_lib('forums',false);
    if ($flib) $flib->update_forum($this->forum['id'],array('last_topics'=>$topics));
 }  
}