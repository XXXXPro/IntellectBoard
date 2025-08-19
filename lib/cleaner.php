<?php

/** ================================
 *  @package IntbPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.06
 *  @url https://intbpro.ru
 *  MindLife FrameWork HTML Cleaner helper class adapted for Intellect Board project
 * 
 *  Removes unallowed HTML tags or tag attributes
 *  ================================ **/

class Library_cleaner extends Library {
  const TAGS_MINIMUM = [
    'a'=>['href','target'],
    'img'=>['alt','src','height','width']
  ];

  const TAGS_MEDIA = [
    'video'=>['src','width','height','loop','muted','controls'],
    'source'=>['src','type'],
    'audio'=>['src','controls','loop']
  ];

  const TAGS_INLINE = [
    'a'=>['href','target','rel'],
    'img'=>['alt','src','height','width'],
    'br'=>[],
    'b'=>[],
    'i'=>[],
    'u'=>[],
    's'=>[],
    'strong'=>[],
    'em'=>[],
    'del'=>[],
    'ins'=>[],
    'kbd'=>[],
    'hr'=>[]
  ];

  const TAGS_FORMAT = [
    'p'=>[],
    'table'=>[],
    'tr'=>[],
    'td'=>['colspan','rowspan'],
    'thead'=>[],
    'tbody'=>[],
    'tfooter'=>[],
    'th'=>['colspan'],
    'ol'=>[],
    'ul'=>[],
    'li'=>[],
    'pre'=>[],
    'code'=>[]
  ];

  const TAGS_HEADERS = [
    'h2'=>[],
    'h3'=>[]
  ];

  const TAGS_ALL = self::TAGS_MINIMUM+self::TAGS_MEDIA+self::TAGS_INLINE+self::TAGS_FORMAT+self::TAGS_HEADERS;

  /** Cleans unallowed HTML tags or attributes
   * @param string $html HTML code to cleanup
   * @param array $tags Hash array of allowed tags and attributes. The keys of 
   * 
   */
  public static function clean(string $html,array $tags=self::TAGS_INLINE,array $schemas=['http','https','ftp','magnet','gemini','gopher']):string {
    $charset = 'UTF-8';
    if (empty($html)) return ''; // чтобы избежать ошибок loadHTML, которая не принимает пустые строки
    $html = \strip_tags($html,'<'.\join('><',\array_keys($tags)).'>'); // at first clean tags except allowed
    $html = \mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, ~0], $charset);
    if (!\class_exists('\\DOMDocument')) trigger_error('DOM extension not loaded!',E_USER_ERROR);
    $dom = new \DOMDocument();
    $dom->formatOutput = false;
    $dom->loadHTML($html, LIBXML_NONET|LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD); // LIBXML_NONET — for protection against XXE, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD — to don't add DOCTYPE and html/body tags
    $xpath = new \DOMXPath($dom);      
    $nodes = $xpath->query('//@*'); // finding all tags with attributes
    foreach ($nodes as $node) { 
      if (isset($tags[$node->parentNode->nodeName])) { // if tag in in list, checking attribute
        $attrs = is_array($tags[$node->parentNode->nodeName]) ? $tags[$node->parentNode->nodeName] : [$tags[$node->parentNode->nodeName]]; // if string specified as value, convert it to array
        if (!in_array($node->nodeName,$attrs)) $node->parentNode->removeAttribute($node->nodeName); // if attribute is not in allowed list, remove it
      }
    }
    $links = $xpath->query('//@href|//@src');
    foreach ($links as $link) {
      $scheme = parse_url($link->textContent,PHP_URL_SCHEME);
      if ($scheme && !in_array(strtolower($scheme),$schemas)) {  // если протокол не в спсике разрешённых, блокируем ссылку       
        $link->parentNode->textContent = 'INSECURE LINK REMOVED: '.htmlspecialchars($link->textContent,ENT_QUOTES|ENT_SUBSTITUTE,$charset).'! '; // show, why link was removed (for admins, moderators and so on)
        $link->nodeValue='#'; // removing dangerous link address
      }
    }
    return $dom->saveHTML();
  }
}