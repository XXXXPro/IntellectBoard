{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<style type="text/css"><!--
.f_item { padding: 4px; border: #cce 1px solid; background: #dde; color: #888 }
.f_item a { font-size: 110%; font-weight: bold; text-decoration: none } 
.t_item { padding: 3px; border: #eee 1px solid; color: #888 }
--></style>
{% endblock %}
{% block content %}
<div id="bookmark_newposts">

{% set prevforum = 0 %}
{% for item in topics %}
{% if item.fid!=prevforum %}
<div class="f_item"><a href="">{{ item.forum_title }}</a> {{ item.forum_descr }}</div>
{% set prevforum = item.fid %}
{% endif %}
<div class="t_item"><a href="{{ url(item.full_hurl) }}new.htm">{{ item.title }}</a> {{ item.last_post_time|longdate }}, {{ macros.user(item.last_poster,item.last_poster_id) }}</div>
{% endfor %}

</div>
{% endblock %}
