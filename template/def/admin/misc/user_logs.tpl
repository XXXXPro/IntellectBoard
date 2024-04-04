{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all #misc_user_logs .ibtable thead { background: #BFDDFF; }
#ib_all #misc_user_logs .ibtable tr { text-align: center }
#ib_all #misc_user_logs .gray { color: #666; font-size: 80%}
/*#ib_all #misc_user_logs a.gray {white-space: pre}
#ib_all #misc_user_logs td { overflow: hidden; }*/ 
#ib_all #misc_user_logs tbody tr:nth-child(odd) { background: #eef8ff }
#ib_all #misc_user_logs td:last-child { text-align: left }
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="misc_user_logs">
<h1>Журнал действий пользователей</h1>
<div><form action="params_user_log.htm" method="get" class="smallform">
<fieldset><legend>Настройки отображения журнала</legend>
Показывать данные за период <input type="date" class="date" name="opts[start_date]" value="{{ opts.start_date }}" size="14" style="margin-right: 24px"/>—
<input type="date" class="date" name="opts[end_date]" value="{{ opts.end_date }}" size="14" style="margin-right: 24px"/> 
по {{ macros.input('opts[perpage]',opts.perpage,3) }} записей на страницу <small>(в журналах используется время сервера, как правило это GMT)</small><br />
<strong>Выбрать записи:</strong> (все критерии выбора объединяются через логическое "И")<br />
по имени пользователя <input type="text" name="opts[names]" value="{{ opts.names }}" size="32" title="Можно указать несколько имен через запятую" />, 
идентификатору cookie <input type="text" name="opts[cookie]" value="{{ opts.cookie }}" size="12" maxlength="12" />, 
IP-адресу <input type="text" name="opts[ip]" value="{{ opts.ip }}" size="15" title="Возможен поиск по неполному совпадению" />,<br /> 
действию <input type="text" name="opts[action]" value="{{ opts.action }}" size="12" />, 
URL страницы <input type="text" name="opts[url]" value="{{ opts.url }}" size="20" title="Возможен поиск по неполному совпадению" />, 
полю UserAgent <input type="text" name="opts[agent]" value="{{ opts.agent }}" size="20" title="Возможен поиск по неполному совпадению" />  
полю Referer <input type="text" name="opts[referer]" value="{{ opts.referer }}" size="20" title="Возможен поиск по неполному совпадению" />.<br />
<button type="submit" name="submit">Показать</button>
<button type="submit" name="clear">Сброс</button>
</fieldset>
</form></div>
<div class="pages right">
{% if (pagedata.pages==1) %}Одна страница{% else %}
Страницы: <ul>
{% for i in 1..pages %}
{% if i==pagedata.page %}<li><b>{{ i }}</b></li>{% else %}<li><a href="?page={{ i }}">{{ i }}</a></li>
{% endif %}
{% endfor %}
</ul>
{% endif %}
</div>
<table class="ibtable">
<col style="width: 7%"/><col style="width: 7%"/><col style="width: 10%"/><col style="width: 24%"><col style="width: 18%"/><col />
<thead><tr><th>Дата<br />Время</th><th>Действие</th><th>Пользователь<br />IP-адрес</th>
<th>Идентификатор cookie<br />User-Agent</th><th>Источник (referer)</th><th>URL<br />Действие</th></tr>
</thead><tbody>
{% for item in log_items %}
<tr><td>{{ item[9] }}<br />{{ item[0] }}</td><td>{{ item[2] }}</td>
<td><span class="username">{{ item[3] }}</span><br />{{ item[4] }}</td><td>{{ item[6] }}<span class="gray">, {{ item[5] }}</span></td>
<td>{% if item[7] %}<a href="{{ item[7] }}" class="gray">{{ item[7] }}</a>{% endif %}</td>
<td>{{ item[8]|raw }}<br /><a href="{{ item[1] }}" class="gray">{{ item[1] }}</a></td></tr>
{% endfor %}
</tbody></table>
<div class="pages right">
{% if (pagedata.pages==1) %}Одна страница{% else %}
Страницы: <ul>
{% for i in 1..pages %}
{% if i==pagedata.page %}<li><b>{{ i }}</b></li>{% else %}<li><a href="?page={{ i }}">{{ i }}</a></li>
{% endif %}
{% endfor %}
</ul>
{% endif %}
</div>
<p>Всего записей, удовлетворяющих заданным условиям: {{ total }}</p>
{% endblock %}