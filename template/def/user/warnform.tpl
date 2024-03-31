{% import 'macro.tpl' as macros %}
<div><label><span>Количество штрафных баллов</span><input type="number" name="warn[value]" min="1" max="{{ get_opt('user_max_warnings') }}" value="10"></label></div>
<div><span>Срок действия предупреждения</span>{{ macros.radio('warn[limit]',{'0':'Бессрочно','1':'Сроком на '},1) }} <label>{{ macros.input('warn[period]',30,4) }} дней</label></div>
<div><label><span>Пояснение модератора:</label><br />
<textarea rows="3" cols="60" name="warn[descr]" style="width: 98%"></textarea></span>
</div>