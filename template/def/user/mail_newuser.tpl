{% extends 'mail.tpl' %}
{% block content %}
<p>Приветствуем вас, {{ admin_name }}!</p>

<p>На форуме
&laquo;<a href="{{ http(url('')) }}">{{ get_opt('site_title') }}</a>&raquo;,
администратором которого вы являетесь, зарегистрировался новый пользователь.</p>

<p>Логин : {{ login }}<br />
EMail : {{ email }}</p>

{% if activate_mode==2 %}
<p>В настоящее время на форуме активация пользователя выполняется только администраторами. <br />
Вам необходимо зайти в Центр Администрирования и вручную активировать этого пользователя, чтобы он мог войти на форум.</p>
{% endif %}
{% endblock %}