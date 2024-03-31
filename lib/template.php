<?php
/** ================================
 *  Intellect Board Pro
 *  http://intbpro.ru
 *  Библиотека операций над шаблонами
 *  ================================ */

class Library_template extends Library {
  /** Получение списка шаблонов
   *
   * @param boolean $locked Включать в список так же шаблоны, помеченные как отключенные администратором
   */
  function get_list($locked=false) {
    $path = './s';
    $dh = opendir($path);
    $result=array();
    while ($curitem = readdir($dh)) {
      if (is_dir($path.'/'.$curitem) && $curitem!='.' && $curitem!='..') {
        $key=$curitem;
        if (file_exists($path.'/'.$curitem.'/name.txt')) $value=file_get_contents($path.'/'.$curitem.'/name.txt'); // читаем название шаблона из файла
        else $value=$key; // если у шаблона нет нормального названия в файле name.txt, берем в качестве него имя каталога
        if ($locked || !file_exists($path.'/'.$curitem.'/locked.txt')) $result[$key]=$value; // если шаблон не заблокирован (нет файла locked.txt) или нужно получить все шаблоны с любым статусом, добавляем его в список
      }
    }
    return $result;
  }

  function is_valid($template) {
    global $app;
    if (!$app->valid_file($template)) return false; // если имя шаблона содержит запрещенные символы, считаем его недопустимым
    if (!file_exists('./s/'.$template)) return false; // если 
    return true;
  }
}