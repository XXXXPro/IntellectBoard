{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all ul.users, #ib_all ul.ignored { list-style: none; margin-top: 12px; display: flex; flex-wrap: wrap }
#ib_all ul.users li { width: 31%; border: #eee 1px solid; border-radius: 16px; font-size: 90%; margin: 0 1% 1em 0; color: #888 }
#ib_all ul.users li .username { font-size: 120% }
#ib_all ul.users li .online { color: #0b0; font-weight: bold }
#ib_all ul.users div.avatar { float: left; margin: 10px; height: 100%; text-align: center; width: {{ get_opt('userlib_avatar_x') }}px }
#ib_all ul.users div.avatar img { border: #ccc 3px solid }
#ib_all ul.users div.mutual img { border: #090 3px solid }
#ib_all ul.users div.nofriend img { border: #900 3px solid }
#ib_all ul.users div.banned img { border: #990 3px solid }
#ib_all ul.users .dellink { float: right; display: block; line-height: 6em; padding: 0 8px; text-decoration: none; font-size: 150%; color: #c00 }
#ib_all ul.users .dellink:hover { background: #ffe }
#ib_all .contact_icon { padding: 2px }
@media screen and (max-width: 1024px) {
#ib_all ul.users li { width: auto; display: block }
}
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="user_search">
<h1>Результаты поиска пользователей</h1>
<div class="pages">{{ macros.pages(pagedata) }}</div>
<ul class="users">
{% for item in users %}
<li>
{% if item.relback=="friend" %}{% set classname=" mutual" %}{% 
elseif item.relback=="ignore" %}{% set classname=" nofriend" %}{% 
elseif item.status==1 %}{% set classname=" banned"%}{% 
else %}{% set classname="" %}{% endif %}
<div class="avatar{{ classname }}">{{ macros.avatar(item.id,item.avatar,item.display_name) }}</div>
{{ macros.user(item.display_name,item.id,item.gender) }}<br />
{{ item.title }}<br />
Зарегистрирован: {{ item.reg_date|longdate }}<br />
Всего сообщений: {{ item.post_count }}, рейтинг: {{ item.rating }}<br />
{% if not item.hidden %}
{% if item.visit1 < lasttime %}{% if item.visit1 %}Последний раз был: {{ item.visit1|longdate }}{% else %}{% if item.gender=='F' %}Давно не появлялась{% else %}Давно не появлялся{% endif %}{% endif %}{% else
%}<span class="online">Онлайн</span>{% endif %}<br />{% endif %}
{% if item.location %}Откуда: {{ item.location }}{% endif %}
<div class="contact_icons">
<a class="contact_icon" href="{{ url('privmsg/new.htm?to='~item.display_name) }}"><i class="fas fa-envelope"></i></a>
{% for contact in item.contacts %}
{% if contact.icon %}
{% if contact.link %}<a class="contact_icon" href="{{ sprintf(contact.link,contact.value) }}"><img src="{{ style(sprintf(contact.icon,contact.value)) }}" alt="{{ contact.c_title~':'~contact.value }}" title="{{ contact.value }}"/></a>{%
else %}<span class="contact_icon"><img class="contact_icon" src="{{ style(sprintf(contact.icon,contact.value)) }}" alt="{{ contact.name~':'~contact.value }}" title="{{ contact.value }}"/></span>{% endif %}
{% endif %}
{% endfor %}
</div>
</li>
{% endfor %}
{% if not users %}<li style="border: 0">По вашему запросу пользователей не найдено</li>{% endif %}
</ul>
<div class="pages">{{ macros.pages(pagedata) }}</div>
<p><a href="{{ url('users/') }}">&laquo; Вернуться к поиску</a></p> 
</div>
{% endblock %}
