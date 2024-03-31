{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
.file_tabs { line-height: 190% }
.file_tabs li { display: inline-block; padding: 1px 8px; background: #dff4ff; margin: 2px }
.file_tabs .tab_active { background: #ffd4bf }
</style>
<script type="text/javascript">
window.onload = function() {
	$(window).bind('keydown',function(e) {
		if (e.keyCode==83 && e.ctrlKey) {
		  $('#style_edit_form').submit();
		  return false;
		}
	});
	$('#style_edit_form').submit(function (e) {
		var data=$(e.target).serialize();
    jQuery.ajax('', {
	    	method : 'post',
	    	data : data,
	    	complete: function(data,status,xhr) {
		    	var textarea=$(e.target).find('textarea'); 
		    	textarea.css('background-color','#efe');
		    	setTimeout(function() { textarea.css('background-color','#f6fff6'); },100); 
		    	setTimeout(function() { textarea.css('background-color',''); },200);
	      }
    });		
		return false;		
	});
};
</script>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="settings_announce">
<h1>Редактор стилей</h1>
<form action="" method="get" class="smallform"><fieldset><legend>Выберите стиль</legend>
Стиль: {{ macros.select('style',style,templates) }} <button type="submit">Выбрать</button>  
{% if locked_style %}Стиль <strong><span class="msg_error">закрыт</span></strong> для пользователей. <a href="toggle_style.htm?style={{ style }}&switch=open">Открыть</a>
{% else %}Стиль <strong><span class="msg_ok">доступен</span></strong> для пользователей. <a href="toggle_style.htm?style={{ style }}&switch=close">Заблокировать</a>{% endif %}
<a class="right" href="create_style.htm"><strong> Создать новый стиль</strong></a>
</fieldset></form>
<h3>Файлы шаблонов</h3>
{%  if tpl_files %}<ul class="file_tabs">
{%  for item in tpl_files %}<li{% if item==filename %} class="tab_active"{% endif %}><a href="?mode=tpl&style={{ style }}&filename={{ item }}">{{ item }}</a></li>
{%  endfor %}
</ul>{% endif %}
<h3>Стилевые файлы</h3>
{%  if css_files %}<ul class="file_tabs">
{%  for item in css_files %}<li{% if item==filename %} class="tab_active"{% endif %}><a href="?mode=css&style={{ style }}&filename={{ item }}">{{ item }}</a></li>
{%  endfor %}
</ul>{% endif %}
{% if style!='def' %}<p><a href="copy_file.htm?style={{ style}}">Скопировать файлы из стиля по умолчанию</a></p>{% endif %}
<h3>Редактирование файла {{ filename }}</h3>
<form action="" method="post" class="ibform" id="style_edit_form"><fieldset>
<div><textarea name="data" style="width: 98%" cols="60" rows="25">{{ data }}</textarea></div>
<div class="submit">
<input type="hidden" name="style" value="{{ style }}"/>
<input type="hidden" name="mode" value="{{ mode }}"/>
<input type="hidden" name="filename" value="{{ filename }}"/>
<input type="hidden" name="authkey" value="{{ authkey }}"/>
<button type="submit">Сохранить</button>
</div>
</fieldset></form>


</div>
{% endblock %}