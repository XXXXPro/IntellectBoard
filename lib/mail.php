<?php
/** ================================
 *  Intellect Board Pro
 *  http://intbpro.ru
 *  Библиотека отправки почты
 *
 *  ================================ */

class Library_mail extends Library {

  function process_mail($maildata,$return_path) {
    global $app;
    for ($i=0, $count=count($maildata); $i<$count; $i++) {

      // Обрабатываем
      if (isset($maildata[$i]['from_name'])) {
        $maildata[$i]['from']=$this->mime_encode($maildata[$i]['from_name']).' <'.$maildata[$i]['from'].'>';
      }
      if (isset($maildata[$i]['to_name'])) {
        $maildata[$i]['to']=$this->mime_encode($maildata[$i]['to_name']).' <'.$maildata[$i]['to'].'>';
      }

      $headers ="From: ".$maildata[$i]['from']."\r\n";
      $headers.="Return-Path: $return_path\r\n";
      if (isset($maildata[$i]['reply'])) $headers.="Reply-To: ".$maildata[$i]['reply']."\r\n";
      $headers.="MIME-Version: 1.0\n";
      if (empty($maildata[$i]['html'])) $headers.="Content-Type: text/plain; charset=utf-8\r\n";
      else $headers.="Content-Type: text/html; charset=utf-8\r\n";
      $headers.="Content-Transfer-Encoding: 8bit\r\n";
      $headers.="Precedence: bulk\r\n";
      if (!empty($maildata[$i]['unsubscribe'])) {
        $headers.="List-Unsubscribe-Post: List-Unsubscribe=One-Click\r\n";
        $headers.='List-Unsubscribe: <'.$maildata[$i]['unsubscribe'].">\r\n";
      }
      if (!empty($maildata[$i]['list-id'])) $headers.='List-id: '.$maildata[$i]['list-id']."\r\n";
      $headers.="X-Priority: 3\r\n";
      $headers.="X-Mailer: Intellect Board 3 Pro Framework";
      $maildata[$i]['subj']=$this->mime_encode($maildata[$i]['subj']);

      // если явно указано, что письмо не в HTML-формате
      if (isset($maildata['html']) && $maildata['html']==false) $maildata[$i]['text']=strip_tags($maildata[$i]['text']);
      // если же письмо в HTML-формате, делаем разбиение на строки меньше заданной длины (по умолчанию 70 символов)
      else $maildata[$i]['text'] = $this->separate_lines($maildata[$i]['text']);
      // собственно, сама отправка
      mail($maildata[$i]['to'], $maildata[$i]['subj'], $maildata[$i]['text'], $headers);
      
      /*$fh=fopen(BASEDIR.'tmp/test.txt','w');
      fputs($fh,$headers);
      fputs($fh,"\r\n");
      fputs($fh,$maildata[$i]['text']);
      fclose($fh);*/
    }
  }

  /** Разделение HTML-сообщения на строки, чтобы оно корректно проходило через любые MTA */
  function separate_lines($text, $block_len=70) {
    $len = mb_strlen($text);
    $result = '';
    for ($i = 0; $i < $len; $i = $i + $block_len) {
      $result .= preg_replace("/ /", " \r\n", mb_substr($text, $i, $block_len), 1);
    }
    return $result;
  }  

  function mime_encode($text) {
         return "=?utf-8?B?".base64_encode($text)."?=";
  }
}
?>
