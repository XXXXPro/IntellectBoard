{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all .avatar { width: 16px; height: 16px }
#ib_all h4 { margin: 1.33em 0 0em 0}
#ib_all #users_moderators ul { list-style: none; padding-left: 2em}
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="users_moderators">
<h1>Модераторы и эксперты</h1>
<p>Администраторы и основатели форума всегда имеют права выполнять модераторские действия в любых разделах.
Указывать их в качестве модераторов имеет смысл только для того, чтобы их имена выводились в соответствующих списках.</p>
<p>Назначить модераторами можно только пользователей из групп с признаком &laquo;Участники команды&raquo;.<br /> 
В данный момент к таковым относятся следующие: {% for group in groups %} <a href="edit_group.htm?level={{ group.level }}">{{ group.name }}</a>{% if not loop.last %}, {% endif %}{% endfor %}. 
</p> 
<p>Внимание: вывод пользователя из этих групп не ведет к автоматическому снятию модераторских прав!</p>
{% for fid,f_title in forums %}{% if moderators[fid] %}
<h4>&raquo; {{ f_title }}</h4>
<ul><li>Модераторы: {% for user in moderators[fid] %}{% if user.role=='moderator' %}{{ macros.avatar(user.uid,user.avatar) }}{{ macros.user(user.display_name,user.uid) }} <a class="confirm" href="delete_mod.htm?uid={{ user.uid }}&amp;fid={{ fid }}&amp;role=moderator&amp;authkey={{ del_key }}">&cross;</a> &nbsp;&nbsp;&nbsp; {% endif %}{% endfor %}</li>
<li>Эксперты: {% for user in moderators[fid] %}{% if user.role=='expert' %}{{ macros.avatar(user.uid,user.avatar) }}{{ macros.user(user.display_name,user.uid) }} <a class="confirm" href="delete_mod.htm?uid={{ user.uid }}&amp;fid={{ fid }}&amp;role=expert&amp;authkey={{ del_key }}">&cross;</a> &nbsp;&nbsp;&nbsp; {% endif %}{% endfor %}</li>
</ul>
{% endif %}{% endfor %}
<br /><br />
<form action="" method="post" class="ibform"><fieldset><legend>Назначение модераторами</legend>
<div style="padding: 5px 10px">Пользователей<br />
{% for user in users %}{{ macros.avatar(user.id,user.avatar) }}{{ macros.checkbox('user['~user.id~']',user.id,0)}}{{ macros.user(user.display_name,user.id) }}{% if not loop.last %}, {% endif %}{% endfor %}</div>
<div style="padding: 5px 10px">назначить <br />
<label>{{ macros.checkbox('role[moderator]','moderator',0) }}модераторами</label><br />
<label>{{ macros.checkbox('role[expert]','expert',0) }}экспертами</label></div>
<div style="padding: 5px 10px">в разделы:<br /> 
{{ macros.select('forums[]',0,forums,6,1) }}</div>
<div class="submit"><button type="submit">Сохранить</button>{{ macros.hidden('authkey',authkey) }}</div>   
</fieldset></form>
<br />
<form action="delete_mod_all.htm" method="post" class="ibform"><fieldset><legend>Снятие всех прав</legend>
<div><label><span>Снять все модераторские права с пользователя</span><input type="text" name="uname" size="32" value="" placeholder="Введите имя"/></label></div>
<div class="submit"><button type="submit">Выполнить</button>{{ macros.hidden('authkey',del_all_key) }}</div>   
</fieldset></form>
</div>
{% endblock %}