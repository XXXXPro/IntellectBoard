<?php

/** ================================
*  @package IntBPro
*  @author 4X_Pro <admin@openproj.ru>
*  @version 3.0
*  @copyright 2015, 4X_Pro, INTBPRO.RU
*  @url http://intbpro.ru
*  Модуль работы с разделами для Центра Администрирования  Intellect Board 3 Pro
*  ================================ */

class forums extends Application_Admin {
  /** Просмотр списка форумов **/
  function action_view() {
    $forumlib = $this->load_lib('forums',true);    
    /* @var $forumlib Library_forums */
    $this->out->categories=$forumlib->list_categories(false,true);
        
    $cond['typeinfo']=true; // получать информацию о типе раздела
    if (empty($_REQUEST['show_all'])) $cond['owner']=0; // извлекаем только разделы общего пользователя, если нет указания, что нужно показать все разделы вообще
    $cond['sortfield']=true;// извлекаем данные о порядке сортировки 
    $forums = $forumlib->list_forums($cond); 
        
    foreach ( $forums as $curforum ) {
      $this->out->categories[$curforum['category_id']]['forums'][]=$curforum; // добавляем в список форумов данной категории для вывода
    }
    $this->out->sort_key = $this->gen_auth_key(false,'sort');
    $this->out->is_founder = $this->is_admin(true); // проверка, является ли пользователь Основателем, чтобы иметь возможность удалять разделы
  }
  
  /** Сохранение полей сортировки для разделов и категорий **/
  function action_sort() {
    if (!empty($_POST['cat_sort'])) {
      foreach ($_POST['cat_sort'] as $id=>$value) {
        $sql = 'UPDATE '.DB_prefix.'category SET sortfield='.intval($value).' WHERE id='.intval($id);
        $this->db->query($sql);
      }
    }
    if (!empty($_POST['sort'])) {
      foreach ($_POST['sort'] as $id=>$value) {
        $sql = 'UPDATE '.DB_prefix.'forum SET sortfield='.intval($value).' WHERE id='.intval($id);
        $this->db->query($sql);
      }
    }
    $this->message('Сортировка разделов и категорий выполнена!',1);
    $this->redirect($this->http(str_replace('sort.htm','view.htm',$_SERVER['REQUEST_URI'])));
  }
  
  /** Провера корректности данных категории **/
  function category_pre_check($data) {
    $result = array();
    if (empty($data['title'])) $result=array('text'=>'Название категории не может быть пустым','level'=>3);
    return $result;    
  }

  /** Создание категории **/
  function action_create_category() {
    if ($this->is_post()) { // не выносим обновление категории в библиотеку, т.к. они создаются только через АЦ и их создание тривиально
      $errors = $this->category_pre_check($_POST['category']);
      if (empty($errors)) {
        $this->db->insert(DB_prefix.'category', $_POST['category']);
        $this->message('Категория создана!',1);
        $this->redirect($this->http(str_replace('create_category.htm','view.htm',$_SERVER['REQUEST_URI'])));
      }
      else $this->out->category = $_POST['category'];
    }
    return 'forums/category.tpl';
  }
  
  /** Редактирование категории (на данный момент только названия) **/
  function action_edit_category() {
    if (empty($_REQUEST['id'])) {
      $this->message('Не указан идентификатор категории',3);
      $this->redirect($this->http(str_replace('category_edit.htm','view.htm',$_SERVER['REQUEST_URI'])));
    }
    if ($this->is_post()) { // не выносим обновление категории в библиотеку, т.к. они создаются только через АЦ и их создание тривиально
      $errors = $this->category_pre_check($_POST['category']);
      if (empty($errors)) {
        $this->db->update(DB_prefix.'category', $_POST['category'], 'id='.intval($_REQUEST['id']));
        $this->message('Категория отредактирована!',1);
        $this->redirect($this->http(str_replace('edit_category.htm','view.htm',$_SERVER['REQUEST_URI'])));
      }
      else $this->out->category = $_POST['category'];      
    }
    else {
      $forumlib = $this->load_lib('forums',true);    
      /* @var $forumlib Library_forums */
      $cat=$forumlib->list_categories($_REQUEST['id'],true);
      $this->out->category=$cat[$_REQUEST['id']];
    }
    $this->out->id=$_REQUEST['id'];
    return 'forums/category.tpl';    
  }
  
  function action_delete_category() {
    if (empty($_REQUEST['id'])) {
      $this->message('Не указан идентификатор категории',3);
    }
    else {
      $id = $_REQUEST['id'];
      $sql = 'SELECT COUNT(*) FROM '.DB_prefix.'forum WHERE category_id='.intval($id);
      $count = $this->db->select_int($sql);
      if ($count>0) {
        $this->message('Нельзя удалить категорию, в которой есть разделы! Удалите сначала их или перенесите в другую категорию!',3);
      }
      else {
        $sql = 'DELETE FROM '.DB_prefix.'category WHERE id='.intval($id);
        $this->db->query($sql);
        $this->message('Категория удалена!',1);
      }
    }
    $this->redirect($this->http(str_replace('category_edit.htm','view.htm',$_SERVER['REQUEST_URI'])));
  } 
  

  /** Получение списка возможных р **/
  function parent_forums($id=false) {
    $sql = 'SELECT f.id, f.title FROM '.DB_prefix.'forum f, '.DB_prefix.'forum_type ft '.
        'WHERE f.module=ft.module AND ft.allow_subforums=\'1\' AND f.id!='.intval($id).' ORDER BY f.sortfield';
    return array('0'=>'Главная страница')+$this->db->select_simple_hash($sql); 
  }  
  
  function action_create_forum() {
    if (empty($_REQUEST['type'])) { // если не указан тип форума, предлагаем пользователю его выбрать
      $sql = 'SELECT module, typename FROM '.DB_prefix.'forum_type ORDER BY sortfield';
      $this->out->types = $this->db->select_simple_hash($sql);
    }
    else {
      if (!$this->valid_file($_REQUEST['type'])) trigger_error('Некорректный тип раздела!',E_USER_ERROR);
      $forumlib = $this->load_lib('forums',true);
      /* @var $forumlib Library_forums */
      $sql='SELECT id, title FROM ' . DB_prefix . 'category ORDER BY sortfield';
      $this->out->categories = $this->db->select_simple_hash($sql);
      
      $templatelib = $this->load_lib('template');
      /* @var $templatelib Library_template */
      if ($templatelib) $this->out->templates = array(''=>'Стиль сайта по умолчанию')+$templatelib->get_list($this->is_admin()); // если пользователь -- админ, он может выбрать любой шаблон, иначе -- только незаблокированные
      $this->out->parent_forums = $this->parent_forums();
      
      if ($this->is_post()) {
        $data=$_REQUEST['forum'];
        unset($data['id']); // чтобы id форума присвоился автоматически
        $extdata = isset($_REQUEST['extdata']) ? $_REQUEST['extdata'] : array();
        $data['attach_types'] = isset($_REQUEST['filetypes']) ? array_sum($_REQUEST['filetypes']) : 0; // суммируем значения checkboxов filetypes, чтобы получить attach_types в виде битовой маски  
        $errors = $forumlib->check_forum($data);
        if (empty($errors)) {
          $forumlib->create_forum($data,$extdata,$_REQUEST['type'],0);
          $this->message('Новый раздел создан!',1);
          $this->reset_session_cache(); // сбрасываем, т.к. при создании нового форума нужно перекешировать информацию о разделах в сессии            
          if ($forumlib->regenerate_routes()) $this->redirect($this->http(str_replace('create_forum.htm','view.htm',$_SERVER['REQUEST_URI'])));
          else {            
            $this->message('Не удалось обновить файл для управления переадресацией запросов! Вам необходимо сделать это вручную!',2);
            $this->redirect($this->http(str_replace('create_forum.htm','routes.htm',$_SERVER['REQUEST_URI'])));
          }          
        }
        else {
          $this->out->forumdata = $data;
          $this->out->extdata = $extdata;
          $this->message($errors);
        }
      }
      else {
        $this->out->forumdata = $forumlib->set_new_forum();
      }
      if ($_REQUEST['type']==='gallery') $this->out->forumdata['maxattach']=24; // для галерей число прикреплённых файлов увеличено
      $this->out->type = $_REQUEST['type'];
      $this->out->time = $this->time; // текущее время используется некоторыми типами разделов для генерации URL
      return 'forums/edit_forum.tpl';      
    }
  }
  
  function action_edit_forum() {
    $id = $_REQUEST['id'];
    if (empty($_REQUEST['id'])) {
      $this->message('Не указан идентификатор раздела',3);
      $this->redirect($this->http(str_replace('edit_forum.htm','view.htm',$_SERVER['REQUEST_URI'])));
    }
    
    $forumlib = $this->load_lib('forums',true);
    /* @var $forumlib Library_forums */
    $sql='SELECT id, title FROM ' . DB_prefix . 'category ORDER BY sortfield';
    $this->out->categories = $this->db->select_simple_hash($sql);
    
    $templatelib = $this->load_lib('template');
    /* @var $templatelib Library_template */
    if ($templatelib) $this->out->templates = array(''=>'Стиль сайта по умолчанию')+$templatelib->get_list($this->is_admin()); // если пользователь -- админ, он может выбрать любой шаблон, иначе -- только незаблокированные
    
    $this->out->parent_forums = $this->parent_forums($id);
    
    if ($this->is_post()) {
      $data=$_REQUEST['forum'];
      $extdata = isset($_REQUEST['extdata']) ? $_REQUEST['extdata'] : array();
      $data['attach_types'] = isset($_REQUEST['filetypes']) ? array_sum($_REQUEST['filetypes']) : 0; // суммируем значения checkboxов filetypes, чтобы получить attach_types в виде битовой маски
      $data['id']=$id;      
      $errors = $forumlib->check_forum($data);
      if (empty($errors)) {
        $forumlib->update_forum($data,$extdata);
        $this->message('Изменения в настройках раздела «'.$data['title'].'» сохранены!',1);
        $this->reset_session_cache(); // сбрасываем, т.к. при обновлении форума нужно перекешировать информацию о разделах в сессии
        if ($forumlib->regenerate_routes()) $this->redirect($this->http(str_replace('edit_forum.htm','view.htm',$_SERVER['REQUEST_URI'])));
        else {
          $this->message('Не удалось обновить файл для управления переадресацией запросов! Вам необходимо сделать это вручную!',2);
          $this->redirect($this->http(str_replace('edit_forum.htm','routes.htm',$_SERVER['REQUEST_URI'])));
        }                
      }
      else {
        $this->out->forumdata = $data;
        $this->out->extdata = $extdata;
        $this->message($errors);
      }
    }
    else {
      $this->out->forumdata = $forumlib->get_forum($id,true);
      if (empty($this->out->forumdata)) $this->output_404('Раздела с таким номером не существует!');
      $this->out->type = $this->out->forumdata['module'];
      $extdata = $this->get_text($id, 3); // 3 -- сериализованные расширенные данные раздела
      if ($extdata) $this->out->extdata = unserialize($extdata); 
    }   
  }
  
  function action_delete_forum() {
    if (!$this->is_admin(true)) $this->output_403('Только пользователь с правами Основателя может удалять разделы!');
    $forumlib = $this->load_lib('forums',true);
    /* @var $forumlib Library_forums */
    $id = $_REQUEST['id'];
    if (empty($_REQUEST['id'])) {
      $this->message('Не указан идентификатор раздела',3);
      $this->redirect($this->http(str_replace('delete_forum.htm','view.htm',$_SERVER['REQUEST_URI'])));
    }
    $this->out->forumdata = $forumlib->get_forum($id);
    if (empty($this->out->forumdata)) $this->output_404('Раздела с таким номером не существует!');    
    if ($this->is_post()) {
      if ($this->out->forumdata['hurl']===$_POST['forum_hurl']) { // если корректно введен HURL для подтверждения
        $dellib = $this->load_lib('delete',true);
        /* @var $dellib Library_delete */
        $dellib->delete_forums($id);
        $this->message('Раздел удален!',1);
        if ($forumlib->regenerate_routes()) $this->redirect($this->http(str_replace('delete_forum.htm','view.htm',$_SERVER['REQUEST_URI'])));
        else {
          $this->message('Не удалось обновить файл для управления переадресацией запросов! Вам необходимо сделать это вручную!',2);
          $this->redirect($this->http(str_replace('delete_forum.htm','routes.htm',$_SERVER['REQUEST_URI'])));
        }        
      }
      else $this->message('Введен некорректный HURL форума!',3);
    }
  }
  
  /** Операции по изменению настроек для группы разделов **/
  function action_mass() {
    if ($this->is_post()) {
      $change = array();
      if ($_POST['forum']['max_attach']==='') unset($_POST['forum']['max_attach']);
      foreach ($_POST['forum'] as $key=>$value) if ($value!=-1) $change[]='"'.$this->db->slashes($key).'"=\''.$this->db->slashes($value).'\'';
      $sum = isset($_POST['filetypes']) ? array_sum($_POST['filetypes']) : 0;
      if ($_POST['attaches']==='change') $change[]='"attach_types"='.intval($sum);
      if (!empty($change)) {
        if ($_POST['range']==='all' || !empty($_POST['ids'])) {
          $sql = 'UPDATE '.DB_prefix.'forum SET '.join(',',$change);
          if ($_POST['range']!=='all') $sql.=' WHERE '.$this->db->array_to_sql($_POST['ids'], 'id');
          $this->db->query($sql);
          $this->message('Настройки разделов изменены!',1);
          $this->redirect($this->http($_SERVER['REQUEST_URI']));          
        }
        else $this->message('Не выбран ни один раздел для изменения',2);
      }
      else $this->message('Не выбрано изменение ни одной опции',2);
    }
      $forumlib = $this->load_lib('forums',true);
      /* @var $forumlib Library_forums */
      $this->out->categories=$forumlib->list_categories(false,true);
      
      $cond['allow_mass']=true; // получать информацию только о разделах, поддерживающих массовые операции
      $cond['owner']=0; // извлекаем только разделы общего пользователя, если нет указания, что нужно показать все разделы вообще
      $forums = $forumlib->list_forums($cond);
      
      foreach ( $forums as $curforum ) {
        $this->out->categories[$curforum['category_id']]['forums'][]=$curforum; // добавляем в список форумов данной категории для вывода
      }

      $templatelib = $this->load_lib('template');
      /* @var $templatelib Library_template */
      if ($templatelib) $this->out->templates = array('-1'=>'Оставить без изменений',''=>'Стиль сайта по умолчанию')+$templatelib->get_list($this->is_admin()); // если пользователь -- админ, он может выбрать любой шаблон, иначе -- только незаблокированные
  }
  
  /** Настройка прав доступа для отдельного раздела. **/
  function action_access() {
    $id = $_REQUEST['id'];
    if (empty($_REQUEST['id'])) {
      $this->message('Не указан идентификатор раздела',3);
      $this->redirect($this->http(str_replace('access.htm','view.htm',$_SERVER['REQUEST_URI'])));
    }    
    $this->out->fields = $this->get_access_fields();    
    if ($this->is_post()) {
      //$sql = $this->db->lock_tables(DB_prefix.'access',true);
      $sql = 'DELETE FROM '.DB_prefix.'access WHERE fid='.intval($id);
      $this->db->query($sql);
      foreach ($_POST['inherit'] as $group=>$value) {
        if ($value==0) { // если права не наследуются
          $data=$_POST['access'][$group];
          foreach ($this->out->fields as $field) $data[$field]=!empty($data[$field]) ? '1' : '0';
          $data['gid']=$group;
          $data['fid']=$id;
          $this->db->insert(DB_prefix.'access',$data);          
        }
      }
      // $sql = $this->db->unlock_tables(DB_prefix.'access');
      $this->reset_session_cache(); // выставляем признак необходимости обновить данные, закешированные в сесии, чтобы изменение прав повлияло сразу     
      $this->message('Права доступа к разделу изменены!',1);
      $this->redirect($this->http($_SERVER['REQUEST_URI']));      
    }
    else {
      $forums = array();
      $next_id=$id;
      while ($next_id!=0) { // получаем данные о текущем разделе и всех его родительских разделах
        $sql = 'SELECT f.title, f.id, f.parent_id FROM '.DB_prefix.'forum f WHERE id='.intval($next_id);
        $curforum=$this->db->select_row($sql);
        $sql = 'SELECT * FROM '.DB_prefix.'access WHERE fid='.intval($next_id);
        $curforum['access'] = $this->db->select_hash($sql, 'gid');       
        $next_id=$curforum['parent_id'];
        $forums[]=$curforum;
      }
      // получаем данные для главной страницы
      $curforum=array('title'=>'Главная страница','id'=>0);
      $sql = 'SELECT * FROM '.DB_prefix.'access WHERE fid=0';
      $curforum['access'] = $this->db->select_hash($sql, 'gid');
      $forums[]=$curforum;
      
      $sql = 'SELECT level, name FROM '.DB_prefix.'group ORDER BY level DESC';
      $groups = $this->db->select_all($sql);
      for ($i=0,$count1=count($groups);$i<$count1;$i++) {
        for ($j=0,$count2=count($forums);$j<$count2 && !isset($groups[$i]['access']);$j++) { 
          // в массиве forums разделы упорядочены по порядку наследования, от ближайшего предка к первой странице, поэтому в результате первым будет найден ближайший предок (или сам раздел), у которого права для текущей группы не унаследованы, а выставлены собственнные  
          if (isset($forums[$j]['access'][$groups[$i]['level']])) {
            $groups[$i]['access']=$forums[$j]['access'][$groups[$i]['level']];
            $groups[$i]['inherit']=($forums[$j]['id']!=$id); // признак того, что раздел имеет свои права, а не унаследованные
            $groups[$i]['title']=$groups[$i]['inherit'] ? (($forums[$j]['id']!=0) ? 'от раздела '.$forums[$j]['title'] : 'от главной страницы') : '';            
          }
        }
      }
      $this->out->groups = $groups;
      $this->out->forum_title = $forums[0]['title'];
      $this->out->forum_id = $id;
    }
  }
  
  function action_routes() {
    $forumlib = $this->load_lib('forums',true);
    /* @var $forumlib Library_forums */
    $this->out->routes = $forumlib->build_routes();
  }
}