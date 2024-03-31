{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<link type="text/css" rel="preload" as="style" href="{{ style('addrbook.css') }}" onload="this.rel='stylesheet'" />
{% endblock %}
{% block content %}
<div id="addrbook_view">{% import 'macro.tpl' as macros %}
<h1>Адресная книга</h1>
<form action="add.htm" method="get"><fieldset><legend>Добавление пользователя</legend>
<label>Добавить пользователя: <input type="text" name="logins" size="40" placeholder="Имена, разделенные запятыми" />
<input type="hidden" name="type" value="friend" /><input type="hidden" name="authkey" value="{{ add_key }}" /><input type="submit" value="Добавить" />
</fieldset></form><ul class="friends">
{% for item in friends %}
<li class="fadeout">
<a class="dellink ajax confirm" href="delete.htm?id={{ item.id }}&amp;authkey={{ del_key }}">x</a>
{% if item.relback=="friend" %}{% set classname=" mutual" %}{% 
elseif item.relback=="ignore" %}{% set classname=" nofriend" %}{% 
elseif item.status==1 %}{% set classname=" banned"%}{% 
else %}{% set classname="" %}{% endif %}
<div class="avatar{{ classname }}">{{ macros.avatar(item.id,item.avatar,item.display_name) }}</div>
{{ macros.user(item.display_name,item.id,item.gender) }}<br />
{{ item.title }}<br />
{% if item.visit1 < lasttime %}Последний раз был: {{ item.visit1|longdate }}{% else
%}<span class="online">Онлайн</span>{% endif %}<br /><br />
<a href="{{ url('privmsg/new.htm?to='~item.display_name) }}"><img src="{{ style('icons/priv.gif') }}" alt="Личное сообщение" title="Личное сообщение" /></a>
{% for contact in contacts[item.id] %}
{% if contact.link %}<a href="{{ sprintf(contact.link,contact.value) }}"><img src="{{ style(sprintf(contact.icon,contact.value)) }}" alt="!!{{ contact.c_title~':'~contact.value }}" title="{{ contact.value }}"/></a>{%
else %}<img src="{{ style(sprintf(contact.icon,contact.value)) }}" alt="!!{{ contact.name~':'~contact.value }}" title="{{ contact.value }}"/>{% endif %}
{% endfor %}
</li>
{% endfor %}
{% if friends|length==0 %}<li style="border: 0">Ваша адресная книга пока еще пуста!</li>{% endif %}
</ul>
<br style="clear: both "/>
</div>
{% endblock %}
