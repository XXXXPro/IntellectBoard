{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}<link rel="stylesheet" type="text/css" href="{{ style('addrbook.css') }}" />
{% endblock %}
{% block content %}
<div id="addrbook_view">{% import 'macro.tpl' as macros %}
<h1>Список игнорируемых пользователей</h1>
<form action="add.htm" method="get"><fieldset><legend>Добавление пользователя</legend>
<label>Добавить пользователя: <input type="text" name="logins" size="40" placeholder="Имена, разделенные запятыми" />
<input type="hidden" name="type" value="ignore" /><input type="hidden" name="authkey" value="{{ add_key }}" /><input type="submit" value="Добавить" />
</fieldset></form><ul class="ignored">
{% for item in ignored %}
<li class="fadeout">
<a class="dellink confirm ajax" href="delete.htm?id={{ item.id }}&amp;authkey={{ del_key }}">x</a>
<div class="avatar">{{ macros.avatar(item.id,item.avatar,item.display_name) }}</div>
{{ macros.user(item.display_name,item.id,item.gender) }}<br />
{{ item.title }}
</li>
{% endfor %}
{% if ignored|length==0 %}<li style="border: 0">Ваш черный список пуст.</li>{% endif %}
</ul>
<br style="clear: both "/>
</div>
{% endblock %}
