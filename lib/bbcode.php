<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Модуль для обработки сообщений перед сохранением/выводом
 *  Содержит работу со смайликами, BoardCode и т.п.
 *  ================================ */

class Library_bbcode extends Library {
  private static $search; // здесь будут рег. выражения для поиска, в виде static они сделаны для того, чтобы заполнять массив только при первом вызове функции, а при последующих -- использовать готовый
  private static $replace; // здесь будут рег. выражения для замены
  private static $ext_search; // здесь будут рег. выражения для поиска расширенных тегов
  private static $ext_replace; // здесь будут рег. выражения для замены расширенных тегов
  private static $smiles; // кеш смайликов
  private static $link_search; // здесь будут рег. выражения для поиска невыделенных ссылок
  private static $link_replace;

  /** Обработчик сообщения перед его выводом. Выполняет экранирование HTML,
  * обработку BoardCode, ссылок и смайликов (именно в такой последовательности)
  * @param $params array Массив с данными, какую именно обработку сообщения проводить.
  * @param $text string Текст для парсинга. Может быть равен false, в этом случае текст будет взят из $params['text']
  * Может содержать ключи:
  * text
  * html -- в сообщении разререшно использование HTML
  * bcode -- в сообщении разрешено использование BoardCode
  * smiles -- в сообщении разрешены смайлики
  * links -- проводить автоматическую обработку гиперссылок
  * attach -- информация о прикрепленных файлах, необходимая для обработки тега attach
  **/
  function parse_msg(&$params,$text=false) {
    if (!$text) $text = $params['text'];
    $randstr = hash('sha256',Library::$app->time.rand()); // случайная строка
    if (!empty($params['bcode'])) { // если включены теги, сначала обрабатываем тег [php], так как highlight_string не может обрабатывать код, прошедший через htmlspecialchars
       $php_count = preg_match_all('|\[php\](.*?)\[/php\]|s',$text,$php_match);
      for ($i=0; $i<$php_count; $i++) $text=str_replace($php_match[0][$i],'++php+'.$randstr.'+'.$i.'++',$text); // заменяем все [code] на спецпоследовательности
    }

    if (empty($params['html'])) $text = htmlspecialchars($text); // если использование HTML запрещено, экранируем все символы
    if (!empty($params['bcode'])) $text=str_ireplace(array('[code]','[/code]'),array('<code>','</code>'),$text); // если включено использование тегов bcode, производим предварительное преобразование тега code в HTML
    // выявляем те последовательности, которые не должны изменяться в процессе обработки (теги <code> и [nocode])
    if (!empty($params['html']) || !empty($params['bcode'])) {
      $code_count = preg_match_all('|<code>(.*?)</code>|s',$text,$code_match);
      for ($i=0; $i<$code_count; $i++) $text=str_replace($code_match[0][$i],'++code+'.$randstr.'+'.$i.'++',$text); // заменяем все [code] на спецпоследовательности
      if (!empty($params['bcode'])) {
        $nocode_count = preg_match_all('|\[nocode\](.*?)\[/nocode\]|s',$text,$nocode_match);
        for ($i=0; $i<$nocode_count; $i++) $text=str_replace($nocode_match[0][$i],'++nocode+'.$randstr.'+'.$i.'++',$text); // заменяем все [code] на спецпоследовательности
        if (!empty($params['html']) && !empty($params['bcode'])) { // использовать тег [nohtml] имеет смысл только тогда, когда разрешен и HTML, и BoardCode
          $nohtml_cb = function($matches) { return htmlspecialchars($matches[1]); };  // обработчик для тега [php]: вызывает стандартную функцию hightlight_string и обрамляет ее в тег <code>
          $text = preg_replace_callback('|\[nohtml\](.*?)\[/nohtml\]|i',$nohtml_cb,$text);
        }

        // все подготовительные операции завершены, можно произвести обработку тегов. Перед обработкой проверим, есть ли там вообще символ [, с которого начинаются все теги. Если нет, то можно пропустить все проверки и сэкономить время
        if (strpos($text,'[')!==false) {
          $text = $this->process_bcode($text,true,true); // в параметрах передается, какие теги BBCode должны сработать
          $text = $this->process_bcode_ext($text);
        }
      }
    }
    if (!empty($params['links'])) $text = $this->process_links($text);
    $text=nl2br($text); // TODO: предусмотреть ситацию, когда перевод строк можно отключить
    if (!empty($params['smiles'])) $text = $this->process_smiles($text);
    // if (!empty($params['typograf']))
    $text = $this->process_typograf($text);
    $text = $this->check_bad_links($text);
    $links_mode = empty($params['links_mode']) ? 'allow' : $params['links_mode']; // по умолчанию ссылки разрешены
    $text = $this->strip_links($text,$links_mode); // удаление ссылок в случае необходимости

    if (!empty($params['attach'])) {
      $preview_x = Library::$app->get_opt('posts_preview_x') or 240;
      $preview_y = Library::$app->get_opt('posts_preview_y') or 180;
      for ($i=0, $count=count($params['attach']); $i<$count; $i++) {
          $attach_link = '<a href="'.Library::$app->url("f/up/1/".$params['attach'][$i]['oid'].'-'.$params['attach'][$i]['fkey'].'/'.htmlspecialchars($params['attach'][$i]['filename'])).'" class="attach">';
          if ($params['attach'][$i]['format']==="image") {
            $attach_full = $attach_link.'<img src="'.
            Library::$app->url('f/up/1/pr/'.$preview_x.'x'.$preview_y.'/'.$params['attach'][$i]['oid'].'-'.$params['attach'][$i]['fkey'].'.'.$params['attach'][$i]['extension']).'" alt="{{ attach.filename }}" /></a>';
          }
          else { 
            $attach_full = $attach_link.htmlspecialchars($params['attach'][$i]['filename']).' ('.ceil($params['attach'][$i]['size']/1024.0).' Кб)</a>';
          }
          $old_text=$text;
          $text=str_replace('[attach='.($i+1).']',$attach_full,$text);
          $text=preg_replace('|\[attachlink='.($i+1).'\](.*?)\[/attachlink\]|s',$attach_link."$1</a>",$text);
          if ($old_text!==$text) $params['attach'][$i]['processed']=true; // ставим признак, что файл обработан, чтобы не выводить его еще раз в списке прикрепленных файлов снизу
      }
    }

    if (!empty($params['bcode'])) {
      // обработка blocklink
      preg_match_all('|\[blocklink=(https?://[^>"\'\]\s]+)\]|i', $text, $matches);
      if (!empty($params['blocklinks'])) $links = json_decode($params['blocklinks'], true);
      else $links = array();  

      foreach ($matches[1] as $oldurl) {
        if (!empty($links[$oldurl])) $linkdata= $links[$oldurl];
        else $linkdata=array();
        if (!empty($linkdata['url'])) $url=$linkdata['url'];
        else $url = $oldurl;
        $domain = parse_url($url,PHP_URL_HOST);
        if (!empty($linkdata['title'])) {
          $linkblock = '<a class="blocklink" href="'.htmlspecialchars($url).'"><b>'.htmlspecialchars($linkdata['title']).'</b>';
          $linkblock.= '<span class="linkdesc">';
          if (!empty($linkdata['image'])) $linkblock.= '<img src="'.$linkdata['image'].'" alt="'.htmlspecialchars($linkdata['title']).'" class="linkimg" />';
          if (!empty($linkdata['desc'])) $linkblock .= htmlspecialchars($linkdata['desc']);
          $linkblock.='</span>';
          $linkblock.= '<span class="linkdomain">'.htmlspecialchars($domain).'</span>';
          $linkblock.= '</a>';
        }
        else {
          $linkblock = '<a class="blocklink" href="'.htmlspecialchars($url).'"><b>'.htmlspecialchars($url).'</b><span class="linkdomain">'. htmlspecialchars($domain).'</span></a>';
        }
        $text = str_ireplace('[blocklink='.$oldurl.']',$linkblock,$text);
      }
    }


    // обратная замена спецпоследовательностей (производится в обратном порядке)
    if (!empty($php_count)) for ($i=0; $i<$php_count; $i++) $text=str_replace('++php+'.$randstr.'+'.$i.'++',highlight_string($php_match[1][$i],true),$text); // заменяем все [code] на спецпоследовательности
    if (!empty($nocode_count)) for ($i=0; $i<$nocode_count; $i++) $text=str_replace('++nocode+'.$randstr.'+'.$i.'++',$nocode_match[1][$i],$text); // заменяем все [code] на спецпоследовательности
    if (!empty($code_count)) for ($i=0; $i<$code_count; $i++) $text=str_replace('++code+'.$randstr.'+'.$i.'++',$code_match[0][$i],$text); // заменяем все [code] на спецпоследовательности
    return $text;
   }

   /** Упрощенная обработка BBCode для подписей: вызываются только preocess_bcode и process_smiles **/
   function parse_sig($text,$mode='none') {
     $text = htmlspecialchars($text);
    $text = $this->process_bcode($text,true,true); // в параметрах передается, какие теги BBCode должны сработать
    $text = $this->process_smiles($text);
    // TODO: проверка на наличие прав размещать ссылки вообще
    $text = $this->check_bad_links($text);
    $text = $this->strip_links($text,$mode); // удаление ссылок в случае необходимости
    $text = nl2br($text);
    return $text;
   }

   /** Собственно обработка кодов (кроме требующих экранирование, т.е. code, nocode, nohtml, php, они обрабатываются в parse_msg)
   * @param $text string -- текст сообщения, который требуется обработать
   * @param $params array -- хеш-массив, указывающий, какие группы тегов обрабатывать. Возможные варианты:
   * img -- файлы картинок
   * media -- файлы аудио и видео
   * blocks -- теги блочного вывода
   * special -- блоки особого оформления (списки, оффтопик, таблицы и т.п.)
   * Обработка базовых тегов производится в любом случае
   **/
  function process_bcode($text,$link,$img) {
    if (preg_match_all('|\[font=([^]]*)&quot;([^]]*)\](.*?)\[/font\]|s',$text,$matches)) {
      foreach ($matches as $match) {
        $new = str_replace('&quot;','"',$match[0]);
        $text= str_replace($match,$new,$text);
      }
    }
    if (empty(self::$search) || empty(self::$replace)) {
      self::$search[]='|\[b\](.*?)\[/b\]|s'; self::$replace[]='<b>$1</b>';
      self::$search[]='|\[i\](.*?)\[/i\]|s'; self::$replace[]='<i>$1</i>';
      self::$search[]='|\[u\](.*?)\[/u\]|s'; self::$replace[]='<u>$1</u>';
      self::$search[]='|\[s\](.*?)\[/s\]|s'; self::$replace[]='<s>$1</s>';
      self::$search[]='|\[sup\](.*?)\[/sup\]|s'; self::$replace[]='<sup>$1</sup>';
      self::$search[]='|\[sub\](.*?)\[/sub\]|s'; self::$replace[]='<sub>$1</sub>';
      self::$search[]='|\s*\[h2\](.*?)\[/h2\]\s*|s'; self::$replace[]='<h2>$1</h2>';
      self::$search[]='|\s*\[h3\](.*?)\[/h3\]\s*|s'; self::$replace[]='<h3>$1</h3>';
      self::$search[]='|\s*\[h4\](.*?)\[/h4\]\s*|s'; self::$replace[]='<h4>$1</h4>';
      self::$search[]='!\[color=([a-zA-Z\-]+|#[0-9a-fA-F]{3,6})\](.*?)\[/color\]!s'; self::$replace[]='<span style="color: $1">$2</span>';
      // TODO: тег grad доделаем потом
      // self::$search[]=; self::$replace[]='[font=$1"$2]$3[/font]';
      self::$search[]='|\[font=([a-zA-Z\\- ,\"]+)\](.*?)\[/font\]|s'; self::$replace[]='<span style=\'font-family: $1\'>$2</span>';
      self::$search[]='|\[size=([1234567])\](.*?)\[/size\]|s'; self::$replace[]='<span class="size$1">$2</span>';
      self::$search[]='!\[shadow=(\d+),?([a-z\-]+|#[0-9a-f]{3,6})\](.*?)\[/shadow\]!s'; self::$replace[]='<span style="text-shadow: $1px $1px $1px $2">$3</span>';

      self::$search[]='|\[email\]([a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\.\-]+)\[/email\]|'; self::$replace[]='<a href="mailto:$1">$1</a>';
      self::$search[]='|\[email=([a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\.\-]+)\](.*?)[/email]|'; self::$replace[]='<a href="mailto:$1">$2</a>';
      self::$search[]='|\[email=([a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\.\-]+)\]|'; self::$replace[]='<a href="mailto:$1">$1</a>';

      // теги таблиц
      self::$search[]='|\[table\](.*?)\[/table\]|s'; self::$replace[]='<table class="ib_outer_table"><tr><td><table class="ib_user_table">$1</table></td></tr></table>';
      self::$search[]='!\s*\[td(\s+(col|row)span=&quot;\d+&quot;)?\](.*?)\[/td\]\s*!s'; self::$replace[]='<td$1>$3</td>';
      self::$search[]='|\s*\[tr\](.*?)\[/tr\]\s*|s'; self::$replace[]='<tr>$1</tr>';

      if ($link) {
        self::$search[]='|\[url=([\d\w\./\?][^\]"\']+)\](.*?)\[/url\]|s'; self::$replace[]='<a href="$1">$2</a>'; // первый символ URL всегда либо буква, либо косая черта, либо точка. Все остальное -- подозрительно и не должно вызывать срабатывания regexp
        self::$search[]='|\[url\]([\d\w\./\?][^\]"\']+)\[/url\]|s'; self::$replace[]='<a href="$1">$1</a>';
        self::$search[]='|\[reply-to=(https?://[\d\w\./\?][^\]"\']+)\](.*?)\[/reply-to\]|s'; self::$replace[]='<a class="u-in-reply-to" href="$1">$2</a>'; // первый символ URL всегда либо буква, либо косая черта, либо точка. Все остальное -- подозрительно и не должно вызывать срабатывания regexp
      }

      if ($img) {
        self::$search[]='|\[img=(\d+)x(\d+)\]([\d\w\./\?][^\]"\']+)\[/img\]|'; self::$replace[]='<img src="$3" alt="" width="$1" height="$2" class="lightbox" />';
        self::$search[]='|\[img\]([\d\w\./\?][^\]"\']+)\[/img\]|'; self::$replace[]='<img src="$1" alt="" class="lightbox" />';
        self::$search[]='|\[img=([\d\w\./\?][^\]"\']+)\]|'; self::$replace[]='<img src="$1" alt="" class="lightbox" />';
      }
    }
    $text = preg_replace(self::$search,self::$replace,$text); // и все замены делаем одним regexpом
    return $text;
  }

  function process_bcode_ext($text) {
    if (empty(self::$ext_search) || empty(self::$ext_replace)) {
      self::$ext_search[]='|\[audio\](https?://[\d\w\.:/\?][^\]"\']+)\[/audio\]|'; self::$ext_replace[]='<audio src="$1" controls>Ваш броузер не поддерживает воспроизведение аудио. Попробуйте <a href="$1">скачать файл</a>.</audio>';
      self::$ext_search[]='|\[audio=(https?://[\d\w\.:/\?][^\]"\']+)\]|'; self::$ext_replace[]='<audio src="$1" controls>Ваш броузер не поддерживает воспроизведение аудио. Попробуйте <a href="$1">скачать файл</a>.</audio>';
      self::$ext_search[]='|\[video\](https?://[\d\w\.:/\?][^\]"\']+)\[/video\]|'; self::$ext_replace[]='<video src="$1" controls>Ваш броузер не поддерживает воспроизведение видео. Попробуйте <a href="$1">скачать файл</a>.</video>';
      self::$ext_search[]='|\[video=(https?://[\d\w\.:/\?][^\]"\'])+\]|'; self::$ext_replace[]='<video src="$1" controls>Ваш броузер не поддерживает воспроизведение видео. Попробуйте <a href="$1">скачать файл</a>.</video>';      
      self::$ext_search[]='|\[youtube\](https?://youtu.be/)?([A-Za-z0-9_\-]+)\[/youtube\]|'; self::$ext_replace[]='<iframe width="560" height="315" src="https://www.youtube.com/embed/$2" frameborder="0" allowfullscreen></iframe>';
      self::$ext_search[]='|\[youtube=https?://youtu.be/([A-Za\-z0-9_\-]+)\]|'; self::$ext_replace[]='<iframe width="560" height="315" src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>';

      self::$ext_search[]='|\[left\](.*?)\[/left\]|s'; self::$ext_replace[]='<div style="text-align: left">$1</div>';
      self::$ext_search[]='|\[right\](.*?)\[/right\]|s'; self::$ext_replace[]='<div style="text-align: right">$1</div>';
      self::$ext_search[]='|\[center\](.*?)\[/center\]|s'; self::$ext_replace[]='<div style="text-align: center">$1</div>';
      self::$ext_search[]='|\[justify\](.*?)\[/justify\]|s'; self::$ext_replace[]='<div style="text-align: justify">$1</div>';
      self::$ext_search[]='|\[float=left\](.*?)\[/float\]|s'; self::$ext_replace[]='<div class="left">$1</div>';
      self::$ext_search[]='|\[float=right\](.*?)\[/float\]|s'; self::$ext_replace[]='<div class="right">$1</div>';

      self::$ext_search[]='|\[hr\]|'; self::$ext_replace[]='<hr />';
      self::$ext_search[]='|\[quote\](.*?)\[/quote\]|s'; self::$ext_replace[]='<div class="quote"><blockquote class="qfold">$1</blockquote></div>';
      self::$ext_search[]='|\[q\](.*?)\[/q\]|s'; self::$ext_replace[]='<div class="quote"><blockquote class="folded">$1</blockquote></div>';

      self::$ext_search[]='|\[quote=([^\]"\',]+),(\d+)\](.*?)\[/quote\]|s'; self::$ext_replace[]='<div class="quote"><span class="username">$1</span> <a href="post-$2.htm">написал(а)</a>: <blockquote>$3</blockquote></div>';
      self::$ext_search[]='|\[q=([^\]"\',]+),(\d+)\](.*?)\[/q\]|s'; self::$ext_replace[]='<div class="quote"><span class="username">$1</span> <a href="post-$2.htm">написал(а)</a>: <blockquote>$3</blockquote></div>';

      self::$ext_search[]='|\[quote=([^\]"\']+)\](.*?)\[/quote\]|s'; self::$ext_replace[]='<div class="quote"><span class="username">$1</span> написал(а): <blockquote>$2</blockquote></div>';
      self::$ext_search[]='|\[q=([^\]"\']+)\](.*?)\[/q\]|s'; self::$ext_replace[]='<div class="quote"><span class="username">$1</span> написал(а): <blockquote>$2</blockquote></div>';
        // TODO: доделать обработку тегов quote большой вложенности

      self::$ext_search[]='|\[off\](.*?)\[/off\]|s'; self::$ext_replace[]='<div class="offtopic">$1</div>';
      self::$ext_search[]='|\[pre\](.*?)\[/pre\]|s'; self::$ext_replace[]='<pre>$1</pre>';
      self::$ext_search[]='|\[cut\](.*?)\[/cut\]|s'; self::$ext_replace[]='<a class="cutlink" href="#">Показать скрытый текст &raquo;</a><span class="invis">$1</span>';
      self::$ext_search[]='|\[cut=([^\]]+)\](.*?)\[/cut\]|s'; self::$ext_replace[]='<a class="cutlink" href="#">$1</a><span class="invis">$2</span>';
      self::$ext_search[]='|\[spoiler\]|s'; self::$ext_replace[]='<details>';
      self::$ext_search[]='|\[/spoiler\]|s'; self::$ext_replace[]='</details>';
      self::$ext_search[]='|\[spoiler=([^\]]+)\]|s'; self::$ext_replace[]='<details><summary>$1</summary>';

      // математические теги
      self::$ext_search[] = '|\[math\](.*?)\[/math\]|s'; self::$ext_replace[] = '<span class="mathtex">\\($1\\)</span>';
      self::$ext_search[] = '|\[mathblock\](.*?)\[/mathblock\]|s'; self::$ext_replace[] = '<div class="mathtex">$$$1$$</div>';
      self::$ext_search[] = '|\[asciimath\](.*?)\[/asciimath\]|s';  self::$ext_replace[] = '<span class="asciimath">`$1`</span>';
    }
    $text = preg_replace(self::$ext_search,self::$ext_replace,$text); // и все замены делаем одним regexpом

      // TODO: обработчики тегов спецблоков, требующие дополнительных действий, будут здесь
    $text = preg_replace_callback('!\s*\[(list|ul|ol)=?([a-z\-]+)?\]\s*(\[\*\])?(.*?)\[/\\1\]\s*!s',array($this,'list_process'),$text); // списочные теги обрабатываем специальной функцией
    $text = preg_replace_callback('|\[level=(\d+)\](.*?)\[/level\]|',array($this,'level_check'),$text); // проверку уровня доступа тоже вынесем отдельно
    return $text;
  }

  /** Обработчик списковых тегов. Заменяется [*] на пару </li><li>, при этом для первого и последнего элементов открывающий и закрывающий теги добавляются принудительно **/
  function list_process($matches) {
    $old_style = strpos($matches[4],'[*]');
    if ($old_style!==false) {
      $matches[4]=str_replace(array('[*]',"\n</li>","\r</li>"),array('</li><li>','</li>','</li>'),$matches[4]); // заменяем [*] на теги и убираем одну пустую строку перед ними, если есть (чтобы не было двойного перевода строк вида <br /></li><li>)
      $matches[4]='<li>'.$matches[4].'</li>';
    }
    else {
      $matches[4]=str_replace('[li]','<li>',$matches[4]);
      $matches[4]=str_replace('[/li]','</li>',$matches[4]);
    }
    $matches[4]=str_replace("</li>\r\n<li>",'</li><li>',$matches[4]);
    if ($matches[2]) $matches[2] = ' style="list-style-type: '.$matches[2].'"';
    if ($matches[1]==='list') $matches[1]='ul';

    return '<'.$matches[1].$matches[2].'>'.trim($matches[4]).'</'.$matches[1].'>';
  }

  /** Проверка уровня доступа пользователя для просмотра скрытого текста **/
  function level_check($matches) {
    $level=intval($matches[1]);
    if ($level>=Library::$app->userdata['level']) return $matches[2];
    else return '<div class="nolevel">У вас недостаточный уровень для просмотра этого сообщения.</div>';
  }

  /** Обработка ссылок, вставленных без [url] или <a href> **/
  function process_links($text) {
    if (empty(self::$link_search) || empty(self::$link_replace)) {
      $domains = 'aero|biz|com|edu|gov|info|int|mobi|name|net|org|pro|tel|travel|online|guru|club|ru|su|moscow|eu|ua|com\\.ua|kz|kg|by|uz|ge|az|am|co\\.il'; // список доменов верхнего уровня, которые распознаем
      self::$link_search[]='/(^|\s)((www\.)?[\w.\-]+\.('.$domains.')(:[1-9][0-9]*)?([\/?][^\s"]*?)?)([,\.!?]?([\s"\']|$))/is'; self::$link_replace[]='$1<a href="http://$2">$2</a>$7';
      self::$link_search[]='/(^|\s)((https?:\/\/)?[\w.\-]+\.('.$domains.')(:[1-9][0-9]*)?([\/?][^\s"]*?)?)([,\.!?]?([\s"\']|$))/is'; self::$link_replace[]='$1<a href="$2">$2</a>$7';
      self::$link_search[]='/(^|\s)([а-яА-Я0-9\.\-]+\.(рф|РФ|москва|МОСКВА|бел|БЕЛ)(:[1-9][0-9]*)?([\/?][^\s"]*?)?)([,\.!?]?([\s"\']|$))/isu'; self::$link_replace[]='$1<a href="http://$2">$2</a>$6';
      self::$link_search[]='/(^|\s)(https?:\/\/[а-яА-Я0-9\.\-]+\.(рф|РФ|москва|МОСКВА|бел|БЕЛ)(:[1-9][0-9]*)?([\/?][^\s"]*?)?)([,\.!?]?([\s"\']|$))/isu'; self::$link_replace[]='$1<a href="$2">$2</a>$6';      
    }
    $text = preg_replace(self::$link_search,self::$link_replace,$text); // и все замены делаем одним regexpом
    return $text;
  }

  /** Типографирование текста **/
  function process_typograf($text) {
    $text = str_replace(
      array(' -- ','(c)','(r)','(tm)'),
      array(' &mdash; ','&copy;','&reg;','&trade;'),
      $text);
    // TODO: решить, нужно ли типографирование вообще, если да, скопировать типограф из TextCMS.
    return $text;
  }

  /** Обработка смайликов: замена их кодов на тег <img>. Файлы смайликов должны хранится в каталоге www/sm/ (относительно корня движка).
  **/
  function process_smiles($text) {
    $smiles = $this->load_smiles();
    $from = array();
    $to = array();
    for ($i=0, $count=count($smiles); $i<$count; $i++) {
      $from[]=$smiles[$i]['code']; // обработка символов < и >, так как в $text они приходят уже заэкранированными
      $to[]='<img class="smile" src="'.Library::$app->url('sm/'.$smiles[$i]['file']).'" alt="'.htmlspecialchars($smiles[$i]['descr']).'" />';
      if (strpos($smiles[$i]['code'],'>')!==false || strpos($smiles[$i]['code'],'<')!==false) { // если в коде смайлика есть символы < или >, нужна доп. обработка, так как они при выключенном HTML придут уже закодированными
        $from[]=str_replace('>','&gt;',str_replace('<','&lt;',$smiles[$i]['code'])); // обработка символов < и >, так как в $text они приходят уже заэкранированными
        $to[]='<img class="smile" src="'.Library::$app->url('sm/'.$smiles[$i]['file']).'" alt="'.htmlspecialchars($smiles[$i]['descr']).'" />';
      }
    }
    if ($count) $text = str_replace($from,$to,$text);
    return $text;
  }

  /** Загружает данные об имеющихся смайликах.  **/
  function load_smiles() {
    if (empty(self::$smiles)) {
      self::$smiles = Library::$app->get_cached('Smiles');
      if (self::$smiles===NULL) { // если в кеше смайликов нет или кеш не работает, получаем их из базы
        $sql = 'SELECT code,file,descr,mode FROM '.DB_prefix.'smile ORDER BY sortfield';
        self::$smiles = Library::$app->db->select_all($sql);
        Library::$app->set_cached('Smiles',self::$smiles);
      }
    }
    return self::$smiles;
  }

  /** Сортирует смайлики в хеш, пригодный для передачи в SCEditor **/
  function load_smiles_hash() {
    $smiles =  $this->load_smiles();
    $result = array('dropdown'=>array(),'more'=>array(),'hidden'=>array());
    for ($i=0,$count=count($smiles);$i<$count;$i++) {
       $smiles[$i]['code']=addslashes($smiles[$i]['code']);
       $result[$smiles[$i]['mode']][]=$smiles[$i];
    }
    return $result;
  }

  /** Удаление картинок и ссылок с небезопасными адресами (вида javascript: или vbscript: ) в целях защиты от XSS-атак
  **/
  function check_bad_links($text) {
    $text = preg_replace('|<a[^>]+href=["\']?(\w+script:.*?)["\']?[^>]+>(.*?)</a>|i','<span class="bad_link">Небезопасная ссылка удалена!</span>',$text);
    $text = preg_replace('|<img[^>]+src=["\']?(\w+script:.*?)["\']?[^>]+>|i','<span class="bad_link">Картинка с небезопасным адресом удалена!</span>',$text);
    $text = preg_replace('|<audio[^>]+src=["\']?(\w+script:.*?)["\']?[^>]+>(.*?)</audio>|i','<span class="bad_link">Небезопасный аудио-объект удален!</span>',$text);
    $text = preg_replace('|<video[^>]+src=["\']?(\w+script:.*?)["\']?[^>]+>(.*?)</video>|i','<span class="bad_link">Небезопасный видео-объект удален!</span>',$text);
    return $text;
  }

  function strip_links_callback($matches) {
    $host=parse_url($matches[2],PHP_URL_HOST);
    if (empty($host) || $host===$_SERVER['HTTP_HOST'] || $host==='www.'.$_SERVER['HTTP_HOST']) return $matches[0];
    else return '<!--noindex--><span class="bad_link">У данного пользователя нет прав размещать ссылки!</span><!--/noindex-->';
  }   

  function strip_links($text,$mode) {  
    if ($mode==='none') $text=preg_replace_callback('|<a([^>]*\W)href=[\'"]?(.*?)[\'"]([^>])*>(.*?)</a>|i',array($this,'strip_links_callback'),$text);
    if ($mode==='none') $text=preg_replace_callback('|<a([^>]*\W)href=(.*)(\s[^>]*)?>(.*?)</a>|i',array($this,'strip_links_callback'),$text);
    if ($mode==='nofollow') $text=preg_replace('|<a\s+([^>]+)>(.*?)</a>|i','<a rel="nofollow ugc" $1>$2</a>',$text);
    else $text=preg_replace('|<a\s+([^>]+)>(.*?)</a>|i','<a rel="ugc" $1>$2</a>',$text);
    return $text;
  }

  /** Подсчет количества смайликов в уже обработанном тексте. Нужно для проверки при отправке сообщения.
  **/
  function count_smiles($text) {
    return substr_count($text,'<img class="smile" src="');
  }

  /** Производит замену слов, которые администрация сайта считает недопустимыми, на их более цензурные версии **/
  function bad_words($text) {
    $badwords = explode("\n",Library::$app->get_text(0,5)); // список недопустимых слов хранится с типом №5 в таблице текстов
    $text=' '.$text.' ';
    $oldw = array(); $neww = array();
    for ($i=0, $count=count($badwords); $i<$count; $i++) {
      $curword = trim($badwords[$i]);
      if ($curword && strpos($curword,'=')!==false) {
        list($old,$new)=explode('=',$badwords[$i]);
        $old=str_replace('?','.',$old); //
        $old=str_replace('*','[^\s,.\-;:?!/"]',$old);
        $oldw[]='/([\s,.\-;:?!>])'.trim($old).'([\s,.\-;:?!<])/isu'; $neww[]='$1'.trim($new).'$2';
      }
    }
    if (count($oldw)>0) $text = preg_replace($oldw,$neww,$text);
    $text = substr($text,1,-1);
    return $text;
  }

  function task_blocklink($params) {
    $ch = curl_init();
    $result = array();
    $all_done = true;
    foreach ($params['links'] as $url) {
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // учитываем редиректы
      curl_setopt($ch, CURLOPT_TIMEOUT, 5); // не ждём больше 5 секунд
      curl_setopt($ch, CURLOPT_HTTPGET, true);
      curl_setopt($ch, CURLOPT_MAXFILESIZE,6*1024*1024); // получаем только первые 6 Mb (для защиты от DoS-атак)
      $html = curl_exec($ch);
      $errno = curl_errno($ch);
      $code = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);

      if ($errno==0 && $code==200) {
        $result[$url]=array();
        $dom = new DOMDocument();
        $dom->formatOutput = false;
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NONET); // LIBXML_NONET — для защиты от XXE
        libxml_use_internal_errors(false);
        $xpath = new DOMXPath($dom);
        // finding title. First from <meta property="og:title", then from title tag
        $og_title = $xpath->query('//meta[@property="og:title"]');
        if (!empty($og_title[0])) $result[$url]['title']= $og_title[0]->getAttribute('content');
        if (empty($result[$url]['title'])) {
          $og_title = $xpath->query('//title');
          if (!empty($og_title[0]))  $result[$url]['title'] = $og_title[0]->textContent;
        }
        // finding description. First from <meta property="og:description", then from title tag
        $og_desc = $xpath->query('//meta[@property="og:desciption"]');
        if (!empty($og_desc[0])) $result[$url]['desc'] = $og_desc[0]->getAttribute('content');
        if (empty($result[$url]['desc'])) {
          $og_desc = $xpath->query('//meta[@name="description"]');
          if (!empty($og_desc[0]))  $result[$url]['desc'] = $og_desc[0]->getAttribute('content');          
        }
        // finding image
        $og_img = $xpath->query('//meta[@property="og:image"]'); 
        if (!empty($og_img[0])) $result[$url]['image'] = $og_img[0]->getAttribute('content');
        if (empty($result[$url]['image'])) {
          $og_img = $xpath->query('//link[@rel="image_src"]'); 
          if (!empty($og_img[0])) $result[$url]['image'] = $og_img[0]->getAttribute('href');
        }
        // finding url
        $og_url = $xpath->query('//meta[@property="og:url"]');
        if (!empty($og_url[0])) $result[$url]['url'] = $og_url[0]->getAttribute('content');
        if (empty($result[$url]['url'])) {
          $og_url = $xpath->query('//link[@rel="canonical"]');
          if (!empty($og_url[0]))  $result[$url]['url'] = $og_url[0]->getAttribute('href');
        }
        // finding type
        $og_type = $xpath->query('//meta[@property="og:type"]');
        if (!empty($og_url[0])) $result[$url]['type'] = $og_type[0]->getAttribute('content');
      }
      else $all_done = false;
    }
    if (!empty($result)) {
      /** @var Library_misc */
      $misc_lib = Library::$app->load_lib('misc',false);
      if ($misc_lib) {
        $all_done = $misc_lib->save_text(json_encode($result),$params['post_id'],19); // 19 -- код данных для ссылок blocklink
      }
      else $all_done = false;
    }
    return $all_done ? 0 : -1;  // возвращаем 0 в качестве индикатора, что задача выполнена или -1, если что-то прошло не так
  }
}

