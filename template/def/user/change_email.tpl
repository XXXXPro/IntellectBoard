{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<div id="user_change_email">
<form action="" method="post" class="ibform" id="user_change_email"><fieldset><legend>Смена Email, указанного при регистрации</legend>
<div><span><label>Логин</label></span><input type="text" name="login" size="32 "/></div>
<div><span><label>Пароль</label></span><input type="password" name="password" size="32 "/></div>
<div><span><label>Новый адрес Email</label></span><input type="text" name="email" maxlength="128" size="32"/></div>
{% if captcha_key %}<div><label><span>Защитный код<small>Введите символы с картинки справа</small></span>{{ macros.captcha(captcha_key,captcha_code,captcha_data) }}</label></div>{% endif %}
<div class="submit"><button type="submit">Сохранить изменения</button></div>
</fieldset></form></div>
{% endblock %}