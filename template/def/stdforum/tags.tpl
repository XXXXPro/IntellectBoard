{% extends 'main.tpl' %}
{% block css %}
<link rel="preload" as="style" type="text/css" href="{{ style('topic.css') }}" onload="this.rel='stylesheet'"/>
{% endblock %}
{% block content %}
<div id="stdforum_tags">
<h1>Список тегов в разделе «{{ forum.title }}»</h1>
{% if tags|length>0 %}
{% include 'stdforum/taglist.tpl' with { 'data' : tags } %}
{% else %}
<p class="notags">На данный момент нет ни одного тега.</p>
{% endif %}
</div>
{% endblock %}