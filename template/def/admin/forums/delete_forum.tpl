{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="forums_edit_forum">
<a href="view.htm">&laquo; К списку разделов</a>
<form action="" method="post" class="ibform"><fieldset>
<legend>Удаление раздела &laquo;{{ forumdata.title }}&raquo;</legend>
<div style="padding: 5px; text-align: center">Для потдверждения удаления раздела введите его HURL: <strong>{{ forumdata.hurl }}</strong></div>
<div><label><span>HURL удаляемого форума</span><input type="text" name="forum_hurl" size="24" /></label></div>
<div class="submit"><button type="submit">Удалить</button></div>
{{ macros.hidden('authkey',authkey) }}{{ macros.hidden('id',forumdata.id) }}
</fieldset></form>
</div>
{% endblock %}