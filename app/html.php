<?php

class HTML {
  static function submit($value,$action='') {
     return '<button type="submit" name="submit" value="'.$action.'">'.$value.'</button>';
  }

  static function input(&$data,$name,$type="text",$size=60,$additional='') {
    $value=HTML::_get($data,$name);
    return '<input type="'.$type.'" size='.$size.' name="'.$name.
     '" value="'.htmlspecialchars($value).'" '.$additional.' >';
  }

  static function checkbox(&$data,$name,$value,$additional='') {
	if (HTML::_isset($data,$name) && HTML::_get($data,$name)==$value) $checked='checked="checked"';
	else $checked='';
    return '<input type="checkbox" name="'.$name.'" value="'.htmlspecialchars($value).'" '.$checked.' '.$additional.' >';
  }

  static function textarea(&$data,$name,$rows=5,$cols=60,$additional='',$prefix=false) {
    $value=HTML::_get($data,$name);
    return '<textarea rows="'.$rows.'" name="'.$name.
     '" cols="'.$cols.'" '.$additional.'>'.htmlspecialchars($value).'</textarea>';
  }

 static function radio(&$data,$name,$values) {
    $tmp='';
    $def_value=HTML::_get($data,$name);
    if (is_array($values)) foreach ($values as $value=>$descr) {
      $tmp.='<label><input type="radio" name="'.$name.'" value="'.$value.'"';
      if ($def_value==$value) $tmp.=' checked';
      $tmp.='>'.$descr.'</label> &nbsp; ';
    }
    return $tmp;
  }

  static function select($data,$name,$values,$additional='',$prefix=false) {
		$fieldname = ($prefix) ? $prefix.'['.$name.']' : $name;
    $tmp = '<select name="'.$fieldname.'" '.$additional.'>';
    if (is_array($values)) foreach ($values as $key=>$value) {

      if ($key==$data[$name]) $tmp.="<option value=\"".$key."\" selected=\"selected\">".$value.'</option>';
      else $tmp.="<option value=\"".$key."\">".$value.'</option>';
    }
    $tmp.= '</select>';
    return $tmp;
  }

	static function messages($messages) {
		$buffer='';
		foreach ($messages as $msg) {
			if ($msg['level']==3) $buffer.='<div class="msg_error">'.htmlspecialchars($msg['text']).'</div>';
			elseif ($msg['level']==2) $buffer.='<div class="msg_warn">'.htmlspecialchars($msg['text']).'</div>';
			else $buffer.='<div class="msg_ok">'.htmlspecialchars($msg['text']).'</div>';
		}
		return $buffer;
	}
	
	static function date_input($data,$name) {
	  $buffer='';
	  if (HTML::_isset($data[$name])) {
	    $do = @date_create(HTML::_get($data,$name));
	  }
	  else $do=date_create();
	  $tmp[$name]['day']=date_format($do,'d');
	  $tmp[$name]['month']=date_format($do,'m');
	  $tmp[$name]['year']=date_format($do,'Y');
	  $days = range(1,31);
	  $months = array(1=>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',
		  7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря');
	  $years = array();
	  for ($i=1900; $i<=date('Y'); $i++) $years[$i]=$i;
      $buffer = HTML::select($tmp,$name.'[day]',$days).'&nbsp;'.HTML::select($tmp,$name.'[month]',$months).'&nbsp;'.HTML::select($tmp,$name.'[year]',$years);
	  return $buffer;
	}
	
	static function calendar_input($data,$name,$time=false) {
	  $buffer='';
	  if (HTML::_isset($data,$name)) {
	    $do = date_create(HTML::_get($data,$name));
	  }
	  else $do=date_crate();
	  if ($time) {
		$maxlen = 16;
		$tmp[$name]=@date_format($do,'d.m.Y h:i');
	  }
	  else {
		$maxlen = 10;
		$tmp[$name]=@date_format($do,'d.m.Y');
	  }
  	  $buffer = HTML::input($tmp,$name,'text',$maxlen,'maxlength="'.$maxlen.'" class="calendar"');
	  return $buffer;
	}
	
	static function generate_form($filename,$data,$select_data=false,$action='',$form_id=false) {
	  if (!file_exists($filename)) {
		trigger_error('Файла с данными для формы не существует!',E_USER_WARNING);
		return false;
	  }
	  if (!function_exists('spyc_load_file')) {
		trigger_error('Не найдена функция spyc_load_file! Проверьте, подключили ли вы модуль обработки YAML.',E_USER_WARNING);
		return false;  
	  }
	  $formdata = spyc_load_file($filename);
	  
	  $buffer ='';
	  $multipart = false; // с помощью этой переменной будем отслеживать, потребуется ли данной форме multipart
	  $val_code = array();
	  foreach ($formdata as $f_id=>$f_set) { // самый внешний уровень описывает fieldsetы
		if (!empty($buffer)) $buffer.='</fieldset>'; // закрываем предыдущий fielset, если он был в буфере
		$buffer.='<fieldset id="'.$f_id.'">'; 
		foreach ($f_set as $f_name=>$f_data) {
		  $val_buffer=false;
		  if (isset($f_data['default']) && !HTML::_isset($data,$f_name)) HTML::_set($data,$f_name,$f_data['default']);	// выставляем значение по умолчанию из YAML-файла, если значение не установлено в переданных данных
		  elseif (!HTML::_isset($data,$f_name)) HTML::_set($data,$f_name,false); // иначе выставляем значение false по умолчанию (исключение будет только для checkbox)
		  
		  $id=(isset($f_data['id'])) ? ' id="'.htmlentities($f_data['id'],ENT_COMPAT).'"' : '';
		  $class=(isset($f_data['class'])) ? ' class="'.htmlentities($f_data['class'],ENT_COMPAT).'"' : '';
		  $descr=(isset($f_data['descr'])) ? htmlspecialchars($f_data['descr']) : htmlspecialchars($f_name); 
		  
		  if ($f_data['type']=='text' || $f_data['type']=='password' || $f_data['type']=='email') { // обычный input, input для пароля или для Email
			if (isset($f_data['validate'])) {
			  $val_buffer='val: "'.$f_data['validate'].'"';
			  if ($f_data['validate']=='int' || $f_data['validate']=='integer' || $f_data['validate']=='float') { 
				if (isset($f_data['min'])) $val_buffer .= ', min: '.$f_data['min'];
				if (isset($f_data['max'])) $val_buffer .= ', max: '.$f_data['max'];
				if (!isset($f_data['size'])) $f_data['size']=11;
			  }
			  if ($f_data['validate']=='url' && !isset($f_data['add_prefix'])) {
				if (!isset($f_data['protocols'])) $protocols = array('http','https');
				else $protocols=$f_data['protocols'];
				$val_buffer.=', proto : [ "'.join('","',$protocols).'" ]';
			  }
			  if ($f_data['validate']=='regexp' && isset($f_data['regexp'])) {
				$val_buffer.=', regexp : "'.addslashes($f_data['regexp']).'"';
			  }
			}			
			$size = (isset($f_data['size'])) ? $f_data['size'] : 60;
			$additional = isset($f_data['maxlength']) ? 'maxlength="'.intval($f_data['maxlength']).'"' : '';
			$buffer.='<div'.$id.$class.'><label><span>'.$descr.'</span>'.HTML::input($data,$f_name,$f_data['type'],$size,$additional).'</label></div>';
		  }
		  if ($f_data['type']=='textarea' || $f_data['type']=='wysiwyg') { // для wisywig здесь будет задаваться только класс, подключение собственно редактора должно делаться извне для обеспечения большей гибкости
			$rows = (isset($f_data['rows'])) ? $f_data['rows'] : 4;
			$cols = isset($f_data['cols']) ? $f_data['cols'] : 60;	  
			$additional='';
			if ($f_data['type']=='wisywig') $additional.=' class="wysiwyg"';
			$buffer.='<div'.$id.$class.'><label><span>'.$descr.'</span>'.HTML::textarea($data,$f_name,$rows,$cols).'</label></div>';
			if (isset($f_data['validate'])) {
			  $val_buffer='val: "'.$f_data['validate'].'"';
			  if ($f_data['validate']=='regexp' && isset($f_data['regexp'])) {
				$val_buffer.=', regexp : "'.addslashes($f_data['regexp']).'"';
			  }
			}
		  }
		  if ($f_data['type']=='file' || $f_data['type']=='imgfile') { // обычный input, input для пароля или для Email
			// TODO: добавить валидацию и поле digit
			// TODO: возможно, потребуются еще какие-то аттрибуты
			$multipart = true; // включаем загрузчик файлов
			if (isset($f_data['multi'])) { 	$multi = 'multiple="1"'; $localname=htmlentities($f_name,ENT_COMPAT).'[]'; }
			else { $multi=''; $localname=htmlentities($f_name,ENT_COMPAT); }
			$buffer.='<div'.$id.$class.'><label><span>'.$descr.'</span>';
			if ($f_data['type']=='imgfile') {
			   //TODO: добавить вывод картинки
			}
			$buffer.='<input type="file" name="'.$localname.'" '.$multi.'/></label></div>';
		  }
		  if ($f_data['type']=='hidden') {
			$buffer.=HTML::input($data,$f_name,$f_data['type']);
		  }
		  if ($f_data['type']=='checkbox') {
			if (!isset($f_data['value'])) $f_data['value']=1;
			$buffer.='<div'.$id.$class.'><label><span>'.$descr.'</span>'.HTML::checkbox($data,$f_name,$f_data['value']).'</label></div>';
		  }
		  if ($f_data['type']=='date') {
			$buffer.='<div'.$id.$class.'><label><span>'.$descr.'</span>'.HTML::date_input($data,$f_name).'</label></div>';
		  }
		  if ($f_data['type']=='calendar') {
			$time = isset($f_data['time']) ? $f_data['time'] : false;
			$buffer.='<div'.$id.$class.'><label><span>'.$descr.'</span>'.HTML::calendar_input($data,$f_name,$time).'</label></div>';
			$val_buffer = ($time) ? 'val: "calendar", time: 1' : 'val: "calendar"';
		  }	  
		  if ($f_data['type']=='radio') {
			if (!isset($f_data['value']) || !is_array($f_data['value'])) $f_data['value']=$select_data[$f_name];
			$buffer.='<div'.$id.$class.'><span>'.$descr.'</span>'.HTML::radio($data,$f_name,$f_data['value']).'</div>';
		  }
		  if ($f_data['type']=='select') {
			if (!isset($f_data['value']) || !is_array($f_data['value'])) $f_data['value']=$select_data[$f_name];
			$buffer.='<div'.$id.$class.'><label><span>'.$descr.'</span>'.HTML::select($data,$f_name,$f_data['value']).'</label></div>';
		  }	  
		  if ($f_data['type']=='legend') { // для названия fieldsetа
			$display=isset($f_data['display']) ? ' style="display: '.htmlentities($f_data['display'],ENT_COMPAT).'"' : '';
			if (isset($f_data['descr'])) {
			  $descr=htmlspecialchars($f_data['descr']);
			}
			else { $display="style='display: none'"; $descr=''; }
			$buffer.='<legend'.$display.'>'.$descr.'</legend>';
		  }
		  if ($f_data['type']=='submit') { // кнопка отправки формы
			$id=(isset($f_data['id'])) ? ' id="'.htmlentities($f_data['id'],ENT_COMPAT).'"' : '';
			$class=(isset($f_data['class'])) ? ' class="'.htmlentities($f_data['class'],ENT_COMPAT).'"' : '';
			$value=(isset($f_data['value'])) ? htmlspecialchars($f_data['value']) : 'Отправить';
			$buffer.='<div'.$id.$class.'><input type="submit" name="'.htmlentities($f_name,ENT_COMPAT).'" value="'.$value.'"/></div>';
		  }
		  if ($f_data['type']=='html' && isset($f_data['code'])) { // произвольный HTML-код
		    $buffer.=$f_data['code'];
		  }
		  if (isset($f_data['callback'])) {
			if ($val_buffer) $val_buffer.=', ';
			$val_buffer.='callback: '.htmlentities($f_data['callback'],ENT_COMPAT);
		  }
		  if (isset($f_data['validate_text'])) {
			if ($val_buffer) $val_buffer.=', ';
			$val_buffer.='txt: "'.htmlspecialchars($f_data['validate_text']).'"';
		  }
		  if (isset($f_data['maxlength'])) {
			if ($val_buffer) $val_buffer.=', ';
			$val_buffer.='maxlen: '.intval($f_data['maxlength']).'';
		  }
		  if ($val_buffer) $val_code[] = $f_name.' : { '.$val_buffer.'}';
		}
	  }
	  $buffer.='</fieldset>';
	  if (!$form_id) $form_id=$_SERVER['REQUEST_URI'].time().$_SERVER['REMOTE_ADDR'];
	  if ($multipart) $multipart=' enctype="multipart/form-data"';
	  $buffer = '<form class="yaml_formgen" method="post" action="'.$action.'" id="'.htmlentities($form_id,ENT_COMPAT).'" '.$multipart.'>'.$buffer.'</form>';
	  $buffer .= '<script type="text/javascript"><!--
	try { YAML_Form_Validator("'.$form_id.'",{
	'.join(",\n",$val_code).'
	});
	}
	catch (e) { console.log(e) }
	--></script>';
	  return $buffer;
	}

	static function validate_form($data,$filename,$callback=false) {
	  if (!file_exists($filename)) {
		trigger_error('Файла с данными для формы не существует!',E_USER_WARNING);
		return false;
	  }
	  if (!function_exists('spyc_load_file')) {
		trigger_error('Не найдена функция spyc_load_file! Проверьте, подключили ли вы модуль обработки YAML.',E_USER_WARNING);
		return false;  
	  }
	  $formdata = spyc_load_file($filename);

	  $result = array();
	  $errors = array();
	  foreach ($formdata as $f_id=>$f_set) { // самый внешний уровень описывает fieldsetы
		foreach ($f_set as $f_name=>$f_data) {
		  if (HTML::_isset($data,$f_name)) { // если такое поле пришло из формы
			$errmsg = false; 
			$fieldname=(isset($f_data['descr']) ? htmlspecialchars($f_data['descr']) : htmlspecialchars($f_name));
			if ($f_data['type']=='date') { // если поле имеет тип "дата" (три select с годом, месяцем и днем), то нужна дополнительная обработка
			  $value=HTML::_get($_data,$f_name.'[day]').'.'.HTML::_get($_data,$f_name.'[month]').'.'.HTML::_get($_data,$f_name.'[year]');
			  if (isset($f_data['timestamp']) && $f_data['timestamp'])  $value=strtotime($value); // если нужно преобразовать результат в TIMESTAMP
			  else { // иначе преобразовываем в ГГГГ-ММ-ДД для хранения поля DATE или DATETIME, в зависимости от того, выставлено ли свойство TIME
				$do=@date_create($value);
				if (!empty($do)) {
				  if (isset($f_data['time']) && $f_data['time']) $value=date_format($do,'Y-m-d H:i:s');
				  else $value=date_format($do,'Y-m-d H:i:s');
				}
				else $errmsg='Некорректно указана дата: '.htmlspecialchars($value).'!';
			  }
			}
			elseif ($f_data['type']=='calendar') { // если поле имеет тип "календарь", то нужна дополнительная обработка
			  $value=HTML::_get($data,$f_name);
			  if (isset($f_data['timestamp']) && $f_data['timestamp'])  $value=strtotime($value); // если нужно преобразовать результат в TIMESTAMP
			  else { // иначе преобразовываем в ГГГГ-ММ-ДД для хранения поля DATE или DATETIME, в зависимости от того, выставлено ли свойство TIME
				$do=@date_create($value);
				if (!empty($do)) {
				  if (isset($f_data['time']) && $f_data['time']) $value=date_format($do,'Y-m-d H:i:s');
				  else $value=date_format($do,'Y-m-d H:i:s');
				}
				else $errmsg='Некорректно указана дата в поле "'.$fieldname.'"!';
			  }
			}
			elseif ($f_data['type']=='legend'|| $f_data['type']=='html') {} // legend и custom, описываемые в YAML, обрабатывать не требуется -- для них из формы ничего не придет
			else $value=HTML::_get($data,$f_name); // для полей остальных типов просто перебрасываем данные в результирующий массив

			if (function_exists('mb_strlen')) {
			  $enc=mb_detect_encoding($value);			  
			  $len=mb_strlen($value,$enc); // если доступна функция для работы с мультибайтными строками, будем использовать ее
			}
			else $flen=strlen($value); // иначе -- обычную strlen, чтобы не возникало ошибки
			if (isset($f_data['maxlength']) && $len>$f_data['maxlength']) $errmsg = 'Поле "'.$fieldname.'" превышает максимально допустимую длину!';		  
			if (isset($f_data['validate'])) { // если задано правило для валидации
			  if ($f_data['validate']=='notempty' && empty($value)) $errmsg = 'Поле "'.$fieldname.'" не может быть пустым!';
			  elseif ($f_data['validate']=='regexp' && isset($f_data['regexp'])) { // валидация по регулярному выражению
				if (!preg_match('|'.$f_data['regexp'].'|isu',$value)) $errmsg = 'Поле "'.$fieldname.'" должно соответствовать регулярному выражению '.$f_data['regexp'].'!';
			  }
			  elseif ($f_data['validate']=='email') { // валидация EMail
				if (!preg_match('|^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\.\-]+$|',$value)) $errmsg = 'Значение в поле "'.$fieldname.'" не является корректным адресом Email!';
			  }
			  elseif ($f_data['validate']=='int' || $f_data['validate']=='integer') { // валидация целого числа
				if (!preg_match('|^[+\-]?\d+$|',$value)) $errmsg = 'Значение в поле "'.$fieldname.'" не является целым числом!';
				$value=intval($value);
				if (isset($f_data['min']) && $value<$f_data['min']) $errmsg = 'Значение в поле "'.$fieldname.'" меньше допустимого значения '.intval($f_data['min']);
				if (isset($f_data['max']) && $value>$f_data['max']) $errmsg = 'Значение в поле "'.$fieldname.'" больше допустимого значения '.intval($f_data['max']);
			  }
			  elseif ($f_data['validate']=='float') { // валидация дробного числа
				if (!preg_match('|^[+\-]?\d+(\.\d+)?$|',$value)) $errmsg = 'Значение в поле "'.$fieldname.'" не является числом с плавающей точкой!';
				$value=floatval($value);
				if (isset($f_data['min']) && $value<$f_data['min']) $errmsg = 'Значение в поле "'.$fieldname.'" меньше допустимого значения '.intval($f_data['min']);
				if (isset($f_data['max']) && $value>$f_data['max']) $errmsg = 'Значение в поле "'.$fieldname.'" больше допустимого значения '.intval($f_data['max']);
			  }
			  elseif ($f_data['validate']=='phone' || $f_data['validate']=='tel') { // валидация телефона
				if (!preg_match('|^(\+\d+\s*)?\(?\d{3}\)?\s*\d{3}-?\d{2}-?\d{2}$|',$value)) $errmsg = 'Значение в поле "'.$fieldname.'" не является телефоном в формате +N (NNN) NNN-NN-NN!';
			  }
			  elseif ($f_data['validate']=='url') { // валидация URL
				if (!isset($f_data['protocols']) || !is_array($f_data['protocols'])) $protocols = array('http','https'); // по умолчанию допустимыми протоколами являются http:// и https://
				else $protocols=$f_data['protocols'];
				$url_checked=false;
				for ($i=0, $count=count($protocols); $i<$count;$i++) {
				  if (substr($value,0,strlen($protocols[$i])+3)==$protocols[$i].'://') $url_checked=true;			  
				}
				if (!$url_checked) {
				  if (isset($f_data['add_prefix'])) $value=$f_data['add_prefix'].'://'.$value;
				  else $errmsg = 'Значение в поле "'.$fieldname.'" не является корректным URL или содержит недопустимый протокол!';
				}
				// TODO: подумать о проверке на то, что URL корректно закодирован
			  }
			  elseif ($f_data['validate']=='ip' || $f_data['validate']=='ip4') { // проверка на IP-адрес
				$valid_ip=true;
				if (substr_count($value,'.')!=3) $valid_ip=false;
				else {
				  $ip_parts = explode('.',$value);
				  for ($i=0; $i<4 && $valid_ip; $i++) if (!is_numeric($ip_parts[$i]) || $ip_parts[$i]<0 || $ip_parts[$i]>255) $valid_ip=false;
				}
				if (!$valid_ip) $errmsg = 'Значение в поле "'.$fieldname.'" не является IP-адресом в формате NNN.NNN.NNN.NNN';
			  }
			}
			
			if (!$errmsg) HTML::_set($result,$f_name,$value); // если валидация прошла, то записываем данные в результат
			else $errors[]=array('level'=>3,'text'=>(isset($f_data['validate_text']) ? $f_data['validate_text'] : $errmsg)); // иначе записываем сообщение об ошибке  в массив ошибок. Если задано сообщение в YAML -- используем его, иначе -- сообщение, сгенерированное функцией валидации
		  }
		  elseif ($f_data['type']=='checkbox') HTML::_set($result,$f_name,false); // если тип -- checkbox и данных от него не пришло, значит, флажок был снят, поэтому пишем false	  
		  elseif ($f_data['type']=='file' || $f_data['type']=='imgfile') { // обработка загруженных файлов
		   if (!isset($f_data['noprocess']) || !$f_data['noprocess']) { // если обработка файлов валидатором не отключена
			$valid=true;
			$tmpdata = HTML::_get($_FILES,$f_name);
			if (is_array($tmpdata['tmp_name'])) {
			   $filesdata=array();
			   for ($i=0, $count=count($tmpdata['tmp_name']); $i<$count; $i++) $filesdata[]=array('tmp_name'=>$tmpdata['tmp_name'][$i],'name'=>$tmpdata['name'][$i],'size'=>$tmpdata['size'][$i],'type'=>$tmpdata['type'][$i],'error'=>$tmpdata['error'][$i]);
			}
			else $filesdata=array($tmpdata); // если загружен только один файл
			$i=0;		
			foreach($filesdata as $filedata) {
				if ($callback==false) { $valid=false; $errors[]=array('level'=>3,'text'=>'Не задана функция-обработчик для проверки загруженных файлов'); }
				elseif (!function_exists($callback)) { $valid=false; $errors[]=array('level'=>3,'text'=>'Функции '.htmlspecialchars($callback).', указанной в качестве еобработчика загрузки файлов, не существует');  }
				elseif ($filedata['error']==UPLOAD_ERR_NO_FILE) {  // если файл не загружен, ошибкой это не считаем, если только не указан явно тип валидации как required
				  $valid=false; if (isset($f_data['validate']) && $f_data['validate']=='notempty') $errors[]=array('level'=>3,'text'=>'Не загружен необходимый файл!');
				}
				else {
				  if ($filedata['error']!=UPLOAD_ERR_OK || !is_uploaded_file($filedata['tmp_name'])) { // если при загрузке файла произошла какая-то ошибка
					if ($filedata['error']==UPLOAD_ERR_INI_SIZE || $filedata['error']==UPLOAD_ERR_FORM_SIZE) {
					  $valid=false; $errors[]=array('level'=>3,'text'=>'Размер файла '.htmlspecialchars($filedata['name']).' превышает допустимый, заданный в php.ini или директиве fFILE_SIZE.');
					} 
					elseif ($filedata['error']==UPLOAD_ERR_NO_TMP_DIR || $filedata['error']==UPLOAD_ERR_CANT_WRITE) {
					  $valid=false; $errors[]=array('level'=>3,'text'=>'Ошибка записи временного файла на диск или недоступен временный каталог');
					}
					else { $valid=false; $errors[]=array('level'=>3,'text'=>'Ошибка загрузки файла '.htmlspecialchars($filedata['name']).', код ошибки: '.$filedata['error']); }
				  }
				  if (isset($f_data['maxsize']) && intval($filedata['size'])>intval($f_data['maxsize'])) {
					$valid=false; $errors[]=array('level'=>3,'text'=>'Файл '.htmlspecialchars($filedata['name']).' превышает максимально допустимый размер в '.$f_data['maxsize'].' байтов'); 
				  }
				  if ($valid && $f_data['type']=='imgfile') { // если загруженное изображение -- картинка, делаем дополнительные проверки
					$imgdata=getimagesize($filedata['tmp_name']);
					if (!$imgdata) { $valid=false; $errors[]=array('level'=>3,'text'=>'Файл '.htmlspecialchars($filedata['name']).' не является изображением!'); }
					if (isset($f_data['maxheight']) && $imgdata[1]>$f_data['maxheight']) { $valid=false; $errors[]=array('level'=>3,'text'=>'Высота изображения превышает '.intval($f_data['maxheight']).' пикселей!'); }
					if (isset($f_data['maxwidth']) && $imgdata[0]>$f_data['maxwidth']) { $valid=false; $errors[]=array('level'=>3,'text'=>$errmsg='Ширина изображения превышает '.intval($f_data['maxwidth']).' пикселей!'); }
					// TODO: потом длделать вписывание изображения в размер при наличии поля resize в YAML
				  }
				}
				if ($valid) {
					list($upload_file,$upload_data)=call_user_func($callback,$filedata['tmp_name'],$filedata['name'],$filedata['size'],$filedata['type']);
					if ($upload_file) {
					  move_uploaded_file($filedata['tmp_name'],$upload_file);
					  HTML::_set($result,$f_name.'['.$i.']',$upload_data);
					  $i++;
					}		
				}
				elseif (file_exists($filedata['tmp_name'])) unlink($filedata['tmp_name']);  // если файл есть, но загрузить не получилось, удаляем его 
			   }
			}
		  }
		}
	  }
	  return array($result,$errors);
	}	

	static function pages($pages,$curpage,$mode=0) {
		if ($pages>1) {
		$buffer='<ul>';
		for ($i=1; $i<=$pages; $i++) {
			if ($i==$curpage) $buffer.='<li><b>'.$i.'</b></li> ';
			elseif ($i==1) $buffer.='<li><a href="./">'.$i.'</a></li> ';
			else $buffer.='<li><a href="'.$i.'.htm">'.$i.'</a></li> ';
		}
		$buffer.='</ul>';
		}
		else $buffer='';
	  return $buffer;
	}

	static function out($text) {
		echo htmlspecialchars($text);
	}

	static function avatar($userdata,$show_empty=false) {
		global $app;
		if ($userdata['avatar']=='none') $buffer='';
		else $buffer = '<img class="avatar" src="'.$app->sitepath.'/f/av/'.$userdata['id'].'.'.$userdata['avatar'].'" alt="'.
						htmlspecialchars($userdata['display_name']).'" />';
		return $buffer;
	}

/** Вывод имени пользователя со ссылкой на его профиль. Адрес профиля пользователя хранится в настройке user_hurl и обрабатывается с помощью sprintf.
 *
 * @param string $name Имя пользователя для вывода
 * @param int $uid Идентификатор пользователя. Можно не указывать, тогда пользователь будет считаться гостем
 * @param boolean $follow Если true, то ссылка на имя пользователя выводится без rel="nofollow"
 * @return string HTML-код имени пользователя
 */
	static function output_user($name,$uid=0,$follow=false) {
		global $app;
		$name = htmlspecialchars($name);
		$url = $app->url(sprintf($app->get_opt('user_hurl'),$name,$uid));
		if ($follow) $follow='';
		else $folow=' rel="nofollow"';
		if ($uid>AUTH_SYSTEM_USERS) $result = '<a href="'.$url.'"'.$follow.' class="username">'.$name.'</a>'; // если ID пользователя больше максимального из зарезирвированных для системных, т.е. пользователь -- не гость
		else $result = '<span class="user">'.$name.'</span>';
		return $result;
	}

	/** Вывод массива списка пользователей **/
	static function output_user_list($users) {
		$buffer = '';
		$first = true;
		foreach ($users as $curuser) {
			if (!$first) $buffer.=', ';
			$buffer.=HTML::output_user($curuser['display_name'],$curuser['uid']);
		}
		return $buffer;
	}

	static function meta($meta) {
		$result = '';
		foreach ($meta as $curitem) {
			if ($curitem['type']=='meta') $result.='<meta name="'.htmlspecialchars($curitem['content']).'" content="'.htmlspecialchars ($curitem['value']).'" />';
			elseif ($curitem['type']=='link') {
				$result.='<link rel="'.htmlspecialchars($curitem['rel']).'" href="'.htmlspecialchars($curitem['href']).'"';
				if ($id) $result.=' id="'.htmlspecialchars ($curitem['id']).'"';
				$result.=' />';
			}
			elseif ($curitem['type']=='js') $result.='<srcipt type="text/javascript" src="'.$curitem['src'].'"></script>';
			elseif ($curitem['type']=='css') $result.='<link rel="stylesheet" type="text/css" href="'.$curitem['href'].'" />';
		}
		return $result;
	}

/** Вывод ссылок на RSS-каналы сайта в теги link
 *
 * @param mixed $rss Строка или массив строк с URLами RSS.
 */
	static function rss($rss) {
		$result = '';
		if (!empty($rss)) {
			if (!is_array($rss)) $rss=array($rss);
			foreach ($rss as $curitem) {
				$result.='<link rel="alternate" type="application/rss+xml" href="'.$curitem['url'].'" title="'.htmlspecialchars($curitem['title']).'" />';
			}
		}
		return $result;
	}

	/** Определение пути к файлу, расположенному в стилевом каталоге.
	 * Если файл есть в текущем стиле, выдается ссылка на него, иначе -- на файл в стиле по умолчанию **.
	 *
	 * @param string $filename Имя файла относительно каталога с шаблоном
	 * @param boolean $lang_dependent Файл является языкозависмыми (в настоящее время параметр не используется).
	 * @return string Путь к файлу относительно корня сайта
	 */
	static function filepath($name,$lang_dependent=false) {
		global $app;
		$filename1 = 's/'.$app->template.'/'.$name; // TODO: добавить выбор шаблона на основе настроек пользователя или раздела
		$filename2 = 's/1/'.$name;
  	if (file_exists($filename1)) $result = $app->sitepath.'/'.$filename1;
		else $result = $app->sitepath.'/'.$filename2;
		return $result;
	}

	/** Вывод меню в виде списка тегов **/
	static function menu($items) {
		$buffer='';
		if (!empty($items) && is_array($items)) {
			$buffer.='<ul>';
			foreach ($items as $curitem) {
				$buffer.='<li><a href="'.$curitem['url'].'">'.htmlspecialchars($curitem['title']).'</a></li> ';
			}
			$buffer.='</ul>';
		}
		return $buffer;
	}
	
	function _isset($data,$name) {
	  $name=str_replace(']','',$name); // убираем закрывающие символы ], чтобы упростить анализ
	  $pos = strpos($name,'[');
	  if ($pos===false) return isset($data[$name]); // если запросили простое имя без скобок, то делаем обычный isset
	  list($name1,$name2)=explode('[',$name,2); // разбиваем имя на две части: имя текущего ключа (до скобки) и имя остальной части (после скобки)
	  if (!isset($data[$name1])) return false; // если текущего ключа нет в массиве данных, то и проверять дальше нечего
	  else return HTML::_isset($data[$name1],$name2); // иначе делаем рекурсию, в которой будем проверять более глубокие элементы
	}

	function _get($data,$name) {
	  $name=str_replace(']','',$name); // убираем закрывающие символы ], чтобы упростить анализ
	  $pos = strpos($name,'[');
	  if ($pos===false) return $data[$name]; // если запросили простое имя без скобок, то возвращаем значение. Проверки isset не делаем специально, чтобы были notice в случае ошибок,  для нее есть отдельная функция!
	  list($name1,$name2)=explode('[',$name,2); // разбиваем имя на две части: имя текущего ключа (до скобки) и имя остальной части (после скобки)
	  return HTML::_get($data[$name1],$name2); // иначе делаем рекурсию, где возьмем более глубокие элементы
	}

	function _set(&$data,$name,$value) {
	  $name=str_replace(']','',$name); // убираем закрывающие символы ], чтобы упростить анализ
	  $pos = strpos($name,'[');
	  if ($pos===false) $data[$name]=$value; // если запросили простое имя без скобок, то возвращаем значение. Проверки isset не делаем специально, чтобы были notice в случае ошибок,  для нее есть отдельная функция!
	  else {
	    list($name1,$name2)=explode('[',$name,2); // разбиваем имя на две части: имя текущего ключа (до скобки) и имя остальной части (после скобки)
	    if (!isset($data[$name1])) $data[$name1]=array(); // если ключа еще нет, создаем ключ с пустым массивом
  	    HTML::_set($data[$name1],$name2,$value); // иначе делаем рекурсию, где возьмем более глубокие элементы
	  }
	}
}

?>
