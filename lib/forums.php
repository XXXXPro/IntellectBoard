<?php
/** ================================
*  @package IntBPro
*  @author 4X_Pro <admin@openproj.ru>
*  @version 3.0
*  @copyright 2007,2010, 2012-2015 4X_Pro, INTBPRO.RU
*  @url http://www.intbpro.ru
*  Библиотека работы с разделами
*  ================================ */

class Library_forums extends Library {

  /** Извлечение данных о категориях (или категории с указанным номером) **/
  function list_categories($category=false,$sortfield=false) {
    $sql='SELECT id, title, collapsed ';
    if ($sortfield) $sql.=', sortfield ';
    $sql.='FROM ' . DB_prefix . 'category ';
    if ($category) $sql.='WHERE id=' . $category . ' ';
    $sql.='ORDER BY sortfield';
    return Library::$app->db->select_hash($sql,'id');
  }

  /** Получение информации об отдельно взятом форуме **/
  function get_forum($id,$extdata=false) {
    $sql = 'SELECT f.* ';
    if ($extdata) $sql.=', tx.data AS extdata ';
    $sql.='FROM '.DB_prefix.'forum f ';
    if ($extdata) $sql.='LEFT JOIN '.DB_prefix.'text tx ON (tx.id=f.id AND tx.type=3) ';
    $sql.='WHERE f.id='.intval($id);
    $result=Library::$app->db->select_row($sql);
    if ($extdata && !empty($result['extdata'])) $result['extdata']=unserialize($result['extdata']);
    return $result;
  }

  /** Получение информации об имеющихся разделах
   *
   * @param array $cond Массив с данными для выбора разделов. Может содержать ключи:
   *  visits_user -- получать время последних визитов
   *  views -- получать количество просмотров
   *  lastpost -- данные о последнем сообщении
   *  parent -- извлекать разделы, у которых родительский имеет указанный номер
   *  category -- извлекать разделы, принадлежащие только определенной категории
   *  start -- извлекать только разделы, для которых включен показ на главной
   *  owner -- если этот ключ не пуст, извлекать только разделы с указанным владельцем
   *  extdata -- если этот ключ не пуст, извлекаются расширенные данные форума, которые хранятся в сериализованном виде в таблице text с type=3
   *  all -- извлекать все данные о разделах, а не только основные (название, описание, категория, модуль, значки и статистика)
   *     **/
  function list_forums($cond) {
    if (empty($cond['all'])) $columns='f.id, category_id, title, descr, hurl, f.module, lastmod, post_count, topic_count, icon_new, icon_nonew';
    else $columns='f.*';
    if (!empty($cond['sortfield'])) $columns.=', f.sortfield';
    if (!empty($cond['views'])) $columns.=', views';
    if (!empty($cond['lastpost'])) $columns.=', p.uid, p.author, p.postdate';
    if (!empty($cond['visits_user'])) $columns.=', visit2, mark_time ';
    if (!empty($cond['extdata'])) $columns.=', tx.data AS extdata';
    if (!empty($cond['typeinfo'])) $columns.=', ft.module, ft.typename, ft.has_rules, ft.has_foreword, ft.allow_mass, ft.route, ft.skip_sitemap';

    $where = '1=1';
    if (!empty($cond['start'])) $where.=' AND is_start=\'1\'';
    if (!empty($cond['category'])) $where.=' AND category_id='.intval($cond['category']);
    if (isset($cond['parent'])) $where.=' AND parent_id='.intval($cond['parent']);
    if (isset($cond['owner'])) $where.=' AND owner='.intval($cond['owner']);
    if (!empty($cond['allow_mass'])) $where.=' AND ft.allow_mass=\'1\'';

    $sql = 'SELECT  '.$columns.' FROM '.DB_prefix.'forum f ';
    if (!empty($cond['views'])) $sql.='LEFT JOIN '.DB_prefix.'views v ON (f.id=v.oid AND v.type="forum") ';
    if (!empty($cond['lastpost'])) $sql.='LEFT JOIN '.DB_prefix.'post p ON (p.id=f.last_post_id) ';
    if (!empty($cond['visits_user'])) $sql.='LEFT JOIN '.DB_prefix.'last_visit lv ON (lv.uid='.intval($cond['visits_user']).' AND lv.oid=f.id AND lv.type=\'forum\') '.
      'LEFT JOIN '.DB_prefix.'mark_all ma ON (ma.fid=f.id AND ma.uid='.intval($cond['visits_user']).') ';
    if (!empty($cond['extdata'])) $sql.='LEFT JOIN '.DB_prefix.'text tx ON (tx.id=f.id AND tx.type=3) ';
    if (!empty($cond['typeinfo']) || !empty($cond['allow_mass'])) $sql.='LEFT JOIN '.DB_prefix.'forum_type ft ON (f.module=ft.module) ';
    $sql.='WHERE '.$where.' ORDER BY f.sortfield';

    $result = Library::$app->db->select_all($sql);

    if (!empty($cond['extdata'])) // десериализуем расширенные данные раздела, если таковые имеются
      for ($i=0, $count=count($result);$i<$count; $i++)
        if (!empty($result[$i]['extdata'])) $result[$i]['extdata']=unserialize($result[$i]['extdata']);
    return $result;
  }

  /** Проверка правильности параметров перед созданием или редактированием раздела **/
  function check_forum($data) {
    $result = array();
    if (empty($data['title'])) $result[]=array('text'=>'Не задано название раздела!','level'=>3);
    if (!preg_match('|^[A-Za-z0-9_\-/\.]+$|',$data['hurl'])) $result[]=array('text'=>'Некорректный URL форума','level'=>3);
    // проверяем, что такого HURL нет у других разделов
    $sql = 'SELECT COUNT(id) FROM '.DB_prefix.'forum WHERE hurl=\''.Library::$app->db->slashes($data['hurl']).'\'';
    if (!empty($data['id'])) $sql.=' AND id!='.intval($data['id']);
    $count = Library::$app->db->select_int($sql);
    if ($count>0) $result[]=array('text'=>'Такой URL уже используется!','level'=>3);
    return $result;
  }

  /** Установка начальных настроек для нового раздела **/
  function set_new_forum() {
    $result = array('template'=>'','template_override'=>0,'bcode'=>1,'max_smiles'=>16,
        'max_attach'=>5,'attach_types'=>255,'is_stats'=>1,'is_flood'=>0,'is_start'=>1,
        'sort_mode'=>'DESC','sort_column'=>'last_post_time','polls'=>1,'selfmod'=>0,
        'sticky_post'=>2,'rate'=>2,'rate_value'=>0,'rate_flood'=>0,'tags'=>1);
    return $result;
  }

  /** Создание раздела **/
  function create_forum($data,$extdata,$type,$owner=0) {
    $data['module']=$type;
    $data['owner']=$owner;
    $data['lastmod']=Library::$app->time; // время последней модификации раздела меняем на текущее, чтобы не выдавались версии страниц из кеша со старыми настройками
    Library::$app->db->insert(DB_prefix.'forum', $data);
    $id=Library::$app->db->insert_id();
    if ($id && !empty($extdata)) {
      $sql = 'INSERT INTO '.DB_prefix.'text (data,id,type) VALUES (\''.Library::$app->db->slashes(serialize($extdata)).'\', '.intval($id).', 3)';
      Library::$app->db->query($sql);
    }
    if ($data['parent_id']!=0) {
      $sql ='UPDATE '.DB_prefix.'forum SET lastmod='.intval(Library::$app->time).' WHERE id='.intval($data['parent_id']);
      Library::$app->db->query($sql);
    }
    return $id;
  }

  /** Изменение данных раздела.
  *  Данные о разделе состоят из двух частей: основные данные, которые хранятся в таблице prefix_forum и должны соответствовать по структуре
  *  и дополнительные данные, которые хранятся в сериализованном виде prefix_text и могут иметь произвольную структуру.
  *  
  * @param mixed $data — хеш с основными данными о разделе или номер раздела. Если передается только номер, то таблица основных данных не изменяется, а изменяются только расширенные данные.
  * @param array $extdata — хеш с расширенными данными. При записи данных происходит добавление ключей к уже имеющимся через array_merge.
  **/
  function update_forum($data,$extdata=false) {
    if (is_numeric($data)) {
      $id=intval($data);
      $data = array('id'=>intval($data));
    }
    else $id=$data['id'];
    $oldtext = Library::$app->get_text($id, 3); // 3 -- сериализованные данные раздела
    if ($oldtext) {
      $olddata = unserialize($oldtext);
      $extdata = array_merge($olddata,$extdata);
    }
    $data['lastmod']=Library::$app->time; // время последней модификации раздела меняем на текущее, чтобы не выдавались версии страниц из кеша
    if (!is_numeric($data)) Library::$app->db->update(DB_prefix.'forum', $data, 'id='.intval($id));
    $sql = 'DELETE FROM '.DB_prefix.'text WHERE id='.intval($id).' AND type=3';
    Library::$app->db->query($sql);
    if (!empty($extdata)) {
      $sql = 'INSERT INTO '.DB_prefix.'text (data,id,type) VALUES (\''.Library::$app->db->slashes(serialize($extdata)).'\', '.intval($id).', 3)';
      Library::$app->db->query($sql);
    }
    if ($data['parent_id']!=0) { // обновляем Lastmod у родительского форума, чтобы раздел стал видимым сразу
      $sql ='UPDATE '.DB_prefix.'forum SET lastmod='.intval(Library::$app->time).' WHERE id='.intval($data['parent_id']);
      Library::$app->db->query($sql);
    }
  }

  /** Перегенерация файла с настройками роутинга запросов
   * @return bool Возвращает TRUE, если удалось сгененировать новый файл роутинга etc/routes.cfg, если файл недоступен для записи
   *   **/
  function regenerate_routes() {
    if (!is_writable(BASEDIR.'etc/routes.cfg')) return false;
    $routes=$this->build_routes();
    $result = file_put_contents(BASEDIR.'etc/routes.cfg', $routes);
    return $result;
  }

  /** Генерация строки с данными для routes.cfg **/
  function build_routes() {
    $sql = 'SELECT f.id, f.title, f.hurl, ft.route FROM '.DB_prefix.'forum f, '.DB_prefix.'forum_type ft '.
        'WHERE f.module=ft.module ORDER BY f.sortfield';
    $forums = Library::$app->db->select_all($sql);
    $buffer = '';    
    $routes = file_get_contents(BASEDIR.'etc/routes.txt')."\n";
    for ($i=0, $count=count($forums);$i<$count;$i++) {
      $tmp2 = $forums[$i]['route'];
      if ($forums[$i]['hurl']==='/') $forums[$i]['hurl']=''; // необходимо для корректной обработки корневого раздела
      unset($forums[$i]['route']); // убираем поле route из тех же соображений
      foreach ($forums[$i] as $key=>$value) $tmp = str_replace('<<<'.$key.'>>>',$value,$tmp);
      $buffer.=$tmp;
      $buffer = str_replace('RewriteRule ^/','RewriteRule ^',$buffer); // для корректной обработки корневого раздела
      foreach ($forums[$i] as $key=>$value) $tmp2 = str_replace('<<<'.$key.'>>>',$value,$tmp2);
      $routes.=$tmp2."\n";
    }
    if ($mainpage=Library::$app->get_opt('forum_mainpage')) {
      $route_index_data = '^'.$mainpage.'$ mainpage.php'; 
    }
    else $route_index_data = '^$ mainpage.php'; 
    $routes = str_replace('<<<index_route>>>',$route_index_data,$routes);

    return $routes;
  }
}
