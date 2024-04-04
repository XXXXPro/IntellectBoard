{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<style>
#ib_all #oauth_authorization_endpoint { text-align: center}
#ib_all #oauth_authorization_endpoint label { display: block; text-align: left; margin-left: 10% }
#ib_all #link_select { margin: 30px 0 }
#ib_all .infolink, #ib_all #myidlink { color: #800; font-family: mono; }
#ib_all #oauth_authorization_endpoint button, #ib_all #oauth_authorization_endpoint #cancel_link { display: block; font-size: 150%; width: 80%; padding: 10px; margin: 20px auto }
#ib_all #remotesilte { font-size: 150%; color: #c00; font-family: mono }
</style>
{% endblock %}
{% block content %}
<form action="" method="post" class="postform" id="oauth_authorization_endpoint"><fieldset>
<legend>Авторизация на стороннем ресурсе</legend>
<p>Вы действительно хотите использовать авторизоваться на сайте <div id="remotesite">{{ oauth.client_id }}?</div></p>

<p>После авторизации вы будете перенаправлены на <div class="infolink">{{ oauth.redirect_uri }}</div></p>
{% if not scope %}
<small>Никакие ваши конфиденциальные данные (такие как Email, пароль, доступ к заркытым разделам и личным сообщениям) 
указанному сайту переданы не будут.<br /> Ему будет доступен только URL вашего профиля на данном форуме.</small>
{% endif %}

<div id="link_select">
{% if mode==1 %}
<span class="msg_warn">Внимание!</span> В качестве идентификатора на стороннем сайте будет использована страница вашего профиля: <span id="myidlink">{{ me }}</span>
{% else %}
В качестве идентификатора на стороннем сайте будет использован адрес <span id="myidlink">{{ me }}</span>
{% endif %}
</div>


<button type="submit" name="confirm">Да, войти на сайт</button>
<input type="hidden" name="csrf" value="{{ oauth.csrf }}" />
<input type="hidden" name="client_id" value="{{ oauth.client_id }}" />
<input type="hidden" name="redirect_uri" value="{{ oauth.redirect_uri }}" />
<input type="hidden" name="state" value="{{ oauth.state }}" />

<a id="cancel_link" href="{{ oauth.redirect_uri }}?state={{ oauth.state }}&amp;error=access_denied&amp;error_description=User%20declined%20authorization">Нет, отмена авторизации</a>
</fieldset></form>
{% endblock %}