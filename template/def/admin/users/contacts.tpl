{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="users_contacts">
<h1>Список контактов для профилей пользователя</h1>
<p>Поля &laquo;Значок&raquo; и &laquo;Ссылка&raquo; при выводе обрабатываются с помощью функции sprintf. 
При выводе в профиле пользователя вместо %s будет выводиться контакт, указаный пользователем.
Поля, для которых включен признак &laquo;Нужно разрешение&raquo; будут выводиться только для пользователей, у которых есть право размещать гиперссылки.<br />
Поле &laquo;Идентфикиатор&raquo; нужно только авторизации через социальные сети. Его значение должно совпадать со значением, возвращаемым библиотекой авторизации.
</p>
<form action="" method="post" class="ibform"><fieldset><legend>Контакты </legend>
<table><col /><col /><col /><col style="width: 3%"/><col style="width: 2%"/><col /><col style="width: 2%"/>
<thead><tr><th>Название</th><th>Значок</th><th>Ссылка</th><th>Порядок отобр.</th><th>Нужно разр.</th><th>Идентификатор</th><th>Уда&shy;лить</th></tr></thead>
<tbody>{% for item in contacts %}
<tr><td>{{ macros.input('items['~item.cid~'][c_title]',item.c_title,30) }}</td>
<td>{{ macros.input('items['~item.cid~'][icon]',item.icon,24) }}</td>
<td>{{ macros.input('items['~item.cid~'][link]',item.link,36) }}</td>
<td>{{ macros.input('items['~item.cid~'][c_sort]',item.c_sort,3) }}</td>
<td>{{ macros.checkbox('items['~item.cid~'][c_permission]',1,item.c_permission) }}</td>
<td>{{ macros.input('items['~item.cid~'][c_name]',item.c_name,14) }}</td>
<td style="background: #fee">{{ macros.checkbox('delete[]',item.cid,0) }}</td></tr>
{% endfor %}
<tr><th colspan="7">Добавить новые контакты</th></tr>
{% for item in 0..4 %}
<tr><td>{{ macros.input('newitems['~loop.index~'][c_title]','',30) }}</td>
<td>{{ macros.input('newitems['~loop.index~'][icon]','',24) }}</td>
<td>{{ macros.input('newitems['~loop.index~'][link]','http://%s',36) }}</td>
<td>{{ macros.input('newitems['~loop.index~'][c_sort]',100,3) }}</td>
<td>{{ macros.checkbox('newitems['~loop.index~'][c_permission]',1,0) }}</td>
<td>{{ macros.input('newitems['~loop.index~'][c_name]','',14) }}</td>
<td></td></tr>
{% endfor %}</tbody>
</table>
<div class="submit"><button type="submit">Сохранить</button>
<input type="hidden" name="authkey" value="{{ authkey }}" />
</fieldset></form>
</div>
{% endblock %}