{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style>
#ib_all #settings_subactions tr:nth-child(odd) { background: #eee }
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="settings_subactions">
<h1>Вспомогательные блоки</h1>
{% if not read_only %}<a class="actionbtn" href="subaction_new.htm">Новый блок</a>{% endif %}
<table><col /><col /><col /><col /><col style="width: 4%"/><col style="width: 10%"/>
<thead><tr><th>Блок</th><th>Описание<th>Статус</th><th>Действия</th></tr></thead>
<tbody>{% for item in subactions %}
<tr class="center"><td>{{ item.block }}</td>
<td>{{ item.name }}<br />{{ item.descr2 }}</td>
<td>{% if item.active %}<a href="subaction_change.htm?id={{ item.id }}&amp;enable=0&amp;authkey={{ toggle_key }}" title="Нажмите, чтобы отключить"><i class="fas fa-lightbulb"></i></a>
{% else %}<a href="subaction_change.htm?id={{ item.id }}&amp;enable=1&amp;authkey={{ toggle_key }}"><i class="far fa-lightbulb"></i></a>{% endif %}</td>
<td>{% if not read_only %}<a href="subaction_edit.htm?id={{ item.id }}">Редактировать</a>{% endif %}</td>
{% endfor %}</tbody>
</table>
{% if not read_only %}<a class="actionbtn" href="subaction_new.htm">Новый блок</a>{% endif %}
</div>
{% endblock %}