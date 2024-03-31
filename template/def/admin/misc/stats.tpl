{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="misc_stats">
<form action="" method="get" style="float: right"><fieldset style="border: #ccc 1px solid; padding: 5px 10px"><legend>Выбор периода</legend>
<label>Выберите период для отображения статистики: {{ macros.select('period',period,{1:'1 день',3:'3 дня',7:'1 неделя',14:'2 недели',30:'1 месяц',90:'3 месяца',180:'6 месяцев',365:'1 год'}) }}</label>
<button type="submit">Показать</button>
</fieldset></form>
<h1>Статистика</h1>
<h3>Показатели динамики развития форума</h3>
<table class="ibtable"><col style="width: 40%" /><col style="width: 20%" /><col style="width: 20%" /><col style="width: 20%" />
<thead><tr><th>Показатель</th>
<th>Значение за период <br />с {{ time1|shortdate }} <br />по {{ (time2-1)|shortdate }}</th>
<th>Значение за период <br />с {{ time2|shortdate }} <br />по {{ now|shortdate }}</th>
<th>Изменение</th></tr></thead>
<tbody>
<tr><td>Регистрация новых пользователей</td><td style="text-align: center">{{ stats.users[1] }}</td>
<td style="text-align: center">{{ stats.users[2] }}</td>
<td  style="text-align: center">{% if stats.users[3]<0 %}<span class="msg_error">
{{ stats.users[3] }}</span>{% else %}<span class="msg_ok">{{ stats.users[3] }}</span>{% endif %}</td></tr>
<tr><td>Отправленные сообщения</td><td style="text-align: center">{{ stats.posts[1] }}</td>
<td style="text-align: center">{{ stats.posts[2] }}</td>
<td  style="text-align: center">{% if stats.posts[3]<0 %}<span class="msg_error">
{{ stats.posts[3] }}</span>{% else %}<span class="msg_ok">{{ stats.posts[3] }}</span>{% endif %}</td></tr>
<tr><td>Новые темы</td><td style="text-align: center">{{ stats.topics[1] }}</td>
<td style="text-align: center">{{ stats.topics[2] }}</td>
<td  style="text-align: center">{% if stats.topics[3]<0 %}<span class="msg_error">
{{ stats.topics[3] }}</span>{% else %}<span class="msg_ok">{{ stats.topics[3] }}</span>{% endif %}</td></tr>
<tr><td>Активные темы</td><td style="text-align: center">{{ stats.active_topics[1] }}</td>
<td style="text-align: center">{{ stats.active_topics[2] }}</td>
<td  style="text-align: center">{% if stats.active_topics[3]<0 %}<span class="msg_error">
{{ stats.active_topics[3] }}</span>{% else %}<span class="msg_ok">{{ stats.active_topics[3] }}</span>{% endif %}</td></tr>
<tr><td>Сообщений на тему</td><td style="text-align: center">{{ stats.per_topic[1] }}</td>
<td style="text-align: center">{{ stats.per_topic[2] }}</td>
<td  style="text-align: center">{% if stats.per_topic[3]<0 %}<span class="msg_error">
{{ stats.per_topic[3] }}</span>{% else %}<span class="msg_ok">{{ stats.per_topic[3] }}</span>{% endif %}</td></tr>
<tr><td>Сообщений на пользователя</td><td style="text-align: center">{{ stats.per_user[1] }}</td>
<td style="text-align: center">{{ stats.per_user[2] }}</td>
<td  style="text-align: center">{% if stats.per_user[3]<0 %}<span class="msg_error">
{{ stats.per_user[3] }}</span>{% else %}<span class="msg_ok">{{ stats.per_user[3] }}</span>{% endif %}</td></tr>
<tr><td>Просмотры тем</td><td style="text-align: center">{{ stats.visits[1] }}</td>
<td style="text-align: center">{{ stats.visits[2] }}</td>
<td  style="text-align: center">{% if stats.visits[3]<0 %}<span class="msg_error">
{{ stats.visits[3] }}</span>{% else %}<span class="msg_ok">{{ stats.visits[3] }}</span>{% endif %}</td></tr>
<tr><td>Личные сообщения</td><td style="text-align: center">{{ stats.pm[1] }}</td>
<td style="text-align: center">{{ stats.pm[2] }}</td>
<td  style="text-align: center">{% if stats.pm[3]<0 %}<span class="msg_error">
{{ stats.pm[3] }}</span>{% else %}<span class="msg_ok">{{ stats.pm[3] }}</span>{% endif %}</td></tr>
</tbody>
</table>
<p style="font-size: 120%; text-align: center"><a href="stats_graph.htm">График основных показателей статистики</a></p>
<p><small>Примечания: <ol>
<li>Удаленные объекты в статистике не учитываются.</li>
<li>Расчеты по темам и сообщениям ведутся для всех разделов без учета опции &laquo;статистически значимый раздел&raquo;.</li>
<li>В &laquo;активных темах&raquo; учитываются те темы, в которых последнее сообщение было отправлено в указанный период.</li>
<li>Просмотры тем считаются только для зарегистрированных пользователей и только в том случае, если последний заход в тему попадает в указанный период.</li>
<li>Соотношения &laquo;сообщений на пользователя&raquo; и &laquo;сообщений в теме&raquo; считаются для всех существующих тем и пользователей, существоваших в данном периоде, а не только для вновь созданных.</li></ol>
</small></p>
{% if visited_topics %}
<div style="width: 49%; display: inline-block; vertical-align: top">
<h3>25 самых посещаемых тем</h3><ul style="list-style: none">
{% for item in visited_topics %}
<li><a href="{{ url(item.full_hurl) }}">{{ item.title }}</a>, {{ item.visits|incline('%d заход','%d захода','%d заходов') }}</li>
{% endfor %}</ul></div>
{% endif %}
{% if visited_topics %}
<div style="width: 49%; display: inline-block; vertical-align: top">
<h3>25 самых активных тем</h3><ul style="list-style: none">
{% for item in active_topics %}
<li><a href="{{ url(item.full_hurl) }}">{{ item.title }}</a>, {{ item.active_posts|incline('%d сообщение','%d сообщения','%d сообщений') }}</li>
{% endfor %}</ul></div>
{% endif %}
</div>
{% endblock %}