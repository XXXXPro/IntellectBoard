<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2010, 2012-2013 4X_Pro, INTBPRO.RU
 *  @url http://www.intbpro.ru
 *  Модуль работы с шаблонизатором Twig
 *  ================================ */

 class Library_twig extends Library implements iParser {
    private $dir = 'def' ; /** Каталог с шаблоном **/
    private $template = 'main.tpl'; /** Имя файла для вывода **/

    /** Установка стиля, файлы которого будут использоваться для вывода **/
    function set_style($stylename) {
       $this->dir=$stylename;
    }

    /** Задает файл шаблона для вывода текущей страницы **/
    function set_template($tmpl) {
      $this->template = $tmpl;
    }

    /** Формирует путь к файлу в текущем стилевом каталоге. **/
    function style_path($filename) {
      return Library::$app->url('s/'.$this->dir.'/'.$filename);
    }

    function get_cache_dir() {
      $cachedir = Library::$app->get_opt('cache_template_dir');
      if (!$cachedir) $cachedir = BASEDIR.'tmp';
       return $cachedir;
    }

    /* Преобразует данные EXIF вида A/B в дробные значения */
    function exif_format($text) {
      if (preg_match('|^\d+/\d+$|',$text)) {
        $data = explode('/',$text,2);
        return rtrim(rtrim(sprintf('%.4f',$data[0]/$data[1]),'0'),',');
      }
      else return $text;
    }
    

    /** Вызов шаблонизатора и обработка данных **/
    function generate_html($data,$mail=false,$minify=false) {
      if (!class_exists('Twig_Autoloader')) require_once BASEDIR.'opt/autoload.php';

      $mode = intval(Library::$app->get_opt('debug')) > 1;

      $result = '';

      try {
        $cachedir = $this->get_cache_dir();
        $loader = new Twig_Loader_Filesystem(array(BASEDIR.'template/'.$this->dir, BASEDIR.'template/def'));
        $twig = new Twig_Environment($loader, array('cache'=>$cachedir,'auto_reload'=>$mode,'autoescape'=>'html','debug' => $mode));
        // $twig->addExtension(new Twig_Extension_Optimizer());
        if ($mode) $twig->addExtension(new Twig_Extension_Debug());

        $f_getopt = new Twig_SimpleFunction('get_opt', array(Library::$app,'get_opt')); // добавляем возможность получать настройки IntB из шаблона
        $twig->addFunction($f_getopt);
        $f_url = new Twig_SimpleFunction('url', array(Library::$app,'url')); // функция для формирования URL
        $twig->addFunction($f_url);
        $f_http = new Twig_SimpleFunction('http', array(Library::$app,'http')); // функция для формирования URL
        $twig->addFunction($f_http);
        $f_style = new Twig_SimpleFunction('style', array($this,'style_path')); // функция для формирования путей к стилевым файлам
        $twig->addFunction($f_style);
        $f_guest = new Twig_SimpleFunction('is_guest', array(Library::$app,'is_guest')); // функция для проверки, является ли пользователь гостем
        $twig->addFunction($f_guest);
        $f_incline = new Twig_SimpleFilter('incline', array(Library::$app,'incline')); // функция для склонения числительных
        $twig->addFilter($f_incline);
        $f_sprintf = new Twig_SimpleFunction('sprintf', 'sprintf'); // sprintf часто используется для вывода данных в шаблоне
        $twig->addFunction($f_sprintf);
        $longdate = new Twig_SimpleFilter('longdate', array(Library::$app,'long_date'));
        $twig->addFilter($longdate);
        $shortdate = new Twig_SimpleFilter('shortdate', array(Library::$app,'short_date'));
        $twig->addFilter($shortdate);
        $exif = new Twig_SimpleFilter('exif', array($this,'exif_format'));
        $twig->addFilter($exif);


        $result = $twig->render($this->template,  (array)$data);
        if ($minify) $result=$this->minify_html($result,false);
      }
      catch (Exception $e) {
        $result = 'Ошибка шаблонизатора: '.$e->getMessage();
        if (!$mail && Library::$app->get_opt('debug')>=4) $result.='<h1>Содержимое буфера выводимых данных</h1><pre>'.print_r($data,true).'</pre><br />##DEBUG#';
        _dbg($result);
      }

      return $result;
    }

    /** Минификация HTML-кода
     * **/
    function minify_html($text,$remove_comments = true) {
      $key=hash('sha256',mt_rand()).'-';
      // processing pre tag (saving its contents)
      $pre_count=preg_match_all('|(<pre[^>]*>.*?</pre>)|is',$text,$pre_matches);
      for ($i=0; $i<$pre_count; $i++) $text=str_replace($pre_matches[0][$i],'<PRE|'.$i.'|'.$key.'>',$text);
      // processing code tag
      $code_count=preg_match_all('|(<code[^>]*>.*?</code>)|is',$text,$code_matches);
      for ($i=0; $i<$code_count; $i++) $text=str_replace($code_matches[0][$i],'<CODE|'.$i.'|'.$key.'>',$text);
      // processing script tag
      $script_count=preg_match_all('|(<script[^>]*>.*?</script>)|is',$text,$script_matches);
      for ($i=0; $i<$script_count; $i++) $text=str_replace($script_matches[0][$i],'<SCRIPT|'.$i.'|'.$key.'>',$text);
      // processing textarea tag
      $textarea_count=preg_match_all('|(<textarea[^>]*>.*?</textarea>)|is',$text,$textarea_matches);
      for ($i=0; $i<$textarea_count; $i++) $text=str_replace($textarea_matches[0][$i],'<TEXTAREA|'.$i.'|'.$key.'>',$text);
      
      // processing comments if they not to be removed
      if (!$remove_comments) {
        $comment_count=preg_match_all('|(<!--.*?-->)|s',$text,$comment_matches);
        for ($i=0; $i<$comment_count; $i++) $text=str_replace($comment_matches[0][$i],'<COMMENT|'.$i.'|'.$key.'>',$text);
      }
      // removing comments if need
      if ($remove_comments) {
        $text = preg_replace('|(<!--.*?-->)|s','',$text);
      }
      // replacing html entities
      $text = preg_replace('|&nbsp;|',' ',$text); // replacing with non-breaking space (symbol 160 in Unicode)
      $text = preg_replace('|&mdash;|','—',$text);
      $text = preg_replace('|&ndash;|','–',$text);
      $text = preg_replace('|&laquo;|','«',$text);
      $text = preg_replace('|&raquo;|','»',$text);
      $text = preg_replace('|&bdquo;|','„',$text);
      $text = preg_replace('|&ldquo;|','“',$text);
      $text = preg_replace('|(</?\w+[^>]+?)\s+(/?>)|s','$1$2',$text); // removing all contunous spaces
      while (preg_match('|<(/?\w+[^>]+/?)>\s\s+<(/?\w+?)|s',$text)) {
        $text = preg_replace('|<(/?\w+[^>]+/?)>\s\s+<(/?\w+?)|s','<$1> <$2',$text); // removing all spaces and newlines between tags
      }
      $text = preg_replace('|\s\s+|s',' ',$text); // removing all contunous spaces
      // restoring processed comments
      if (!$remove_comments) {
        for ($i=0; $i<$comment_count; $i++) $text=str_replace('<COMMENT|'.$i.'|'.$key.'>',$comment_matches[0][$i],$text);
      }
      // restoring textarea tag
      for ($i=0; $i<$textarea_count; $i++) $text=str_replace('<TEXTAREA|'.$i.'|'.$key.'>',$textarea_matches[0][$i],$text);      
      // restoring script tag
      for ($i=0; $i<$script_count; $i++) $text=str_replace('<SCRIPT|'.$i.'|'.$key.'>',$script_matches[0][$i],$text);
      // restoring code tag
      for ($i=0; $i<$code_count; $i++) $text=str_replace('<CODE|'.$i.'|'.$key.'>',$code_matches[0][$i],$text);
      // restoring pre tag
      for ($i=0; $i<$pre_count; $i++) $text=str_replace('<PRE|'.$i.'|'.$key.'>',$pre_matches[0][$i],$text);
      return $text;
    }

    function clear_cache() {
      $cachedir = $this->get_cache_dir();
      $files = glob($cachedir.'/??/??/*.php');
      for ($i=0, $count=count($files); $i<$count; $i++) unlink($files[$i]);
      $files = glob($cachedir . '/??/*.php');
      for ($i = 0, $count = count($files); $i < $count; $i++) unlink($files[$i]);
    }
 }
