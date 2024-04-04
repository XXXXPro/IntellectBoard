{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
<div id="users_ip">
<h1>Заблокированные IP-адреса</h1>
<p>Вы можете заблокировать доступ пользователям, заходящим с определенных диапазонов IP-адресов либо на указанное время, либо навсегда.
Если вам нужно заблокировать только один адрес, а не диапазон, указывайте его в качестве начального IP. 
Чтобы заблокировать адреса без ограничения времени, укажите в поле &laquo;Срок блокировки&raquo; значение -1.</p> 
<form action="" method="post" class="ibform"><fieldset>
<table class="ibtable" style="text-align: center"><thead><tr><th>Начальный IP</th><th>Конечный IP</th><th>Срок блокировки в минутах</th></tr></thead>
<tbody>
{% for item in ips %}
<tr><td><input type="text" name="ips[start][{{ loop.index }}]" value="{{ item.start_ip }}" size="15" /></td>
<td><input type="text" name="ips[end][{{ loop.index }}]" value="{{ item.end_ip }}" size="15" /></td>
<td><input type="text" name="ips[till][{{ loop.index }}]" value="{{ item.till }}" size="8" /></td></tr>
{% endfor %}
</tbody></table>
<div class="submit"><button type="submit">Сохранить</button></div>
<input type="hidden" name="authkey" value="{{ authkey }}" />
</fieldset></form>
{% endblock %}