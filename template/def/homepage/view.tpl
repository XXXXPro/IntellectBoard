{% import 'macro.tpl' as macros %}
{% extends 'main.tpl' %}
{% block css %}
{% if subforums %}
<link rel="stylesheet" type="text/css" href="{{ style('forums.css') }}" />
{% endif %}
{% endblock %}
{% block content %}
<div id="statpage_view">

{{ macros.sub_block(IntB_subactions['action_start']) }}

{{ static_text|raw|nl2br }}
{% if mod_link %}
<div class="mod_actions"><a href="edit.htm">Редактировать раздел</a></div>
{% endif %}
</div>

{{ macros.sub_block(IntB_subactions['action_end']) }}

{% if subforums %}
<table class="ibtable categories"><col /><col /><col /><col /><col />
<thead><tr><th class="cat_icon"></th><th class="cat_name">Название</th><th class="cat_views">Просмотров</th>
<th class="cat_topics">Тем и сообщений</th><th class="cat_last">Последнее сообщение</th></tr></thead>
<tbody>
{% for forum in subforums %}
{% include var ~ forum.module ~ '/title.tpl' %}
{% endfor %}
</tbody>
</table>
{% endif %}
{% endblock %}
