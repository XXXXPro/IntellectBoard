{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('post.css') }}" />
<style>
#ib_all .postform .photo_item { display: flex; align-items: center; }
#ib_all .postform .photo_item>* { margin: 0 5px }
#ib_all .postform .photo_item input[type="text"] { flex-grow: 1; align-self: center; }
#ib_all .postform .photo_item .photo_delete { color: #800; font-size: 1.4em }
</style>
{% endblock %}
{% block content %}
<div id="gallery_topic_reply">
{% if preview %}
{% set post = preview %}
{% include 'stdforum/p_item.tpl' %}
{% endif %}
{% include 'gallery/postform.tpl' %}
</div>
{% endblock %}
