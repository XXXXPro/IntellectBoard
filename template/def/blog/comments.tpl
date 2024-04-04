{% if (posts|length>0) %}<h3 id="comments">{{ (topic.post_count-1)|incline('%d комментарий:','%d комментария:','%d комментариев:') }}</h3>
<meta itemprop="interactionCount" value="UserComments:{{posts|length}}"/>{% endif %}
{% if comments_remain>0 and opts.sort!='DESC' %}<a href="?more={{ more }}#comments" class="more_comments">Показать еще {{ comments_remain|incline('%d комментарий','%d комментария','%d комментариев') }}</a>{% endif %}
{% endif %}

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
<div class="postnumber"><a href="post-{{ post.id }}.htm">#{% if opts.sort=='DESC' %}{{ topic.post_count-loop.index }}{% else %}{{ topic.post_count-1-posts|length+loop.index }}{% endif %}</a></div>
{% include 'stdforum/postact.tpl' %}
</div>
<div class="comment_text" itemprop="commentText">{{ post.text|raw }}</div>
</div></div>
{% endfor %}
