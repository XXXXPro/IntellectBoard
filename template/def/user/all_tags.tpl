{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<style type="text/css">
#user_tags { font-size: 1.6em; list-style: none }
#user_tags li { display: inline-block; padding: 0 0.4em; }
#user_tags li a { text-decoration: none }
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="user_search">
<h1>Полный список интересов пользователей</h1>
<ul id="user_tags">
{% for item in tags %}<li style="font-size: {{ 100*(max_tag/(item.count*2)+0.5) }}%"><a href="./tag-{{ item.tagname }}/">{{ item.tagname }}</a> </li>{% endfor %}
</ul>
<a href="{{ url('users/') }}" style="display: block; margin-top: 2em">Вернуться к поиску</a>
</div>
{% endblock %}
