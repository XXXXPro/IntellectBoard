{% import 'macro.tpl' as macros %}
{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all #online_view .ibtable caption { text-align: left; font-size: 120%; margin: 20x }
</style>
{% endblock %}
{% block content %}
<div id="online_view">
<table class="ibtable">
<caption>За последние {{ online_time|incline('%d минуту','%d минуты','%d минут') }} на сайте были:</caption>
<col style="width: 10%"/><col style="width: 15%" />{% if is_admin %}<col style="width: 10%">{% endif %}<col />
<thead><th>Время</th><th>Имя</th>{% if is_admin %}<th>IP</th>{% endif %}<th>Действие</th></thead>
<tbody>{% for item in online_users %}
{% if item.type==-2 %}{% set class='online_team' %}
{% elseif item.type==-1 %}{% set class='online_user' %}
{% elseif item.type>0 %}{% set class='online_bot' %}
{% else %}{% set class='online_guest' %}{% endif %}
<tr{% if item.type==-128 %} style="font-style:italic"{% endif %}><td class="center {{ class }}">{{ item.visittime|longdate }}</td>
<td class="center"> 
{% if item.type<=0 %}{{ macros.user(item.display_name,item.uid) }}
{% else %} {{ item.bot_name }}{% endif %}</td>
{% if is_admin %}<td class="center"><a href="https://nic.ru/whois/?searchWord={{ item.ip }}">{{ item.ip }}</a></td>{% endif %}
<td>{{ item.text|raw }}</td></tr>
{%	endfor %}</tbody></table>
</div>
{% endblock %}