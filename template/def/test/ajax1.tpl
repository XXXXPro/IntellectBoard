{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block content %}
{% if not intb.is_ajax %}
<script type="text/javascript"><!--
function ajax_test() {
  document.getElementById('ajax_field').innerHTML='';
  xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() { 
     if (this.readyState == 4) {
        // если запрос закончил выполняться
        if(this.status == 200) {
          console.log(this.responseText);
          document.getElementById('ajax_field').innerHTML=this.responseText;
        }
        else document.getElementById('ajax_field').innerHTML='Ошибка обращения к серверу: '+this.status;
      }
   }
   xmlhttp.open('GET', '../test/ajax.php?ajax=1', true); 
   xmlhttp.send(null);  // отослать запрос
   return false;   
}
--></script>
<h1>Проводим AJAX-тест IntB</h1>
<p>Эта часть страницы сгенерирована обычным образом.</p>
<p>Сначала в поле ниже будет выведено содержимое $_SERVER при обычном запрое</p>
<p>А по нажатию <a href="#" onclick="return ajax_test()">на ссылку</a> оно обновится на подгруженное с помощью AJAX<p>
<div id="ajax_field">
{% else %}
А вот эту часть мы загрузили уже с помощью AJAX:::<div>
{% endif %}
<pre style="border: #ccc 1px sold; height: 100px; overflow: auto">{{ server }}</pre>
А это общая часть, которая выводится и в обычно шаблоне, и в AJAX!
</div>
{% endblock %}
