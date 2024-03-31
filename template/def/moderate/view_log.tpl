{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<style type="text/css">
#ib_all #moderate_view_log .ibtable tr { text-align: center;  }
#ib_all #moderate_view_log .ibtable td { vertical-align: middle }
#ib_all #moderate_view_log .ibtable tbody tr:hover { background-color: #eef }
</style>
{% endblock %}
{% block content %}
<div id="moderate_view_log">
<h1>Лог модераторских действий</h1>
<div class="pages right" style="clear: both">
Показать лог действий за последние 
{% if show!='3days' and show!='' %}<a href="?show=3days">3 суток</a>{% else %}<b>3 суток</b>{% endif %}, 
{% if show!='week' %}<a href="?show=week">неделю</a>{% else %}<b>неделю</b>{% endif %}, 
{% if show!='month' %}<a href="?show=month">месяц</a>{% else %}<b>месяц</b>{% endif %}, 
{% if show!='3months' %}<a href="?show=3months">3 месяца</a>{% else %}<b>3 месяца</b>{% endif %}, 
{% if show!='all' %}<a href="?show=all">все время</a>{% else %}<b>все время</b>{% endif %}.</div>
<table class="ibtable"><col style="width: 15%"/><col /><col style="width: 15%; text-align: center;"/>
<thead><th>Дата и исполнитель</th><th>Суть действия</th><th>Отмена</th></thead>
<tbody>
{% for item in mod_items %}
<tr><td>{{ item.time|shortdate }}, {{ macros.user(item.display_name,item.uid) }}</td>
<td style="text-align: left">{{ item.descr|raw }}</td>
<td><a class="confirm" href="rollback.htm?id={{ item.id }}&amp;authkey={{ rollback_key }}">Откатить действие</a></td></tr>
{% endfor %}
{% if mod_items|length == 0 %}<tr><td colspan="3">На данный момент нет действий, которые можно было бы отменить.</td></tr>{% endif %}
</tbody>
</table>
<small class="center">В логе не показываются удалённые сообщения. Их можно восстановить в «<a href="trashbox.htm">Корзине</a>»</small>
</div>
{% endblock %}