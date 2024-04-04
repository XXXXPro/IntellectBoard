<li><a href="{{ url('privmsg/') }}">Личные сообщения</a> 
{% if data==0 %}(новых нет){% else %}<span class="pm_new">{{ data|incline('%d новое!','%d новых!','%d новых!') }}</span>{% endif %}</li>
