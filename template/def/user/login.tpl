{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block content %}
<form action="" method="post" class="ibform" id="user_login"><fieldset><legend>Вход на форум</legend>
<div><label><span>Логин</span><input type="text" name="login" size="32" required="required"/></label></div>
<div><label><span>Пароль</span><input type="password" name="password" size="32 "required="required"/></label></div>
<div><label><span>Запомнить меня на этом компьютере</span><input type="checkbox" name="long" value="1" /></label></div>
<div class="submit"><input type="hidden" name="referer" value="{{ referer }}" />
	<button type="submit">Войти</button></div>
</fieldset></form>
<p class="center"><a href="register.htm" rel="nofollow">Зарегистрироваться</a> &nbsp;&nbsp;&nbsp;&nbsp;
	<a href="forgot.htm" rel="nofollow">Забыли пароль</a></p>
{% if get_opt('site_social_lib') %}
<h2>Вход через социальные сети</h2>
{% include 'user/social_big_'~get_opt('site_social_lib')~'.tpl' %}
{% endif %}	
{% endblock %}