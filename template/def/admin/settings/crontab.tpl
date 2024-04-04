{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<h1>Планировщик заданий</h1>
<p>Текущий режим работы планировщика: {% if
get_opt('cron_img')==0 %}<b>системный cron</b><br />
<small>Если задания не выполняются, вам необходимо проверить, настроен ли запуск файла app/cron.php в вашем системном планировщике заданий cron. Если у вас нет к нему доступа, переключитесь в режим запуска планировщика через тег &lt;img&ht;</small> 
{% 
elseif get_opt('cron_img')==1 %}<b>через тег &lt;img&gt;</b><br />
<small>Если ваш хостинг поддерживает crontab, рекомендуем настроить выполнение задачи Intellect Board через него. Это немного снизит нагрузку на сервер.</small>{% 
endif %}</p>
<form action="" method="post" class="ibform"><fieldset><legend>Доступные задания</legend>
<table><col style="width: 30%" /><col style="width: 20%"><col style="width: 25%" /><col />
<thead><tr><th>Описание</th><th>Библотека/функция</th><th>Параметры</th><th>Период, мин.</th><th>Следующее выполнение</th></tr></thead>
<tbody>{% for item in cron %}
<tr><td>{{ item.description }}</td>
<td>{{ item.library }}/{{ item.proc }}</td><td>{{ macros.input('cron['~item.id~'][params]',item.params,30) }}</td>
<td>{{ macros.input('cron['~item.id~'][period]',item.period,6) }}</td><td>{{ item.nextrun|longdate }}</td>
{% endfor %}</tbody>
</table>
<div class="submit"><button type="submit">Сохранить</button>
<input type="hidden" name="authkey" value="{{ authkey }}" /><input type="hidden" name="fid" value="{{ fid }}" /></div>
</fieldset></form>
{% endblock %}