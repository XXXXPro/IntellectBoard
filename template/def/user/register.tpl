{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<form action="" method="post" class="ibform" id="user_register">
	<fieldset><legend>Регистрация пользователя</legend>
		<div><label><span>Имя пользователя</span>{{ macros.input("basic[login]",formdata.basic.login,32,'required="required"') }}</label>
		<span style="width: auto; display: inline; float: none" id="check_result"></span></div>
		<div><label><span>Пароль</span>{{ macros.password("basic[password]",formdata.basic.password,32,'required="required"') }}</label></div>
		<div><label><span>Подтверждение пароля</span>{{ macros.password("password_confirm",formdata.password_confirm,32,'required="required"') }}</label></div>
		<div><label><span>Email</span>{{ macros.input("basic[email]",formdata.basic.email,32,128,'required="required"') }}</label></div>
		<div><label><span>Часовой пояс</span>{{ macros.select("settings[timezone]",formdata.settings.timezone,timezones) }}</label></div>
		{% if captcha_key %}<div><label><span>Защитный код<small>Введите символы с картинки справа</small></span>{{ macros.captcha(captcha_key,captcha_code,captcha_data) }}</label></div>{% endif %}
		{% if get_opt('userlib_reg_question') %}
		<div><label><span>Контрольный вопрос: {{ get_opt('userlib_reg_question') }}<small>Для регистрации необходимо правильно ответить на вопрос</small></span>{{ macros.input('answer',answer,40) }}</label></div>{% endif %}
		<div class="center"><small>После регистрации вы сможете указать дополнительные настройки в профиле пользователя</small></div>
		<div class="submit"><button type="submit">Зарегистрироваться</button></div>
		<input type="hidden" name="referer" value="{{ referer }}">
		<input type="hidden" name="accepted" value="1">
	</fieldset>
</form>
{% endblock %}
