{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all #access_table { border-collapse: collapse; margin: -2px }
#ib_all #access_table td, #ib_all #access_table th { border: #eee 1px solid; vertical-align: middle; }
#ib_all .group_row { background: #f8f8ff;  }
#ib_all .access_row { text-align: center }
#ib_all .inherited { background: #f4f4f4; opacity: 0.9 }
#ib_all .access_row td { padding: 10px 0 }
</style>
<script type="text/javascript">
<!--
window.onload=function() {
	$('.group_row input[value=1]:checked').parents('.group_row').next().find('input[type=checkbox]').attr('disabled','disabled');
	$('.group_row input[value=1]').click(function (e) {
		var elms = $(e.target).parents('.group_row').next().addClass('inherited').find('input[type=checkbox]').attr('disabled','disabled');
	});
	$('.group_row input[value=0]').click(function (e) {
		$(e.target).parents('.group_row').next().removeClass('inherited').find('input[type=checkbox]').attr('disabled',false);
	});	
};
//-->
</script>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="forums_access">
<a href="view.htm">&laquo; К списку разделов</a>
<p>На данной странице задаются права доступа к разделу для каждой из групп участников. 
Права могут либо наследваться от родительских разделов (или главной страницы в случае их отсутствия), 
либо быть заданными явно для данного раздела. <br />
Изменять права для текущего раздела возможно только в том случае, если у него выставлен флажок "Собственные права доступа". В случае наследования прав какие-либо изменения сохраняться не будут.</p>  
<form action="" method="post" class="ibform"><fieldset>
<legend>Права доступа для раздела &laquo;{{ forum_title }}&raquo;</legend>
<table id="access_table" class="ibtable">
<thead><tr>{% for item in fields %}<th>{{ item }}</th>{% endfor %}</tr></thead>
<tbody>
{% for item in groups %}
<tr class="group_row"><td colspan="{{ fields|length }}"><strong>{{ item.name }}</strong> ({{ item.level }}):
{{ macros.radio('inherit['~item.level~']',{'0':'Собственные права доступа','1':'Наследование '~item.title},item.inherit) }}</td></tr>
<tr class="access_row{%  if item.inherit %} inherited{% endif %}">{% for field in fields %}<td>{{ macros.checkbox('access['~item.level~']['~field~']',1,item.access[field]) }}</td>{% endfor %}
{% endfor %}
</tbody>
<tfoot><tr>{% for item in fields %}<th>{{ item }}</th>{% endfor %}</tr></tfoot>
</table>
<div class="submit"><button type="submit">Сохранить права доступа</button></div>
{{ macros.hidden('authkey',authkey) }}{{ macros.hidden('id',forum_id) }}
</fieldset></form>
</div>
{% endblock %}

