{% extends 'mail.tpl' %}
{% block content %}
<p>Приветствуем вас, <span class="username">{{ user.display_name }}!</span></p>
<p>Пользователь <span class="username">{{ sender }}</span> отправил отправил 
вам новое личное соообщение с темой &laquo;<b>{{ thread.title }}</b>&raquo;. </p>

{% if user.email_fulltext %}
<p>Текст сообщения: </p><hr /><br />
{{ parsed|raw }}
<br /><br /><hr />
{% endif %}

<p>Перейти к сообщению и ответить: <a href="{{ http(url('privmsg/'~thread.id~'/')) }}">{{ http(url('privmsg/'~thread.id~'/')) }}</a></p>

<p>Вы получили это сообщение потому что являетесь зарегистрированным пользователем форума &laquo;{{ get_opt('site_title') }}&raquo;.<br />
Если вы не хотите получать эти уведомления, отключите их в <a href="{{ http(url('user/update.htm')) }}">настройках вашего профиля</a>.
</p>
{% endblock %}