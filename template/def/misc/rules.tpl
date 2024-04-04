{% extends intb.is_ajax ? 'ajax.tpl' : onlytext ? 'onlytext.tpl' : 'main.tpl' %}
{% block css %}
<script type="text/css">
#ib_all .rules textarea { margin: 10px; padding: 5px; width: 96% }
#ib_all .rules label { margin: 5px 0}
#ib_all .rules .submit { text-align: center; }
#ib_all .rules .accept { font-size: 120%; margin: 5px 1% }
#ib_all .rules .submit button { font-size: 140%; padding: 3px 10px; font-weight: bold }
</script>
{% endblock %}
{% block content %}
{% if onlytext %}<h2>Правила {% if forum %}раздела «{{ forum.title }}»{% else %}форума{% endif %}</h2>{% endif %}
<div id="misc_rules">
{{ rules|raw|nl2br }}
</div>
{% endblock %}
