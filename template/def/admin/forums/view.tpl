{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all .category { background: #f8f8ff }
#ib_all .forum:nth-child(odd) { background: #f9f9f9 }
#ib_all .category td:first-child { font-size: 110%; font-weight: bold }
#ib_all fieldset { border : 0 }
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="forums_view">
<h1>Список разделов форума</h1>
<form action="sort.htm" method="post"><fieldset>
<a href="create_category.htm" class="actionbtn">Новая категория</a> <a href="create_forum.htm" class="actionbtn">Новый раздел</a>
<a href="?show_all=1">Показать личные разделы</a>
<table class="ibtable">{# <col style="width: 5%" /> #}<col style="width: 38%" />
<col style="width: 17%" /><col style="width: 12%" /><col style="width: 6%" /><col />
<thead><tr>{# <th>ID</th> #}<th>Название</th><th>Тип</th><th>URL</th><th>Порядок</th><th>Действия</th></tr></thead>
<tbody>
{% for cat in categories %}
<tr class="category"><td colspan="{% if cat.id %}3{% else %}5{% endif %}">&raquo; {% if cat.id %}{{ cat.title }}{% else %}Без категории{% endif %}</td>
{% if cat.id %}<td><input type="text" name="cat_sort[{{ cat.id }}]" value="{{ cat.sortfield}}" size="3" /></td>
<td><a href="edit_category.htm?id={{ cat.id }}">Редактировать</a> 
{% if cat.forums|length==0 %}<a href="delete_category.htm?id={{ cat.id }}" style="color: red">Удалить</a>{% endif %}</td>{% endif %}</tr>
{% for item in cat.forums %}
<tr class="forum">{# <td>{{ item.id }}</td> #}<td><a href="edit_forum.htm?id={{ item.id }}">{{ item.title }}</a></td>
<td>{{ item.typename }}</td><td>/{{ item.hurl }}/</td>
<td><input type="text" name="sort[{{ item.id }}]" value="{{ item.sortfield}}" size="3" /></td>
<td>{%  if item.has_rules %}<a href="{{ url('moderate/'~item.hurl~'/edit_rules.htm') }}">Правила</a> {% endif %}
{%  if item.has_foreword %}<a href="{{ url('moderate/'~item.hurl~'/edit_foreword.htm') }}">Введение</a> {% endif %}
<a style="color: #996" href="access.htm?id={{ item.id }}">Доступ</a> {%  if is_founder %}<a href="delete_forum.htm?id={{ item.id }}" style="color: red">Удалить</a>{%  endif %}</td>
</tr>
{% endfor %}
{% endfor %} 
</tbody>
</table>
<a href="create_category.htm" class="actionbtn">Новая категория</a> <a href="create_forum.htm" class="actionbtn">Новый раздел</a> <button type="submit" class="actionbtn">Пересортировать</button>
<input type="hidden" name="authkey" value="{{ sort_key }}" />
</fieldset></form>
</div>
<p>Примечание: по умолчанию выводятся только разделы общего пользования. <br />
Чтобы вывести список всех разделов, включая личные разделы пользователей (блоги, фотоальбомы и т.д), <a href="?show_all=1">нажмите сюда</a> (на форуме с большим числом участников может занять много времени!) 
или зайдите в профиль нужного пользователя.</p>  
{% endblock %}