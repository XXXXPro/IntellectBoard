{% import 'macro.tpl' as macros %}
<form action="{{ url('search/') }}" class="ibform" method="post"><fieldset><legend>Поиск по форуму</legend>
<div style="padding: 5px 10px">
<div class="submit" style="padding: 0">
<input type="text" style="width: 90%" name="search[query]" value="{{ search.query }}" placeholder="Введите текст для поиска" />
<button type="submit">Найти</button></div>
<div><small>Внимание: существует ограничение на минимальную длину строки для поиска. Обычно оно равно трем символам, но на некоторых форумах может отличаться.<br />
</small></div>
<div>Искать в {{ macros.radio('search[output_mode]',{'posts':'сообщениях','topics':'темах'},search.output_mode) }}</div>

<div class="search_col"><label><input type="checkbox" name="extdata[by_forum]" class="flipper" value="1" {% if extdata.by_forum %}checked="checked"{% endif %} />Искать в определенных разделах</label>
<div>{{ macros.select('extdata[selected][]',extdata.selected,forum_list,5,1) }}
</div>
</div>

<div class="search_col"><label><input type="checkbox" name="extdata[by_date]" class="flipper" value="1" {% if extdata.by_date %}checked="checked"{% endif %} />Искать по дате</label>
<div>
с&nbsp;&nbsp; {{ macros.input('extdata[start_date]',extdata.start_date,14,14,'class="date"') }}<br />
по {{ macros.input('extdata[end_date]',extdata.end_date,14,14,'class="date"') }}
</div>
</div>

<div class="search_col" id="search_third"><label><input type="checkbox" name="extdata[by_value]" class="flipper" value="1" {% if extdata.by_value %}checked="checked"{% endif %} />Искать с учетом ценности</label>
<div>
{{ macros.radio('extdata[flood]',{'all':'Все сообщения','noflood':'Все, кроме помеченных как флуд','valued':'Только ценные сообщения'},extdata.flood) }}
</div>
</div></div>

</fieldset></form>

