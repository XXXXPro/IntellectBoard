{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('post.css') }}" />
<style type="text/css">
#ib_all .mod_accept { background-image: url('icons/mod_ok.gif'); color: #080 }
</style>
{% endblock %}
{% block content %}
<div id="moderate_premod">

<h1>Сообщения на премодерации {% if topic %} в теме &laquo;{{ topic.title }}&raquo;{% else
%}в разделе &laquo;{{ forum.title }}&raquo;{% endif %}</h1>
<div class="posts">
{% for post in posts %}
{% include 'stdforum/p_item.tpl' %}
{% endfor %}
{% if posts|length==0%}<p>На премодерации нет ни одного сообщения!</p>{% endif %}
</div>


</div>
{% endblock %}
