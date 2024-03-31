{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="misc_trashbox">
<h1>Очистка корзины</h1>
<p>Данная функция позволяет полностью удалить перемещенные в корзину темы и сообщения без возможности их восстановления.
При этом также будут удалены все приложенные файлы и служебная информация. Отменить выполнение этого действия невозможно!    
</p>

<form action="" method="post" class="ibform"><fieldset><legend>Очистка корзины</legend>
<div><span>Очистить</span><label><input type="checkbox" name="posts" checked="checked" value="1" />сообщения</label> 
<label><input type="checkbox" name="topics" checked="checked" value="1" />темы,</label></div>
<div><label><span>которые были помеченны к удалению более</span><input type="text" size="3" name="days" value="7" /> дней назад</label>.</div>
<div><label><span>Подверждение</span><input type="checkbox" name="confirm" value="1" />удалить окончательно</label></div>
<div class="submit"><button type="submit">Сохранить</button>{{ macros.hidden('authkey',authkey) }}</div>
</fieldset></form>
</div>
{% endblock %}