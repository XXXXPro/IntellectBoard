<?php
/** ================================
*  @package IntBPro
*  @author 4X_Pro <admin@openproj.ru>
*  @version 3.0
*  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
*  @url http://www.intbpro.ru
*  Библиотека генерации тизеров и заголовков из полного текста сообщения
*  ================================ */

class Library_teaser extends Library {
/** Определяет позицию, где должен заканчиваться teaser сообщения так, чтобы разрезание было максимально корректным. 
  * Обработка выполняется следующим образом: вырезается содержимое тегов <pre> и <table>, HTML-комментарии.
  * Приоритеты такие:
  * разрезание по началу абзаца
  * разрезание по переводу строки
  * разрезание по началу некоторых тегов (img, audio, video, code, pre, hr, iframe, object)
  * Если при попытках такого разрезания результат оказывается короче $min_length, делается попытка разрезать по точке, восклицательному или вопросительному знакам. 
  * В случае невозможности и этого — по знакам препинания (двоеточие, запятая, точка с запятой, тире).
  * Если невозможно и это — по пробелу, а в случае его отсутствия — берётся просто $length символов
  * @param string $parsed HTML-код сообщения (уже обработанный Library_bbcode)
  * @param integer $length — желаемая длина 
  * @param integer $min_length — минимальная длина
  * @return string — Teaser статьи без HTML-тегов.
  */
  function get_teaser($parsed,$length,$min_length=0) {
    $teaser = chop($this->get_teaser_preprocess($parsed,$length,$min_length));
    $teaser = preg_replace("|\n\n+|","<p>",$teaser);
    $teaser = str_replace('<p class="intb_wrap_code">','',$teaser);
    $teaser = preg_replace('|<p>\s*(<p>)+|','<p>',$teaser);
    $teaser = nl2br($teaser);
    $teaser = preg_replace('|<p>(<br\s*/?>)+|','<p>',$teaser);
    $teaser = Library_tagcloser::fix_unclosed($teaser);
    return $teaser;
  }

  /** Почти вся обработка teaser делается тут. Разбиение на две функции сделано из-за необходимости постобработки и большого количества return 
  * @param string $parsed HTML-код сообщения (уже обработанный Library_bbcode)
  * @param integer $length — желаемая длина 
  * @param integer $min_length — минимальная длина
  */
  private function get_teaser_preprocess($parsed,$length,$min_length=0) {

     // предварительная обработка — расстановка переводов строк
     $parsed = preg_replace('|(<p\W)|is',"\n\n\n$1",$parsed);
     $parsed = preg_replace('|(<br\W)|is',"\n\n$1",$parsed);

     // убираем содержимое тегов, которые не должно идти в тизерзы или заголовки
     $parsed = $this->clear_tags($parsed,true); // для тизеров вместо кода пишем подсказу, что он есть

//     $parsed = trim(strip_tags($parsed)); // TODO: возможно, разрешить теги u,i,b,em,strong?
     $slen = mb_strlen($parsed);

     if ($slen<=$length) return $parsed; // если строка и так короче желаемой длины, просто ограничимся её очисткой
     $parsed = mb_substr($parsed,0,$length);
     
     $pos = mb_strrpos($parsed,"\n\n\n");
     if ($pos>=$min_length && $pos!==false) return mb_substr($parsed,0,$pos+1);

     $pos = mb_strrpos($parsed,"\n\n");
     if ($pos>=$min_length && $pos!==false) return mb_substr($parsed,0,$pos+1);

     $pos = mb_strrpos($parsed,"\n");
     if ($pos>=$min_length && $pos!==false) return mb_substr($parsed,0,$pos+1);

     $pos = max(mb_strrpos($parsed,"."),mb_strrpos($parsed,"!"),mb_strrpos($parsed,"?"));
     if ($pos>=$min_length && $pos!==false) return mb_substr($parsed,0,$pos+1);

     $pos = max(mb_strrpos($parsed,":"),mb_strrpos($parsed,","),mb_strrpos($parsed,";"),mb_strrpos($parsed,'—'));
     if ($pos>=$min_length && $pos!==false) return mb_substr($parsed,0,$pos+1);

     $pos = mb_strrpos($parsed," ");
     if ($pos>=$min_length && $pos!==false) return mb_substr($parsed,0,$pos+1);
     
     return mb_substr($parsed,0,$length); // крайний случай, если не удалось найти ни одного подходящего разделителя
  }

  /**  
  *  @param string $parsed HTML-код сообщения (уже обработанный Library_bbcode)
  */
  function clear_tags($parsed,$code_hint=false) {
     $parsed = preg_replace('|(</h\d>)|is',"\n\n$1",$parsed); // перевод строки после заголовка
     $parsed = preg_replace('/(<(img|audio|video|pre|hr|iframe|object|code|blockquote)\W)/is',"\n$1",$parsed); // место, где вставлены указанные теги, тоже может служить точкой разбиения
     $parsed = preg_replace('/\s*\n\*s/',"\n",$parsed); // убираем лишние пробелы рядом с переводами строк
     $parsed = preg_replace('|<table\W.*?</table>|is','',$parsed); // содержимое таблиц удаляется, так как его вывод в teaser всё равно бессмысленен
     $parsed = preg_replace('|<pre\W.*?</pre>|is','',$parsed); // из тех же соображений удаляем исходный код
     if ($code_hint) $parsed = preg_replace('|<code\W.+</code>|is','<code>См. код в полном сообщении.</code>',$parsed); // в зависимости от режима либо показываем подсказку, что есть код
     else $parsed = preg_replace('|<code\W.+</code>|is','',$parsed); // либо убираем его вовсе
     $parsed = preg_replace('|<audio\W.*?</audio>|is','',$parsed); // удаляем теги audio, video и object, так как внутри них может быть fallback-содержимое
     $parsed = preg_replace('|<video\W.*?</video>|is','',$parsed); // 
     $parsed = preg_replace('|<object\W.*?</object>|is','',$parsed); // 
     return $parsed;
  }

  /** Возвращает строку без тегов не более указанной длины, сформированную из начала текста 
  * @param string $parsed HTML-код сообщения (уже обработанный Library_bbcode)
  * @param integer $length — желаемая длина 
  * @param integer $min_length — минимальная длина
  */
  function get_title($parsed,$length,$min_length=0) {
     $parsed = $this->clear_tags($parsed);
     $parsed = str_replace("\n"," ",str_replace(array('<p>','<br>','<br />','<br/>'),' ',$parsed));
     $parsed = trim(strip_tags($parsed));
     if (mb_strlen($parsed)<=$length) return $parsed; // тривиальный случай, возвращаем строку как есть, без многоточия в конце
     $parsed = mb_substr($parsed,0,$length);
     $punctuation = array(',','.','!',':',';','?','|','-','—','…');
     $rpos = -1;
     foreach ($punctuation as $item) {
       $pos = mb_strrpos($parsed,$item);
       if ($pos!==false && $pos>$rpos) $rpos = $pos; // если нашли более правый знак препинания, запоминаем его позицию
     }
     if ($rpos>=$min_length) $parsed = mb_substr($parsed,0,$rpos); // если нашли подходящий знак препинания и длина строки достаточна, обрезаем по нему
     else { // если при обрезке по знакам препинания строка получается короче желаемой длины, обрезаем по пробелу
        $space_pos = mb_strrpos($parsed,' ');
        if ($space_pos!==false && $space_pos>=$min_length) $parsed = mb_substr($parsed,0,$min_length); // если пробелов нет, или строка опять слишком короткая, делаем просто указанную длину
        else $parsed = trim(mb_substr($parsed,0,$min_length)); // иначе режем по самому правому пробелу и потом удаляем лишние
     }
     return $parsed.'…'; // добавляем многоточие в конец
  }
}