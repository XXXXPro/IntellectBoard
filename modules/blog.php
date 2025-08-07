<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2009-2011,2013-2015 4X_Pro, INTBPRO.RU
 *  http://intbpro.ru
 *  Модуль блога
 *  ================================ */

require_once(BASEDIR.'app/forum.php');
require_once(BASEDIR.'modules/stdforum.php');

class blog extends stdforum {
  function action_view_forum() {
    $fid = $this->forum['id'];

    list($cond,$need_count,$perpage,$tperpage)=$this->view_forum_build_cond($fid); // формируем массив $cond с параметрами для выборки темы
    $cond['first']=true; // извлекаем данные о первых сообщениях
    $cond['order']='first_post_date'; // и сортируем по дате создания сообщения, а не комментария
    if (!$need_count && $this->has_sticky() && empty($cond['with_tags'])) { // sticky-темы выдаем только в том случае, если нет каких-то сложных условий фильтрации и если они вообще предусмотрены данным видом раздела
      $cond['sticky']=true;
      $this->out->sticky=$this->view_forum_get_topics($cond,$tperpage);
      unset($cond['sticky']); //
    }

    list($this->out->pages,$cond)=$this->view_forum_pagedata($perpage, $cond,$need_count);
    if ($this->forum['tags']) $cond['tags']=true;
    $this->out->topics=$this->view_forum_get_topics($cond,$tperpage);

    $tlib = $this->load_lib('topic',true);
    $this->out->topics = $tlib->get_first_posts($this->out->topics,array('ratings'=>$this->forum['rate'],'attach'=>true)); // получаем тексты сообщений для всех выводимых тем

    // обработка сообщений для вывода
    for ($i=0, $count=count($this->out->topics); $i<$count; $i++) {
      $this->out->topics[$i]['post']['norate']=$this->check_rateable($this->out->topics[$i]['post']); // проверка возможности рейтинговать запись
      $this->out->topics[$i]['post']['text']=$this->blog_preprocess($this->out->topics[$i]['post'],$this->out->topics[$i],false); // выполняем предобработку сообщения
    }

    $this->view_forum_misc();
    if (!empty($this->out->moderator)) $this->view_forum_moderator();
    $this->fix_view(); // фиксируем просмотр раздела
  }

  function action_newtopic($anonym=false) {
    $template=parent::action_newtopic($anonym);
    $this->out->editpost['topmsg']='Новая запись в блог';
    return $template;
  }
  
  function view_topic_get_posts($cond) {
    $sort = $this->out->opts['sort']; // смотрим порядок сортировки    
    $perpage = $this->get_posts_perpage($this->out->topic['id']);
    $cond['perpage']=$perpage;    
    if ($sort=='DESC') $cond['start']=0;
    else $cond['start']=max(0,$this->topic['post_count']-$perpage);

    if (isset($_REQUEST['more'])) {
      if ($this->get_request_type()==0) { // если не AJAX-запрос, то увеличиваем количество комментариев до запрошенного значения, и всё
        $cond['perpage']=$perpage+intval($_REQUEST['more']); 
        if ($sort!='DESC') $cond['start']=max(0,$this->topic['post_count']-$cond['perpage']);
      }
      else { // для AJAX-запросов — высчитываем смещение так, чтобы выдать perpage последних
        if ($sort=='DESC') $cond['start']=$_REQUEST['more'];
        else $cond['start']=max($this->topic['post_count']-$perpage-intval($_REQUEST['more']),1);
        $cond['perpage']=min($perpage,$this->topic['post_count']-$_REQUEST['more']-1); // проверка, чтобы не выдавать сам пост
      }
    }

    return parent::view_topic_get_posts($cond);
  }
  
  function action_view_topic() {
    if (!empty($_REQUEST['page'])) $this->redirect($this->http($this->url($this->topic['full_hurl']))); // у блога страниц нет.
    $this->forum['sticky']=3; // первое сообщение (текст записи блога) -- всегда прикрепленное
    parent::action_view_topic();
    $sort = $this->out->opts['sort']; // смотрим порядок сортировки
    if (count($this->out->posts)==$this->topic['post_count']) { // если из базы вытащили все сообщения
      if ($sort=='DESC') $this->out->article = array_pop($this->out->posts); // если в настройках стоит обратный порядок вывода сообщений, статья лежит в последнем сообщении
      else $this->out->article = array_shift($this->out->posts); // иначе -- в первом
    }
    else { // иначе статью нужно вытаскивать из базы отдельно
      list($cond2,$tmp,$tmp) = $this->view_topic_build_cond($this->topic['id']);
      $cond2['id']=$this->topic['first_post_id'];
      $tmp = parent::view_topic_get_posts($cond2); // вызываем сразу родительнский класс, так как для получения статьи нам не нужна дополнительная коррекция парамеров
      $this->out->article = $tmp[0];
    }
    $perpage = $this->out->opts['perpage'];
    unset($this->out->pages); // разбиение на страницы в блоге не используется, поэтому убираем данные о нем, чтобы не генерировались ненужные теги link
    $this->out->article['text']=$this->blog_preprocess($this->out->article,$this->topic,true); // обработка записи блога (teaserbreak и тому подобное)
    $this->out->editpost['topmsg']='Написать комментарий';
    $this->out->form_params['form_class']='miniform';
    $this->out->more = isset($_REQUEST['more']) ? intval($_REQUEST['more'])+$perpage: $perpage;
    $this->out->comments_remain=min($perpage,$this->topic['post_count']-count($this->out->posts)-1); 
    if (isset($_REQUEST['more']) && $this->get_request_type()==1) $this->out->comments_remain=$this->topic['post_count']-intval($_REQUEST['more'])-$perpage-1;
    if (empty($this->topic['descr'])) {
      $text = strip_tags($this->out->article['text']);
      $descr = substr($text,0,strpos($text,'.')+1);
      $this->meta('description',strip_tags($descr));
    }
  }

  function set_opengraph() {
    if (!empty($this->topic)) {
      $this->meta('og:title',$this->topic['title'],true);
      $this->meta('og:type','article',true);
      $this->meta('og:url',$this->http($this->url($this->topic['full_hurl'])),true);
      $this->meta('og:description',$this->topic['descr'],true);
      $this->meta('og:site_name',$this->get_opt('site_title'),true);
      if (!empty($this->out->article['attach']) && !empty($this->out->article['attach'][0]) && $this->out->article['attach'][0]['format']==='image') {
        $attach=$this->out->article['attach'][0];
        $this->out->article_pic = $this->http($this->url('f/up/1/'.$attach['oid'].'-'.$attach['fkey'].'/'.$attach['filename']));
        $this->meta('og:image',$this->out->article_pic,true);
      }
      else {
        $sitepic = $this->get_opt('site_picture');
        if ($sitepic && strpos($sitepic,'://')===false) $sitepic=$this->http($this->url($sitepic));
        if (!empty($sitepic)) $this->meta('og:image',$sitepic,true);
        $this->out->article_pic = $sitepic;
      }
    }
    else parent::set_opengraph();
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
    for ($i=0; $i<$count; $i++) {
      $data[$i]['text']=$data[$i]['post']['text']; // сообщение уже обработано на предмет boardcode в get_first_posts
      $data[$i]['link']=$this->http($this->url($this->forum['hurl'].'/'.$data[$i]['t_hurl']));
      $data[$i]['title']=$data[$i]['title'];
      $data[$i]['postdate']=$data[$i]['post']['postdate'];
      $data[$i]['author']=$data[$i]['post']['author'];
      $data[$i]['comments']=intval($data[$i]['post_count'])-1;
    }
    $this->out->items=$data;
  }
  
  function action_turbo() {
    $tlib = $this->load_lib('topic',true);
    $cond['topics']=true;
    $cond['noflood']=true;
    $cond['sort']='DESC';
    $cond['first']=true;

    $period = 10;
    if ($this->if_modified_time) $cond['after_time']=intval($this->if_modified_time);
    $cond['offset']=0;
    $cond['perpage']=500; // таков лимит Яндекса

    /* @var Library_bbcode */
    $bbcode = $this->load_lib('bbcode');

    $this->out->intb->link=$this->http($this->url($this->forum['hurl'].'/'));
    $this->out->intb->descr=$this->forum['descr'];

    $cond['order']='first_post_date';
    $cond['fid']=$this->forum['id'];

    $data = $tlib->list_topics($cond);
    $data = $tlib->get_first_posts($data);

    if (empty($data) && $this->if_modified_time) $this->output_304();
    
    $buffer = '<?xml version="1.0" encoding="utf-8"?>
<rss
    xmlns:yandex="http://news.yandex.ru"
    xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:turbo="http://turbo.yandex.ru"
    version="2.0">
    <channel>
    <title>'.htmlspecialchars($this->forum['descr']).'</title>
    <description>'.htmlspecialchars($this->forum['descr']).'</description>
    <link>'.$this->http($this->url($this->forum['hurl'].'/')).'</link>';
    
    $count=count($data);
    for ($i=0; $i<$count; $i++) {
      $data[$i]['post']['html']=1;
      $content = $data[$i]['post']['text'];
      $content = str_replace('&mdash;','—',$content);
//      $content = str_replace('&','&amp;',$content);
      $content = preg_replace('|\&\w+;|','&amp;$1',$content);
      $content = preg_replace('|\[teaserbreak(=[^\]]*)?\]|','',$content);
      $content = str_replace('" frameborder="0" allowfullscreen></iframe>','"></iframe>',$content);
      $count2 = preg_match_all('|<img([^>]*)>|',$content,$matches);
      for ($j=15;$j<$count2;$j++) $content=str_replace($matches[0][$j],'',$content); // соблюдаем ограничение на 15 картинок
      $buffer.='<item turbo="true">';
      $buffer.='<link>'.$this->http($this->url($this->forum['hurl'].'/'.$data[$i]['t_hurl'])).'</link>';
      $buffer.='<title>'.htmlspecialchars($data[$i]['title']).'</title>';
      $buffer.='<pubDate>'.date('r',$data[$i]['post']['postdate']).'</pubDate>';
      $buffer.='<turbo:author>'.htmlspecialchars($data[$i]['post']['author']).'</turbo:author>';
      $buffer.='<turbo:content>'.$content;
      $buffer.="</turbo:content></item>";
    }
    $buffer.='</channel></rss>';
    return $buffer;    
  }

  function blog_preprocess($post,$topic,$full=true) {
    if ($full) {
      $post['text']=preg_replace('|\[teaserbreak(=[^\]]*?)?\]|','<a name="readmore"></a>',$post['text']);
    }
    else {
      $pos=strpos($post['text'],'[teaserbreak');
      if ($pos!==false) {
        if (preg_match('|\[teaserbreak=([^\]]*)\]|',$post['text'],$matches)) $nexttext = str_replace('&quot;','',$matches[1]);
        else $nexttext='Читать далее…';
        $post['text']=substr($post['text'],0,$pos).' <a href="'.$topic['t_hurl'].'#readmore">'.$nexttext.'</a>';
      }
    }
    $post['text'] = preg_replace('|</li>\s*<br />|is','</li>',$post['text']);
    $post['text'] = preg_replace('|</ul>\s*<br />|is','</ul>',$post['text']);
    $post['text'] = preg_replace('|(<ul[^>]*>)\s*<br />|is','$1',$post['text']);
    $post['text'] = preg_replace('|<br />\s*</li>|is','</li>',$post['text']);
    $post['text'] = preg_replace('|<br />\s*<a name="readmore"></a>\s*<br />|is','<a name="readmore"></a>',$post['text']);
    $post['text'] = preg_replace('|</p>\s*<br />\s*<p|is','</li><p',$post['text']);
    $post['text'] = preg_replace('#</p>\s*<br />\s*<(h\d|ul|ol)#is','</li><$1',$post['text']);
    $post['text'] = preg_replace('|</pre>\s*<br />|is','</pre>',$post['text']);
    return $post['text']; 
  }

  function update_extdata() {
     /* @var Library_topic */
     $tlib = $this->load_lib('topic',false);
     if (!$tlib) return false; // если библиотеку тем загрузить не удалось, выходим, не отображая ничего

     $cond['fid']=$this->forum['id'];
     $cond['first']=true;
     $forumlib = $this->load_lib('forums',false);
     if ($forumlib) $forum = $forumlib->get_forum($this->forum['id'],true);
     else $forum=$this->forum;
     
     $cond['perpage']=(!empty($forum['extdata']['mainpage_posts'])) ? $forum['extdata']['mainpage_posts'] : 3; // число сообщений для вывода, по умолчанию три
     $cond['order']='first_post_date';
     $topics = $tlib->list_topics($cond);

     $flib = $this->load_lib('forums',false);
     if ($flib) $flib->update_forum($this->forum['id'],array('last_topics'=>$topics));
  }
   
  function post_redirect($pid) {
    $tid=$this->topic['id'];
    $cond['tid']=$tid;
    if ($pid==$this->topic['first_post_id']) $this->redirect($this->http($this->url($this->topic['full_hurl']))); // если запросили саму статью, то редирект на обычный URL
    if ($pid==$this->topic['last_post_id']) $this->redirect($this->http($this->url($this->topic['full_hurl'].'#p'.intval($pid)))); // если запросили последнее сообщение, то тоже редирект сразу, так как оно видно всегда

    list($need_count,$perpage,$sort)=$this->view_topic_params($tid);
    $total = $this->topic['post_count']-1; // общее количество сообщений без текста статьи
    $tlib=$this->load_lib('topic',true); // ошибка загрузки библиотеки является критичной, без нее перехода не получится
    $more = 0;
    
    $cond['after_pid']=$pid;  
    $count = $tlib->count_posts($cond)+1;
    $more = $count - $perpage;

    if ($more>0) $this->redirect($this->http($this->url($this->topic['full_hurl'].'?more='.intval($more).'#p'.$pid)));
    else $this->redirect($this->http($this->url($this->topic['full_hurl'].'#p'.$pid)));    
  }  

  function set_form_fields($perms,$action,$topic=false) {
    if ($this->is_guest()) $result['username']=true;
    if ($topic) {
      $result['topic_block']=true; // нужно ли выводить блок «параметры темы» и поле topic_title в нем
      $result['topic_descr']=true;
      $result['topic_hurl']=true;
    }
    $result['area_class']='bbcode'; // класс (или классы) для вывода основного блока textarea
//    if (!$topic) $result['form_class']='miniform';
    $result['area_rows']=$topic ? 40 : 6; // если пишем сообщение, высота строк должна быть большой, если комментарий — нет.
    $result['attach']=$perms['attach']; // если есть права, выводим блок прикрепления файлов
    $result['allowed']=false; // список разрешенного: HTML, BBCode и т.п.

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
    }
    if ($topic) {
      $result['sticky']=$perms['sticky'];
      $result['sticky_post']=false; // для блога не имеет смысла
      $result['favorites']=true;
    }
    $result['lock']=$perms['lock']; // возможность закрыть тему определяется соответствующими правами
    $result['poll']=$topic && $perms['poll'];

    $result['tags']=$perms['tags'] && $topic; // теги можно редактировать только для тем
    $result['postdate']=$topic;  // для блогов возможны backdated-записи
    $result['social_login']=!$topic && $this->is_guest(); // для гостей возможна авторизация через соцсети для комментариев
    if ($this->forum['owner']>AUTH_SYSTEM_USERS && $this->get_uid()==$this->forum['owner']) $result['no_export']=true; // если раздел — собственный, то репост в соцсети может делать только владелец

    return $result;
  }    

  function set_rss() {
    $this->link($this->url($this->forum['hurl'].'/rss.htm'),'alternate',false,'application/rss+xml'); // выводим ссылку на RSS в
    /* $websub = $this->get_opt('websub_hub');
    if ($websub) {
       if ($this->action==='view_topic') {
         $this->link($websub,'hub');
         $this->link($this->http($this->url($this->topic['full_hurl'])),'self');
       }
       if ($this->action==='view_forum') {
         $this->link($websub,'hub');
         $this->link($this->http($this->url($this->forum['hurl'].'/')),'self');
       }
    }*/
    if ($this->forum['micropub']) {
      $this->link($this->http($this->url($this->forum['hurl'].'/micropub.htm')),'micropub');
    }
    return array(array('url'=>$this->url($this->forum['hurl'].'/rss.htm'),'title'=>'Подписка на обновления'));
  }
  
  /* Переопределяем get_request_type для действия turbo, в котором выводится XML с Турбо-страницей для Яндекса. */
  function get_request_type() {
    if ($this->action=='turbo') return 4;
    elseif ($this->action=='micropub') return 4;
    else return parent::get_request_type();
  }

  function get_mime() {
    if ($this->action=='turbo') return 'application/xml; charset=utf-8';
    else return parent::get_mime();
  }

}
