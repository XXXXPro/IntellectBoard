{% import 'macro.tpl' as macros %}
<div><label><span>Звание участников группы</span>{{ macros.input('group[name]',group.name) }}</label></div>
<div><label><span>Флуд-интервал<br /><small>(время, через которое должно пройти после отправки предыдущего сообщения для отправки следующего)</small></span>{{ macros.input('group[floodtime]',group.floodtime,3) }} сек.</label></div>
<div><label><span>Количество ЛС-тем в час<br /><small>(для предотвращения рассылки спама через ЛС, 0 &mdash; нет ограничений)</small></span>{{ macros.input('group[privmsg_hour]',group.privmsg_hour,5) }}</label></div>
<div><label><span>Участники могут выставлять себе особое звание</span>{{ macros.checkbox('group[custom_title]',1,group.custom_title) }}</label></div>
<div><label><span>Макс. размер прикрепленных файлов</span>{{ macros.input('group[max_attach]',group.max_attach,5) }} Кб</label></div>
<div><span>Режим отображения ссылок</span>{{ macros.radio('group[links_mode]',{'none':'Запрещены вообще','premod':'С премодерацией','nofollow':'С nofollow','allow':'Без ограничений'},group.links_mode) }}</div>
</fieldset>
<fieldset><legend>Условия вступления и глобальные права</legend>
<div><label><span><b>Специальная группа</b><br /><small>(пользователей в нее может добавить только администратор)</small></span>{{ macros.checkbox('group[special]',1,group.special) }}</label></div>
<div><label><span>Количество сообщений для вступления в группу<br /><small>(для обычных групп)</small></span>{{ macros.input('group[min_posts]',group.min_posts,5) }}</label></div>
<div><label><span>Количество дней после регистрации для вступления<br /><small>(для обычных групп)</small></span>{{ macros.input('group[min_reg_time]',group.min_reg_time,3) }}</label></div>
<div><label><span style="color: #0c0">Статус "Участники команды"</span>{{ macros.checkbox('group[team]',1,group.team) }}</label></div>
<div><label><span style="color: #c00">Администраторы форума</span>{{ macros.checkbox('group[admin]',1,group.admin) }}</label></div>
<div><label><span style="color: #c00">Основатели форума</span>{{ macros.checkbox('group[founder]',1,group.founder) }}</label></div>
