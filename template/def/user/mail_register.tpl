{% extends 'mail.tpl' %}
{% block content %}
<p>Приветствуем вас, {{ regdata.login }}!</p>

<p>Вы зарегистрировались на форуме &laquo;<a href="{{ http(url('')) }}">{{ get_opt('site_title') }}</a>&raquo;</p>

<p>Ваш логин : {{ regdata.login }}<br />
Ваш пароль: {{ uncrypt_password }}</p>

<p>Для подтверждения вашей регистрации перейдите по следующей ссылке: <br />
<a href="{{ keylink }}">{{ keylink }}</a></p>

<p>Если вы не регистрировались на этом форуме, просто проигнорируйте это письмо.</p>
{% endblock %}