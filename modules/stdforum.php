<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.05
 *  @copyright 2007,2009-2011,2013-2015,2018,2020-2023 4X_Pro, INTBPRO.RU
 *  @url https://intbpro.ru
 *  Модуль вывода списка тем в обычном форуме
 *  ================================ */

require_once(BASEDIR.'app/forum.php');

class stdforum extends Application_Forum {
  /** Вывод списка тем в разделе
  * Действия:
  * Проверка наличия правил у раздела
  * Получение вводного текста
  * Определение количества тем на странице и порядка сортировки: сначала из данных сессии, потом из настроек пользователя, потом из настроек раздела и наконец из общефорумных
  * Определение других данных для вывода тем
  * Определение данных для построения списка страниц
  * Извлечение данных о приклеенных темах
  * Извлечение данных о прочих темах
  * Если пользователь -- модератор, получение информации о количестве тем на премодерации
  **/
  function action_view_forum() {
    $fid = $this->forum['id'];
     list($cond,$need_count,$perpage,$tperpage)=$this->view_forum_build_cond($fid); // формируем массив $cond с параметрами для выборки темы

    if (!$need_count && $this->has_sticky()) { // sticky-темы выдаем только в том случае, если нет каких-то сложных условий фильтрации и если они вообще предусмотрены данным видом раздела
      $cond['sticky']=true;
      $this->out->sticky=$this->view_forum_get_topics($cond,$tperpage);
      $cond['sticky']=false;
    }

    list($this->out->pages,$cond)=$this->view_forum_pagedata($perpage, $cond,$need_count);
    $this->out->topics=$this->view_forum_get_topics($cond,$tperpage);

    $this->view_forum_misc();
    if (!empty($this->out->moderator)) $this->view_forum_moderator();
    $this->fix_view(); // фиксируем просмотр раздела
  }

  /** Вспомогательные действия при просмотре форума: вывод вводного текста, ссылки на правила, и т.п. **/
  function view_forum_misc() {
    $fid = $this->forum['id'];
    $this->out->rules = $this->get_text($fid,0); // правила имеют код типа 0
    $this->out->start_text = $this->get_text($fid,2);  // текст с типом 2 -- вводный

    $this->out->roles = $this->build_moderators_list();
    $this->out->moderator = $this->check_access('moderate');

    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку
    $this->out->perms=$tlib->get_permissions();
    $this->out->allow_share = true;

    $this->out->subforums = $this->get_subforums(); // получаем подразделы

    $pagelink = $this->out->pages['page'] > 1 ? $this->out->pages['page'].'.htm' : '';
    $this->link($this->http($this->url($this->forum['hurl'])).'/'.$pagelink, 'canonical'); // выводим атрибут canonical

  }

  /** Дополнительные действия, если форум просматривает модератор **/
  function view_forum_moderator() {
    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку
    $this->out->premod_count = $tlib->count_posts(array('fid'=>$this->forum['id'],'premod'=>true));
    if ($this->out->premod_count) $this->lastmod=$this->time;
  }

  /** Получение и первичная обработка информации о темах **/
  function view_forum_get_topics($cond,$tperpage) {
    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку
    /* @var $tlib Library_topic */
    $result=$tlib->list_topics($cond);
     for ($i=0, $count=count($result); $i<$count; $i++) { // генерируем страницы
      $tpages['total']=$result[$i]['post_count'];
      $tpages['perpage']=$tperpage;
      $tpages['page']=NULL; // никакую страницу не надо показывать как выделенную
      if (isset($_SESSION['topic'.$result[$i]['id']]) && isset($_SESSION['topic'.$this->out->sticky[$i]['id']]['perpage'])) $tpages['perpage'] = intval($_SESSION['topic'.$result[$i]['id']]['perpage']);
      $result[$i]['pages']=$this->get_pages($tpages,false,false);
    }
    if ($count>0 && empty($cond['sticky'])) { // если раздел не пуст
      if (empty($cond['start'])) $this->meta('description',$this->forum['descr']);
      else $this->meta('description','Темы с '.$this->short_date($result[0]['first_post_date']).' по '.$this->short_date($result[$count-1]['last_post_date']).'. '.$this->forum['descr']);
    }
    return $result;
  }

  /** Формировние данных о количестве страниц, а также установка граничных условий в $cond **/
  function view_forum_pagedata($perpage,$cond,$need_count) {
    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку
    $pagedata['perpage'] = $perpage;
    $pagedata['page'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
    $sticky_count = (empty($this->out->sticky)) ? 0 : count($this->out->sticky);
    if (!$need_count) $pagedata['total']=$this->forum['topic_count']-$sticky_count; // приклеенные темы уже выведены, поэтому вычитаем их число из общего количества тем
    else {
      $pagedata['total']=$tlib->count_topics($cond);
    }
    $pagedata=$this->get_pages($pagedata,false,true);
    $cond['perpage']=$pagedata['perpage'];
    $cond['start']=$pagedata['start'];
    return array($pagedata,$cond);
  }

  /** Данная функция определяет, поддерживает ли данный тип разделов sticky-темы **/
  function has_sticky() {
    return true;
  }

  /** Определение параметров для отображения списка тем в разделе на основе настроек (раздела, пользователя и сайта в целом) и данных сессии, установленных в action_params_forum.
   **/
  function view_forum_params($fid) {
    $need_count = false; // эта переменная определяет, нужен ли отдельный запрос для подсчета количества тем.
    // Если у нас нет какого-то необычного параметра сортировки (типа "только ценные" или по ключевому слову), то запрос не нужен, данные будут взяты из $forum[topic_count]

    // определяем число тем на странице
    $perpage = $this->get_topics_perpage($fid);

    // определяем поле сортировки
    $order = false;
    if (isset($_SESSION['forum'.$fid]) && isset($_SESSION['forum'.$fid]['order'])) $order = $_SESSION['forum'.$fid]['order'];
    if (!in_array($order,array('last_post_time','first_post_id','post_count','valued_count','flood_coeff'))) $order=false; // защита от попыток вбросить что-то через форму
    if (!$order) $order=$this->forum['sort_column'];
    if (!$order) $order='last_post_time';

    // определяем порядок сортировки
    $sort = false;
    if (isset($_SESSION['forum'.$fid]) && isset($_SESSION['forum'.$fid]['sort'])) $sort = $_SESSION['forum'.$fid]['sort'];
    if ($sort!=='ASC') $sort='DESC'; // защита от вброса некорректных значений через форму
    if (!$sort) $sort = $this->forum['sort_mode']; // берем из настроек раздела. Хотя они приоритетнее настроек пользователя, но по умолчанию там стоит пустая строка, означающая считать эту настройку пустой, в этом случае она будет браться из настроек пользователя
    if (!$sort) $sort = 'DESC'; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль

    // выборка тем по автору, в сессии должен хранится uid автора (он определяется в action_params)
    $author_name='';
    $author_id = false;
    if (isset($_SESSION['forum'.$fid]) && isset($_SESSION['forum'.$fid]['author'])) {
      $author_name = $_SESSION['forum'.$fid]['author_name'];
      $author_id=$_SESSION['forum'.$fid]['author'];
      $need_count = true;
    }

    // фильтрация тем
    $filter = false;
    if (isset($_SESSION['forum'.$fid]) && isset($_SESSION['forum'.$fid]['filter'])) {
      $filter = $_SESSION['forum'.$fid]['filter'];
      if ($filter!=='all') $need_count = true; // если в результате настроек фильтрации оказалось, что нужно выводить не все, включаем подсчет количества тем
    }

    // ограничение вывода по времени
    $period = false;
    if (isset($_SESSION['forum'.$fid]) && isset($_SESSION['forum'.$fid]['period'])) $period = intval($_SESSION['forum'.$fid]['period']);
    if (!$period) $period=$this->get_opt('topics_period','user');
    if ($period) $need_count = true;

    $text=false;
    if (!empty($_SESSION['forum'.$fid]) && !empty($_SESSION['forum'.$fid]['text'])) $text=$_SESSION['forum'.$fid]['text'];
    if (!empty($text)) {
      $need_count = true;
    }

    // определяем число сообений на странице темы. Нужно для того, чтобы сгенерировать ссылки
    $tperpage=$this->get_posts_perpage();

    return array($need_count,$perpage,$order,$sort,$author_id,$author_name,$filter,$period,$text,$tperpage);
  }

  /** Построение массива условий для выборки тем в разделе на основе данных, получаемых из forum_params **/
  function view_forum_build_cond($fid) {
    list($need_count,$perpage,$order,$sort,$author_id,$author_name,$filter,$period,$text,$tperpage)=$this->view_forum_params($fid);
    $this->out->opts=array('sort'=>$sort,'perpage'=>$perpage,'author_name'=>$author_name,'order'=>$order,'filter'=>$filter,'period'=>$period,'text'=>$text);

    if (!$this->is_guest()) { // если пользователь -- не гость, учитываем новые сообщения
      $sql = 'SELECT MAX(mark_time) FROM '.DB_prefix.'mark_all WHERE (fid=0 OR fid='.intval($this->forum['id']).') AND uid='.intval($this->get_uid());
      $cond['subscr']=true;
      $cond['new_time']=$this->db->select_int($sql);
    }
    $cond['fid']=$this->forum['id'];
    $cond['last']=true;
    $cond['first']=true;
    $cond['sort']=$sort;
    $cond['views']=true;
    if ($period) $cond['timelimit']=intval($this->time-$period*60*60);

    if ($author_id) $cond['owner']= $author_id;
    if ($filter==='valued') $cond['valued']=1;
    elseif ($filter==='unanswered') $cond['unanswered']=true;
    elseif ($filter==='noflood') $cond['flood_limit']=intval($this->get_opt('flood_limit','user'))/100;
    elseif ($filter==='myposts') $cond['posted']=true;
    if ($text) $cond['topic_title']=$text;

    if (!empty($_REQUEST['tags'])) {
      $cond['with_tags']=$_REQUEST['tags'];
      $need_count = true;
    }

    if (!empty($this->forum['polls'])) $cond['polls']=true;
    if ($this->forum['selfmod']==2) $cond['curator']=true; // если на форуме включён режим явного назначения кураторов, выведем их в списке тем
    return array($cond,$need_count,$perpage,$tperpage);
  }

  function action_params_forum() {
    if ($this->bot_id!=0) $this->output_403('Поисковым роботам запрещено пользоваться настройками фильтра тем во избежание дубликатов страниц!');
    $this->session();
    $fid = $this->forum['id'];
    if (isset($_REQUEST['clear']) && isset($_SESSION['forum'.$fid])) unset($_SESSION['forum'.$fid]);
    else {
      $fields = array('perpage','order','sort','author_name','filter','period','text');
      foreach ($fields as $curfield) $_SESSION['forum'.$fid][$curfield]=$_GET[$curfield];
      if ($_SESSION['forum'.$fid]['perpage']<1 || $_SESSION['forum'.$fid]['perpage']>255) unset($_SESSION['forum'.$fid]['perpage']); // если задано некорректное значение числа тем на страницу, оно сбрасывается
      if ($_SESSION['forum'.$fid]['author_name']) {
        $userlib = $this->load_lib('userlib',false);
        if (!$userlib) {
          $this->message('Ошибка подключения библиотеки userlib!',2);
          $_SESSION['forum'.$fid]['author_name']='';
        }
        else {
          $uid=$userlib->get_uid_by_name($_SESSION['forum'.$fid]['author_name']);
          if (!$uid) {
            $this->message('Пользователя с таким именем на форуме не найдено!',2);
            $_SESSION['forum'.$fid]['author_name']='';
          }
          else $_SESSION['forum'.$fid]['author']=$uid;
        }
      }
    }
    $_SESSION['starttime']=$this->time; // чтобы после применения параметров не показывалась страница из кеша
    $this->redirect($this->http($this->url($this->forum['hurl'].'/')));
  }

  function action_view_topic() {
    $fid = $this->forum['id'];
    $tid = $this->topic['id'];
    // Закомментировано,т.к. аналогичная проверка должна проводиться в init_object
    // if ($this->topic['status']!=0) $this->error404('Запрошенной темы не существует или она была удалена или перемещена');
    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку

    list($cond,$need_count,$perpage) = $this->view_topic_build_cond($tid);
    $this->out->pages = $this->view_topic_pagedata($perpage, $cond, $need_count);
    $this->out->posts = $this->view_topic_get_posts($cond);
    if ($this->is_moderator()) $this->view_topic_moderator($tid);
    if ($this->forum['polls']) $this->out->poll = $this->view_topic_poll($tid);

    $this->fix_view(); // фиксируем просмотр раздела
    $this->out->perms=$tlib->get_permissions();
    if (!empty($this->out->perms['post'])) $this->out->editpost=$this->view_topic_newpost();
    $this->view_topic_misc($fid);
    $this->out->form_params = $this->set_form_fields($this->out->perms,'reply',false);
  }

  function view_topic_get_posts($cond) {
    /** @var Library_topic **/
    $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку
    $result = $tlib->get_posts($cond);
    if ($this->forum['sticky_post']==0) $need_sticky=0; // sticky-сообщения отключены
    elseif ($this->forum['sticky_post']==3) $need_sticky=1; // sticky-сообщения принудительно включены
    else $need_sticky = $this->topic['sticky_post'];  // иначе -- по настройкам темы
    $need_sticky = $need_sticky && (!empty($cond['start']) || $cond['sort']=='DESC'); // если вывод делается не с первой страницы или вывод идет в обратном порядке, и sticky_post включен
    if ($this->get_request_type()==1) $need_sticky = false; // если это AJAX-подгрузка следующей страницы, то sticky-сообщение не нужно при любых других параметрах
<<<<<<< HEAD
    $start=isset($cond['start']) ? $cond['start'] : 0;
=======
>>>>>>> 1a1624e (Initial commit for Intb 3.05)

    if ($need_sticky) { // если первое сообщение в теме приклеенное, а тема выводится не с начала, извлекаем его отдельно
      $cond['id']=$this->topic['first_post_id'];
      unset($cond['start']);
      $sticky = $tlib->get_posts($cond);
      if (isset($sticky[0])) {
        $sticky[0]['sticky']=1;
        array_unshift($result, $sticky[0]);
      }
      $this->out->has_sticky = 1;
    }

    $bbcode = $this->load_lib('bbcode');
    /* @var $bbcode Library_bbcode */
    $tid = $this->topic['id'];
    $marked_messages = isset($_SESSION['moderate_'.$tid]) ? $_SESSION['moderate_'.$tid] : array(); // в данном ключе сессии хранится список сообщений, помеченных модератором для обработки
    $collapse = $this->get_opt('longposts','user'); // настройка сворачивания длинных сообщений

    for ($i=0, $count=count($result); $i<$count; $i++) {
      if ($bbcode) {
        $result[$i]['text']=$bbcode->parse_msg($result[$i]);
        $result[$i]['signature']=$bbcode->parse_sig($result[$i]['signature'],$result[$i]['links_mode']);
      }
      $result[$i]['editable']=$this->check_editable($result[$i]);
      $result[$i]['marked']=in_array($result[$i]['id'], $marked_messages);
      $result[$i]['norate']=$this->check_rateable($result[$i]);
//      if ($collapse>0) { // определяем, нужно ли показывать сообщение свернутым
//        if (preg_match_all('|<br\s*/? >|', $result[$i]['text'])>5) {
//          if ($collapse==1 || ($result[$i]['value']=-1 && $collapse==2)) $result[$i]['collapsed']=true;
//        }
//      }
    }
<<<<<<< HEAD
    if ($count>0) { 
      if ($start==0) {
=======
    if ($count>0 && empty($cond['sticky'])) { // если раздел не пуст
      if (empty($cond['start'])) {
>>>>>>> 1a1624e (Initial commit for Intb 3.05)
        if (!empty($this->topic['descr'])) $this->meta('description',$this->topic['descr']);
        else $this->meta('description','Автор: '.$result[0]['author'].', тема создана '.$this->long_date($result[0]['postdate']).' и содержит '.
          $this->incline($this->topic['post_count'],'%d сообщение','%d сообщения','%d сообщений').($this->topic['valued_count']? ', из них '.$this->incline($this->topic['valued_count'],'%d ценное','%d ценных','%d ценных'):'').'.');
      }
      else $this->meta('description','Сообщения с '.$this->short_date($result[0]['postdate']).' по '.$this->short_date($result[$count-1]['postdate']).'. '.$this->topic['descr']);
    }
    $this->out->delete_key=$this->gen_auth_key($this->get_uid(),'delete_post',$this->url('moderate/'.$this->topic['full_hurl']));
    return $result;
  }

  /** Проверка, может ли пользователь редактировать данное сообщение **/
  function check_editable($post) {
    if ($this->is_moderator()) return true;
    if (!$this->is_guest() && $post['uid']==$this->get_uid() && $this->check_access('edit')
      && empty($post['locked']) && empty($this->topic['locked']) && empty($this->forum['locked'])) return true;
    return false;
  }

  function view_topic_pagedata($perpage,&$cond,$need_count) {
    $pagedata['perpage'] = $perpage;
    $pagedata['page'] = !empty($_REQUEST['page']) ? $_REQUEST['page'] : '1';

    if (!$need_count) $pagedata['total']=$this->topic['post_count']; // TODO: обработка sticky-сообщения
    else {
      $tlib = $this->load_lib('topic',true); // отсутствие форумной библиотеки означает невозможность нормальной работы форума, поэтому будем рассматривать эту ситуацию как фатальную ошибку
      $pagedata['total']=$tlib->count_posts($cond);
    }
    $pagedata=$this->get_pages($pagedata,false,true);
    $cond['perpage']=$pagedata['perpage'];
    $cond['start']=$pagedata['start'];
    return $pagedata;
  }

  function view_topic_poll($tid) {
    $tlib = $this->load_lib('topic',true);
    $result = $tlib->get_poll($tid);
    if (!empty($result) && !$this->is_guest() && empty($result['pvid']) &&
    $this->check_access('vote') && empty($result['closed'])) $result['allow_vote']=true; // проверка, можно ли пользователю голосовать, если нет, будут показаы результаты голосования
    return $result;
  }

  /** Вспомогательные действия при выводе темы (список модераторов, ссылка на правила) **/
  function view_topic_misc($fid) {
    $this->out->roles = $this->build_moderators_list();
    $this->out->rules = $this->get_text($fid,0); // правила имеют код типа 0
    $this->out->allow_share = true;
    $this->out->bookmark_key=$this->gen_auth_key(false,'change_mode');
    if ($this->forum['tags']) {
      /** @var Library_tags **/
      $taglib = $this->load_lib('tags',false);
      if ($taglib) {
        $this->out->tags = $taglib->get_tags($this->topic['id']);
      }
    }
    if ($this->forum['webmention']>0) $this->link($this->http($this->url($this->topic['full_hurl'].'webmention.htm')),'webmention');
    $pagelink = $this->out->pages['page'] > 1 ? $this->out->pages['page'] . '.htm' : '';
    $this->link($this->http($this->url($this->topic['full_hurl'])) . $pagelink, 'canonical'); // выводим атрибут canonical    
  }

  function view_topic_newpost() {
    /** @var Library_topic $tlib */
    $tlib = $this->load_lib('topic',true);
    $editpost['post'] = $tlib->set_new_post($this->out->perms);
    $editpost['action']='reply.htm';
    $editpost['newtopic']=false;
    $editpost['topmsg']='Отправка ответа в тему';

    $this->out->draft_name = 'topic'.$this->topic['id']; // имя черновика для автосохранения на стороне клиента
    $this->out->authkey = $this->gen_auth_key(false,'reply');

    $bbcode = $this->load_lib('bbcode',false);
    if ($bbcode) $this->out->smiles = $bbcode->load_smiles_hash();

    if ($this->is_guest() && $this->get_opt('captcha')) { // для гостя необходим ввод CAPTCHA
      $antibot = $this->load_lib('antibot',false);
      if ($antibot) $antibot->captcha_generate();
    }
    if ($this->get_opt('subscribe','user')=='All') $editpost['subscribe']=1;
    return $editpost;
  }

  function view_topic_moderator($tid) {
    $tlib = $this->load_lib('topic',true);
    $this->out->premod_count = $tlib->count_posts(array('tid'=>$tid,'premod'=>true));
    if ($this->out->premod_count) $this->lastmod=$this->time;
  }

  /** Получение условий для отображения темы. Условия могут храниться в сессии (ключ topic<номер_темы>) или,
  * в случае отсутствия, браться из настроек раздела или пользователя.
  * Также должны быть предусмотрены значения по умолчанию на случай, если нигде в перечисленных местах значений нет, чтобы форум работал корректно в любом случае.
  **/
  function view_topic_params($tid) {
    $need_count = false;
    // определяем число тем на странице
    $perpage = $this->get_posts_perpage($tid);

    // определяем порядок сортировки
    $sort = false;
    if (isset($_SESSION['topic'.$tid]) && isset($_SESSION['topic'.$tid]['sort'])) $sort = $_SESSION['topic'.$tid]['sort'];
    if ($sort && $sort!=='DESC' && $sort!=='rating') $sort='ASC'; // защита от вброса некорректных значений через форму
    if (!$sort) $sort = $this->get_opt('msg_order','user'); // берем из настроек пользователя
    if (!$sort) $sort = 'ASC'; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль

    // фильтрация тем
    $filter = false;
    if (isset($_SESSION['topic'.$tid]) && isset($_SESSION['topic'.$tid]['filter'])) {
      $filter = $_SESSION['topic'.$tid]['filter'];
      if ($filter!=='all') $need_count = true; // если в результате настроек фильтрации оказалось, что нужно выводить не все, включаем подсчет количества тем
    }

    // выборка тем по автору, в сессии должен хранится uid автора (он определяется в action_params)
    $author_name=false;
    $author_id=false;
    if (isset($_SESSION['topic'.$tid]) && isset($_SESSION['topic'.$tid]['author'])) {
      $author_name = $_SESSION['topic'.$tid]['author_name'];
      $need_count = true;
      $author_id = $_SESSION['topic'.$tid]['author'];
    }
    return array($need_count,$perpage,$sort,$filter,$author_id,$author_name);
  }

  function view_topic_build_cond($tid) {
    $cond['tid']=$tid;
    $cond['user']=true;
    $cond['blocklinks']=true; // подгрузка данных для тега blocklink из отдельного поля в таблице text
    if (!$this->is_guest()) $cond['relation']=true; // если пользователь -- не гость, нужно делать проверку на сообщения от игнорируемых пользователей
    if (!empty($this->forum['rate']) && !$this->is_guest()) $cond['ratings']=true; // если пользователь не гость и рейтинги включены, получаем информацию, голосовал ли он за это сообщение

    list($need_count,$perpage,$sort,$filter,$author_id,$author_name) = $this->view_topic_params($tid);
    $this->out->opts=array('sort'=>$sort,'perpage'=>$perpage,'author_name'=>$author_name,'filter'=>$filter);

    if ($filter==='valued') $cond['valued']=1;
    elseif ($filter==='noflood') $cond['noflood']=1;
    if ($author_id) $cond['owner']=$author_id;
    $cond['sort']=$sort;
    if ($sort==='rating') {
      $cond['order']='rating';
      $cond['sort']='DESC';
    }

    if ($this->forum['max_attach']>0) $cond['attach']=true;
    return array($cond,$need_count,$perpage);
  }

  /** Сохранение параметров отображения темы в сессию **/
  function action_params_topic() {
    if ($this->bot_id!=0) $this->output_403('Поисковым роботам запрещено пользоваться настройками фильтра тем во избежание дубликатов страниц!');
    $this->session();
    $tid = $this->topic['id'];
    if (isset($_REQUEST['clear']) && isset($_SESSION['topic'.$tid])) unset($_SESSION['topic'.$tid]);
    else {
      $fields = array('perpage','sort','author_name','filter');
      foreach ($fields as $curfield) $_SESSION['topic'.$tid][$curfield]=$_GET[$curfield];
      if ($_SESSION['topic'.$tid]['perpage']<1 || $_SESSION['topic'.$tid]['perpage']>255) unset($_SESSION['topic'.$tid]['perpage']); // если задано некорректное значение числа сообщений на страницу, оно сбрасывается
      if ($_SESSION['topic'.$tid]['author_name']) {
        $userlib = $this->load_lib('userlib',false);
        if (!$userlib) {
          $this->message('Ошибка подключения библиотеки userlib!',2);
          $_SESSION['forum'.$fid]['author_name']=false;
        }
        else {
          $uid=$userlib->get_uid_by_name($_SESSION['topic'.$tid]['author_name']);
          if (!$uid) {
            $this->message('Пользователя с таким именем на форуме не найдено!',2);
            $_SESSION['topic'.$tid]['author_name']=false;
          }
          else $_SESSION['topic'.$tid]['author']=$uid;
        }
      }
    }
    $_SESSION['starttime']=$this->time; // чтобы после применения параметров не показывалась страница из кеша
    $this->redirect($this->http($this->url($this->topic['full_hurl'])));
  }

  /** Переход к указанному сообщению. Поскольку у пользователей может различаться количество сообщений
  * на страницу, а также порядок вывода сообщений, необходимо произвести расчет номера выводимой страницы.
  * Он делается в процедуре post_redirect.
  **/
  function action_post() {
    if (empty($_GET['post'])) $this->output_404('Некорректно указан номер сообщения');
    $pid = $_GET['post'];
    $this->post_redirect($pid);
  }

  function action_last() {
    $this->post_redirect($this->topic['last_post_id']);
  }

  /** Редирект к первому непрочитанному сообщению (т.е. тому, дата которого больше даты последнего посещения темы)
  **/
  function action_new() {
    $uid = $this->get_uid();
    $sql = 'SELECT visit1,visit2 FROM '.DB_prefix.'last_visit '.
    'WHERE oid='.intval($this->topic['id']).' AND type=\'topic\' AND uid='.intval($uid);
    $lastvisit = $this->db->select_row($sql);

    $online_time = $this->get_opt('online_time');
    $lasttime=0;
    if ($lastvisit) {
      $limittime=$this->time-$online_time*60;
      if ($lastvisit['visit1']<$limittime) $lasttime = $lastvisit['visit2']; // если последний заход в тему был недавно, то берем время предыдущего захода
      else $lasttime=$lastvisit['visit1'];
    }

    $sql = 'SELECT MAX(mark_time) AS mark_time FROM '.DB_prefix.'mark_all '.
    'WHERE (fid='.intval($this->forum['id']).' OR fid=0) AND uid='.intval($uid);
    $marktime = $this->db->select_int($sql);
    $lasttime = max($lasttime,$marktime); // если время отметки раздела как прочтенного больше

    $tlib = $this->load_lib('topic',true);
    $cond['tid']=$this->topic['id'];
    $cond['after_time']=$lasttime;
    $cond['notext']=true; // чтобы не тащить из базы текст
    $cond['count']=1;
    $posts = $tlib->get_posts($cond);
    if ($posts) $pid = $posts[0]['id'];
    else $pid=$this->topic['last_post_id'];

    $this->post_redirect($pid);
  }

  function post_redirect($pid) {
    $tid=$this->topic['id'];
    $cond['tid']=$tid;

    if (isset($_SESSION['topic'.$tid])) { // сбрасываем ограничения по автору сообщения и фильтру отображения, так как они могут помешать
      if (isset($_SESSION['topic'.$tid]['author'])) { unset($_SESSION['topic'.$tid]['author']); unset($_SESSION['topic'.$tid]['author_name']); }
      if (isset(  $_SESSION['topic'.$tid]['filter'])) unset($_SESSION['topic'.$tid]['author']['filter']);
    }

    list($need_count,$perpage,$sort)=$this->view_topic_params($tid);
    $total = $this->topic['post_count']; // общее количество сообщений
    $tlib=$this->load_lib('topic',true); // ошибка загрузки библиотеки является критичной, без нее перехода не получится
    if ($sort==='DESC') { // если вывод сообщений в обратном порядке
      $cond['after_pid']=$pid;  // то считаем количество сообщений, оставшихся до конца темы (то есть с id > $pid)
      if ($pid==$this->topic['last_post_id']) $count=1; // эти проверки позволят избежать лишнего запроса к базе в действиях last и new
      elseif ($pid==$this->topic['first_post_id']) $count=$this->topic['post_count'];
      else $count = $tlib->count_posts($cond)+1;
    }
    else {
      $cond['before_pid']=$pid; // иначе -- количество сообщений от начала темы до указанного (то есть с id < $pid )
      if ($pid==$this->topic['last_post_id']) $count=$this->topic['post_count']; // эти проверки позволят избежать лишнего запроса к базе в действиях last и new
      elseif ($pid==$this->topic['first_post_id']) $count=1;
      else $count = $tlib->count_posts($cond)+1;
    }
    $page = ceil(($count)/$perpage); // определяем номер страницы, на которой окажется наше сообщение
    // и, наконец, делаем редирект: если номер страницы не равен единице, то на страницу с указанным номером
    if ($page!=1) $this->redirect($this->http($this->url($this->topic['full_hurl'].$page.'.htm#p'.$pid)));
    else $this->redirect($this->http($this->url($this->topic['full_hurl'].'#p'.$pid)));
  }

  function action_vote() {
    $tlib = $this->load_lib('topic');
    $tid = $this->topic['id'];
    $poll = $tlib->get_poll($tid);
    if (empty($_GET['vote'])) $this->output_403('Не указан вариант для голосования!');
    if ($this->is_guest()) $this->output_403('Гости не могут участвовать в голосованиях!');
    if (empty($poll)) $this->output_404('Данная тема не содержит голосования!');
    if ($poll['pvid']) $this->message('Вы уже голосовали в данном опросе!',3);
    elseif (!$this->check_access('vote')) $this->message('У вас недостаточно прав для голосования в этой теме',3);
    else {
      $tslib = $this->load_lib('tsave');
      $vdata['pvid']=intval($_GET['vote']);
      $tslib->save_vote($vdata);
      $this->message('Ваш голос засчитан!',1);
    }
    $this->redirect($this->http($this->url($this->topic['full_hurl'])));
  }

  function action_rate() {
    $tlib = $this->load_lib('topic',true);
    /* @var $tlib Library_topic */
    $tid = $this->topic['id'];
    if (empty($_GET['p'])) $this->output_403('Не указано сообщение для изменения рейтинга!');
    else $pid=intval($_GET['p']);
    if (empty($_GET['d'])) $this->output_403('Не указано направление изменения рейтинга!');
    $direction = $_GET['d'];
    $post = $tlib->get_posts(array('tid'=>$tid,'id'=>array($pid),'ratings'=>true));
    if (empty($post) || empty($post[0])) $this->output_404('Не найдено сообщение для изменения рейтинга!',3);
    elseif ($direction!='pro' && $direction!='contra') $this->message('Ошибочно указано значение для изменения рейтинга!',3);
    else {
      $errmsg = $this->check_rateable($post[0]);
      if (!empty($errmsg)) $this->message($errmsg,3);
      else {
        $tslib = $this->load_lib('tsave',true);
        /* @var $tslib Library_tsave */
        $data['id']=$pid;
        $data['tid']=$post[0]['tid'];
        $data['value']=$direction=='contra' ? -1 : 1;
        $data['uid_rated']=$post[0]['uid'];
        if ($this->forum['rate_value'] && intval($post[0]['rating'])+intval($data['value'])>=intval($this->forum['rate_value'])) $data['valued']=true;
        elseif ($this->forum['rate_flood']<0 && $post[0]['rating']+$data['value']<=$this->forum['rate_flood']) $data['flood']=true;
        $tslib->save_rating($data);
        $this->message('Ваш голос засчитан!',1);
      }
    }
    if ($this->get_request_type()!=1) { // если запрос сделан не через AJAX, редиректим пользователя обратно к отрейтингованому сообщению
      $referer = $this->referer();
      $this->post_redirect($post[0]['id']);
    }
    else { // а если через AJAX, выдаем JSON с новым рейтингом и запретом на повторное изменение
      $result['value']=$post[0]['rating']+$data['value'];
      $result['message']='Вы уже голосовали за это сообщение!';
      $result['result']='done';
      $this->output_json($result);
    }
  }

  function action_reply($anonym=false) { // если $anonym==true, сообщение отправляется от имени гостя // TODO: подумать, возможно, есть более адекватный способ это сделать
    $tlib = $this->load_lib('topic',true);
    $this->out->perms = $tlib->get_permissions();
    if ($this->bot_id!=0) $this->output_403('Поисковым роботом запрещено использовать форму ответа!');

    if ($this->out->perms['attach']) $atlib = $this->load_lib('attach',true);
    $bbcode = $this->load_lib('bbcode');

    if ($this->is_post()) {
      if (empty($_POST['authkey']) && !$this->is_guest()) $this->output_403('Отсутствует ключ авторизации, подозрение на CSRF-атаку.');
      $tslib = $this->load_lib('tsave',true);
      $post=$tslib->get_post_data($_POST['post'],$this->out->perms);
      if ($anonym) { $post['author']='Guest'; $post['uid']=1; } // если включен режим анонимности, отправляем от имени гостя
      unset($post['id']); // сбрасываем идентификатор сообщения, т.к. это ответ, а не редактирование
      $parsed = $bbcode->parse_msg($post);

//      $del_attach=0;
//      if ($atlib && !empty($_POST['detach'])) $del_attach=$atlib->delete_uploads($_POST['detach'],"0",1); // если часть файлов помечена к удалению,*/

      $errors = $this->post_pre_check($post,$this->out->perms,$parsed,$post['status']);
      if (!empty($errors)) $this->message($errors); // если возникли ошибки, выводим их
      else {
        $post['text']=$bbcode->bad_words($post['text']); // обработка запрещенных слов

          $tslib->save_post($post,$anonym); // при сохранении должен был проставиться id

          // обработка приложенных файлов
          if (!empty($_FILES['attach']) && $atlib) $atlib->process_files($_FILES['attach'],$post['id'],1); // 1 означает что файл загружается как прикрепленный к сообщению
//          if (!empty($_POST['preattach']) && $atlib) $atlib->process_preuploads($_POST['preattach'],$post['id'],1);

          $lock=false;
          if (!empty($_POST['topic']['locked']) && $this->out->perms['lock']) $lock=true; // если запрошено закрытие темы и есть необходимые права
          if ($post['status']==0) { // обновляем данные $this->topic, чтобы избежать лишнего SQL-запроса, но только в том случае
            $this->topic['last_post_id']=$post['id'];
            $this->topic['post_count']++;
            $tslib->increment($post,false,$lock); // увеличиваем счетчик, а также закрываем тему, если $lock=true
            $this->post_postprocess($post,$parsed); // в этой процедуре будет различная обработка типа увеличения счетчиков и т.п.
          }
          else $this->output_msg($this->url($this->topic['full_hurl']),'Ваше сообщение поставлено на премодерацию, оно станет доступным после одобрения модератором!','Вернуться в тему');
          $this->post_redirect($this->topic['last_post_id'],201); // и в любом случае делаем редирект на последнее сообщение темы
      }
    }
    if ($this->is_post()) {
      $this->out->editpost=$_POST;
    }
    else {
      $this->out->editpost['post']=$tlib->set_new_post($this->out->perms);
      if (!empty($_REQUEST['quote'])) {
        $qdata = $tlib->get_posts(array('tid'=>$this->topic['id'],'id'=>$_REQUEST['quote']));
        $this->out->editpost['post']['text']='[quote='.$qdata[0]['author'].']'.$qdata[0]['text'].'[/quote]';
      }
    }
    $this->out->editpost['action']='reply.htm';
    $this->out->editpost['edittopic']=false;
    $this->out->editpost['topmsg']='Отправка ответа в тему';

    $this->out->draft_name = 'topic'.$this->topic['id']; // имя черновика для автосохранения на стороне клиента
    $this->out->authkey = $this->gen_auth_key(); // аутентификационный ключ нужен для того, чтобы если пользователя разлогинит по таймауту, его сообщение все равно бы отправилось
    $this->out->smiles = $bbcode->load_smiles_hash();

    if ($this->is_guest() && $this->get_opt('captcha')) { // для гостя необходим ввод CAPTCHA
      $antibot = $this->load_lib('antibot',false);
      if ($antibot) $antibot->captcha_generate();
    }

    if ($this->get_opt('subscribe','user')=='All') $this->out->editpost['subscribe']='1';
    $this->out->form_params = $this->set_form_fields($this->out->perms,'reply',false);
    //TODO : добавить вывод последних сообщений в обратном порядке, но это потом.
  }

  function action_preview() {
    if (!$this->is_post()) $this->output_403('Предпросмотр доступен только через POST-метод!');
    if ($this->bot_id!=0) $this->output_403('Поисковым роботом запрещено использовать предпросмотр!');
    $tlib = $this->load_lib('topic',true);
    $this->out->perms = $tlib->get_permissions();

    if (empty($this->topic)) {
      $this->out->topic=$_POST['topic'];
    }

    $bbcode = $this->load_lib('bbcode');
    $tslib = $this->load_lib('tsave',true);
    $userdata=$this->load_user($this->get_uid(),2);
    if (empty($_POST['post']['author'])) $_POST['post']['author']=$userdata['basic']['display_name'];
    $post=$tslib->get_post_data($_POST['post'],$this->out->perms);
    $parsed = $bbcode->parse_msg($post);

    $errors = $this->post_pre_check($post,$this->out->perms,$parsed,$post['status'],false);
    if (!empty($errors)) {
      $this->out->errors=$errors;
        }
    $post['text']=$bbcode->bad_words($parsed); // обработка запрещенных слов
    $post['preview']=true;
    $post['uid']=$userdata['basic']['id'];
    $post['author']=$userdata['basic']['display_name'];
    $post['postdate']=$this->time;
    $post['avatar']=$userdata['basic']['avatar'];
    $post['signature']=$bbcode->parse_sig($userdata['basic']['signature'],$post);
    $post=array_merge($post,$userdata['ext_data']);
    $post['norate']='Нельзя оценивать неотправленное сообщение!';
    if (!empty($_POST['attach']) && !empty($_POST['attach'][0])) {
      $post['attach']=array();
      foreach  ($_POST['attach'] as $attach) {
        $post['attach'][]=array('filename'=>$attach,'size'=>"0",'fkey'=>'#');
      }
    }
    $this->out->forum['rate']=false;
    $this->out->post=$post;
  }

  /** Подписка/отписка на тему **/
  function action_change_mode() {
    if ($this->is_guest()) $this->output_403('Гости не имеют возможности пользоваться подпиской и закладками');
    if (empty($this->topic)) $this->output_403('Не указана тема!');
    if (empty($_GET['authkey'])) $this->output_403('Неверно указан ключ аутентификации');
    if (empty($_GET['mode']) || !in_array($_GET['mode'], array('subscribe','bookmark'))) $this->output_403('Не указано, что требуется изменить');
    $mode = $this->db->slashes($_GET['mode']);
    if (empty($_GET['cancel'])) { // если параметр unsub не задан, значит подписываемся
      $sql = 'UPDATE '.DB_prefix.'last_visit SET "'.$mode.'"=\'1\' '.
      'WHERE uid='.intval($this->get_uid()).' AND type=\'topic\' AND oid='.intval($this->topic['id']);
      if ($mode=='subscribe') $msg='Вы подписались на тему!';
      elseif ($mode=='bookmark') $msg='Тема добавлена в закладки!';
    }
    else {
     $sql = 'UPDATE '.DB_prefix.'last_visit SET "'.$mode.'"=\'0\' '.
       'WHERE uid='.intval($this->get_uid()).' AND type=\'topic\' AND oid='.intval($this->topic['id']);
     if ($mode=='subscribe') $msg='Вы отписались от темы!';
     elseif ($mode=='bookmark') $msg='Тема удалена из закладок!';
    }
    if ($this->db->query($sql)) $this->message($msg,1);
    $this->redirect($this->referer());
  }

  function action_newtopic($anonym=false) { // $anonym true -- для отправки сообщений от имени гостя, используется при вызове из классов-наследников
    /* @var Library_topic */
    $tlib = $this->load_lib('topic',true);
    $this->out->perms = $tlib->get_permissions();
    $bbcode = $this->load_lib('bbcode');
    if ($this->bot_id!=0) $this->output_403('Поисковым роботам запрещено использовать форму ответа!');
    if ($this->is_post()) {
      if (empty($_POST['authkey']) && !$this->is_guest()) $this->output_403('Отсутствует ключ авторизации, подозрение на CSRF-атаку.');
      /* @var Library_tsave */
      $tslib = $this->load_lib('tsave',true);
      $topic = $tslib->get_topic_data($_POST['topic'],$this->out->perms);
      unset($topic['id']); // сбрасываем идентификатор темы, так как создаем новую

      $post=$tslib->get_post_data($_POST['post'],$this->out->perms);
      unset($post['id']); // сбрасываем идентификатор сообщения, т.к. это отправка нового, а не редактирование
      if ($anonym) { $post['author']='Guest'; $post['uid']=1; } // если включен режим анонимности, отправляем от имени гостя
      $parsed = $bbcode->parse_msg($post);

      $errors = array_merge($this->topic_pre_check($topic,$this->out->perms),$this->post_pre_check($post,$this->out->perms,$parsed,$post['status']));
      if (!empty($errors)) {
        if ($this->get_request_type()===4) return $errors; // если у нас вызов через API (например, из micropub), возвращаем ошибки туда
        else $this->message($errors); // иначе выводим их
      }
      else {
        $post['text']=$bbcode->bad_words($post['text']); // обработка запрещенных слов

          $this->db->begin(); // начинаем транзакцию в БД
          if ($post['status']==1) $topic['status']='1'; // если сообщение уходит на премодерацию, то и тема тоже
          $tagline=false;
          if ($this->out->perms['tags']) $tagline = $_POST['tagline'];
          if ($tslib->save_topic($topic,false,$tagline)) {
            $this->topic = $topic; // загружаем данные о теме в $this->topic, чтобы не использовать $override при вызове save_post (а также избежать проблем в post_process, где могут быть зависимости от этой переменной)
            $this->topic['full_hurl']=$this->forum['hurl'].'/'.(!empty($topic['hurl']) ? $topic['hurl'] : $topic['id']).'/';
            if ($this->out->perms['tags'] && !empty($_POST['tagline'])) {
              /** @var Library_tags **/
              $taglib = $this->load_lib('tags',false);
              if ($taglib) {
                $taglib->set_tags($_POST['tagline'],$this->topic['id'],0); // 0 -- идентификатор тега для темы
              }
            }
            $tslib->save_post($post,$anonym); // при сохранении должен был проставиться id
            if (!empty($_POST['create_poll']) && $this->check_access('poll')) { // если запрошено создание голосования и на это есть права
              $tslib->save_poll($topic['id'], $_POST['poll'], $_POST['vote']);
            }
            $lock=false;
            if (!empty($_POST['topic']['locked']) && $this->out->perms['lock']) $lock=true; // если запрошено закрытие темы и есть необходимые права
            if ($post['status']==0) { // обновляем данные $this->topic, чтобы избежать лишнего SQL-запроса, но только в том случае
              $this->topic['last_post_id']=$post['id'];
              $this->topic['post_count']=1;
              $tslib->increment($post,true,$lock); // увеличиваем счетчик, а также закрываем тему, если $lock=true
              $this->post_postprocess($post,$parsed,true); // в этой процедуре будет различная обработка типа увеличения счетчиков и т.п.
            }
            // обработка приложенных файлов
            if (!empty($_FILES['attach']) && $this->out->perms['attach']) {
              /** @var Library_attach $atlib */              
              $atlib = $this->load_lib('attach',true);
              if ($atlib) $atlib->process_files($_FILES['attach'],$post['id'],1); // 1 означает что файл загружается как прикрепленный к сообщению
            }
            $this->db->commit(); // завершаем транзакцию (подумать, тут ли это надо делать
            $hurl = !empty($this->topic['hurl']) ? $this->topic['hurl'] : $this->topic['id'];
            if ($post['status']==1) $this->output_msg($this->url($this->forum['hurl'].'/'),'Ваше сообщение поставлено на премодерацию, оно станет доступным после одобрения модератором!','Вернуться в раздел');
            if ($this->get_request_type()!==4) $this->newtopic_redirect($hurl,$post['id']); // редирект вынесен в отдельную функцию и выполняется при стандартных типах ответа
            else return false; // если вызов через API, то возвращаем false, чтобы передать функции micropub (или аналогичным), что всё хорошо и нужно выдать статус 201
          }
          else $this->message('Не удалось сохранить тему',3);
      }
    }
    if ($this->is_post()) $this->out->editpost=$_POST;
    else $this->out->editpost['post']=$tlib->set_new_post($this->out->perms);
    $this->out->editpost['action']='newtopic.htm';
    $this->out->editpost['edittopic']=true;
    $this->out->editpost['topmsg']='Создание новой темы';

    if ($bbcode) $this->out->smiles = $bbcode->load_smiles_hash();

    $this->out->draft_name = 'newtopic'.$this->forum['id']; // имя черновика для автосохранения на стороне клиента, для новых тем генерируется по принципу newtopic+номер форума
    $this->out->authkey = $this->gen_auth_key(); // аутентификационный ключ нужен для того, чтобы если пользователя разлогинит по таймауту, его сообщение все равно бы отправилось
    if ($this->get_opt('subscribe','user')=='All' || $this->get_opt('subscribe','user')=='My') $this->out->editpost['subscribe']='1';

    if ($this->is_guest() && $this->get_opt('captcha')) { // для гостя необходим ввод CAPTCHA // TODO: подумать, может быть, сделать это задваемым
      $antibot = $this->load_lib('antibot',false);
      if ($antibot) $antibot->captcha_generate();
    }
    $this->out->form_params = $this->set_form_fields($this->out->perms,'newtopic',true);

    return 'stdforum/reply.tpl';
  }

  function action_edit() {
    if ($this->is_guest()) $this->output_403('Гостям запрещено редактировать сообщения!');
    if (empty($_REQUEST['id'])) $this->output_404('Не указан номер сообщения!');
    $pid = intval($_REQUEST['id']);
    $tlib = $this->load_lib('topic',true);
    $this->out->perms = $tlib->get_permissions();
    if (!$this->is_moderator()) $this->out->perms['poll']=false; // не модератор не может редактировать/создавать голосование
    $oldposts = $tlib->get_posts(array('tid'=>$this->topic['id'],'id'=>array($pid),'all'=>true,'attach'=>true));
    if (empty($oldposts)) $this->output_404('Сообщение с таким номером не найдено!');
    $oldpost = $oldposts[0];
    if (!$this->check_editable($oldpost)) $this->output_403('Вы не можете редактировать это сообщение.');
    $modlib = $this->load_lib('moderate',false);
    // проверка прав на вынесение предупреждения. Делается через check_access, а не через
    // is_moderator, чтобы исключить возможность вынесения предупреждения создателями
    // темы при включенной самомодерации
    $this->out->allow_warning = $this->check_access('moderate') && $oldpost['uid']!=$this->get_uid();
    if ($this->is_post()) {
      if (empty($_POST['authkey']) && !$this->is_guest()) $this->output_403('Отсутствует ключ авторизации, подозрение на CSRF-атаку.');
      $tslib = $this->load_lib('tsave',true);
      $post=$tslib->get_post_data($_POST['post'],$this->out->perms);
      $post['id']=$pid;
      $post['uid']=$oldpost['uid'];
      $post['author']=$oldpost['author'];
      $post['editcount']=$oldpost['editcount'];
      $bbcode = $this->load_lib('bbcode');
      $parsed = $bbcode->parse_msg($post);

      $errors = $this->post_pre_check($post,$this->out->perms,$parsed,$post['status'],false); // false означает, что для сообщения не надо проверять таймаут, поскольку мы его редактируем
      if ($this->topic['first_post_id']==$pid) {
        $topic = $_POST['topic'];
        $topic['id']=$oldpost['tid'];
        $errors = $errors + $this->topic_pre_check($topic, $this->out->perms);
      }
      if (!empty($errors)) {
       $this->message($errors); // если возникли ошибки, выводим их
       $this->out->editpost['post']=$post;
       $this->out->editpost['topic']=$_POST['topic'];
       $this->out->editpost['tagline']=$_POST['tagline'];
      }
      else {
        $post['text']=$bbcode->bad_words($post['text']); // обработка запрещенных слов

          $tslib->save_post($post); // при сохранении должен был проставиться id
          if (!empty($_FILES['attach']) || !empty($_POST['detach'])) {
            /** @var Library_attach $atlib */
            $atlib = $this->load_lib('attach',false);
            if ($atlib) {
              if (!empty($_POST['detach'])) {
                $atlib->delete_uploads($_POST['detach'],$post['id'],1);
              }
              if (!empty($_FILES['attach']) && $this->out->perms['attach']) {
                if ($atlib) {
                  $atlib->process_files($_FILES['attach'],$post['id'],1,false); // 1 означает что файл загружается как прикрепленный к сообщению, false — не трогаем главные файлы
                }
              }
            }
          }
          if ($this->out->perms['lock']) $_POST['topic']['locked']=isset($_POST['topic']['locked'])?1:0; // если нет прав на закрытие темы, сбрасываем соответствующий параметр
          else unset($_POST['topic']['locked']);
          if ($this->out->perms['favorites']) $_POST['topic']['favorites']=isset($_POST['topic']['favorites'])?1:0; // если нет прав на добавление в Избранное, сбрасываем
          else unset($_POST['topic']['favorites']);
          if ($this->topic['first_post_id']==$pid) { // если редактируем первое сообщение темы, пересохраняем данные не только о сообщении, но и о теме тоже
            $_POST['topic']['id']=$this->topic['id']; // чтобы нельзя было редактировать "не ту" тему
            $tslib->save_topic($_POST['topic'],false);
            if ($this->out->perms['tags']) {
              /** @var Library_tags **/
              $taglib = $this->load_lib('tags',false);
              if ($taglib) {
                $taglib->set_tags($_POST['tagline'],$this->topic['id'],0); // 0 -- идентификатор тега для темы
              }
            }
          }          

          // заносим действие в лог действий модератора для возможности отката
          $logdata['pid']=$pid;
          $logdata['data']=$oldpost;
          $logdata['type']=1; // 1 -- код действия "редактирование сообщения"

          if ($modlib) $modlib->log_action($logdata);
          $modlib->topic_resync($this->topic['id']); // пересчитываем тему
          $modlib->forum_resync($this->forum['id']); // пересчитываем раздел
          $this->update_extdata(); // обновляем кешированные данные (нужно для наследуемых разделов)

          if (!empty($_POST['delete_vote'])) { // если выбрано удаление голосования
            $tslib->delete_vote($this->topic['id']);
          }
          elseif (!empty($_POST['create_poll']) && $this->is_moderator()) { // редактировать опросы могут только модераторы
            $tslib->save_poll($this->topic['id'], $_POST['poll'], $_POST['vote']);
          }

          if ($this->out->allow_warning && $_POST['warn_user']) { // если запрошено вынесение предупреждения пользователя
            $warnlib = $this->load_lib('warning',false);
            /* @var $warnlib Library_warning */
            if ($warnlib) {
              $_POST['warn']['pid']=$oldpost['id'];
              if ($warnlib->make_warning($oldpost['uid'], $_POST['warn'])) {
                $this->message('Автору сообщения вынесено предупреждение!',2);
                $pmlib = $this->load_lib('privmsg',false);
                /* @var $pmlib Library_privmsg */
                if ($pmlib) {
                  $pmdata['thread']['title']='Вам вынесено предупреждение за нарушение правил форума';
                  $pmdata['uids']=array($oldpost['uid'],$this->get_uid());
                  $pmdata['post']['text']=$_POST['warn']['descr'];
                  list($pm_thread,$pm_id)=$pmlib->save_message($pmdata);
                  $pmdata['thread']['id']=$pm_thread;
                  $pmdata['post']['id']=$pm_id;
                  $notify_lib = $this->load_lib('notify',false);
                  if ($notify_lib) {
                    $userdata = $this->load_user($this->get_uid(),0);
                    $notify_lib->new_pm($pmdata['thread'],$pmdata['post'],$pmdata['post']['text'],$this->get_username(),$userdata['email']);
                  }
                }
              }
            }
          }
          $this->process_blocklinks($post,$parsed); // обработка blocklinks на случай, если они изменились при редактировании
          

          if ($post['status']==1) $this->output_msg($this->url($this->topic['full_hurl']),'Ваше сообщение поставлено на премодерацию, оно станет доступным после одобрения модератором!','Вернуться к теме');
          if (empty($_POST['topic']['hurl'])) $this->post_redirect($pid); // редиректим пользователя обратно к сообщению
          else $this->redirect($this->http($this->url($this->forum['hurl'].'/'.$_POST['topic']['hurl'].'/')));
      }
    }
    else {
      $this->out->editpost['post']=$oldpost;
      if ($this->out->perms['tags'] && $this->topic['first_post_id']==$oldpost['id']) { // если теги разрешены и редактируем первое сообщение темы
        /** @var Library_tags **/
        $taglib =  $this->load_lib('tags',false);
        if ($taglib) {
          $this->out->editpost['tagline']=$taglib->get_tags_string($this->topic['id'],0);
        }
      }
    }

    $this->out->editpost['action']='edit.htm';
    $this->out->editpost['topmsg']='Редактирование сообщения';
    $this->out->authkey = $this->gen_auth_key(); // аутентификационный ключ нужен для того, чтобы если пользователя разлогинит по таймауту, его сообщение все равно бы отправилось
    if ($this->topic['first_post_id']==$oldpost['id']) { // если редактируем первое сообщение темы, то можно редактировать некоторые параметры и самой темы
      $this->out->editpost['edittopic']=true;
      if (!$this->is_post()) {
        $this->out->editpost['topic']=$this->topic;
        if ($this->is_moderator()) { // только модераторы могут редактировать уже существующие голосования
          $this->out->editpost['poll'] = $tlib->get_poll($this->topic['id']);
          if (!empty($this->out->editpost['poll']['endtime'])) $this->out->editpost['poll']['period']=round(($this->out->editpost['poll']['endtime']-$this->time)/(24*60*60)); // считаем число дней до окончания голосования
        }
      }
    }
    $this->out->form_params = $this->set_form_fields($this->out->perms,'edit',$this->topic['first_post_id']==$oldpost['id']);
    return 'stdforum/reply.tpl';
  }

  function action_rss() {
    $tlib = $this->load_lib('topic',true);
    $cond['topics']=true;
    $cond['noflood']=true;
    $cond['sort']='DESC';

    $period = $this->get_opt('topics_period','user');
    if ($period<=0 || $period>30) $period=30; // если у пользователя не выставлен лимит или он слишком велик, выставляем его равным 30 дням во избежание выгрузки всей базы
    $cond['after_time']=max(intval($this->if_modified_time),$this->time-$period*24*60*60);

    $limit = $this->get_opt('rss_max_items');
    if (!$limit) $limit=250;
    $cond['offset']=0;
    $cond['perpage']=$limit; //

    $bbcode = $this->load_lib('bbcode');
    /* @var $bbcode Library_bbcode */

    if (empty($this->topic)) { // если запрошен RSS для форума
      $this->out->intb->link=$this->http($this->url($this->forum['hurl'].'/'));
      $this->out->intb->descr=$this->forum['descr'];
      $cond['fid']=$this->forum['id'];

      $data = $tlib->get_posts($cond);
      if (empty($data) && $this->if_modified_time) $this->output_304();
      $count=count($data);
      for ($i=0; $i<$count; $i++) {
        $data[$i]['text']=$bbcode->parse_msg($data[$i]);
        $data[$i]['link']=$this->http($this->url($data[$i]['full_hurl'].'post-'.$data[$i]['id'].'.htm'));
        $data[$i]['title']=$data[$i]['t_title'].', сообщение от '.($this->long_date($data[$i]['postdate']));
      }
    }
    else {
      $this->out->intb->link=$this->http($this->url($this->topic['full_hurl']));
      $this->out->intb->descr=$this->topic['descr'];
      $cond['tid']=$this->topic['id'];

      $data = $tlib->get_posts($cond);
      if (empty($data) && $this->if_modified_time) $this->output_304();

      $count=count($data);
      $start=$this->topic['post_count']-$count;
      for ($i=0; $i<$count; $i++) {
        $data[$i]['text']=$bbcode->parse_msg($data[$i]);
        $data[$i]['link']=$this->http($this->url($this->topic['full_hurl'].'post-'.$data[$i]['id'].'.htm'));
        $data[$i]['title']=$this->topic['title'].', сообщение #'.($start+$i+1);
      }
    }
    $this->out->items=$data;
  }

  function action_webmention() {
    if (!$this->forum['webmention']) $this->output_404($this->lang('Webmention отключен для данного сайта!'));
    if (empty($this->topic)) $this->output_404($this->lang('Webmention в Intellect Board поддерживается только для тем и сообщений.'));
    if (!isset($_POST['source'])) $this->output_400('No webmention source specified','no source');
    if (!isset($_POST['target'])) $this->output_400('No webmention target specified','no target');

    $source = parse_url($_POST['source']);
    if (!$source || ($source['scheme']!=='http' && $source['scheme']!=='https') || $source['host']==='localhost' || preg_match('|^127\.\d+\.\d+\.\d+$|',$source)) $this->output_400('Invalid source URL specified','Bad source');
    $target = parse_url($_POST['target']);
    if (!$target || ($target['scheme']!=='http' && $target['scheme']!=='https')) $this->output_400('Invalid target URL specified','Bad target');
    $target_host = strtolower($target['host']);
    if (substr($target_host,0,4)==='www.') $target_host=substr($target_host,4);
    $host = strtolower($_SERVER['HTTP_HOST']);
    if (substr($host,0,4)==='www.') $host=substr($host,4);
    if ($host!==$target_host) $this->output_403($this->lang('Некорректный адрес целевого сайта!'));
    
    if ($this->is_domain_blacklisted($source['host'])) $this->output_403($this->lang('Ваш сайт в чёрном списке! Приём упоминаний с него запрещён.'));
    $topic_url = $this->url($this->topic['full_hurl']);
    $url_len = strlen($topic_url);
    if (substr($target['path'],0,$url_len)!==$topic_url) $this->output_403($this->lang('URL уведомления не соответствует URL темы!'));

    if (!function_exists('curl_init')) $this->output_404($this->lang('Расширение curl не установлено, поэтому использование WebMention невозможно.'));
    $ch = curl_init($_POST['source']);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5); // для предотвращения перегрузки
    $html = curl_exec($ch);
    if (curl_getinfo($ch,CURLINFO_RESPONSE_CODE)===200) {     
      /** @var Library_topic */
      if (!preg_match('|<a[^>]*\Whref=[\'"]?'.$_POST['target'].'[\'"][^>]*>|i',$html)) $this->output_400('Link not found','No link'); // проверка, что в HTML-коде страницы есть ссылка
      
      $tlib = $this->load_lib('topic',true);
      $post = $tlib->set_new_post(array());
      $post['uid']=2; // Webmentions появляются от имени пользователя System
      $post['author']='Webmention'; // но при этом в качестве автора указывается WebMention, чтобы проще было обнаружить такие записи
      $post['bcode']=1;
      $post['text']=$this->lang('Эта тема была упомянута по адресу [url]'.$_POST['source'].'[/url]');
      $post['tid']=$this->topic['id'];
      $post['status']=$this->forum['webmention']==2 ? '1' : '0'; // если режим 2, то отправляем сообщение на премодерацию
      /** @var Library_tsave */
      $tslib = $this->load_lib('tsave',true);
      if ($tslib->save_post($post,true)) {
        $tslib->increment($post,false,false); // увеличиваем счетчик сообщений в теме
        header('HTTP/1.0 201 Created');
        $post_url = $this->http($topic_url.'post-'.$post['id'].'.htm');
        header('Location: '.$post_url);
      }
      $this->shutdown();
      exit();
    }
    else $this->output_404('Failed to fetch and check source URL','Fetch error');
  }
  
  function action_tags() {
    if ($this->forum['tags']==0) $this->output_404('В данном разделе теги не применяются!');
    $blocklib = $this->load_lib('blocks',true);
    list($trash,$tags) = $blocklib->block_tag_list(",,1"); // выбрать все теги текущего раздела с сортировкой по алфавиту, а не количеству
    $this->out->tags = $tags;
    return 'stdforum/tags.tpl';
  }

  /** Создание тем с помощью протокола MicroPub */
  function action_micropub() {
    if (empty($this->forum['micropub'])) $this->output_403($this->lang('Исползование Micropub запрещено настройками раздела!'));
/*    $fh = fopen(BASEDIR.'/tmp/micropub.log','w');
    fputs($fh,print_r($_SERVER,true)."\n\n==========\n");
    fputs($fh,print_r($_POST,true));
    fputs($fh,print_r($_FILES,true));
    fclose($fh);*/
    if (!empty(trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) && !empty($_POST['access_token'])) {
      $this->output_400('Multiple authentication methods must be rejected','bad request');
    }
    $token = !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? str_replace('Bearer ','',$_SERVER['REDIRECT_HTTP_AUTHORIZATION']) : str_replace(' ','+',$_POST['access_token']); // Bearer из заголовка имеет больший приоритет, для 
    $sql = 'SELECT uid, scope FROM '.DB_prefix.'oauth_token WHERE token=\''.$this->db->slashes($token).'\' AND expires>'.intval($this->time);
    $udata = $this->db->select_row($sql);
    if (empty($udata) || $udata['uid']<=AUTH_SYSTEM_USERS) {
      header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
      echo 'OAuth token is invalid or expired.';
    }
    else {
      $this->set_user($this->load_user($udata['uid'],1));
      if (!empty($_POST['action'])) {
        $this->output_400('Only posting is now supported, no deleting or updating','invalid_response');
      }
      if ($_SERVER['CONTENT_TYPE']==='application/json') { // если данные переданы в формате JSON, раскодируем их и передаём в $_POST
        $json = file_get_contents('php://input');
        $data = json_decode($json);  
        if (!empty($data)) {
          foreach ($data->properties as $k=>$v) {
            if ($k==='content') { 
              if (is_object($v[0]) && !empty($v[0]->html)) { $_POST['content']=$v[0]->html; $_POST['post']['html']=1; }
              else $_POST['content']=$v[0];
            }
            else $_POST[$k]=is_array($v) && is_object($v[0]) && isset($v[0]->value) ? $v[0]->value : $v; // Для обработки photo с тегом alt
          }
          $_POST['h']='entry';
        }
      }
      if (!empty($_POST['h']) && $_POST['h']==='entry') {
        $_POST['authkey']=$this->gen_auth_key(false,'newtopic');

        $_POST['topic']['title']=!empty($_POST['name']) ? $_POST['name'] : '<без темы>';
        $_POST['topic']['descr']=!empty($_POST['summary']) ? $_POST['summary'] : '';
        $_POST['post']['text']=$_POST['content'];
        if (!empty($_POST['_instagram_link'])) { // Ссылки на Instagram работают только пару дней, поэтому копируем к себе
          /** @var Library_download */
          $dllib = $this->load_lib('download');
          if ($dllib) {
            $local_file = 'f/instagram/'.basename($_POST['_instagram_link']);
            $pos=strpos($local_file,'?');
            if ($pos!==false) $local_file=substr($local_file,0,$pos);
            $dllib->get($_POST['_instagram_link'],BASEDIR.'www/'.$local_file);
            $_POST['post']['text']=str_replace($_POST['_instagram_link'],$this->http($this->url($local_file)),$_POST['post']['text']);
          }
        }
        if (!empty($_POST['post']['html']) && !$this->check_access('html')) unset($_POST['post']['html']);
        $_POST['post']['bcode']=1;
        $_POST['post']['smiles']=1;
        $_POST['tagline']=is_array($_POST['category']) ? join(',',$_POST['category']) : $_POST['category'];
        if (!empty($_POST['photo'])) {
          $photos = $_POST['photo'];
          if (!is_array($photos)) $photos=array($photos);
          $_POST['post']['text'].="\n\n";
          foreach ($photos as $photo) $_POST['post']['text'].='[img]'.$photo.'[/img]';
        }
        if (!empty($_POST['video'])) {
          $videos = $_POST['video'];
          if (!is_array($videos)) $photos=array($videos);
          $_POST['post']['text'].="\n\n";
          foreach ($videos as $video) $_POST['post']['text'].='[video]'.$video.'[/video]';
        }        
        if (!empty($_POST['audio'])) {
          $audios = $_POST['audio'];
          if (!is_array($audios)) $photos=array($audios);
          $_POST['post']['text'].="\n\n";
          foreach ($audios as $audio) $_POST['post']['text'].='[audio]'.$audio.'[/audio]';
        }                
        $_FILES['attach']=array();
        foreach (array('photo','video','audio') as $item) {
          if (!empty($_FILES[$item])) {
            foreach (array('name','tmp_name','type','error','size') as $key) {
              $_FILES['attach'][$key]=$_FILES['attach'][$key]+is_array($_FILES[$item][$key]) ? $_FILES[$item][$key] : array($_FILES[$item][$key]);
            }
            unset($_FILES[$item]);
          }
        }
        unset($_POST['content']);
        $result = $this->action_newtopic();        
        if ($result===false) {
          header($_SERVER['SERVER_PROTOCOL'].' 201 Created');
          header('Location: '.$this->http($this->url($this->topic['full_hurl'])));
        }
        else $this->output_400('Errors found: '.print_r($result,true),'bad request');
      }
      else $this->output_400('No h-entry found.','bad request');
    }
    return ''; // возвращаем пустую строку, чтобы не требовалось вывода
  }

  /** Получение VK API access token */
  function action_vk_token() {
    $full_url = $this->http($this->url($this->forum['hurl'].'/vk_token.htm')); // полный путь к URL данного action
    $this->session(); // запускаем сессию, если вдруг не ещё не запущена 
    if ($this->is_guest() || (!$this->is_admin() && $this->get_uid()!=$this->forum['owner'])) $this->output_403('Только администраторы форума или владелец раздела могут выполнять это действие!');
    if (!empty($_GET['code'])) {  // если пришёл authorization code от VK, запоминаем его в сессию и сразу же делаем редирект (так полагается с точки зрения безопасности)
      $_SESSION['vk_auth_code']=$_GET['code'];
      $this->redirect($full_url.'?accepted=1');
    }
    elseif (!empty($_GET['accepted']) && !empty($_SESSION['vk_auth_code'])) { // если на предыдущем шаге приняли и запомнили код
      $params['client_id']=$_SESSION['vk_client_id']; // client_id передаётся в скрытом поле из формы
      $params['code']=$_SESSION['vk_auth_code']; // код авторизации передаётся в сесссии
      $params['client_secret']=$_SESSION['vk_client_secret']; // client_secret пользователь задаёт явно
      $params['redirect_uri']=$full_url;
      $ch=curl_init('https://oauth.vk.com/access_token');
      curl_setopt($ch,CURLOPT_POST,true);
      curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($params));
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
      curl_setopt($ch,CURLOPT_TIMEOUT,5); // не ждём больше 5 секунд
      curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true); // обрабатываем редиректы      
      $result = json_decode(curl_exec($ch),true);      
      $this->out->result=$result;
      $info = curl_getinfo($ch);
      if (empty($result) || $info['http_code']!=200 || empty($result['access_token']) || !empty($result['error_description'])) {
        $msg = 'Ошибка получения токена: ';
        if (!empty($result['error_description'])) $msg.=$result['error_description'];
        $errno = curl_errno($ch);
        if ($errno!=0) $msg.=curl_error($ch);
        $this->message($msg,3);
        _dbg('Отладочная информация по запросу:',$result,$info);
      }
      else {
        $this->out->received = true;
        $this->message('Токен получен и сохранён',1);
        /** @var Library_forums $forum_lib */
        $forum_lib = $this->load_lib('forums',true);
        $fdata = $forum_lib->get_forum($this->forum['id'],true); // получаем расширенные данные о форуме
        $fdata['extdata']['vk_token']=$result['access_token']; // запоминаем полученный token
        $forum_lib->update_forum($this->forum['id'],$fdata['extdata']); // и сохраняем изменённые данные в базу
        // в целях безопасности удаляем данные приложения из сессии
        unset($_SESSION['vk_client_id']);
        unset($_SESSION['vk_client_secret']);
      }
      unset($_SESSION['vk_auth_code']); // сбрасываем auth code в сессии — он больше не нужен
    }
    elseif ($this->is_post()) { // запоминаем client_id и client_secret в сессию и отправляем пользователя авторизоваться VK
      $_SESSION['vk_client_secret']=$_POST['client_secret'];
      $_SESSION['vk_client_id']=$_POST['client_id'];
      $this->redirect('https://oauth.vk.com/authorize?client_id='.$_POST['client_id'].'&redirect_uri='.$full_url.'&display=page&scope=wall,photo');
    }
    if (!empty($_SESSION['vk_client_secret'])) {
      $this->out->client_secret=$_SESSION['vk_client_secret'];
      $this->out->client_id=$_SESSION['vk_client_id'];
    }
    $this->out->authkey = $this->gen_auth_key();
    return 'stdforum/vk_token.tpl';
  }

  /** Предварительная проверка сообщения перед отправкой. В этой функции проверяется следующее:
  * наличие прав на отправку сообщения через permission
  * CAPTCHA для гостей
  * время, прошедшее с прошлого сообщения
  * максимальная и минимальная длина сообщения
  * количество смайликов
  * возможно, количество ссылок
  ** **/
  function post_pre_check($post,$perms,$parsed,&$premod,$timeout_check=true) {
    $result = array();
    if (empty($perms['post'])) $result[]=array('text'=>'У вас нет прав отправлять сообщение в эту тему','level'=>3);
    $antibot = $this->load_lib('antibot');
    // проверка CAPTCHA для гостей
    if ($this->is_guest() && $antibot && $this->get_opt('captcha')) { // проверка CAPTCHA
      if (!$antibot->captcha_check('true')) $result[]=array('text'=>'Неправильно введен защитный код!','level'=>3);
    }
    if (!empty($_POST['post']['comment'])) { // поле comment нужно только для защиты от ботов и должно оставаться пустым в нормальных ситуациях
      $result[]=array('text'=>'Сработала защита от спам-ботов!','level'=>3);
    }
    // проверка длины
    if (function_exists('mb_strlen')) $len = mb_strlen($post['text']);
    else $len = strlen($post['text'])*1.6; // если нет модуля
    $minlen = $this->get_opt('post_minlength');
    $maxlen = $this->get_opt('post_maxlength');
    if ($len==0 || $len<$minlen) $result[]=array('text'=>'Длина сообщения меньше минимально допустимой!','level'=>3);
    if ($maxlen && $len>$maxlen) $result[]=array('text'=>'Длина сообщения больше максимально допустимой!','level'=>3);

    // определение количества смайликов
    $bbcode = $this->load_lib('bbcode',false);
    if ($bbcode) {
      $smile_count = $bbcode->count_smiles($parsed);
      if ($smile_count > $this->forum['max_smiles']) $result[]=array('text'=>sprintf('Количество смайликов в сообщении больше максимально допустимного значения (%d).',$this->forum['max_smiles']),'level'=>3);
    }

    // проверка на наличие стоп-слов
    $stopwords = array_map('trim',explode("\n",$this->get_text(0,4)));
    $stoplist = '';
    for ($i=0, $count=count($stopwords); $i<$count; $i++) {
      if (function_exists('mb_stripos')) { // если установлен модуль работы с Unicode-строками // TODO: может быть, добавить еще проверку на то, что платформа -- Windows, т.к. под Linux с правильно выставленной локалью можно обойтись и без mb_string
        $funcname = 'mb_stripos';
        mb_internal_encoding("UTF-8"); // т.к. по умолчанию в настройках PHP прописано что-то не то
      }
      else $funcname = 'stripos'; // если поддержки
      if (trim($stopwords[$i]) && $funcname($parsed,$stopwords[$i])!==false) {
        if (!empty($stoplist)) $stoplist.=', ';
        $stoplist.=$stopwords[$i];
      }
    }
    if (!empty($stoplist)) $result[]=array('text'=>'Сообщение содержит слова или ссылки, которые администрация считает недопустимыми на этом сайте: '.$stoplist,'level'=>3);

    // проверка на наличие ссылок при условии, что ссылки пользователю запрещены
    $premod=0;
    $links_mode=$this->get_opt('links_mode','group');
    if ($links_mode==='none' || $links_mode==='premod') {
      $links_count = preg_match('|<a[\w]+[^>]*href=[^>]+>|is',$parsed);
      if ($links_count>0) {
        if ($links_mode==='none') $result[]=array('text'=>'У вас недостаточно прав доступа, чтобы отправлять сообщения со ссылками.','level'=>3);
        else $premod = "1";
      }
    }
    if (!$this->check_access('nopremod')) $premod="1"; // если нет права писать без премодерации

    if (!empty($_FILES['attach'])) { // если есть прикрепленные файлы, проверяем их
      if (!$perms['attach']) $result[]=array('text'=>'У вас нет прав на загрузку файлов','level'=>3);
      else { // проверка загружаемых файлов
        $atlib = $this->load_lib('attach',false);
        if ($atlib) $result=$result+$atlib->check_files($_FILES['attach'],$this->get_opt('max_attach','group')*1024,$this->forum['attach_types'],$this->forum['max_attach']); // умножаем на 1024, т.к. max_attach  в базе хранится в Кб.
      }
    }
    // проверка, что гость не использует имя зарегистрированного пользователя
    if ($this->is_guest()) {
      $userlib = $this->load_lib('userlib',false);
      if ($userlib) {
        $canonical = $userlib->canonize_name($post['author']);
        if ($userlib->get_uid_by_name($canonical,true)>1) $result[]=array('text'=>'На форуме есть зарегистрированный пользователь с таким именем!','level'=>3);
      }
    }
    // проверка таймаута. Делается в последнюю очередь и только в том случае, если не возникло каких-то других ошибок, чтобы не заставлять пользователя ждать каждый раз
    if ($timeout_check && empty($result) && !$this->is_moderator() && $antibot) {
      $timeout = $this->get_opt('floodtime','group');
      if ($timeout>0 && !$antibot->timeout_check('post',$timeout)) $result[]=array('text'=>$this->incline($timeout,
          'После отправки вашего предыдущего сообщения прошло меньше %d секунды',
          'После отправки вашего предыдущего сообщения прошло меньше %d секунд',
          'После отправки вашего предыдущего сообщения прошло меньше %d секунд'),'level'=>3);
    }
    return $result;
  }

  function topic_pre_check($topic,$perms) {
    $result = array();
    if (empty($perms['topic'])) $result[]=array('text'=>'У вас нет прав на создание новых тем','level'=>3);
    if (empty($topic['title'])) $result[]=array('text'=>'Название темы не может быть пустым!','level'=>3);
    if (!empty($topic['hurl'])) {
      if (!preg_match('|^[a-zA-Z][A-Za-z\d\-_]*$|',$topic['hurl'])) $result[]=array('text'=>'Некорректно указан URL темы. Он должен начинаться с буквы и содержать только цифры, латинские буквы и символы минус (-) и подчеркивание (_).','level'=>3);
      else {
        $tlib = $this->load_lib('topic');
        if (!$tlib->check_unique_hurl($topic)) $result[]=array('text'=>'Тема с таким URL уже существует на форуме.','level'=>3);
      }
    }
    return $result;
  }

  /** В этой функции делается редирект после создания новой темы. Вынесен в отдельную функцию для переопределения в классах-наследниках **/
  function newtopic_redirect($hurl,$post_id) {
    $this->redirect($this->forum['hurl'].'/'.$hurl.'/#p'.$post_id,201); // при любом раскладе новое сообщение в теме будет первым
  }

  /** Действия после отправки сообщения.
  * Рассылка уведомлений по почте
  * Подписка на тему
  * Добавление темы в закладки
  * Проверка необходимости перевода пользователя в следующую гурппу
  **/
  function post_postprocess($post,$parsed,$newtopic=false) {
    $data['visit1']=$this->time;
    $data['posted']=1;
    if (!empty($_POST['subscribe'])) $data['subscribe']=1;
    if (!empty($_POST['bookmark'])) $data['bookmark']=1;
    $this->db->update(DB_prefix.'last_visit',$data,'oid='.intval($this->topic['id']).' AND type=\'topic\' AND uid='.intval($this->get_uid()));
    if ($this->db->affected_rows()==0) {
      $data['oid']=$this->topic['id'];
      $data['uid']=$this->get_uid();
      $data['type']='topic';
      $this->db->insert_ignore(DB_prefix.'last_visit',$data);
    }

    if ($this->forum['is_stats'] && $post['status']==0) { // если раздел является статистически значимым и сообщение пользователя не ушло на премодерацию
      $userlib=$this->load_lib('userlib',false);
      if ($userlib) { // то подключаем библиотеку пользователя и увеличиваем счетчик ему на единицу
        if (empty($post['uid'])) $post['uid']=$this->get_uid();
        $userlib->increment_user($post['uid']); // в этой же библиотеке будет проверка
      }
    }
    if ($this->is_guest()) { // если пользователь — гость, сохраняем его имя в cookies для упрощения отправки следующих сообщений
      $session_name = defined('CONFIG_session') ? CONFIG_session : 'ib_sid';
      setcookie($session_name.'_guest',$post['author'],false,$this->url('/'));
    }
    if ($post['status']==0) $this->update_extdata(); // обновление кешированных данных (используется в унаследованных разделах), если сообщение доступно сразу

    // подключение библиотеки для уведомлеий о новом сообщении
    // (по умолчанию уведомления отправляются на EMail в соответствии с настройками подписки,
    // с помощью библиотеки notify, но возможна ее замена с целью выполнения других действий)
    $notify_lib_name = $this->get_opt('site_notify_lib');
    /** @var Library_notify $notify_lib */
    $notify_lib = $this->load_lib($notify_lib_name,false);
    if ($notify_lib) {
      if ($newtopic)  $notify_lib->new_topic($post,$this->topic,$this->forum,$parsed);
      else $notify_lib->new_post($post,$this->topic,$this->forum,$parsed);
    } 

    if ($this->forum['webmention']) { // если включена отправка уведомлений через WebMention
      $links_count = preg_match_all('|<a\s+[^>]*href=["\']?(https?://[^>"\']+)["\'][^>]*>?|',$parsed,$links);
      /** @var Library_misc */
      $misc_lib = $this->load_lib('misc',false);
      if ($misc_lib) {
        for ($i=0; $i<$links_count; $i++) {
          $misc_lib->create_task('indieweb','webmention',array('source'=>$this->http($this->url($this->topic['full_hurl'].'post-'.$post['id'].'.htm')),'target'=>$links[1][$i]));
        }
      }
    }
    $this->process_blocklinks($post,$parsed);
  }

  /** Обрабатывает конструкции вида <a class="blocklink" href="http://ссылка"> (такой вид они приобретают после парсинга) — создаёт асинхронную задачу для получения OpenGraph-данных для показа
   * блока с картинкой и кратким описанием ссылки
   */
  function process_blocklinks($post,$parsed) {
    if ($post['bcode']) { // blocklinks нужно обрабатывать только если включен BoardCode
      $filtered = preg_replace('|\[code\].*?\[/code\]|i', '', $parsed);
      $filtered = preg_replace('|\[php\].*?\[/php\]|i', '', $filtered);
      $filtered = preg_replace('|\[nocode\].*?\[/nocode\]|i', '', $filtered);
      $match_count = preg_match_all('|<a class="blocklink" href="(https?://[^>"\'\]\s]+)">|i', $filtered, $matches);

      if ($match_count > 0) {
        $misc_lib = $this->load_lib('misc', false);
        if ($misc_lib) {
          $misc_lib->create_task('bbcode', 'blocklink', array('links' => $matches[1], 'post_id' => $post['id']));
        }
      }
    }
  }

  /** Проверка на то, что пользователь может прорейтинговать данное сообщение.
  * @param $pdata array Хеш данных сообщения (должен включать поле rated)
  * @return string FALSE, если сообщение можно рейтинговать или строку с описанием причины, почему это делать нельзя.
  **/
  function check_rateable($pdata) {
    if ($this->is_guest()) $result='Гости не могут влиять на рейтинг сообщений!';
    elseif (!$this->forum['rate']) $result='На этом форуме запрещено изменение рейтинга!';
    elseif ($pdata['rated']) $result='Вы уже голосовали за это сообщение!';
    elseif ($pdata['uid']==$this->get_uid()) $result='Вы не можете влиять на рейтинг своих сообщений!';
    elseif (!$this->check_access('rate')) $result='У вас недостаточно прав для изменения рейтинга сообщений в этой теме!';
    else $result=false;
    return $result;
  }
 
  /** Подготовка массива с признаками того, какие поля должны быть доступны в форме **/
  function set_form_fields($perms,$action,$topic=false) {
    if ($this->is_guest()) $result['username']=true;
    if ($topic) {
      $result['topic_block']=true; // нужно ли выводить блок «параметры темы» и поле topic_title в нем
      $result['topic_descr']=true;
      $result['topic_hurl']=true;
    }
    $result['area_class']='bbcode'; // класс (или классы) для вывода основного блока textarea
    $result['area_rows']=10; // высота основного блока textarea в строках
    $result['attach']=$perms['attach']; // если есть права, выводим блок прикрепления файлов
    $result['allowed']=true; // список разрешенного: HTML, BBCode и т.п.

    if ($action!=='edit') { // если тему/сообщение не редактируем, а создаем новое
      if (!$this->is_guest()) { // зарегистрированным пользователям показываем checkboxes для добавления в закладки и подписки
        $result['subscribe']=true;
        $result['bookmark']=true;
      }
    }
    else {
      $result['delete']=$perms['delete']; // если редактируем сообщение и есть достаточно прав, выводим checkbox для удаления
      $result['warning']=$this->is_moderator();
      $result['value']=$perms['value']; // ценность сообщения разрешено задавать только при редактировании
      $result['lock_post']=$perms['lock']; // закрыть сообщение можно только при редактировании
      $result['favorites']=$perms['favorites']; // закрыть сообщение можно только при редактировании
    }
    if ($topic) {
      $result['sticky']=$perms['sticky'];
      $result['sticky_post']=$perms['sticky_post'];
    }
    $result['lock']=$perms['lock']; // возможность закрыть тему определяется соответствующими правами
    $result['poll']=$perms['poll'];

    $result['tags']=$perms['tags'] && $topic; // теги можно редактировать только для тем
    $result['postdate']=false;  // для стандартных форумов менять дату сообщения нельзя в любом случае
    if ($this->forum['owner']>AUTH_SYSTEM_USERS && $this->get_uid()==$this->forum['owner']) $result['no_export']=true; // если раздел — собственный, то репост в соцсети может делать только владелец
    if (empty($this->out->rules)) $this->out->rules=$this->get_text($this->forum['id'], 0); // правила имеют код типа 0, предварительная проверка нужна для того, чтобы не извлекать их дважды, если они уже были получены во view_forum_misc

    return $result;
  }

  function set_rss() {
    $this->link('rss.htm','alternate',false,'application/rss+xml'); // выводим ссылку на RSS в
    return array(array('url'=>'rss.htm','title'=>'Подписка на обновления'));
  }

  function set_location() {
    $result = parent::set_location();
    $mainpage=$this->get_opt('forum_mainpage');
    if ($mainpage) {
      array_splice($result,1,0,array(array($this->lang('Форум'),$this->url($mainpage))));
    }
    if ($this->action==='vk_token') $result[]=array('Получение токена VK');
    return $result;
  }
}
