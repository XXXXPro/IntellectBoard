{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
<form action="" method="post" class="ibform" id="user_login"><fieldset><legend>Вход в Центр Администрирования</legend>
<div><label><span>Пароль</span><input type="password" name="password" size="32 "/></label></div>
<div><label><span>Запомнить меня на этом компьютере</span><input type="checkbox" name="long" value="1" /></label></div>
<div class="submit"><button type="submit">Войти</button></div>
</fieldset></form>
{% endblock %}