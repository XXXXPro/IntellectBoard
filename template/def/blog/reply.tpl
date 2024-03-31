{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('post.css') }}" />
{% endblock %}
{% block content %}
<div id="blog_topic_reply">
{% if preview %}
{% set post = preview %}
{% include 'stdforum/p_item.tpl' %}
{% endif %}
{% include 'stdforum/postform.tpl' %}
</div>
{% endblock %}
