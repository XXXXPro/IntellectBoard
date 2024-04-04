{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="forums_category">
<form action="" method="post" class="ibform"><fieldset><legend>Редактирование категории</legend>
<div><label><span>Название категории</span> {{ macros.input('category[title]',category.title) }}</label></div>
<div class="submit"><button type="submit">Сохранить</button></div>
{{ macros.hidden('authkey',authkey) }}{{ macros.hidden('id',id) }}
</fieldset></form>
</div>
{% endblock %}