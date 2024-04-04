{% extends 'mail.tpl' %}
{% block content %}
<p>Приветствуем вас, <span class="username">{{ user.display_name }}!</span></p>
<p>Пользователь <span class="username">{{ post.author }}</span> создал тему 
&laquo;<b>{{ topic.title }}</b>&raquo; в разделе &laquo;{{ forum.title }}&raquo;. </p>

{% if user.email_fulltext %}
<p>Текст первого сообщения темы: </p><hr /><br />
{{ parsed|raw }}
<br /><br /><hr />
{% endif %}

<p>Перейти к сообщению: <a href="{{ http(url(topic.full_hurl)) }}post-{{post.id}}.htm">{{ http(url(topic.full_hurl)) }}post-{{post.id}}.htm</a></p>

<p>Вы получили это сообщение потому что являетесь зарегистрированным пользователем форума &laquo;{{ get_opt('site_title') }}&raquo;
{% if user.type=='forum' and user.oid!=0 %}
и подписались в настройках на получение уведомлений о сообщениях во всем разделе. <br />
<small><a href="{{ http(url('bookmark/unsubscr.htm?unsubscribe_forum='~user.oid~'&authkey='~unsubscribe_key)) }}">Отписаться от уведомлений о новых сообщениях в разделе.</a>
{% elseif user.type=='forum' and user.oid==0 %}
и подписались в настройках на получение уведомлений о всех сообщениях на форуме. <br />
<small><a href="{{ http(url('bookmark/unsubscr.htm?unsubscribe_forum=0&authkey='~unsubscribe_key)) }}'">Отписаться от уведомлений о новых сообщениях на всем форуме</a>.</small>
{% endif %}
</p>
{% endblock %}