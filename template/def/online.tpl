{% import 'macro.tpl' as macros %}
<div class="block online">
<div class="headline">{{ data.online_header }}</div>
<div>В течение {{ get_opt('online_time')|incline('последней %d минтуты','последних %d минут','последних %d минут') }} здесь 
{{ data.online.guests|length|incline(' <b>%d</b> гость','присутствовало <b>%d</b> гостя',' присутствовало <b>%d</b> гостей')|raw }} и
{{ data.online.hidden|length|incline('<b>%d</b> скрытый пользователь','<b>%d</b> скрытых пользователя','<b>%d</b> скрытых пользователей')|raw }}
{% if data.online.users or data.online.team or data.online.bots %}
, а также<br />
{% if data.online.users %}
{{ data.online.users|length|incline('<b>%d</b> участник: ','<b>%d</b> участника: ','<b>%d</b> участников: ')|raw }}
{% for user in data.online.users %}{{ macros.user(user.display_name,user.uid) }}{% if not loop.last 
%}, {% endif %}{% 
endfor %}<br />
{% endif %}

{% if data.online.team %}
{{ data.online.team|length|incline('<b>%d</b> модератор или эксперт: ','<b>%d</b> модератора или эксперта: ','<b>%d</b> модераторов или экспертов: ')|raw }}
{% for user in data.online.team %}{{ macros.user(user.display_name,user.uid) }}{% if not loop.last 
%}, {% endif %}{% 
endfor %}<br />
{% endif %}

{% if data.online.bots %}
{{ data.online.bots|length|incline('<b>%d</b> поисковый робот: ','<b>%d</b> поисковых робота: ','<b>%d</b> поисковых роботов: ')|raw }}
{% for user in data.online.bots %}{{ macros.user(user.bot_name) }}{% if not loop.last 
%}, {% endif %}{% 
endfor %}<br />
{% endif %}

{% endif %}
</div>
<div>
Всего за сегодня присутствовали {{ data.today.guests|length|incline('<b>%d</b> гость','<b>%d</b> гостя','<b>%d</b> гостей')|raw }}, 
{{ data.today.hidden|length|incline('<b>%d</b> скрытый пользователь','<b>%d</b> скрытых пользователя','<b>%d</b> скрытых пользователей')|raw }}
{% if data.total|length>0 %}
{{ data.total|length|incline(' и <b>%d</b> участник',' и <b>%d</b> участника',' и <b>%d</b> участников')|raw }}:
{% for user in data.total %}{{ macros.user(user.display_name, user.uid) }}{% 
if not loop.last %}, {% endif 
%}{% endfor %}
{% endif %}
</div></div>