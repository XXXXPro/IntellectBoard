{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
{% if subforums %}<link rel="stylesheet" href="{{ style('forums.css') }}" />{% endif %}
<link rel="stylesheet" href="{{ style('blog.css') }}" />
{% endblock %}
{% block content %}
<div id="gallery_view_forum" class="forum{{ forum.id }}">
<h1>{{ forum.title }}</h1>
{% if start_text %}
<div class="start_text">
{{ start_text|raw }}
{% if rules %} <a href="rules.htm" class="rules">Правила раздела</a>{% endif %}
</div>
{% else %}
<p class="descr">{{ forum.descr }}{% if rules %} <a href="rules.htm" class="rules">Правила раздела</a>{% endif %}</p>
{% endif %}

{{ macros.sub_block(IntB_subactions['action_start']) }}

<!--noindex-->
<p>
{% if not is_guest() %}<span class="lastvisit">
{% if forum.visit2 %}Последний раз вы заходили в этот раздел {{ forum.visit2|longdate }}.{% else %}
Вы впервые в этом разделе. Добро пожаловать!{% endif %}</span><br />
{% endif %}
{% if roles.moderator|length>0 %}Модераторы: {% for user in roles.moderator %}{% if not loop.first %}, {% endif %}{{ macros.user(user.display_name,user.id) }}{% endfor %}<br />{% endif %}
{% if roles.expert|length>0 %}Эксперты: {% for user in roles.expert %}{% if not loop.first %}, {% endif %}{{ macros.user(user.display_name,user.id) }}{% endfor %}<br />{% endif %}
{% if not is_guest() %} <a class="small_link" href="mark_all.htm">Отметить все темы раздела как прочитанные</a>{% endif %}
</p>
<div class="pages right">{{ macros.pages(pages) }}</div>
{% if perms.topic %}<a class="actionbtn newtopic mainbtn" href="newtopic.htm"><i class="far fa-edit"></i> Новый альбом</a>{% endif %}
<!--/noindex-->

{% if topics %}<ul class="photos">
{% for item in topics %}
<li class="topic_item{% if item.valued_count>0 %} valued{% endif %}{% if item.new %} t_new{% endif %}">
<a href="{{ item.t_hurl }}"><img src="{{ url('f/up/1/pr/'~gallery_x~'x'~gallery_y~'/'~item.first_post_id~'-'~item.fkey~'.'~item.extension) }}" alt="{{ item.title }}" />
<div><span>{{ item.title }}</span><br />
{{ item.attach_count|incline("%d фотография","%d фотографии","%d фотографий") }}, {{ item.first_post_date|shortdate }}{% if item.post_count>1 %}, <i class="far fa-comment"></i> {{ item.post_count-1 }}{% endif %}
</div></a>
</li>
{% endfor %}
</ul>
{% else %}
<p>В разделе пока нет ни одной фотографии. Вы можете стать первым, кто её загрузит!</p>
{% endif %}

<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
{% if perms.topic %}<a class="actionbtn newtopic mainbtn" href="newtopic.htm"><i class="far fa-edit"></i> Новый альбом</a>{% endif %}

{{ macros.sub_block(IntB_subactions['action_end']) }}

{% if is_moderator %}<div class="mod_actions">
{% if user.id==forum.owner and not is_guest() %}<a href="owner_settings.htm">Настройки раздела</a> | {% endif %}
{% if intb.is_admin or get_opt('moder_edit_rules') %}<a href="{{ url('moderate/'~forum.hurl~'/edit_rules.htm') }}">Правила</a> | {% endif %}
{% if intb.is_admin or get_opt('moder_edit_foreword') %}<a href="{{ url('moderate/'~forum.hurl~'/edit_foreword.htm') }}">Вступительное слово</a> | {% endif %}
{% if premod_count>0 %}<a href="{{ url('moderate/'~forum.hurl~'/premod.htm') }}">{{ premod_count|incline('<b>%d</b> сообщение на премодерации', '<b>%d</b> сообщения на премодерации','<b>%d</b> сообщений на премодерации')|raw }}</a>{%
else %}На премодерации нет сообщений{% endif %} |
<a href="{{ url('moderate/'~forum.hurl~'/mod_forum.htm') }}">Модерировать раздел</a> |
<a href="{{ url('moderate/'~forum.hurl~'/trashbox.htm') }}">Корзина</a> |
<a href="{{ url('moderate/'~forum.hurl~'/view_log.htm') }}">Лог действий</a>
</div>{% endif %}

{% endblock %}
