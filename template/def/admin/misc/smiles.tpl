{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="misc_smiles">
<form action="" method="post" class="ibform" enctype="multipart/form-data"><fieldset><legend>Графические смайлики</legend>
<table><col style="width: 15%" /><col style="width: 4%"><col style="width: 20%" /><col style="width: 30%"/><col style="width: 6%"/><col style="width: 3%"/>
<thead><tr><th>Код смайлика</th><th>Изображение</th><th>Пояснение</th><th>Режим отображения</th>
<th>Порядок сортировки</th><th>Удалить</th></tr></thead>
<tbody>{% for code,item in smiles %}
<tr><td>{{ macros.input('smiles['~code~'][code]',code,12) }}</td>
<td><img src="{{ url('sm/'~item.file) }}" alt="{{ item.descr }}" /></td>
<td>{{ macros.input('smiles['~code~'][descr]',item.descr,30) }}</td>
<td>{{ macros.radio('smiles['~code~'][mode]',{'dropdown':'Основной','more':'Дополнительный','hidden':'Скрытый'},item.mode) }}</td>
<td>{{ macros.input('smiles['~code~'][sortfield]',item.sortfield,4) }}</td>
<td style="background: #fee"><input type="checkbox" name="delete[{{ code }}]" value="1"/></td></tr>
{% endfor %}
</tbody></table>
<div><label><span>Загрузить новые смайлики</span><input type="file" name="newsmiles[]" multiple="multiple" /></label></div>
<div class="submit"><button type="submit">Сохранить</button>{{ macros.hidden('authkey',authkey) }}</div>
</fieldset></form>
</div>
{% endblock %}