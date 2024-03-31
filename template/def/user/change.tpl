{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<form action="" method="post" class="ibform" id="user_change"><fieldset><legend>Смена забытого пароля</legend>
<div><span><label>Введите новый пароль</label></span><input type="password" name="password" size="32 "/></div>
<div><span><label>Подтвердите новый пароль</label></span><input type="password" name="password_confirm" size="32 "/></div>
<div class="submit"><button type="submit">Сохранить изменения</button></div>
<input type="hidden" name="authkey" value="{{ authkey }}">
</fieldset></form>
{% endblock %}
