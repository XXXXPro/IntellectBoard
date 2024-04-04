{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
{% if not intb.is_ajax %}
<link rel="stylesheet" type="text/css" href="{{ style('blog.css') }}" />
{% endif %}
{% endblock %}
{% block content %}
{% if not intb.is_ajax %}
<div id="blog_view_topic" class="forum{{ forum.id }} topic{{ topic.id}}">
<article itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting" class="h-entry post postin">
<div class="blogpost_top pu"><div class="h-card p-author">
<div class="blogpost_avatar">
{% if topic.post.uid>3 %}<a href="{{ url(sprintf(get_opt('user_hurl'),article.uid)) }}">{{ macros.avatar(article.uid,article.avatar,article.author,"u-photo") }}</a>
{% else %}{{ macros.avatar(article.uid,article.avatar,article.author,"u-photo") }} 
{% endif %}
</div>
<span itemprop="creator">{{ macros.user(article.author,article.uid,false,"p-name username") }}</span><br />
{% if article.banned %}{% if article.banned_till>=now and article.banned_till!=4294967295 %}Изгнан пожизненно{% else %}Изгнан до {{ article.banned_till|shortdate }}{% endif %}
{% else %}{{ article.user_title }}
{% endif %}
</div>
<time class="clear blogpost_date dt-published" itemprop="datePublished" content="{{ article.postdate|date('Y-m-dTH:i:sP') }}" datetime="{{ article.postdate|date('Y-m-d H:i:sP') }}"><i class="fa fa-calendar-alt"></i> {{ article.postdate|longdate }}</time>
</div>  
<h1 itemprop="name" class="p-name">{{ topic.title }}</h1>
{% if topic.descr %}<p class="description" itemprop="description">{{ topic.descr }}</p>{% endif %}
<div class="blogpost_text ptext e-content" itemprop="articleBody">
{{ article.text|raw }}
{% if article.lastmod %}<small>Последний раз редактировалось {{ article.lastmod|longdate }}</small>{% endif %}
<a class="u-url" rel="self" href="{{ http(url(topic.full_hurl)) }}"></a>
<meta itemprop="mainEntityOfPage" content="{{ http(url(topic.full_hurl)) }}" />
<meta itemprop="dateModified" content="{{ article.lastmod|date('Y-m-dTH:i:sP') }}" />
<meta itemprop="image" content="{{ article_pic }}" />
<span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
<meta itemprop="address" content="Internet, {{ http(url('')) }}" />
<meta itemprop="telephone" content="+10005550001" />
<meta itemprop="name" content="{{ get_opt('site_title') }}"/>
<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
<link itemprop="url contentUrl" href="{{ http(url(get_opt('site_picture'))) }}"/>
<meta itemprop="width" content="32" />
<meta itemprop="height" content="32" />
</div>
{% if poll %}
{% include 'stdforum/poll.tpl' %}
{% endif %}
{% if article.attach %}{% include 'stdforum/attach.tpl' with {'post': article} %}{% endif %}

<div class="blogpost_info">
{% if tags %}
<ul class="tags">
  {% for tag in tags %}<li><a class="p-category" href="../?tags={{ tag|url_encode }}">{{ tag }}</a></li> {% endfor %}
</ul>
{% endif %}
{% if forum.rate and not premod_mode %}<br />
{% include 'stdforum/rating.tpl' with {'post': article} %} &nbsp;
{% endif %}
{% if article.editable %}<a href="edit.htm?id={{ article.id }}"><i class="fas fa-pencil-alt"></i> Редактировать</a>{% endif %}
<a href="javascript:window.print()"><i class="fa fa-print"></i> Распечатать</a>
</div>    
</article>

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
<div class="comment_body postin">
<div class="comment_top pu{% if post.uid==topic.first_post_uid %} topic_author{% endif %}{% if post.uid==topic.owner and topic.owner>0 and forum.selfmod>0 %} topic_curator{% endif %}">
<link itemprop="url" href="#p{{ post.id }}">
{% if topic.post.banned %}<span class="author banned" title="{% if post.banned_till>=now and post.banned_till!=4294967295 %}Изгнан пожизненно{% else %}Изгнан до {{ post.banned_till|shortdate }}{% endif %}">
{% else %}<span class="author" title="{{ post.user_title }}">{% endif %}
{{ macros.user(post.author,post.uid,'username') }}</span>
<time itemprop="commentTime" class="blogpost_date" datetime="{{ post.postdate|date('Y-m-d\\TH:i') }}" content="{{ post.postdate|date('Y-m-dTG:i') }}">{{ post.postdate|shortdate }}</time>
<div class="postnumber"><a href="post-{{ post.id }}.htm">#{% if opts.sort=='DESC' %}{{ topic.post_count-loop.index }}{% else %}{{ comments_remain+loop.index }}{% endif %}</a></div>
{% include 'stdforum/postact.tpl' %}
</div>
<div class="comment_text ptext" itemprop="commentText">{{ post.text|raw }}</div>
</div></div>
{% endfor %}

{% if comments_remain>0 and opts.sort=='DESC' %}<a href="?more={{ more }}#p{{ posts[posts|length-1].id }}" class="load_more more_comments">Показать еще {{ comments_remain|incline('%d комментарий','%d комментария','%d комментариев') }}</a>{% endif %}

{% if not intb.is_ajax %}
</div>
{% if perms.post %}
<!--/noindex-->
<h3 class="noprint">Написать комментарий:</h3>
{% include 'stdforum/postform.tpl' %}

{% elseif topic.locked %}
<p class="nopost">Комментирование данной записи запрещено.</p>
{% elseif forum.locked %}
<p class="nopost">Раздел закрыт, новые ответы не принимаются.</p>
{% else %}
<p class="nopost">У вас нет прав для отправки комментариев.</p>
<!--/noindex-->
{% endif %}

{% if is_moderator %}<div class="mod_actions">{%
if premod_count>0 %}<a href="{{ url('moderate/'~topic.full_hurl~'/premod.htm') }}">{{ premod_count|incline('Премодерация: <b>%d</b> сообщение', 'Премодерация: <b>%d</b> сообщения','Премодерация: <b>%d</b> сообщений')|raw }}</a>{%
else %}На премодерации нет сообщений{% endif %} |
<a href="edit.htm?id={{ topic.first_post_id }}">Редактировать тему</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'move_posts.htm') }}">Перенести/удалить сообщения</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'trashbox.htm') }}">Корзина</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'view_log.htm') }}">Лог действий</a>
</div>{% endif %}
</div>
{% endif %}
{% endblock %}