{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
{% if not intb.is_ajax %}
<link rel="stylesheet" href="{{ style('blog.css') }}" />
{% endif %}
{% endblock %}
{% block content %}
{% if not intb.is_ajax %}
<div id="gallery_view_topic"  class="forum{{ forum.id }} topic{{ topic.id }} post">
<article class="h-entry postin">
<div class="blogpost_top pu"><div class="h-card p-author">
<div class="blogpost_avatar">
{% if topic.post.uid>3 %}<a href="{{ url(sprintf(get_opt('user_hurl'),article.uid)) }}">{{ macros.avatar(article.uid,article.avatar,article.author,"u-photo") }}</a>
{% else %}{{ macros.avatar(article.uid,article.avatar,article.author,"u-photo") }} {% endif %}
</div>
<span itemprop="creator">{{ macros.user(article.author,article.uid,false,"p-name username") }}</span><br />
{% if article.banned %}{% if article.banned_till>=now and article.banned_till!=4294967295 %}Изгнан пожизненно{% else %}Изгнан до {{ article.banned_till|shortdate }}{% endif %}
{% else %}{{ article.user_title }}{% endif %}
</div>
<time class="clear blogpost_date dt-published" itemprop="datePublished" content="{{ article.postdate|date('Y-m-dTH:i:sP') }}" datetime="{{ article.postdate|date('Y-m-d H:i:sP') }}"><i class="fa fa-calendar-alt"></i> {{ article.postdate|longdate }}</time>
</div>  

<h1>{{ topic.title }}</h1>
<p class="descr">{{ topic.descr }}</p>
{{ macros.sub_block(IntB_subactions['action_start']) }}
{% if article.attach %}
{% if gallery_mode == 1 %}
{% for attach in article.attach %}
<div class="attach_preview">
<img src="{{ url(attach.path) }}" alt="{{ topic.title~", "~attach.filename }}" title="{{ attach.descr }}" />
<p class="photo_descr">{{ attach.descr }}</p>
</div>
{% endfor %}
{% elseif gallery_mode == 2 %}
<table>
{% for attach in article.attach %}
<tr><td class="gallery_item attach_preview">
<a class="lightbox" href="{{ url(attach.path) }}" title="{{ attach.descr }}"><img src="{{ url('f/up/1/pr/'~gallery_x~'x'~gallery_y~'/'~article.id~'-'~attach.fkey~'.'~attach.extension) }}" alt="{{ topic.title~", "~attach.filename }}" />
</a>
</td>
<td class="gallery_item_info">
{% if attach.descr %}<p><strong>{{ attach.descr }}</strong></p>{% endif %}
{% if attach.exif %}
<ul>
<li>Дата съёмки: <span>{{ attach.exif.DateTimeOriginal }}</span></li>
{% if attach.exif.Make or attach.exif.Model %}<li>Камера: <span>{{ attach.exif.Make }} {{ attach.exif.Model }}</span></li>{% endif %}
{% if attach.exif.Software %}<li>ПО: <span>{{ attach.exif.Software }}</span></li>{% endif %}
{% if attach.exif.UserComment %}<li>Комментарий: <span>{{ attach.exif.UserComment  }}</span></li>{% endif %}
{% if attach.exif.FNumber %}<li>Диафрагма: <span>f/{{ attach.exif.FNumber|exif }}</span></li>{% endif %}
{% if attach.exif.ExposureTime %} <li>Выдержка: <span>{{ attach.exif.ExposureTime|exif }} с</span></li>{% endif %}
{% if attach.exif.ISOSpeedRatings %} <li>ISO: <span>{{ attach.exif.ISOSpeedRatings }}</span></li>{% endif %}
{% if attach.exif.FocalLength %}<li>Фокусное расстояние: <span>{{ attach.exif.FocalLength|exif }} мм</span></li>{% endif %}
{% if attach.exif.ExposureBiasValue %}<li>Коррекция EV: <span>{{ attach.exif.ExposureBiasValue|exif }}</span></li>{% endif %}
{% if attach.geo_longtitude and attach.geo_latitude %}<li>Координаты: <span><a href="https://maps.yandex.ru/?ll={{ attach.geo_longtitude }},{{ attach.geo_latitude }}&amp;z=15&amp;&amp;mode=search&amp;text={{ attach.geo_latitude }},{{ attach.geo_longtitude }}">{{ attach.geo_latitude|number_format(4) }}, {{ attach.geo_longtitude|number_format(4) }}</a></span></li>{% endif %}
</ul>
{% else %}<p class="photo_descr">Нет данных EXIF</p>
{% endif %}
</td></tr>
{% endfor %}
</table>
{% else %}
<ul class="photos attach_preview">
{% for attach in article.attach %}
<li class="gallery_item">
<a class="lightbox" href="{{ url(attach.path) }}" title="{{ attach.descr }}"><img src="{{ url('f/up/1/pr/'~gallery_x~'x'~gallery_y~'/'~article.id~'-'~attach.fkey~'.'~attach.extension) }}" alt="{{ topic.title~", "~attach.filename }}" />
</a>
</li>
{% endfor %}
</ul>
{% endif %} 
{% else %}
<p>Данная тема не содержит изображений.</p>
{% endif %}
<div class="blogpost_text ptext e-content">{{ article.text|raw }}</div>
{% if poll %}
{% include 'stdforum/poll.tpl' %}
{% endif %}

<!--noindex-->
<div class="blogpost_info noprint">
{% if  forum.tags and tags %}
<ul class="tags">
  {% for tag in tags %}<li><a class="p-category" href="../?tags={{ tag|url_encode }}">{{ tag }}</a></li> {% endfor %}
</ul>
{% endif %}
{% if forum.rate and not premod_mode %}<br />
{% include 'stdforum/rating.tpl' with {'post': article} %} &nbsp;
{% endif %}
{% if article.editable %} <a href="edit.htm?id={{ article.id }}"><i class="fas fa-pencil-alt"></i>Редактировать/добавить фото</a>{% endif %}
{% if gallery_mode %}<a href="{{ url(topic.full_hurl) }}"><i class="fas fa-search-minus"></i>В обычный режим</a>{% endif %}
{% if gallery_mode!=1 %}<a href="{{ url(topic.full_hurl) }}full_photos.htm"><i class="fas fa-search-plus"></i>Все фото в полный размер</a>{% endif %}
{% if gallery_mode!=2 %}<a href="{{ url(topic.full_hurl) }}exif.htm"><i class="fas fa-camera"></i>Данные EXIF</a>{% endif %}
{% if gallery_mode %}<a href="javascript:window.print()"><i class="fas fa-print"></i>Распечатать</a>{% endif %}
</div>  
</article>

{# {% if rules %}
<a href="../rules.htm" rel="nofollow" class="rules">Правила раздела</a><br />
{% endif %}</div> #}
<!--/noindex-->
<div class="posts h-feed">

{% if (posts|length>0) %}<h3 id="comments">{{ (topic.post_count-1)|incline('%d комментарий:','%d комментария:','%d комментариев:') }}</h3>
<meta itemprop="interactionCount" value="UserComments:{{posts|length}}"/>{% endif %}
{% endif %}
{% if comments_remain>0 and opts.sort!='DESC' %}<a href="?more={{ more }}#comments" class="more_comments noprint load_more">Показать еще {{ comments_remain|incline('%d комментарий','%d комментария','%d комментариев') }}</a>{% endif %}

<div class="comments">
{% for post in posts %}
<div class="comment post{% if post.marked %} marked{% endif %} fadeout" itemscope itemtype="http://schema.org/UserComments" id="p{{ post.id }}">
{% if get_opt('avatars','user') %}<div class="comment_avatar">
{{ macros.avatar(post.uid,post.avatar) }}
</div>{% endif %}
<div class="comment_body">
<div class="comment_top">
<link itemprop="url" href="#p{{ post.id }}">
{% if topic.post.banned %}<span class="author banned" title="{% if post.banned_till>=now and post.banned_till!=4294967295 %}Изгнан пожизненно{% else %}Изгнан до {{ post.banned_till|shortdate }}{% endif %}">
{% else %}<span class="author" title="{{ post.user_title }}">{% endif %}
{{ macros.user(post.author,post.uid) }}</span>
<time itemprop="commentTime" class="blogpost_date" datetime="{{ post.postdate|date('Y-m-d\\TH:i') }}" content="{{ post.postdate|date('Y-m-dTG:i') }}">{{ post.postdate|shortdate }}</time>
<div class="postnumber"><a href="post-{{ post.id }}.htm">#{% if opts.sort=='DESC' %}{{ topic.post_count-loop.index }}{% else %}{{ comments_remain+loop.index }}{% endif %}</a></div>
{% include 'stdforum/postact.tpl' %}
</div>
<div class="comment_text" itemprop="commentText">{{ post.text|raw }}</div>
</div></div>
{% endfor %}

{% if pages.page<pages.pages %}
<!--noindex-->
<a class="load_more noprint load_more" href="{{pages.page+1}}.htm?">Следующие сообщения &gt;&gt;&gt;</a>
<!--/noindex-->
{% endif %}
{% if not intb.is_ajax %}
</div>
<!--noindex-->

{{ macros.sub_block(IntB_subactions['action_end']) }}

{% if is_moderator %}<div class="mod_actions noprint">{%
if premod_count>0 %}<a href="{{ url('moderate/'~topic.full_hurl~'/premod.htm') }}">{{ premod_count|incline('Премодерация: <b>%d</b> сообщение', 'Премодерация: <b>%d</b> сообщения','Премодерация: <b>%d</b> сообщений')|raw }}</a>{%
else %}На премодерации нет сообщений{% endif %} |
<a href="edit.htm?id={{ topic.first_post_id }}">Редактировать тему</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'move_posts.htm') }}">Перенести/удалить сообщения</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'trashbox.htm') }}">Корзина</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'view_log.htm') }}">Лог действий</a>
</div>{% endif %}

{% if get_opt('bottom_location') %}{{ macros.location(intb.location,intb.rss) }}{% endif %}

{% if perms.post %}
<h3 class="noprint">Написать комментарий:</h3>
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
