{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}<link rel="stylesheet" type="text/css" href="{{ style('user.css') }}" />{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="user_view_user" class="h-card">
<h1>Профиль пользователя <span class="username p-nickname p-name">{{ userdata.basic.display_name }}</span></h1>
<a class="u-url u-uid" rel="self" href="{{ http(url(sprintf(get_opt('user_hurl'),userdata.basic.id,userdata.basic.login))) }}"></a>
<div class="userleft">
<div class="pm_buttons">
{{ macros.photo(userdata.basic.id,userdata.basic.photo,userdata.basic.display_name,"u-photo") }}<br />
{% if not is_guest() and userdata.basic.id!=user.id %}
{% if relation_from!='ignore' and relation_to!='ignore' %}
<a href="{{ url('privmsg/new.htm?to='~userdata.basic.display_name) }}" class="actionbtn pm_send"><i class="fas fa-envelope"></i> Написать ЛС</a>
{% if relation_from!='friend' %}<a class="actionbtn friend_add" href="{{ url('address_book/add.htm')}}?type=friend&amp;authkey={{ add_key }}&amp;logins={{ userdata.basic.display_name }}">
<i class="fas fa-user-plus"></i> Добавить в друзья</a>{% endif %}
{% elseif relation_from=='ignore' %}<i class="fas fa-user-slash"></i> Пользователь внес вас в список игнорируемых
{% else %}<i class="fas fa-user-slash"></i> Вы внесли пользователя в список игнорируемых
{% endif %}
{% endif %}
{% if can_warn and userdata.basic.id!=user.id %}
<a href="{{ url('admin/users/user_view.htm?uid='~userdata.basic.id) }}" class="actionbtn admin_view"><i class="far fa-id-card"></i> Профиль в АЦ</a>
<a href="{{ url('user/warn.htm?id='~userdata.basic.id) }}" class="actionbtn user_warn"><i class="fas fa-exclamation-triangle"></i> Вынести предупреждение</a>
{% endif %}
</div>

{% if userdata.contacts %}
<h3>Контакты</h3>
<ul class="contacts">
{% for item in userdata.contacts %}{% if not item.c_permission or
(userdata.ext_data.links_mode!='none' and userdata.ext_data.links_mode!='premod') %}
<li {% if item.icon %}style="background-image: url('{{ style(sprintf(item.icon,item.value)) }}')"{% endif %}>
<a {% if userdata.ext_data.links_mode=='nofollow' %}rel="nofollow me" {% else %}rel="me" {% endif %}href="{{ sprintf(item.link,item.value)|e }}">{{ sprintf(item.c_title,item.value)|e }}</a></li>
{% endif %}{% endfor %}
</ul>
{% endif %}
<br /><br />
{% if not is_guest() and userdata.basic.id!=user.id %}<div class="pm_buttons">
<a class="actionbtn warnbtn" href="{{ url('address_book/add.htm') }}?type=ignore&amp;authkey={{ add_key }}&amp;logins={{ userdata.basic.display_name }}"><i class="fas fa-ban"></i> Игнорировать</a></div>
{% endif %}

</div>
<div class="userright">
<div class="data">{{ macros.avatar(userdata.basic.id,userdata.basic.avatar,userdata.basic.display_name,"u-logo") }}
<p><span>Уровень доступа</span><label class="p-role">{% if (userdata.basic.status==2 or userdata.ext_data.banned_till>now) %}Изгнан с форума {% if userdata.ext_data.banned_till %} до {{ userdata.ext_data.banned_till|longdate }}<br />{% endif %}
{% elseif (userdata.basic.title) %}{{ userdata.basic.title }} ({{ userdata.ext_data.name }}){% else %}{{ userdata.ext_data.name }}{% endif %}</label></p>
{# <p><span>Девиз/статус</span>!!!!!</p>#}

{% if userdata.basic.gender=='M' %}<p><span>Пол</span>мужской</p>{% elseif userdata.basic.gender=='F' %}<p><span>Пол</span>женский</p>{% endif %}
{% if userdata.settings.show_birthdate!=0 %}
{% if userdata.settings.show_birthdate=='1' or userdata.settings.show_birthdate=='3' %}<p><span>Дата рождения</span><span class="dt-bday">{{ birthdate }}</span></p>
{% elseif userdata.settings.show_birthdate=='2' %}<p><span>Возраст</span>около {{ user_age|incline('%d год','%d года','%d лет') }}</p>{% endif %}
{% endif %}
{% if userdata.basic.real_name %}<p><span>Реальное имя</span><label class="fn">{{ userdata.basic.real_name }}</label></p>{% endif %}
{% if userdata.basic.location %}<p><span>Откуда</span><label class="p-locality">{{ userdata.basic.location }}</label></p>{% endif %}
{% if userdata.basic.signature %}<p><span>Подпись</span><label class="p=note">{{ userdata.basic.signature|raw }}</label></p>{% endif %}

<p><span>Зарегистрирован</span>{{ userdata.ext_data.reg_date|longdate }}</p>
{% if lastvisit %}<p><span>Последний раз был здесь</span>{{ lastvisit|longdate }}</p>{% endif %}
<p><span>Всего сообщений</span>{{ userdata.ext_data.post_count|incline('%d сообщение','%d сообщения','%d сообщений') }}{% if userdata.ext_data.post_count %}, из них {{ valued_count }} ценных, {{ sprintf("%2.0f",flood_posts/userdata.ext_data.post_count*100) }}% флуда</p>
<p><span>Рейтинг</span>{{ userdata.ext_data.rating }}
{% if userdata.ext_data.warnings %}<p><span>Штрафных баллов</span>{{ userdata.ext_data.warnings }}</p>{% endif %}
{% endif %}</p></div>

<h3>Интересы пользователя: </h3>
<div>{% for item in userdata.interests %}{% if loop.index!=1 %}, {% endif %}<a href="../search/tag-{{ item|url_encode }}/">{{ item }}</a>{% endfor %}
{% if userdata.interests|length==0 %}Пользователь не указал своих интересов{% endif %}</div>

{# <h3>Личные разделы</h3> Пока не используется, в будущем добавить выборку разделов, где пользователь указан владельцем #}

<h3>Список друзей</h3>
<div class="friendlist">{% for item in friend_list %}{% if loop.index!=1%}, {% endif %}<span class="{{ item.friend_type }}"><a href="{{ url(sprintf(get_opt('user_hurl') ?: 'users/profiles/%s.htm',item.id,item.login)) }}" class="username hcard" {% if item.friend_type=='to' or item.friend_type=='mutual' %} rel="friend"{% endif %}>{{ item.display_name }}</a></span>{% endfor %}</div>

{% if forum_posts %}<h3>Активность на форуме</h3>
<table class="forums">
<col style="width: 60%" /><col /><col /><col />
<thead><th>Раздел</th><th>Сообщений всего</th><th>Ценных</th><th>Флуд</th></thead>
<tbody>
{% for item in forum_posts %}
<tr><td class="forum_name"><a href="{{ url(item.hurl) }}/">{{item.title}}</a></td><td>{{ item.count }}</td><td>{{ item.valued }}</td><td>{{ item.flood }}</td></tr>
{% endfor %}
</tbody></table>
{% endif %}
<h3>Поиск сообщений</h3>
<ul>
<li><a href="{{ url('search/user_posts.htm?id='~userdata.basic.id)  }}">Найти все сообщения пользователя</a></li>
<li><a href="{{ url('search/user_posts.htm?valued=1&id='~userdata.basic.id~'&valued=true') }}">Найти сообщения пользователя, признанные ценными</a></li>
<li><a href="{{ url('search/user_topics.htm?id='~userdata.basic.id) }}">Найти все темы, созданные пользователем</a></li>
</ul>

</div>
<br style="clear: both" />
</div>
{% endblock %}
