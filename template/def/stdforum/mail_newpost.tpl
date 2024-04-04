{% extends 'mail.tpl' %}
{% block content %}
<p>Приветствуем вас, <span class="username">{{ user.display_name }}!</span></p>
<p>Пользователь <span class="username">{{ post.author }}</span> отправил новое 
сообщение в тему &laquo;<b>{{ topic.title }}</b>&raquo;, на которую вы подпипсаны. </p>

{% if user.email_fulltext %}
<p>Текст сообщения: </p><hr /><br />
{{ parsed|raw }}
<br /><br /><hr />
{% endif %}

<p>Перейти к сообщению: <a href="{{ http(url(topic.full_hurl)) }}post-{{post.id}}.htm">{{ http(url(topic.full_hurl)) }}post-{{post.id}}.htm</a></p>

<p>Вы получили это сообщение потому что являетесь зарегистрированным пользователем форума «{{ get_opt('site_title') }}»
{% if user.type=='forum' and user.oid!=0 %}
и подписались в настройках на получение уведомлений о сообщениях в разделе «{{ forum.title }}». </p>
<p><a href="{{ http(url('bookmark/unsubscr.htm?unsubscribe_forum='~user.oid~'&authkey='~unsubscribe_key)) }}">Отписаться от уведомлений о новых сообщениях в разделе</a></p>
{% elseif user.type=='forum' and user.oid==0 %}
и подписались в настройках на получение уведомлений о всех сообщениях на форуме. </p>
<p><a href="{{ http(url('bookmark/unsubscr.htm?unsubscribe_forum='~user.oid~'&authkey='~unsubscribe_key)) }}">Отписаться от уведомлений о новых сообщениях на форуме</a></p>
{% else %}  
подписались на получение уведомлений в этой теме.</p>
<p><a href="{{ http(url('bookmark/unsubscr.htm?subscribe='~topic.id~'&authkey='~unsubscribe_key)) }}">Отписаться от этой темы</a></p>
{% endif %}
<p><small><a href="{{ http(url('user/unsubscribe_all.htm?authkey='~unsubscribe_key2)) }}">Отписаться от всех уведомлений форума полностью</a></small></p>
{% endblock %}