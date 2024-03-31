{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all .category { background: #f8f8ff }
#ib_all .forum:nth-child(odd) { background: #f9f9f9 }
#ib_all .category td:first-child { font-size: 110%; font-weight: bold }
#ib_all fieldset { border : 0 }
</style>
{% endblock %}
{% block content %}
<div id="forums_routes">
<a href="view.htm">&laquo; К списку разделов</a>
<h1>Файл обработки запросов</h1>
<p>Скопируйте содержимое содержимое текстового поля ниже в файл www/.htaccess для того, чтобы переадресация проводилась корректно.
Для корректной работы этого файла необходим Web-сервер Apache 2.x со включенным модулем mod_rewrite.</p>
<textarea rows="20" cols="60" style="width: 100%" readonly="readonly">
{{ routes[0] }}
</textarea>  
</div>
{% endblock %}