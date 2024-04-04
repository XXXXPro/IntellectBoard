{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all #forums_create_forum .types label { display: block; padding-left: 40%; line-height: 150% }
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="forums_create_forum">
<h1>Создание нового раздела</h1>
<p>Выберите тип создаваемого раздела:</p>
<form action="" method="get" class="ibform"><fieldset>
<div class="types">{{ macros.radio('type',types) }} 
</div>
<div class="submit"><button type="submit">Создать раздел</button></div>
</fieldset></form>
</div>
{% endblock %}