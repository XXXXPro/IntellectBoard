{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="misc_resync">
<h1>Пересинхронизация данных форума</h1>
<p>Пересинхронизация данных на форуме выполняет пересчет количества сообщений пользователей, 
а также пересчет количества сообщений в темах и разделах, обновление ссылок на 
первое и последнее сообщение каждой темы и форума. <br />
Внимание: пересинхронизация создает большую нагрузку на сервер, а также приводит к обновлению 
даты последнего изменения каждой темы, поэтому используйте ее только тогда, когда в ней 
действительно есть необходимость.  
</p>

<form action="" method="post" class="ibform"><fieldset><legend>Пересинхронизация</legend>
<div><span style="line-height: 250%">Требуемые действия</span>
<label><input type="checkbox" name="objects[topics]" checked="checked" value="1" />пересинхронизация тем и разделов</label><br />
<label><input type="checkbox" name="objects[users]" checked="checked" value="1" />пересинхронизация пользователей</label></div>
<div><label><span>За один проход обрабатывать</span><input type="text" name="step" value="1000" />тем или пользователей</label>
<div class="submit"><button type="submit">Сохранить</button>{{ macros.hidden('authkey',authkey) }}</div>
</fieldset></form>
</div>
{% endblock %}