{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<style type="text/css">
#ib_all #misc_team table { border: #dde 1px solid; }
#ib_all #misc_team table td { text-align: center; padding-bottom: 10px }
#ib_all #misc_team table tr:nth-child(even) { background: #f4f4ff }
#ib_all #misc_team table td:nth-child(2) { text-align: left }
@media screen and (max-width: 480px) {
  #ib_all #misc_team .smallphoto { display: none }
}
</style>
{% endblock %}
{% block content %}
<div id="misc_team">
<h1>Команда нашего сайта</h1>
<table class="design"><col style="width: 33%" /><col />
<tbody>
{% for uid,data in team_users %}
<tr>
<td>{{ macros.user(data.display_name,uid) }}<br />
{{ data.user_title }}<br />
{{ macros.avatar(data.uid,data.avatar,data.display_name) }}<br />
{% if data.location %}Откуда: {{ data.location }}<br />{% endif %}
Регистрация: {{ data.reg_date|longdate }}<br />
{{ macros.photo(data.uid,data.photo,data.display_name,'smallphoto lightbox') }}
</td>
<td>
{{ data.text|raw }}
<p><strong>Должности:</strong> {{ data.roles|raw }}.</p></td></tr>
{% endfor %}
</tbody></table>
</div>
{% endblock %}
