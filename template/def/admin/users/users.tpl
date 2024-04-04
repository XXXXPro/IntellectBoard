{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all ul  { list-style: none }
#ib_all .user_letters { list-style: none; padding: 6px; border: #eee 1px solid; margin-bottom: 5px }
#ib_all .user_letters li { display: inline-block; padding-right: 5px; font-size: 125%; font-weight: bold; line-height: 200% }
#ib_all .user_letters li a { padding: 4px 6px; background: #def; text-decoration: none }
#ib_all fieldset { border: 0 }
#ib_all legend { display: none }

#ib_all ul.users { list-style: none; margin-top: 12px }
#ib_all ul.users li { width: 27.5em; display: inline-block; vertical-align: top; height:11em; border: #eee 1px solid; border-radius: 16px; font-size: 90%; margin: 0 0.5em 1em 0; color: #888; padding: 2px }
#ib_all ul.users li.banned { border-color: #c00 }
#ib_all ul.users li.inactive { border-color: #cc0 }
#ib_all ul.users li.team { border-color: #0c0 }
#ib_all ul.users li.founder { border-color: #00c }
#ib_all ul.users li .username { font-size: 120% }
#ib_all ul.users li span { color: #000 }
#ib_all ul.users div.avatar { float: left; margin: 6px }
#ib_all ul.users div.avatar img { border: #ccc 3px solid; max-height: 120px; max-width: 120px }
#ib_all ul.users li .online { color: #0b0; font-weight: bold }
#ib_all ul.users div.banned img { border: #990 3px solid }
#ib_all ul.users .dellink { float: right; display: block; line-height: 6em; padding: 0 8px; text-decoration: none; font-size: 150%; color: #c00 }
#ib_all ul.users .dellink:hover { background: #ffe }
#ib_all #user_search_form a { white-space: nowrap;} 
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="users_user">
<h1>Участники форума</h1>
<form action="search/" method="get" id="user_search_form"><fieldset><legend>Поиск по имени</legend>
<label>Найти пользователя</label> <input type="text" name="name" placeholder="Имя или его часть" size="20" maxlength="32" />
<button type="submit">Искать</button>
или показать:
{% if show!='unconfirmed' %}<a href="?show=unconfirmed">неактивных пользователей</a>{% else %}<b>неактивных пользователей</b>{% endif %}, 
{% if show!='banned' %}<a href="?show=banned">изгнанных пользователей</a>{% else %}<b>изгнанных польователей</b>{% endif %}, 
{% if show!='team' %}<a href="?show=team">участников команды</a>{% else %}<b>участников команды</b>{% endif %}, 
{% if show!='last' %}<a href="?show=last">последних зарегистрировавшихся</a>{% else %}<b>последних зарегистрировавшихся</b>{% endif %}. 
</fieldset></form>
<ul class="user_letters">
{% for letter in letters1 %}<li>{% if letter==start_letter and not show %}<b>{{ letter }}</b>{% else %}<a href="?letter={{ letter }}">{{ letter }}</a>{% endif %}</li>{% endfor %}
</ul>
{% if show=='' %}<ul class="user_letters">
{% for letter2 in letters2 %}<li>{% if letter2==start_letter2  and not show %}<b>{{ start_letter~letter2 }}</b>{% else %}<a href="?letter={{ start_letter }}&amp;letter2={{ letter2 }}">{{ start_letter~letter2 }}</a>{% endif %}</li>{% endfor %}
</ul>{% endif %}
{% if show=='banned' %}<h3>Изгнанные пользователи</h3>
{% elseif show=='unconfirmed' %}<h3>Неподтвержденные польователи</h3>
{% elseif show=='team' %}<h3>Участники команды форума</h3>
{% elseif show=='last' %}<h3>Последние зарегистрированные</h3>
{% else %}<h3>Пользователи, чьи имена начинаются на &laquo;{{ start_letter~start_letter2 }}&raquo;</h3>{% endif %}
<ul class="users">
{% for item in users %}
<li class="{% if item.status == 2%}banned {% endif %}{% if item.status == 1 %}inactive {% endif %}{% if item.team %}team {% endif %}{% if item.founder %}founder {% endif %}">
<div class="avatar">{{ macros.avatar(item.id,item.avatar,item.display_name) }}</div>
<a href="user_view.htm?uid={{ item.id }}" class="username">{{ item.display_name }}</a><br />
{{ item.name }}{% if item.title %} (<span>{{ item.title }}</span>){% endif %}<br />
Зарегистрирован: <span>{{ item.reg_date|longdate }}</span><br />
Всего сообщений: <span>{{ item.post_count }}</span>, рейтинг: <span>{{ item.rating }}</span><br />
{% if item.visit1 < lasttime %}Последний раз был: <span>{{ item.visit1|longdate }}</span>{% else
%}<span class="online">Онлайн</span>{% endif %}<br />
{% if item.location %}Откуда: <span>{{ item.location }}</span><br />{% endif %}
{% if item.warnings > 0 %}Штрафных баллов: <span style="color: #E00; font-weight: bold">{{ item.warnings }}</span><br />{% endif %}
</li>
{% endfor %}
{% if not users %}<li style="border: 0">Таких пользователей не найдено</li>{% endif %}
</ul>
</div>
{% endblock %}
