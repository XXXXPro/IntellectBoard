{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="view_visited">
<a href="user_view.htm?uid={{ udata.id }}">&laquo; К профилю пользователя</a>
<h1>Темы, просмотренные пользователем <span class="username">{{ udata.display_name }}</span></h1>
{% if viewed_topics %}{% set prev_forum = -1 %}
{% for topic in viewed_topics %}
{% if topic.fid!=prev_forum %}<h3>{{ topic.f_title}}</h3>{% endif %}
<p><a href="{{ url(topic.full_hurl) }}">{{ topic.title }}</a> — {{ topic.visit1|longdate }}</p>
{% set prev_forum=topic.fid %}
{% endfor %}
{% else %}
<p>Пользователь не просмотрел ни одной темы или отметил весь форум как прочитанный</p>
{% endif %}
</div>
{% endblock %}