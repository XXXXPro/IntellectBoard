{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('post.css') }}" />
<style type="text/css">
#ib_all .mod_accept { background-image: url('icons/mod_ok.gif'); color: #080 }
</style>
{% endblock %}
{% block content %}
<div id="moderate_trashbox">

<h1>Удаленные сообщеня {% if topic %} темы &laquo;{{ topic.title }}&raquo;{% else
%}раздела &laquo;{{ forum.title }}&raquo;{% endif %}</h1>

<div class="posts">
{% for post in posts %}
{% include 'stdforum/p_item.tpl' %}
{% endfor %}
{% if posts|length==0%}<p>В корзине нет ни одного сообщения!</p>{% endif %}
</div>

</div>
{% endblock %}
