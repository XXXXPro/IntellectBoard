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
    'img'=>['alt','src','height','width'],
    'details'=>['class'],
    'summary'=>[]
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
    'hr'=>[],
    'span'=>[]
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
    'code'=>[],
    'blockquote'=>[]
  ];

  const TAGS_HEADERS = [
    'h2'=>[],
    'h3'=>[],
    'h4'=>[],
    'h5'=>[],
    'h6'=>[]
  ];

  const TAGS_ALL = self::TAGS_MINIMUM+self::TAGS_MEDIA+self::TAGS_INLINE+self::TAGS_FORMAT+self::TAGS_HEADERS;

  /** Cleans unallowed HTML tags or attributes
   * @param string $html HTML code to sanitize
   * @param array $tags Hash array of allowed tags and attributes. The keys are tag names and values are arrays with attributes allowed for the tag. Attributes class and style are always allowed
   * @param 
   * @return string Sanitized HTML code
   */
  public static function clean(string $html,array $tags=self::TAGS_INLINE,
                               array $schemas=['http','https','ftp','magnet','gemini','gopher','tel','mailto'],
                               array $css_properties=['color', 'background-color', 'background','font','font-size','font-family']):string {
    $charset = 'UTF-8';
    if (empty($html)) return ''; // чтобы избежать ошибок loadHTML, которая не принимает пустые строки
    $html = \strip_tags($html,'<'.\join('><',\array_keys($tags)).'>'); // at first clean tags except allowed
    if (!\class_exists('\\DOMDocument') && !\class_exists('\\Dom\\HTMLDocument')) trigger_error('DOM extension not loaded!',E_USER_ERROR);
    if (version_compare(PHP_VERSION,'8.4','>=')) { // in newer PHP DOMDocument replaced to Dom\HTMLDocument
      $dom = Dom\HTMLDocument::createFromString($html,LIBXML_HTML_NOIMPLIED);
      $xpath = new Dom\XPath($dom);
    }
    else {
      $html = \mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, ~0], $charset);      
      $dom = new \DOMDocument('1.0',$charset);
      $dom->formatOutput = false;
      $dom->loadHTML($html, LIBXML_NONET|LIBXML_HTML_NODEFDTD); // LIBXML_NONET — for protection against XXE, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD — to don't add DOCTYPE and html/body tags
      $xpath = new \DOMXPath($dom);            
    }

    $nodes = $xpath->query('//@*'); // finding all tags with attributes
    foreach ($nodes as $node) { 
      $attr_name = $node->nodeName;
      $tag_name = strtolower($node->parentNode->nodeName);

      if (isset($tags[$tag_name])) { // if tag in in list, checking attribute
        $attrs = is_array($tags[$tag_name]) ? $tags[$tag_name] : [$tags[$tag_name]]; // if string specified as value, convert it to array
        $attrs = $attrs + array('class','style'); // class and style are allowed to all tags
        if (!in_array($attr_name,$attrs)) $node->parentNode->removeAttribute($attr_name); // if attribute is not in allowed list, remove it
        elseif ($attr_name==='style') {
          $style_value = $node->parentNode->getAttribute($attr_name);
          $style_value = self::sanitize_style_attribute($style_value,$css_properties);
          if ($style_value!='') $node->parentNode->setAttribute($attr_name,$style_value); // if all CSS removed, removing style attribute itself to minimize size of HTML code
          else $node->parentNode->removeAttribute($attr_name); 
        }
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
    $result = $dom->saveHTML();
    if (version_compare(PHP_VERSION,'8.4','<')) {
    	$result = substr($result,12,-15);
    	$result = \mb_decode_numericentity($result,[0x80, 0x10FFFF, 0, ~0], $charset);
    }
    return $result;
  }

/**
 * Sanitize a CSS style attribute by allowing only specific properties.
 *
 * @param string $style The raw style attribute string.
 * @param array $allowed_properties List of allowed CSS properties
 * @return string The sanitized style attribute string.
 */
public static function sanitize_style_attribute(string $style,array $allowed_properties=[]): string
{
    // Define a regular expression pattern to find key-value pairs.
    $pattern = '/([a-zA-Z-]+)\s*:\s*([^;]+);?/i';

    $style = preg_replace('|/\*.*?\*/|','',$style); // removing CSScomments to avoid some attacks 

    $sanitized_styles = [];
    if (preg_match_all($pattern, $style, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $property = strtolower(trim($match[1]));
            $value = trim($match[2]);

            // Check if the property is in the allowed list.
            if (in_array($property, $allowed_properties, true)) {
                // Ensure the value is not a malicious URL or script.
                // This is a basic check. For more robust security, use a dedicated library.
                if (strpos($value, 'url(') === false && strpos($value, 'expression(') === false) {
                    $sanitized_styles[] = "{$property}: {$value}";
                }
            }
        }
    }
    return implode('; ', $sanitized_styles);
  }  
}