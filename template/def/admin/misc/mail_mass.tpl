{% extends 'mail.tpl' %}
{% block content %}

{{ text|raw }}

<p>Вы получили это сообщение потому что являетесь зарегистрированным пользователем форума
&laquo;<a href="{{ http(url('')) }}">{{ get_opt('site_title') }}</a>&raquo;
Настроить подписку можно в <a href="{{ http(url('user/update.htm')) }}">профиле пользователя</a>.</p>
<p></p>
<p><a href="{{ http(url('user/unsubscribe_mass.htm?authkey='~key1)) }}">Отписаться от этой рассылки</a></p>
<hr>
<p><small><a href="{{ http(url('user/unsubscribe_all.htm?authkey='~key2)) }}">Отписаться от всех уведомлений форума</a></small></p>
{% endblock %}
