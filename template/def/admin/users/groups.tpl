{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<h1>Уровни доступа</h1>

<p>В Intellect Board Pro почти все права задаются через присвоение пользователю уровня доступа 
(исключением являются права модератора, эксперта и владельца раздела). 
Уровни доступа бывают простыми (присваиваются автоматически при достижении определенного количества сообщений 
и дней после регистрации) и особыми (эти уровни назначать пользователю могут только администраторы).<br />
Модераторы и эксперты могут быть назначены только из числа пользователей, входящих в группы, помеченные как "участники команды".
Кроме того, все пользователи из этих групп выводятся по ссылке "Команда сайта" в главном меню.
</p>

<table style="text-align: center"><col style="width: 2%" /><col style="width: 22%"><col style="width: 18%" /><col style="width: 5%"/><col style="width: 5%"/><col style="width: 5%"/><col />
<thead><tr><th>Уровень</th><th>Название</th><th>Условия вступления</th><th>Участники команды</th><th>Админ.</th><th>Основатель</th><th>Действия</th></tr></thead>
<tbody>{% for item in groups %}
<tr><td>{{ item.level }}</td>
<td {% if item.special %}style="font-weight: bold"{% endif %}>{{ item.name }}{{ item.level==guest_level ? ' *' : '' }}{{ item.level==newuser_level ? ' **' : ''}}</td>
<td>{% if not item.special %}
{% if item.min_posts %}{{ item.min_posts|incline('%d сообщение','%d сообщение','%d сообщений') }}{% endif %}
{% if item.min_posts and item.min_reg_time %}, {% endif %} 
{% if item.min_reg_time %}{{ item.min_reg_time|incline('%d день','%d дня','%d дней') }}{% endif %}
{% if not item.min_posts and not item.min_reg_time %}Доступен сразу{% endif %}
{% else %}Особый{% endif %}</td>
<td>{% if item.team %}<span style="color: #0C0">Да</span>{% else %}Нет{% endif %}</td>
<td>{% if item.admin %}<span style="color: #C00">Да</span>{% else %}Нет{% endif %}</td>
<td>{% if item.founder %}<span style="color: #C00">Да</span>{% else %}Нет{% endif %}</td>
<td style="font-weight: normal"><a href="edit_group.htm?level={{ item.level }}">Редактировать группу и права доступа</a> &nbsp;&nbsp;&nbsp; {% if founder %}<a style="color: #c00" href="delete_group.htm?level={{ item.level }}">Удалить</a>{% endif %}</td></tr>
{% endfor %}</tbody>
</table>
<p>Примечания:<br />
* &mdash; уровень пользователя <a href="" class="username">Guest</a>, настройки которого используются для незарегистрированных пользователей.<br />
** &mdash; уровень пользователя <a href="" class="username">NewUser</a>, настройки которого выставляются только что зарегистрировавшимся пользователям.</p>
{% if founder %}<form action="new_group.htm" method="get" class="ibform"><fieldset><legend style="display:none"></legend>
<div class="submit"><button type="submit">Создать новый уровень доступа</button></div>
</fieldset></form>{% else %}
<p>Для создания новых уровней или удаления существующих необходимо обладать правами основателя форума</p>{% endif %}
{% endblock %}