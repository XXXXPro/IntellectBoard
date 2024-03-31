{% import 'macro.tpl' as macros %}
  <fieldset><legend>Контакты</legend>
<div class="center"><small>Здесь можно указать  различные способы связи с вами, которые будут видны другим пользователям форума: дополнительный адрес EMail, ICQ, профили в социальных сетях, личный сайт и т.п.<br />
Если вы укажете OpenID или профиль социальных сетей ВКонтакте, Mail.Ru или Facebook, вы сможете входить на форум не с помощью логина/пароля, а с помощью аутентификации через соответствующую социальную сеть.<br />
  Если вы хотите удалить уже существующий контакт, выберите "Нет" в левом поле рядом с ним.</small></div>
{% for contact in formdata.contacts %}
    <div><span>{{ macros.select('contacts['~loop.index~'][cid]',contact.cid, contact_types) }}</span>
{{ macros.input('contacts['~loop.index~'][value]',contact.value,'40') }}</div>
{% endfor %}
  </fieldset>
