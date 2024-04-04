{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="users_user_delete">
<a href="user_view.htm?uid={{ udata.id }}">&laquo; К профилю пользователя</a>
<form action="" method="post" class="ibform"><fieldset>
<legend>Удаление пользователя &laquo;{{ udata.display_name }}&raquo;</legend>
<div style="padding: 5px; text-align: center">Для потдверждения удаления пользователя введите его отображаемое имя: <strong>{{ udata.display_name }}</strong></div>
<div><label><span>Имя удаляемого пользователя</span><input type="text" name="confirm_name" size="32" /></label></div>
<div class="submit"><button type="submit">Удалить</button></div>
{{ macros.hidden('authkey',authkey) }}{{ macros.hidden('id',udata.id) }}
</fieldset></form>
</div>
{% endblock %}