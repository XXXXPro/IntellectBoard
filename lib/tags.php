<?php

/** ================================
 *  Intellect Board Pro
 *  http://intbpro.ru
 *  Библиотека работы с тегами
 *  ================================ */

class Library_tags extends Library {
  /** Сохранение строки тегов
   *
   * @global Application $this->app
   * @param string $tag_string Строка тегов
   * @param integer $item Идетнтификатор элемента, к которому относятся теги
   * @param integer $type Тип тега.
   * В данный момент поддерживаются следующие:
   *    0 -- тег для тем, фотографий, статей
   *    1 -- интересы пользователя
   */
  function set_tags($tag_string,$item,$type=0) {
    $tag_string = preg_replace('/,\s+/',',',addslashes(trim($tag_string)));
    if (!empty($tag_string)) {
      if (function_exists('mb_strtolower')) $lowerfunc = 'mb_strtolower';
      else $lowerfunc = 'strtolower';
      $tags = explode(',',$tag_string);

      $sql = 'SELECT LOWER(tagname),id FROM '.DB_prefix.'tagname WHERE tagname IN (\''.join('\',\'',$tags).'\') AND type=\''.intval($type).'\'';
      $taghash = Library::$app->db->select_simple_hash($sql);

      for ($i=0, $count=count($tags);$i<$count;$i++) {
        if (!isset($taghash[$lowerfunc($tags[$i])])) {
          $sql = 'INSERT INTO '.DB_prefix.'tagname (tagname,count,type) VALUES (\''.$tags[$i].'\',0,\''.intval($type).'\')';
          Library::$app->db->query($sql);
          $taghash[$tags[$i]]=Library::$app->db->insert_id();
        }
      }
    }
    else $taghash = array();

    $sql = 'SELECT tag_id FROM '.DB_prefix.'tagentry WHERE item_id='.intval($item);
    $oldtags = Library::$app->db->select_all_numbers($sql);

    $proc_tags = array();
    foreach ($taghash as $tag=>$id) {     // вставляем теги в таблицу связей тег-идентификатор и фиксируем, какие вставили
      if (!in_array($id, $oldtags)) {
        $sql = 'INSERT INTO '.DB_prefix.'tagentry (tag_id,item_id) VALUES ('.$id.','.intval($item).')';
        Library::$app->db->query($sql);
        $proc_tags[]=$id;
      }
    }
    if (!empty($proc_tags)) { // если вставили хоть один новый тег, то обновляем количество
      $sql = 'UPDATE '.DB_prefix.'tagname SET count=count+1 WHERE id IN ('.join(',',$proc_tags).')';
      Library::$app->db->query($sql);
    }

    $lost_tags = array_diff($oldtags, array_values($taghash)); // определяем, какие теги были удалены по сравнению с предыдущим разом
    if (!empty($lost_tags)) {
      $sql = 'DELETE FROM '.DB_prefix.'tagentry WHERE item_id='.intval($item).' AND tag_id IN ('.join(',',$lost_tags).')';
      Library::$app->db->query($sql);
      $sql='UPDATE '.DB_prefix.'tagname SET count=count-1 WHERE id IN ('.join(',',$lost_tags).')';
      Library::$app->db->query($sql);
      $sql = 'DELETE FROM '.DB_prefix.'tagname WHERE count<=0';
      Library::$app->db->query($sql);
    }
  }

  function get_tags($item,$type=0) {
    $sql = 'SELECT t.tagname FROM '.DB_prefix.'tagentry ti, '.DB_prefix.'tagname t '.
    'WHERE t.id=ti.tag_id AND ti.item_id='.intval($item).' AND t.type=\''.intval($type).'\' '.
    'ORDER BY tagname';
    $tags = Library::$app->db->select_all_strings($sql);
    return $tags;
  }

  /** Получает теги для множества объектов в виде хеша где ключ объекта — его идентификатор, значение — массив тегов  **/
  function get_tags_by_ids($items,$type=0) {
    $sql = 'SELECT ti.item_id, t.tagname FROM '.DB_prefix.'tagentry ti, '.DB_prefix.'tagname t '.
    'WHERE t.id=ti.tag_id AND '.Library::$app->db->array_to_sql($items,'ti.item_id').' AND t.type=\''.intval($type).'\' '.
    'ORDER BY tagname';
    $tags = Library::$app->db->select_all($sql);
    $result=array();
    for ($i=0, $count=count($tags);$i<$count;$i++) if ($tags[$i]['tagname']!='') {
      $id=$tags[$i]['item_id'];
      if (empty($result[$id])) $result[$id]=array();
      $result[$id][]=$tags[$i]['tagname'];
    }
    return $result;
  }

  function get_tags_string($item,$type=0) {
    $tags = $this->get_tags($item,$type);
    return join(', ',$tags);
  }

  /** Получение списка всех тегов указанного типа
   *
   * @global Application $this->app
   * @param integer $type Тип тегов (0 -- теги тем/статей/фотографий, 1 -- интересы пользователей)
   * @param <type> $limit Максимальное количество тегов, которое требуется выдать
   * @return array Массив с тегами, каждый элемент которого содержит хеш с ключами tagname и count
   */
  function get_all_tags($type=0,$limit=false) {
    $sql = 'SELECT tagname,count FROM '.DB_prefix.'tagname WHERE type=\''.intval($type).'\' ORDER BY count DESC';
    $tags = Library::$app->db->select_all($sql,$limit);
    return $tags;
  }

  /** Получает список идентификаторов объектов по тегам
   * @param string $tags Строка с тегами, разделенными запятыми
   * @param integer $type Тип объектов, которые ищутся по тегу (0 -- темы/статьи/фотографии, 1 -- интересы пользователей)
   **/
  function get_ids_by_tags($tags,$type=0) {
    if (!is_array($tags)) $tags = explode(',',preg_replace('|\s*,\s*|',',',$tags));
    $sql = 'SELECT item_id FROM '.DB_prefix.'tagname tn, '.DB_prefix.'tagentry te WHERE '.Library::$app->db->array_to_sql($tags,'tagname').
       ' AND tn.id=te.tag_id AND tn.type='.intval($type);
    $ids = Library::$app->db->select_all_numbers($sql);
    return $ids;
  }
}

?>
