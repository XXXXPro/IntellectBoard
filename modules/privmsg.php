<?php

/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010-2014 4X_Pro, INTBPRO.RU
 *  http://intbpro.ru
 *  Модуль личных сообщений
 *  ================================ */

class privmsg extends Application {

/** Выдача списка тем личных сообщений **/
  function action_view() {
    $pmlib = $this->load_lib('privmsg',true);

    $pg['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;

    $uid = $this->get_uid();
    $pg['total'] = $pmlib->count_threads(array('uid'=>$uid));

    $pg['perpage'] = $this->get_opt('topics_per_page','user');
    if (!$pg['perpage']) $pg['perpage']=20;

    $this->out->pages = $this->get_pages($pg, false, true); // генерируем страницы со ссылками в link и возможностью быстрого перехода

    $cond = $this->out->pages;
    $cond['uid']=$uid;
    $cond['users']=true;

    $this->out->threads = $pmlib->get_threads($cond);

/*    $tperpage = $this->get_opt('topics_per_page','user'); // берем из настроек пользователя
    if (!$tperpage) $tperpage = $this->get_opt('topics_per_page');  // берем из настроек сайта в целом
    if (!$tperpage) $tperpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль*/

    for ($i=0, $count=count($this->out->threads); $i<$count; $i++) {
      /*$tpages['total']=$this->out->threads[$i]['total'];
      $tpages['perpage']=$tperpage;
      $tpages['page']=NULL; // никакую страницу не надо показывать как выделенную*/
      // $this->out->threads[$i]['pages']=$this->get_pages($tpages,false,false);
      $this->lastmod=max($this->lastmod, $this->out->threads[$i]['last_post_date']);
    }
  }

  /** Просмотр личных сообщений одной из тем **/
  function action_thread() {
    $thread_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    if (!$thread_id) {
      $this->output_404('Не указан номер темы переписки!');
    }

    $pmlib = $this->load_lib('privmsg',true);
    $cond['uid']=$this->get_uid();
    $cond['users']=true;
    $cond['relations']=true;
    $cond['pm_thread']=$thread_id;
    $threads=$pmlib->get_threads($cond);
    $this->out->thread = $threads[0];

    if (empty($this->out->thread)) {
      $this->output_404('Не найдена запрошенная тема личной переписки!');
    }

    $this->out->recepients =array(); // массив тех, кто получит ответ (то есть подписан на тему и не забанил автора)
    $this->out->ignored = array();
    for ($i=0, $count=count($threads[0]['users']); $i<$count; $i++) {
      if ($threads[0]['users'][$i]['uid']!=$cond['uid'])
        if ($threads[0]['users'][$i]['type']!=='ignore') $this->out->recepients[]=$threads[0]['users'][$i];
        else $this->out->ignored[]=$threads[0]['users'][$i];
    }

    $show = isset($_REQUEST['show']) ? $_REQUEST['show'] : false;
    $this->out->show = $show;

    $per_page = $this->get_opt('posts_per_page','user');
    if (!$per_page) $per_page = 10;
    $time = time();

    // Определяем режим вывода сообщений в теме
    // если общее число сообщений меньше, чем умещается на страницу или явно указано показать все, выводим все
    if ($this->out->thread['total']<=$per_page || $show=='all') {
      // дополнительных ограничений не требуется
    }
    // если есть непрочитанные сообщения и их больше, чем общее количество, выводим все непрочитанные и пару предыдущих
    elseif ($this->out->thread['unread']>$per_page) {
      $cond['start']=$this->thread['total']-$this->thread['unread']-2;
      $cond['perpage']=$this->thread['unread']+2;
    }
    // выводим сообщения за неделю
    elseif ($show==='week') {
      $cond['postdate'] = ($time-7*24*60*60);
    }
    // выводим сообщения за месяц (для простоты будем считать его равным 30 дням всегда)
    elseif ($show==='month') {
      $cond['postdate'] = ($time-30*24*60*60);
    }
    // выводим сообщения за последние 3 месяца (точнее, 90 дней)
    elseif ($show==='3months') {
      $cond['postdate'] = ($time-90*24*60*60);
    }
    // выводим сообщения за сутки
    elseif ($show==='day') {
      $cond['postdate']= ($time-24*60*60);
    }
    // во всех остальных случаях выводим последнюю страницу сообщений
    else {
      $cond['start']=$this->out->thread['total']-$per_page; $cond['perpage']=$per_page;
    }

    $pmlib->mark_read($this->get_uid(),$thread_id); // засчитываем просмотр темы

    $pm = $pmlib->get_messages($cond);
    $this->out->hidden_pm = $this->out->thread['total']-count($pm); // количество

    $bbcode = $this->load_lib('bbcode',false); // ЛС могут функционировать и без BBCode
    if ($bbcode) for ($i=0, $count=count($pm);$i<$count;$i++) { // обработка смайликов и BoardCode
      $pm[$i]['text']=$bbcode->parse_msg($pm[$i]);
      $this->lastmod=max($this->lastmod,$pm[$i]['postdate']);
    }
    $this->out->privmsg_pm = $pm;

    $this->out->max_pms = $this->get_opt('privmsg_hour','group');
    $this->out->perms = $pmlib->get_permissions();

    $this->out->draft_name = 'pm'.$thread_id; // имя черновика для автосохранения на стороне клиента
    $this->out->authkey = $this->gen_auth_key(false,'send'); // аутентификационный ключ нужен для того, чтобы если пользователя разлогинит по таймауту, его сообщение все равно бы отправилось

    $this->out->post=$pmlib->set_new_post($this->out->perms);
    $this->out->deletekey = $this->gen_auth_key(false,'delete');

    $bbcode = $this->load_lib('bbcode');
    if ($bbcode) $this->out->smiles = $bbcode->load_smiles_hash();
  }

  /** Действие по отправке сообщения **/
  function action_send() {
    if (!$this->is_post()) $this->redirect($this->http($this->url('privmsg/'))); // если тип запроса не POST, значит, что-то не так, делаем редирект на список ЛС
    $pm_thread = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    $pmlib = $this->load_lib('privmsg',true);
    $this->out->perms = $pmlib->get_permissions();
    $errors = array();
    $post = $_POST['post'];

    $bbcode = $this->load_lib('bbcode',false);
    if ($bbcode) $parsed = $bbcode->parse_msg($post);

    if (empty($pm_thread)) { // если thread_pm не задан или равен нулю, создается новая тема
      $post['thread'] = $_POST['thread'];

      $_POST['uids']=array($this->get_uid()); // для начала добавляем себя
      $names = explode(',',$_POST['recepients']);

      // проверка количества сообщений в час (в целях защиты от несанкционированной рекламы)
      $pm_per_hour = $this->get_opt('privmsg_hour','group');
      if ($pm_per_hour>0) {
        $pm_lib = $this->load_lib('privmsg');
        $timelimit = $this->time - 60*60;
        $pm_sent = $pm_lib->count_threads(array('uid'=>$this->get_uid(),'lasttime'=>$timelimit));
        if ($pm_sent>=$pm_per_hour) $errors[]=array('text'=>$this->incline($pm_per_hour,'При вашем уровне доступа разрешается создавать не более %d темы в час!','При вашем уровне доступа разрешается создавать не более %d тем в час!','При вашем уровне доступа разрешается создавать не более %d тем в час!'),'level'=>3);
        if ($pm_per_hour==1) $pm_per_hour=2; // чтобы можно было отправить сообщение одному получателю, даже если стоит лимит на 1 сообщение в час
        if (count($names)>$pm_per_hour-1) $errors[]=array('text'=>$this->incline($pm_per_hour-1,'Вы можете указать не более %d получателя сообщения!','Вы можете указать не более %d получателей сообщения!','Вы можете указать не более %d получателей сообщения!'),'level'=>3);
      }

      for ($i=0, $count=count($names); $i<$count; $i++) $names[$i]=trim($names[$i]);
      $userlib = $this->load_lib('userlib',true);
      $udata = $userlib->list_users(array('login'=>$names,'relations'=>true));
      for ($i=0, $count=count($udata); $i<$count; $i++) {
        if ($udata[$i]['type']!=='ignore' && $udata[$i]['id']!==$this->get_uid()) $_POST['uids'][]=$udata[$i]['id'];
        elseif ($udata[$i]['id']===$this->get_uid()) $errors[]=array('text'=>'Вы не можете отправлять сообщения самому себе.','level'=>3);
        else $errors[]=array('text'=>'Пользователь '.$udata[$i]['display_name'].' внес вас в список игнорируемых. Отправка ему личных сообщений невозможна.','level'=>3);
      }
      if (count($udata)<count($names)) {
        for ($i=0, $count=count($names); $i<$count; $i++) {
          $found = false;
          $found_str = '';
          for ($j=0, $count2=count($udata); $j<$count2 && !$found; $j++) if ($udata[$j]['display_name']===$names[$i]) $found=true;
          if (!$found) {
            if ($found_str) $found_str.=', '.$names[$i];
            else $found_str=$names[$i];
          }
        }
        if (count($names)-count($udata)==1) $errors[]=array('text'=>'Пользователь '.$found_str.' не найден!','level'=>3);
        else $errors[]=array('text'=>'Пользователи '.$found_str.' не найдены!','level'=>3);
      }

      $errors=$errors + $this->thread_pre_check($_POST['thread'],$this->out->perms);
      $this->out->thread=$_POST['thread'];
      $this->out->post=$_POST['post'];
      $this->out->recepients = $_POST['recepients'];
      $this->out->newpost = true;
    }
    else { // если отправляем ответ в уже существующую тему
      $threads=$pmlib->get_threads(array('pm_thread'=>$pm_thread,'uid'=>$this->get_uid(),'users'=>true,'relations'=>true)); // получаем информацию о теме, чтобы убедиться, что она существует и пользователь на нее подписан
       if (empty($threads)) $this->output_404('Темы с таким номером не существует!');
      $_POST['post']['pm_thread']=$threads[0]['id'];
      $_POST['thread']=$threads[0];
      $_POST['uids']=array();
      $found = false;
      for ($i=0, $count=count($threads[0]['users']); $i<$count; $i++) {
        $found=$found || $threads[0]['users'][$i]['uid']===$this->get_uid();
        if ($threads[0]['users'][$i]['type']!=='ignore') $_POST['uids'][]=$threads[0]['users'][$i]['uid'];
      }
      if (!$found) $errors[]=array('text'=>'Вы не подписаны на данную тему!','level'=>3);
      elseif (count($_POST['uids'])<2) $errors[]=array('text'=>'Все пользователи данной темы либо отписались от нее, либо занесли вас в черный список!','level'=>3);
      $this->out->post=$post;
    }
    $errors = $errors + $this->pm_pre_check($post,$this->out->perms,$parsed);
    if (!empty($errors)) {
      $this->message($errors);
      return 'privmsg/new.tpl';
    }
    list($pm_thread,$pm_id)=$pmlib->save_message($_POST);
    $_POST['thread']['id']=$pm_thread;
    $_POST['post']['id']=$pm_id;
    $this->pm_postprocess($_POST['thread'],$_POST['post'],$parsed);
    $this->message('Сообщение отправлено!');
    $this->redirect($this->http($this->url('privmsg/'.$pm_thread.'/#pm'.$pm_id)));
  }

  function action_unsubscribe() {
    $thread_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    if (!$thread_id) {
      $this->output_404('Не указан номер темы переписки!');
    }
    $pmlib = $this->load_lib('privmsg',true);
    $pmlib->unsubscribe($thread_id,$this->get_uid());
    $this->message('Вы были отписаны от темы. Теперь сообщения в ней вам не доступны.',1);
    $this->redirect($this->http($this->url('privmsg/')));
  }

  function action_delete() {
    $thread_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    if (!$this->is_post() && empty($_REQUEST['authkey'])) $this->output_403('Не указан ключ аутентификации!');
    if (!$thread_id) {
      $this->output_404('Не указан номер темы переписки!');
    }
    $pmlib = $this->load_lib('privmsg',true);
    $pmlib->delete($thread_id,$_REQUEST['del'],$this->get_uid());
    if ($this->get_request_type()!=1) {
      $this->message('Выбранные сообщения удалены.',1);
      $this->redirect($this->http($this->url('privmsg/'.$thread_id.'/')));
    }
    else $this->output_json(array('result'=>'done'));
  }

  function action_new() {
    $this->out->newpost=true;
    $this->out->max_pms = $this->get_opt('privmsg_hour','group');
    $pmlib = $this->load_lib('privmsg',true);
    $this->out->perms = $pmlib->get_permissions();
    $this->out->post=$pmlib->set_new_post($this->out->perms);
    if (!empty($_REQUEST['to'])) $this->out->recepients=$_REQUEST['to'];
    $this->out->draft_name = 'pm_new'; // имя черновика для автосохранения на стороне клиента

    $userlib = $this->load_lib('userlib',false); // отсутствие пользовательской библиотеки в данном случае некритично, без нее просто не будет списка друзей
    if ($userlib) {
      $this->out->friends = $userlib->list_users(array('friends_list'=>$this->get_uid()));
    }
    $this->out->authkey = $this->gen_auth_key(false,'send'); // аутентификационный ключ нужен для того, чтобы если пользователя разлогинит по таймауту, его сообщение все равно бы отправилось
    $bbcode = $this->load_lib('bbcode');
    if ($bbcode) $this->out->smiles = $bbcode->load_smiles_hash();
  }

  /** Предварительная проверка сообщения на возможность отправки/.
  * Проверяется следующее:
  * Сообщение не пустое
  * После отправки предыдущего сообщения прошел интервал флуд-контроля (возможно, сделать опциональным)
  * Количество сообщений за час не превышает максимально допустимого
  * В тексте сообщения нет стоп-слов
  * Проверку количества получателей вынести в отдельную функцию из
  **/
  function pm_pre_check($post,$perms,$parsed) {
    $result = array();

    if (function_exists('mb_strlen')) $len = mb_strlen($post['text']);
    else $len = strlen($post['text'])*1.6; // если нет модуля для работы с Unicode-строками нет, берем обыную длину и умножаем на 1.6
    $minlen = $this->get_opt('post_minlength');
    $maxlen = $this->get_opt('post_maxlength');
    if ($len==0 || $len<$minlen) $result[]=array('text'=>'Длина сообщения меньше минимально допустимой!','level'=>3);
    if ($maxlen && $len>$maxlen) $result[]=array('text'=>'Длина сообщения больше максимально допустимой!','level'=>3);

    // проверка таймаута. Если таймаут равен нулю, считаем, что ограничений нет.
    $timeout = $this->get_opt('floodtime','group');
    if ($timeout>0) {
      $antibot = $this->load_lib('antibot',false);
      if ($antibot && !$antibot->timeout_check('privmsg',$timeout)) $result[]=array('text'=>$this->incline($timeout,
        'После отправки вашего предыдущего сообщения прошло меньше %d секунды',
        'После отправки вашего предыдущего сообщения прошло меньше %d секунд',
        'После отправки вашего предыдущего сообщения прошло меньше %d секунд'),'level'=>3);
    }

    // определение количества смайликов
    $bbcode = $this->load_lib('bbcode',false);

    // проверка на наличие стоп-слов
    $stopwords = explode("\n",$this->get_text(0,4));
    $stoplist = '';
    for ($i=0, $count=count($stopwords); $i<$count; $i++) {
      if (stripos($parsed,$stopwords[$i])!==false) {
        if (!empty($stoplist)) $stoplist.=', ';
        $stoplist.=$stopwords[$i];
      }
    }
    if (!empty($stoplist)) $result[]=array('text'=>'Сообщение содержит слова или ссылки, которые администрация считает недопустимыми на этом сайте: '.$stoplist,'level'=>3);

    // проверка на наличие ссылок при условии, что ссылки пользователю запрещены
    $links_mode=$this->get_opt('links_mode','group');
    if ($links_mode==='none') {
      $links_count = preg_match('|<a[\w]+[^>]*href=[^>]+>|is',$parsed);
      if ($links_count>0) {
        if ($links_mode==='none') $result[]=array('text'=>'У вас недостаточно прав доступа, чтобы отправлять сообщения со ссылками.','level'=>3);
      }
    }

    return $result;
  }

  /** Проверка темы на возможность создания/отправки сообщений **/
  function thread_pre_check($thread) {
    $result = array();
    if (empty($thread['title'])) $result[]=array('text'=>'Название темы не может быть пустым','level'=>3);
    return $result;
  }

  /** Выполнение действий после отправки сообщения, в частности, рассылки уведомлений **/
  function pm_postprocess($thread,$pm,$parsed) {
    $notify_lib = $this->load_lib('notify',false);
    if ($notify_lib) {
      $userdata = $this->load_user($this->get_uid(),2);
      $notify_lib->new_pm($thread,$pm,$parsed,$this->get_username(),$userdata['basic']['email']);
    }
  }

  function action_mark_all() {
    $pmlib = $this->load_lib('privmsg',true);
    $pmlib->mark_read($this->get_uid(),false); // false означает, что отмечаем как просмотренные все цепочки сообщений
    $this->message('Все личные сообщения отмечены как прочитанные');
    $this->redirect($this->http($this->url('privmsg/')));
  }

  function  process() {
    if (!$this->get_opt('enable_privmsg')) $this->output_403('Личные сообщения выключены на этом форуме!');
    if ($this->is_guest()) { // гостей к функциям этого модуля пускать не имеет смысла, если заходит гость, отпраляем его на 403
      $this->output_403('Войдите на форум как пользователь, чтобы иметь возможность пользоваться личными сообщениями!',true);
    }
    return parent::process();
  }

  function set_title() {
    if ($this->action==='thread') $result=$this->out->thread['title'];
    else $result = 'Личные сообщения';
    return $result;
  }

  function set_location() {
    $result = parent::set_location();
    if ($this->action!=='view') $result[1]=array('Личные сообщения',$this->url('privmsg/'));
    else $result[1]=array('Личные сообщения');
    if ($this->action==='thread') $result[2]=array($this->out->thread['title']);
    return $result;
  }

  function get_action_name() {
    $result='Просматривает личные сообщения';
    return $result;
  }
}
