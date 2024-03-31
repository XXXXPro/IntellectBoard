{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block content %}
<div id="user_warn">
<form action="" method="post" class="ibform"><fieldset><legend>Предупреждение пользователю {{ username }}</legend>
{% include 'user/warnform.tpl' %}
<div class="submit"><button type="submit">Вынести предупреждение</button></div>
</fieldset></form>
</div>
{% endblock %}