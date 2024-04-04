<?php

class Library_indieweb extends Library {
  public static $ch;

    /** Отправляет уведомление по протоколу WebMention  */
    function task_webmention ($params) {
      // если source или target не указаны, завершаем задачу
      if (empty($params['source']) || empty($params['target'])) return 0;
      // если хост отправителя и получателя совпадают, то нет смысла делать упоминание, чтобы не поднимать тему лишний раз
      if (parse_url($params['source'],PHP_URL_HOST)==parse_url($params['target'],PHP_URL_HOST)) return 0;
      $endpoint = $this->get_endpoint($params['target'],'webmention');      
      if (!empty($endpoint)) {
        $ch = Library_indieweb::$ch;
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_URL,$endpoint);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($params));
        curl_exec($ch);
        $errno = curl_errno($ch);
        $status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        if ($errno==0 && in_array($status,array(200,201,202,400,401,402,403,404))) return 0;
        else return -1;
      }
      return 0;
    }

    /** Делает запрос на заданный URL, чтобы извлечь с него адрес точки вызова (endpoint) с укзаанным типом rel
     *  Поиск ведётся сначала в HTTP-заголовках типа Link, затем — в тегах link в HTML-части ответа. 
     *  Если сервер возвращает статус, отличный от 200, endpoint считается не найденным.
     *  Если существует несколько endpoints, будет возвращен URL самой первой. 
     *  @param string $url URL, на который будет сделан запрос
     *  @param string $rel Тип endpoint, который требуется найти.
     *  @return mixed False, если endpoint найти не удалось или строку с её адресом
     */
    function get_endpoint($url,$rel) {
      $result=false;

      if (empty(Library_indieweb::$ch)) Library_indieweb::$ch=curl_init();      
      $ch = Library_indieweb::$ch;
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
      curl_setopt($ch,CURLOPT_TIMEOUT,5); // не ждём больше 5 секунд
      curl_setopt($ch,CURLOPT_HTTPGET,true);
      curl_setopt($ch,CURLOPT_HEADER,true); // нам нужны заголовки
      curl_setopt($ch,CURLOPT_URL,$url);
      curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true); // обрабатываем редиректы
      $resp = curl_exec($ch);

      $req_info = curl_getinfo($ch);
      if ($req_info['http_code']!=200) { // если код ответа ошибочный, логгируем и дальше ничего не обрабатываем
        Library::$app->log_entry('indieweb', E_USER_ERROR, __FILE__, print_r($req_info, true));
        return false;
      }

      list($headers,$body)=explode("\r\n\r\n",$resp,2); // лимит в 1 выставляем, чтобы не разрезать тело ответа
      $headers=explode("\r\n",$headers);
      foreach ($headers as $header) {
        if (strpos($header,': ')!==false) {
          list($hname,$hvalues)=explode(": ",$header,2);
          if (strtolower($hname)=="link") {
            $hvalues = explode(',',$hvalues);
            foreach ($hvalues as $hvalue) {
              list($hlink,$hrel)=explode('; rel=',$hvalue,2);
              $hrel = str_replace(array('\'','"'),'',$hrel); // удаляем ' и "
              $hrel = explode(' ',$hrel); // на случай, если rel содержит несколько значений
              if (in_array($rel,$hrel)) {
                $result = trim(str_replace(array('<','>'),'',$hlink));
                break;
              }
            }
          }
        }
      }
      if (!$result) {
        $doc = new DOMDocument;
        @$doc->loadHTML($body);       
        $xpath = new DOMXPath($doc);        
        $query = "//link[contains(concat(' ',normalize-space(@rel),' '),' $rel ')][@href]|//a[contains(concat(' ',normalize-space(@rel),' '),' $rel ')][@href]";       
        $entries = $xpath->query($query);
        if ($entries->length>0) {
          $result=$entries->item(0)->getAttribute('href');
          if ($result=='') $result=$url; 
        }
      }
      if ($result && parse_url($result,PHP_URL_HOST)=='') { // если не указан хост, то ссылка относительная, поэтому возьмём хост (и, возможно, путь) из $url
        if ($result[0]=='/') $dir = ''; // если путь начинается с /, то он — абсолютный, и $dir заполнять не нужно
        else {
          $dir = dirname(parse_url($url,PHP_URL_PATH)).'/'; // если путь относительный, то извлекаем начальную часть пути из исходного URL 
          $dir = str_replace('\\','/',$dir);
          $dir = str_replace('//','/',$dir);
        }
        $result = parse_url($url,PHP_URL_SCHEME).'://'.parse_url($url,PHP_URL_HOST).$dir.$result; // собираем всё вместе
      }
      return $result;
    }
}
