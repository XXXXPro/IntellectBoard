{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('blog.css') }}" />
{% endblock %}
{% block content %}
<div id="blog_forum_view" itemscope itemtype="http://schema.org/Blog" class="h-feed">
<h1 itemprop="name">{{ forum.title }}</h1>
{% if start_text %}
<div class="start_text">
{{ start_text|raw }}
{% if rules %} <a href="rules.htm" class="rules">Правила раздела</a>{% endif %}
</div>
{% else %}
<p class="descr">{{ forum.descr }}</p>
{% if rules %} <a href="rules.htm" class="rules">Правила раздела</a>{% endif %}
{% endif %}

{{ macros.sub_block(IntB_subactions['action_start']) }}

<div class="pages right">{{ macros.pages(pages) }}</div>
{% if perms.topic %}<a class="actionbtn newtopic mainbtn" href="newtopic.htm"><i class="fas fa-pencil-alt"></i> Написать в блог</a>{% endif %}

{% if sticky|length %}
<div class="blog_sticky"><h4>Рекомендуем обратить внимание:</h4>
<ul>
{% for topic in sticky %}
<li><a href="{{ topic.t_hurl }}">{{ topic.title }}</a> — {{ topic.first_post_date|shortdate }}</li> 
{% endfor %}
</ul>
</div>
{% endif %}

{% for topic in topics %}
<div class="blogpost h-entry" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
<h3><a class="p-name u-url u-uid" href="{{ topic.t_hurl }}">{{ topic.title }}</a></h3>
<span class="h-card p-author">
{% if topic.post.uid>3 %}<a href="{{ url(sprintf(get_opt('user_hurl'),topic.post.uid)) }}" class="avatar">{{ macros.avatar(topic.post.uid,topic.post.avatar,topic.post.author,"u-photo") }}</a>
{% else %}{{ macros.avatar(topic.post.uid,topic.post.avatar,topic.post.author,"u-photo") }} {% endif %}
{% if topic.post.banned %}<span class="author banned" title="{% if topic.post.banned_till>=now and topic.post.banned_till!=4294967295 %}Изгнан пожизненно{% else %}Изгнан до {{ topic.post.banned_till|shortdate }}{% endif %}">
{% else %}<span class="author" title="{{ topic.post.user_title }}">{% endif %}
{{ macros.user(topic.post.author,topic.post.uid,false,"p-name u-url") }}
</span>
</span>
<time class="blogpost_date dt-published" itemprop="datePublished" content="{{ topic.first_post_date|date('Y-m-dTH:i:sP') }}" datetime="{{ topic.first_post_date.postdate|date('Y-m-d H:i:sP') }}"><i class="fa fa-calendar-alt"></i> {{ topic.first_post_date|longdate }}</time>
<div class="blogpost_text e-content">
{{ topic.post.text|raw }}
</div>

{% if item.post.attach %}<div class="attach">Прикрепленные файлы:<ul>
{% for attach in item.post.attach %}
{% if attach.format=='image' and get_opt('pics','user') %}
<li class="attach_preview"><a class="lightbox" href="{{ url('f/up/1/'~attach.oid~'-'~attach.fkey~'/'~attach.filename) }}"><img src="{{ url('f/up/1/pr/'~get_opt('posts_preview_x')~'x'~get_opt('posts_preview_y')~'/'~attach.oid~'-'~attach.fkey~'.'~attach.extension) }}" alt="{{ attach.filename }}" /></a>
{% else %}<li class="attach_{{ attach.format }}"><a href="{{ url('f/up/1/'~attach.oid~'-'~attach.fkey~'/'~attach.filename) }}">{{ attach.filename }}</a> ({{ macro.filesize(attach.size) }}){% endif %}</li>
{% endfor %}
</ul></div>
{% endif %}

<div class="blogpost_info">
{% if topic.tags %}
<ul class="tags">
  {% for tag in topic.tags %}<li><a class="p-category" href="?tags={{ tag|url_encode }}">{{ tag }}</a></li> {% endfor %}
</ul>
{% endif %}

{% if forum.rate and not premod_mode %}<br />{% include 'stdforum/rating.tpl' with {'post': topic.post} %} &nbsp; {% endif %}
{% if is_moderator %}<a href="{{ topic.t_hurl }}/edit.htm?id={{ topic.post.id }}"><i class="fas fa-pencil-alt"></i> Редактировать</a>{% endif %}
<span><a href="{{ topic.t_hurl }}#comments"><i class="fa fa-comment-alt"></i> {% if topic.post_count>1 %}{{ (topic.post_count-1)|incline("%d комментарий", "%d комментария", "%d комментариев") }}
{% else %}Написать комментарий{% endif %}</a>
 </span>
</div>
</div>
{% endfor %}

{% if topics|length==0 %}<p>Пока в этом блоге нет ни одного сообщения.</p>{% endif %}

{{ macros.sub_block(IntB_subactions['action_end']) }}

<br style="clear: both" />
{% if pages.pages>1 %}<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>{% endif %}
</div>
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
{% endblock %}
