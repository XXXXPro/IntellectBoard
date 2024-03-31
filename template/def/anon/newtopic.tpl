{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block content %}
<div id="topic_reply">
{% include 'anon/form.tpl' %}
</div>
{% endblock %}
