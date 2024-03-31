{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<form action="" method="get" class="rules"><fieldset style="border: 0; padding: 10px"><legend style="display: none"></legend>
<h1>Правила форума</h1>
<div style="text-align: center">
<div style="width: 96.5%; margin: auto; height: 27em; overflow: auto; text-align: left; border: #ccc 1px solid; padding: 5px" >{{ rules|raw }}</div>
</div>
<div class="accept"><label><input type="checkbox" name="accepted" value="1" required="required">Я принимаю эти правила и обязуюсь их соблюдать</label></div>
{% if social_login %}
<div>Учетная запись, с помощью которой вы входите, не связана ни с одним из профилей пользователей форума. Вы будете зарегистрированы как новый пользователь со следующими данными:
<ul><li>Логин: <b>{{ social_login }}</b></li>
<li>Имя, показываемое другим пользователям: <b>{{ social_name }}</b></li>
<li>Адрес EMail: <b>{{ social_email }}</b></li> 
</ul> 
<p>В дальнейшем вы можете изменить эти данные в настройках профиля пользователя. Если вы захотите входить не через социальную сеть, а с помощью логина и пароля, воспользуйтесь функцией "сброс пароля".</p> 
</div>
{% endif %}
<div class="submit"><button type="submit">Зарегистрироваться</button></div>
<input type="hidden" name="referer" value="{{ referer }}">
</fieldset></form>
{% endblock %}
