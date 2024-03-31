{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="misc_counters">
<h1>Счетчики и JavaScript</h1>
<p>Данная страница предназначена для вставки кодов счетчиков и систем аналитики, а также произвольного 
пользовательского JavaScript. Вставка возможна в секцию head, начало и конец страницы.
</p>   

<form action="" method="post" class="ibform"><fieldset><legend>Коды счетчиков и систем аналитики</legend>
<div style="padding: 5px 10px"><label>Код в секции head:<br />
<textarea name="counter_h" style="width: 98%;" rows="3">{{ counter_h }}</textarea></label>
</div>
<div style="padding: 5px 10px"><label>Код в начале страницы:<br />
<textarea name="counter_t" style="width: 98%;" rows="4">{{ counter_t }}</textarea></label>
</div>
<div style="padding: 5px 10px"><label><strong>Код в конце страницы</strong> (счетчики и системы аналитики рекомендуется вставлять сюда):<br />
<textarea name="counter_f" style="width: 98%;" rows="6">{{ counter_f }}</textarea></label>
</div>
<div class="submit"><button type="submit">Сохранить</button>{{ macros.hidden('authkey',authkey) }}</div>
</fieldset></form>
</div>
{% endblock %}