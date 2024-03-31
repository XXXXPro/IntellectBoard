{% import 'macro.tpl' as macros %}
  <fieldset><legend>Редактирование профиля пользователя {{ formdata.basic.display_name }} 
  {% if formdata.basic.display_name!=formdata.basic.login %}(логин {{ formdata.basic.login }}){% endif %}</legend>
{% if is_admin or admin_edit_mode %}
    <div><label><span>Логин пользователя</span>{{ macros.input('basic[login]',formdata.basic.login,32) }}</label></div>
{% endif %}
    <div><label><span>Пароль<small>Оставьте поле пустым, если не хотите менять пароль</small></span>{{ macros.password('basic[password]',formdata.basic.password,32) }}</label></div>
{% if not admin_edit_mode %}<div><label><span>Подтверждение пароля</span>{{ macros.password('password_confirm',formdata.password_confirm,32) }}</label></div>{% endif %}
    <div><label><span>Email</span>{{ macros.input('basic[email]',formdata.basic.email,32) }}</label></div>
    <div><label><span>Отображаемое имя</span>{{ macros.input('basic[display_name]',formdata.basic.display_name,32,64) }}</label></div>
{% if (get_opt('custom_title','group')) %}
    <div><label><span>Особое звание пользователя</span>{{ macros.input('basic[title]',formdata.basic.title,32) }}</label></div>
{% endif %}
    <div><label><span>Интересы</span>{{ macros.textarea('interests_str',formdata.interests_str,6,60) }}</label></div>
    <div><label><span>Реальное имя</span>{{ macros.input('basic[real_name]',formdata.basic.real_name,32,64) }}</label></div>
  </fieldset>

