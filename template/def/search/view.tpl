{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all #search_view .ibform div { background: none }
.search_col { display: inline-block; vertical-align: top; width: 30%; margin-right: 2% }
#search_third label { display: block }
</style>
{% endblock %}
{% block content %}
<div id="search_view">
{% include 'search/form.tpl' %}
</div>
{% endblock %}
