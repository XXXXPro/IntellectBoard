{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<script  type="text/javascript">IntB_css('{{ style('post.css') }}');</script>
<noscript><link type="text/css" href="{{ style('post.css') }}" rel="stylesheet" /></noscript>
<style type="text/css">

</style>
{% endblock %}
{% block content %}
<div id="misc_levels">
<h1>Уровни доступа участников форума</h1>
<table class="ibtable">
<col style="width: 12%" /><col style="width: 29%" /><col />
<thead><tr>
<th>Уровень</th><th>Название</th><th>Условия получения</th>
</tr></thead>
<tbody>
{% for level in groups %}
<tr><td>{{ level.level }}</td><td>{{ level.name }}</td><td>
{% if level.special %}назначается администрацией
{% else %}
{% if level.min_posts %}за {{ level.min_posts|incline('%d сообщение','%d сообщения','%d сообщений') }}{% endif %}
{% if level.min_reg_time %} после {{ level.min_reg_time|incline('%d дня','%d дней участия','%d дней') }} с момента регистрации{% endif %}
{% endif %}
</td>
{% endfor %}
</tbody>
</table>
</div>
{% endblock %}
