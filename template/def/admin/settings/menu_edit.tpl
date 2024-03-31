{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<h1>Редактор меню</h1>
<p>Чтобы удалить ненужный пункт меню, оставьте поле &laquo;Ссылка&raquo; около него пустым. Если нужно добавить больше пунктов, нажите &laquo;Сохранить&raquo;, появятся дополнительные поля ввода.</p>
<form action="" method="post" class="ibform"><fieldset><legend>Пункты меню &laquo;{{ menu.descr }}&raquo;</legend>
<table><col style="width: 24%" /><col style="width: 40%"><col style="width: 3%" /><col />
<thead><tr><th>Название</th><th>Ссылка</th><th>Порядок отображения</th><th>Доступность</th></tr></thead>
<tbody>{% for item in menu_items %}
<tr><td>{{ macros.input('items['~item.id~'][title]',item.title,30) }}</td>
<td>{{ macros.input('items['~item.id~'][url]',item.url,25) }} <label>{{ macros.checkbox('items['~item.id~'][hurl_mode]',1,item.hurl_mode) }} Относительно корня</label></td>
<td>{{ macros.input('items['~item.id~'][sortfield]',item.sortfield,3) }}</td><td>
<label>{{ macros.checkbox('items['~item.id~'][show_guests]',1,item.show_guests) }} Гости</label>
<label>{{ macros.checkbox('items['~item.id~'][show_users]',1,item.show_users) }} Пользователи</label>
<label>{{ macros.checkbox('items['~item.id~'][show_admins]',1,item.show_admins) }} Админы</label></td>
{% endfor %}
{% for item in 0..4 %}
<tr><td>{{ macros.input('newitems['~loop.index~'][title]',item.title,30) }}</td>
<td>{{ macros.input('newitems['~loop.index~'][url]','',25) }} <label>{{ macros.checkbox('newitems['~loop.index~'][hurl_mode]',1,1) }} Относительно корня</label></td>
<td>{{ macros.input('newitems['~loop.index~'][sortfield]',100,3) }}</td><td>
<label>{{ macros.checkbox('newitems['~loop.index~'][show_guests]',1,1) }} Гости</label>
<label>{{ macros.checkbox('newitems['~loop.index~'][show_users]',1,1) }} Пользователи</label>
<label>{{ macros.checkbox('newitems['~loop.index~'][show_admins]',1,1) }} Админы</label></td>
{% endfor %}</tbody>
</table>
<div class="submit"><button type="submit">Сохранить</button>
<input type="hidden" name="authkey" value="{{ authkey }}" /><input type="hidden" name="fid" value="{{ fid }}" /></div>
</fieldset></form>
{% endblock %}