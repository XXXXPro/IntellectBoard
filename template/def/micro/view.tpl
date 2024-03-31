{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<style>
#ib_all ul.microblog {list-style: none; padding: 0; font-size: 12px;}
#ib_all ul.microblog>li {border-top: 1px solid #EAF4FF; padding: 8px 4px; margin: 8px 0;position:relative}
#ib_all ul.microblog>li:first-child {border-top: 0}
#ib_all ul.microblog .mb_item {font-size:120%; padding:8px 0}
#ib_all ul.microblog .mb_top .avatar {height: 16px; width: 16px; vertical-align: bottom}
#ib_all ul.microblog .mb_bottom,ul.microblog .mb_date  {color: #657786}
#ib_all ul.microblog .prating {display: inline-block; font-size: 140%}
#ib_all ul.microblog .mb_bottom br {display: none}
#ib_all ul.microblog .banned { text-decoration: line-through; }
#ib_all #micro_view .smallform { text-align: right; }

#ib_all .postact { list-style: none; display: block; float: right }

#ib_all .post a.blocklink { padding: 9px; border: #dcddde 1px solid; display: block; text-decoration: none; max-width: 480px; margin: 6px 0; }
#ib_all .post a.blocklink b { display: block; font-size: 120%; border-bottom: #dcddde 1px solid; padding: 0 6px 6px 6px; color: #0101C4; margin-bottom: 10px; }
#ib_all .post a.blocklink img { display: block; margin: 10px 0; max-height: 200px }
#ib_all .post a.blocklink .linkdesc { padding: 0 6px; display: block; }
#ib_all .post a.blocklink .linkdomain { display: block; font-size: 100%; color: #A3CEFF; font-weight: bold; padding: 6px 6px 0 6px }
</style>
{% endblock %}
{% block content %}
<div id="micro_view" class="forum{{ forum.id }}">
<h1>{{ forum.title }}</h1>
{% if start_text %}
<div class="start_text">
{{ start_text|raw }}
{% if rules %} <a href="rules.htm" class="rules">Правила раздела</a>{% endif %}
</div>
{% else %}
<p class="descr">{{ forum.descr }}</p>
{% if rules %} <a href="rules.htm" class="rules">Правила раздела</a>{% endif %}
{% endif %}

{% if perms.topic %}{% include 'stdforum/postform.tpl' %}{% endif %}

{{ macros.sub_block(IntB_subactions['action_start']) }}

<ul class="microblog h-feed">
{% for topic in topics %}
<li id="p{{topic.post.id}}" class="post h-entry">
<div class="mb_top"><span class="h-card p-author">
{% if topic.post.uid>3 %}<a href="{{ url(sprintf(get_opt('user_hurl'),topic.post.uid)) }}" class="avatar">{{ macros.avatar(topic.post.uid,topic.post.avatar,topic.post.author,"u-photo") }}</a>
{% else %}{{ macros.avatar(topic.post.uid,topic.post.avatar,topic.post.author,"u-photo") }} {% endif %}
{% if topic.post.banned %}<span class="author banned" title="{% if topic.post.banned_till>=now and topic.post.banned_till!=4294967295 %}Изгнан пожизненно{% else %}Изгнан до {{ topic.post.banned_till|shortdate }}{% endif %}">
{% else %}<span class="author p-name" title="{{ topic.post.user_title }}">{% endif %}
{{ macros.user(topic.post.author,topic.post.uid,"p-name") }}
</span></span>
<time datetime="{{ topic.post.postdate|date('Y-m-d\\TH:i:sP') }}" class="mb_date dt-published"> · {{ topic.post.postdate|shortdate }}</time>
<div class="postact">
{% if is_moderator %}<a href="https://nic.ru/whois/?searchWord={{ topic.post.ip }}">IP</a> {% endif %}
{% if is_moderator %}<a href="edit.htm?id={{ topic.post.id }}" title="Редактировать"><i class="fas fa-pencil-alt"></i></a>{% endif %}
{% if is_moderator
%}<a class="ajax confirm" title="Удалить" href="{{ url('moderate/'~topic.full_hurl~'delete_post.htm?id='~post.id~'&authkey='~delete_key) }}"><i class="far fa-trash-alt"></i></a>{%
endif %}</div>
</div>
<div class="mb_item e-content p-name">{{ topic.post.text|raw }}</div>
<div class="mb_bottom">
{% if not post.preview %}{% include 'stdforum/postact.tpl' with { 'post': topic.post } %}{% endif %}
{% if forum.rate and not premod_mode %}<br />
{% include 'stdforum/rating.tpl' with {'post': topic.post} %}
{% endif %}
</div>
</li>
{% endfor  %}
{% if topics|length==0 %}<li>Хорошая новость: у нас пока еще нет новостей!</li>{% endif %}
</ul>

<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
{% if perms.topic %}<a class="actionbtn newtopic mainbtn" href="newtopic.htm">Новая запись</a>{% endif %}

{{ macros.sub_block(IntB_subactions['action_end']) }}

<!--noindex-->
<form action="params_forum.htm" method="get" class="smallform">
<fieldset><legend>Настройки отображения раздела</legend>
Показывать по {{ macros.input('perpage',opts.perpage,3) }} записей, вывести {{ macros.select('filter',opts.filter,{all:'все записи',valued:'только ценные',noflood:'кроме флуда' }) }},
{% if forum.owner==0 %} созданные <input type="text" name="author_name" value="{{ opts.author_name }}" size="12" maxlength="32" placeholder="имя автора" /> {% endif %}
<button type="submit" name="submit">Показать</button>
<button type="submit" name="clear">Сброс</button>
</fieldset>
</form>

<div>
{% if not is_guest() %}<span class="lastvisit">
{% if forum.visit2 %}Последний раз вы заходили в этот раздел {{ forum.visit2|longdate }}.{% else %}
Вы впервые в этом разделе. Добро пожаловать!{% endif %}</span><br />
{% endif %}
{% if roles.moderator|length>0 %}Модераторы: {% for user in roles.moderator %}{% if not loop.first %}, {% endif %}{{ macros.user(user.display_name,user.id) }}{% endfor %}<br />{% endif %}
{% if roles.expert|length>0 %}Эксперты: {% for user in roles.expert %}{% if not loop.first %}, {% endif %}{{ macros.user(user.display_name,user.id) }}{% endfor %}<br />{% endif %}
{% if rules %}<a href="rules.htm" class="rules">Правила раздела</a>{% endif %}
</div>
<!--/noindex-->

{% if is_moderator %}<div class="mod_actions">
{% if user.id==forum.owner and not is_guest() %}<a href="owner_settings.htm">Настройки раздела</a> | {% endif %}
{% if intb.is_admin or get_opt('moder_edit_rules') %}<a href="{{ url('moderate/'~forum.hurl~'/edit_rules.htm') }}">Правила</a> | {% endif %}
{% if intb.is_admin or get_opt('moder_edit_foreword') %}<a href="{{ url('moderate/'~forum.hurl~'/edit_foreword.htm') }}">Вступительное слово</a> | {% endif %}
{% if premod_count>0 %}<a href="{{ url('moderate/'~forum.hurl~'/premod.htm') }}">{{ premod_count|incline('<b>%d</b> сообщение на премодерации', '<b>%d</b> сообщения на премодерации','<b>%d</b> сообщений на премодерации')|raw }}</a>
{% else %}На премодерации нет сообщений{% endif %} |
<a href="{{ url('moderate/'~forum.hurl~'/mod_forum.htm') }}">Модерировать раздел</a> |
<a href="{{ url('moderate/'~forum.hurl~'/trashbox.htm') }}">Корзина</a> |
<a href="{{ url('moderate/'~forum.hurl~'/view_log.htm') }}">Лог действий</a>
</div>{% endif %}

</div>
{% endblock %}
