{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
{% if not intb.is_ajax %}
<link rel="preload" as="style" type="text/css" href="{{ style('post.css') }}" onload="this.rel='stylesheet'"/>
{% endif %}
{% endblock %}
{% block content %}
{% if not intb.is_ajax %}
<div id="stdforum_view_topic"  class="forum{{ forum.id }} topic{{ topic.id }}">
<h1>{{ topic.title }}</h1>
<p class="descr">{{ topic.descr }}</p>
{{ macros.sub_block(IntB_subactions['action_start']) }}

{% if poll %}
{% include 'stdforum/poll.tpl' %}
{% endif %}
<!--noindex--><div class="topic_start">
<div class="right" style="text-align: right"><form action="params_topic.htm" method="get" class="smallform">
<fieldset><legend>Настройки отображения темы</legend>
Показывать по {{ macros.input('perpage',opts.perpage,3) }} сообщений  с сортировкой {{ macros.select('sort',opts.sort,{DESC:'по убыванию',ASC:'по возрастанию',rating:'по рейтингу'}) }}.<br/>
Выводить {{ macros.select('filter',opts.filter,{all:'все сообщения',valued:'только ценные сообщения',noflood:'сообщения, не являющиеся флудом'}) }},
отправленные <input type="text" name="author_name" value="{{ opts.author_name }}" size="12" maxlength="32" placeholder="имя автора" />.
<button type="submit" name="submit">Показать</button>
<button type="submit" name="clear">Сброс</button>
</fieldset>
</form>
</div>

{% if not is_guest() %}<span class="lastvisit">
{% if topic.visit2 %}Последний раз вы просматривали эту тему {{ topic.visit2|longdate }}.{% else %}
Вы впервые в этой теме. Добро пожаловать!{% endif %}</span><br />&nbsp;
{% endif %}
{% if roles.moderator|length>0 %}Модераторы: {% for user in roles.moderator %}{% if not loop.first %}, {% endif %}{{ macros.user(user.display_name,user.id) }}{% endfor %}<br />{% endif %}
{% if roles.expert|length>0 %}Эксперты: {% for user in roles.expert %}{% if not loop.first %}, {% endif %}{{ macros.user(user.display_name,user.id) }}{% endfor %}<br />{% endif %}
{% if forum.selfmod>0 and roles.curator %}Куратор темы: {{ macros.user(roles.curator.display_name,roles.curator.id) }}{% endif %}
{% if rules %}
<a href="../rules.htm" rel="nofollow" class="rules">Правила раздела</a><br />
{% endif %}</div>
<div style="clear: both" class="pages right">{{ macros.pages(pages) }}</div>
{% if perms.post %}<a class="actionbtn reply mainbtn" href="reply.htm"><i class="far fa-comment-alt"></i> Ответить</a>{% endif %}
{% if perms.topic %}<a class="actionbtn newtopic" href="newtopic.htm"><i class="far fa-edit"></i> Новая тема</a>{% endif %}
{% if not is_guest() %}{% if not topic.bookmark %}<a class="actionbtn bookmark minbtn" href="change_mode.htm?mode=bookmark&amp;authkey={{ bookmark_key }}"><i class="far fa-bookmark"></i> В закладки</a>{% endif %}
{% if not topic.subscribe %}<a class="actionbtn subscribe minbtn" href="change_mode.htm?mode=subscribe&amp;authkey={{ bookmark_key }}" title="Подпишитесь на тему, чтобы получать уведомления о новых ответах в ней на EMail"><i class="far fa-envelope"></i> Подписаться</a>{%
else %}<a class="actionbtn subscribe minbtn" href="change_mode.htm?mode=subscribe&amp;cancel=1&amp;authkey={{ bookmark_key }}"><i class="fa fa-minus-circle"></i> Отписаться</a>{% endif %}{% endif %}
<a class="actionbtn print minbtn" href="javascript:window.print()"><i class="fa fa-print"></i> Распечатать</a>
<!--/noindex-->
<div class="posts h-feed">
{% endif %}
{% for post in posts %}
{% include 'stdforum/p_item.tpl' %}
{% endfor %}
{% if pages.page<pages.pages %}
<!--noindex-->
<a class="load_more" href="{{pages.page+1}}.htm?">Следующие сообщения &gt;&gt;&gt;</a>
<!--/noindex-->
{% endif %}
{% if not intb.is_ajax %}
</div>
<!--noindex-->
<div class="pages right">{{ macros.pages(pages) }}</div>
{% if perms.post %}<a class="actionbtn reply mainbtn" href="reply.htm"><i class="far fa-comment-alt"></i> Ответить</a>{% endif %}
{% if perms.topic %}<a class="actionbtn newtopic" href="newtopic.htm"><i class="far fa-edit"></i> Новая тема</a>{% endif %}
{% if not is_guest() %}{% if not topic.bookmark %}<a class="actionbtn bookmark minbtn" href="change_mode.htm?mode=bookmark&amp;authkey={{ bookmark_key }}"><i class="far fa-bookmark"></i> В закладки</a>{% endif %}
{% if not topic.subscribe %}<a class="actionbtn subscribe minbtn" href="change_mode.htm?mode=subscribe&amp;authkey={{ bookmark_key }}" title="Подпишитесь на тему, чтобы получать уведомления о новых ответах в ней на EMail"><i class="far fa-envelope"></i> Подписаться</a>{%
else %}<a class="actionbtn subscribe minbtn" href="change_mode.htm?mode=subscribe&amp;cancel=1&amp;authkey={{ bookmark_key }}"><i class="fa fa-minus-circle"></i> Отписаться</a>{% endif %}{% endif %}
<a class="actionbtn print minbtn" href="javascript:window.print()"><i class="fa fa-print"></i> Распечатать</a>

{% if forum.tags and tags|length>0%}
<ul class="tags">
  {% for tag in tags %}<li><a href="../?tags={{ tag|url_encode }}">{{ tag }}</a></li> {% endfor %}
</ul>
{% endif %}

{{ macros.sub_block(IntB_subactions['action_end']) }}

{% if is_moderator %}<div class="mod_actions">
{% if forum.selfmod>0 %}<a href="{{ url('moderate/'~topic.full_hurl~'curator.htm') }}">Куратор</a> | {% endif %}
{% if premod_count>0 %}
<a href="{{ url('moderate/'~topic.full_hurl~'/premod.htm') }}">{{ premod_count|incline('Премодерация: <b>%d</b> сообщение', 'Премодерация: <b>%d</b> сообщения','Премодерация: <b>%d</b> сообщений')|raw }}</a>
{% else %}На премодерации нет сообщений{% endif %} |
<a href="edit.htm?id={{ topic.first_post_id }}">Редактировать тему</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'move_posts.htm') }}">Перенести/удалить сообщения</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'trashbox.htm') }}">Корзина</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'view_log.htm') }}">Лог действий</a>
</div>{% endif %}

{% if get_opt('bottom_location') %}{{ macros.location(intb.location,intb.rss) }}{% endif %}

{% if perms.post %}
{% include 'stdforum/postform.tpl' %}

{% elseif topic.locked %}
<p class="nopost">Тема закрыта, новые ответы не принимаются.</p>
{% elseif forum.locked %}
<p class="nopost">Раздел закрыт, новые ответы не принимаются.</p>
{% else %}
<p class="nopost">У вас нет прав для отправки сообщений в эту тему.</p>
{% endif %}
</div>
<!--/noindex-->
{% endif %}
{% endblock %}
