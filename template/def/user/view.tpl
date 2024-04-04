{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all ul  { list-style: none }
#ib_all #user_letters { list-style: none; text-transform: uppercase }
#ib_all #user_letters li { display: inline-block; padding-right: 5px; font-size: 125%; font-weight: bold; line-height: 200% }
#ib_all #user_letters li a { padding: 4px 6px; background: #def; text-decoration: none }
#ib_all #user_leftcol, #ib_all #user_midcol, #ib_all #user_rightcol { display: inline-block; vertical-align: top }
#ib_all #user_leftcol, #ib_all #user_midcol { width: 28%; margin-right: 2%; }
#ib_all #user_rightcol { width: 39%; }
#ib_all #user_main { margin-bottom: 1.5em }
#ib_all #user_tags li { display: inline-block; padding-right: 5px; }
#ib_all #user_tags li a { text-decoration: none }
#ib_all fieldset { border: 0 }
#ib_all legend { display: none }
#ib_all h4 { font-size: 100%; background: #D8EAFF; padding: 5px; margin: 5px 0 }
#ib_all #last_users { color: #888 }
@media screen and (max-width: 960px) {
  #ib_all #user_leftcol input { display: block; max-width: 75% }
}
@media screen and (max-width: 480px) {
  #ib_all #user_leftcol, #ib_all #user_midcol, #ib_all #user_rightcol { display: block; width: auto }
  #ib_all #user_tags { font-size: 70% }
  #ib_all #user_leftcol input { display: initial; max-width: 70% }
}
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="user_view">
<h1>Участники форума</h1>
<div id="user_main">
<p>Выберите первую букву имени:</p>
<ul id="user_letters">
{% for letter in letters %}<li><a href="search/letter-{{ letter }}/">{{ letter }}</a></li>{% endfor %}
</ul>
<form action="search_redir.htm" method="get"><fieldset><legend>Поиск по имени</legend>
<label>Или введите имя пользователя для поиска:</label> <input type="text" name="name" placeholder="Имя" size="20" maxlength="32" required="required" />
<button type="submit">Найти</button>
</fieldset></form>
</div>
<div id="user_leftcol">
<h4>Поиск по месту жительства:</h4>
<form action="search_redir.htm" method="get"><fieldset><legend>Поиск по месту</legend>
<input type="text" name="location" placeholder="Город" size="32" maxlength="32"  required="required" />
<button type="submit">Найти</button>
</fieldset></form>
<h4>Поиск по группам:</h4>
<ul>
{% for item in groups %}<li><a href="search/group-{{ item.level }}/">{{ item.name }}</a> ({{ item.usercount }})</li>{% endfor %}
</ul>
</div>

<div id="user_midcol">
<h4>Последние зарегистрировавшиеся:</h4>
<ul id="last_users">
{% for item in last_users %}<li>{{ macros.user(item.display_name,item.id) }} &mdash; {{ item.reg_date|longdate }}</li>{% endfor %}
</ul>
</div>

<div id="user_rightcol">
<h4>Поиск по интересам:</h4>
<ul id="user_tags">
{% for item in tags %}<li style="font-size: {{ 100+item.count*200/max_tag }}%"><a href="search/tag-{{ item.tagname|url_encode }}/">{{ item.tagname }}</a> </li>{% endfor %}
</ul>
<a style="display: block; text-align: right; margin-top: 1em; font-size: 80%" href="search/tags.htm">Все интересы</a>
</div>
</div>
{% endblock %}
