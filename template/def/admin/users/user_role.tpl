{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{# % import 'macro.tpl' as macros % #}
<div id="users_role">
<a href="user_view.htm?uid={{ uid }}">&laquo; К профилю пользователя</a>
<p>Описание роли пользователя, состоящего в какой-либо из групп с признаком "Команда форума",
выводится на странице "Команда" вместе с его аватаром, фотографией и перечнем должностей на форуме.</p>
<form action="" method="post" class="ibform"><fieldset><legend>Командная роль пользователя <strong class="username">{{ display_name }}</strong></legend>
<div> 
<textarea style="width: 98%; margin: auto" cols="60" rows="25" name="text" class="wysiwyg">{{ role|raw }}</textarea>
</div>
<div class="submit"><button type="submit">Сохранить</button>
<input type="hidden" name="authkey" value="{{ authkey }}" /><input type="hidden" name="uid" value={{ uid }} /></div>
</fieldset></form>
</div>
{% endblock %}