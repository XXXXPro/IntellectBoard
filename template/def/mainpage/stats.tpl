{% import 'macro.tpl' as macros %}
<div class="mainpage_stats">&laquo;{{ get_opt('site_title')|raw }}&raquo; включает в себя
{{ total_topics|incline('<b>%d</b> тему','<b>%d</b> темы','<b>%d</b> тем')|raw }}, содержащих
{{ total_posts|incline('<b>%d</b> сообщение','<b>%d</b> сообщения','<b>%d</b> сообщений')|raw }}.<br />
На форуме {{ total_users|incline('<b>%d</b> зарегистрированный участник','<b>%d</b> зарегистрированных участника','<b>%d</b> зарегистрированных участников')|raw }}.<br />
Последний зарегистрировавшийся участник: {{ macros.user(last_user.display_name,last_user.id) }}.
</div>
