{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="forums_edit_forum">
<a href="view.htm">&laquo; К списку разделов</a>
<h1>{% if forumdata.id %}Настройки раздела &laquo;{{ forumdata.title }}&raquo;{% 
else %}Создание нового раздела{% endif %}</h1>
<form action="" method="post" class="ibform accordion">
{% include type~'/settings.tpl' %}
<fieldset style="border: 0"><div class="submit"><button type="submit">Сохранить</button></div>
{{ macros.hidden('authkey',authkey) }}{{ macros.hidden('type',type) }}{{ macros.hidden('id',forumdata.id) }}
</fieldset></form>
</div>
{% endblock %}