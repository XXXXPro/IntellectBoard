{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('user.css') }}" />
<style>
<!--
.warnings li { padding: 5px 0}
.warnings li:nth-child(even) { background: #eee }
.warn_expired { color: #666 }
.gray { color: #999}
.forum_title { width: 45%; display: inline-block; overflow: hidden }
-->
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="user_view_user">
<a href="users.htm">&laquo;К списку участников</a>
<h1>Профиль пользователя <span class="username">{{ userdata.basic.display_name }}</span></h1>
<div class="userleft">
<div class="pm_buttons">
{{ macros.photo(userdata.basic.id,userdata.basic.photo,userdata.basic.display_name) }}<br />
</div>

{% if userdata.contacts %}
<h3>Контакты</h3>
<ul class="contacts">
{% for item in userdata.contacts %}{% if not item.c_permission or 
(userdata.ext_data.links_mode!='none' and userdata.ext_data.links_mode!='premod') %}  
<li {% if item.icon %}style="background-image: url('{{ style(sprintf(item.icon,item.value)) }}')"{% endif %}>
<a {% if userdata.ext_data.links_mode=='nofollow' %}rel="nofollow" {% endif %}href="{{ sprintf(item.link,item.value)|e }}">{{ sprintf(item.c_title,item.value)|e }}</a></li>
{% endif %}{% endfor %}
</ul>
{% endif %}

</div>
<div class="userright">
<div class="data">
<div class="right">{{ macros.avatar(userdata.basic.id,userdata.basic.avatar,userdata.basic.display_name) }}
<ul>
<li><a href="user_edit.htm?uid={{ userdata.basic.id }}" class="actionbtn" style="color: #040; border-color: #0a0">Редактировать профиль</a></li>
{%  if userdata.ext_data.team %}<li><a href="user_role.htm?uid={{ userdata.basic.id }}" class="actionbtn" style="color: #030; border-color: #080">Роль в команде</a></li>{% endif %}
{% if (not userdata.ext_data.founder or founder) and (userdata.basic.id!=user.id) %}
<li><a href="user_change_group.htm?uid={{ userdata.basic.id }}" class="actionbtn" style="color: #004; border-color: #00a">Изменить группу</a></li>
{% if userdata.basic.status==1 %}<li><a href="user_activate.htm?uid={{ userdata.basic.id }}&amp;authkey={{ activate_key }}" class="actionbtn" style="color: #440; border-color: #aa0">Активировать пользователя</a></li>{% endif %}
{% if userdata.basic.status==0 %}<li><a href="user_ban.htm?uid={{ userdata.basic.id }}&amp;authkey={{ ban_key }}" class="actionbtn" style="color: #440; border-color: #aa0">Изгнать пользователя</a></li>{% else 
%}<li><a href="user_ban.htm?uid={{ userdata.basic.id }}&amp;authkey={{ ban_key }}&amp;unban=1" class="actionbtn" style="color: #040; border-color: #0a0">Вернуть пользователя</a></li>{% endif %}
<li><a href="user_delete.htm?uid={{ userdata.basic.id }}" class="actionbtn" style="color: #400; border-color: #a00">Удалить с форума</a></li>
{% endif %}
</ul>
</div>
<p><span>Уровень доступа</span>
{% if (userdata.basic.title) %}{{ userdata.basic.title }} ({{ userdata.ext_data.name }}){% else %}{{ userdata.ext_data.name }}{% endif %} 
{% if (userdata.basic.status==2 or userdata.ext_data.banned_till) %}&nbsp;&nbsp;<strong style="color: #cc0000">Изгнан {% if userdata.ext_data.banned_till %} до {{ userdata.ext_data.banned_till|longdate }}{% else %}пожизненно{% endif %}</strong>
{% elseif (userdata.basic.status==1) %}&nbsp;&nbsp;&nbsp;<b>Пользователь не активирован!</b>{% endif %}
</p>
<p><span>Зарегистрирован</span>{{ userdata.ext_data.reg_date|longdate }}</p>
{% if lastvisit %}<p><span>Последний раз был здесь</span>{{ lastvisit|longdate }}</p>{% endif %} 
<p><span>Основной адрес Email</span><a href="mailto:{{ userdata.basic.email }}">{{ userdata.basic.email }}</a></p>
<p><span>IP-адрес при регистрации</span><a href="https://nic.ru/whois/?searchWord={{ userdata.ext_data.reg_ip }}">{{ userdata.ext_data.reg_ip }}</a></p>
<p><span>Подпись</span>{{ userdata.basic.signature|raw }}</p>

<p><span>Всего сообщений</span><strong>{{ userdata.ext_data.post_count|incline('%d сообщение','%d сообщения','%d сообщений') }}</strong>{% if userdata.ext_data.post_count %}, из них {{ valued_count }} ценных, {{ sprintf("%2.0f",flood_posts/userdata.ext_data.post_count*100) }}% флуда</p>
<p><span>Цепочки личных сообщений</span><strong>{{ pm_total }}</strong> всего, <strong>{{ pm_lastday }}</strong> за последние сутки</p>
<p><span>Рейтинг</span><strong>{{ userdata.ext_data.rating }}</strong></p>
{% endif %}
<p><a href="view_visited.htm?uid={{userdata.basic.id}}">Список просмотренных тем</a></p>
</div>

<h3>Предупреждения и штрафные баллы {%  if userdata.ext_data.warnings %}(всего {{ userdata.ext_data.warnings }}){% endif %}</h3>
<ul class="warnings">
{% if warnings|length>0 %}
{% for item in warnings %}<li{% if item.warntill<now %} class="warn_expired"{% endif %}> 
{{ item.value|incline('<b>%d</b> штрафной балл','<b>%d</b> штрафных балла','<b>%d</b> штрафных баллов')|raw }},  
<span class="gray">действует</span> {% if item.warntill!=4294967295 %}до <b>{{ item.warntill|longdate }}</b>{% else %}<b>бессрочно</b>{% endif %}.<br />
<span class="gray">Вынесено модератором </span>{{ macros.user(item.moderator,item.moderator_id) }} {{ item.warntime|longdate }}. 
<a href="user_delete_warning.htm?uid={{ userdata.basic.id }}&amp;warn_id={{ item.id }}&amp;authkey={{ warn_key }}">Удалить</a><br />
<span class="gray">Комментарий модератора:</span> {{ item.descr }}<br />
{% if item.post_hurl %}Ссылка на сообщение: <a href="{{ url(item.post_hurl) }}">{{ item.post_hurl }}</a>{% endif %}</li>
{% endfor %}
{% if warnings|length==10 %}<a href="?all_warnings=1">Показать все предупреждения</a>{% endif %}
{% else %}
<li>У пользователя нет предупрежденй.</li>
{% endif %}
<ul>

<h3>Личные разделы</h3>
<ul class="personal_forums">
{% if personal_forums|length>0 %}
{% for item in personal_forums %}
<li><span class="forum_title"><a href="{{ url(item.hurl) }}">{{ item.title }}</a></span>
<a href="../forums/edit_forum.htm?id={{ item.id }}">Редактировать</a> <a href="../forums/delete_forum.htm?id={{ item.id }}" style="color: red">Удалить</a></li>
{% endfor %}
{% else %}
<li>У пользователя нет ни одного личного раздела</li>
{% endif %}
</ul>

</div>
<br style="clear: both" />
</div>
{% endblock %}
