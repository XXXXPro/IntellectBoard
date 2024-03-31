{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<div id="micro_newtopic">
{% include 'stdforum/postform.tpl' %}
</div>
{% endblock %}
