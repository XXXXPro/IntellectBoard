{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="users_delete_group">
<h1>Удаление уровня доступа</h1>
<p><a href="groups.htm">&laquo; К списку уровней</a></p>
<form action="" class="ibform" method="post"><fieldset><legend>Удаление уровня</legend>
<div><label><span style="color: #c00">Удалить уровень доступа &laquo;{{ name }}&raquo;?</span><input type="checkbox" name="confirm" value="1" /></label></div>
<div><span>Перенести всех пользователей в группу</span>{{ macros.select('new_level',0,groups) }}</div>
 <div class="submit"><button type="submit">Удалить</button>
{{ macros.hidden('authkey',authkey)}}{{ macros.hidden('level',level) }}</div>
</fieldset></form>

</div>
{% endblock %}