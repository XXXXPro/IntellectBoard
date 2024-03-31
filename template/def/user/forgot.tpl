{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<form action="" method="post" class="ibform" id="user_forgot"><fieldset><legend>Восстановление забытого пароля</legend>
<div class="center">Для	восстановления пароля нужно указать логин или адрес EMail, который был указан в профиле пользователя.<br />
После этого на электронную почту будет выслано письмо со ссылкой, позволяющей произвести смену пароля.</div>
<div><span><label>Логин</label></span><input type="text" name="login" size="32 "/></div>
<div class="center">или</div>
<div><span><label>Адрес Email</label></span><input type="text" name="email" size="32 "/></div>
{% if captcha_key %}<div><label><span>Защитный код<small>Введите символы с картинки справа</small></span>{{ macros.captcha(captcha_key,captcha_code,captcha_data) }}</label></div>{% endif %}
<div class="submit"><button type="submit">Выслать пароль</button></div>
</fieldset></form>
{% endblock content %}