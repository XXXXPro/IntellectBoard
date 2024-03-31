<!DOCTYPE html>
<head>
<link rel="stylesheet" href="{{ style('s.css') }}"/>
<link rel="stylesheet" href="{{ style('blog.css') }}" />
<style>
#ib_all { padding: 0 5px }
</style>
<title>Предварительный просмотр сообщения</title>
</head>
<body>
<div id="ib_all">
<div id="stdforum_view_topic"  class="forum{{ forum.id }} topic{{ topic.id }}">
<h1>{{ topic.title }}</h1>
<p class="descr">{{ topic.descr }}</p>
<div class="posts">

<div class="comment post{% if post.marked %} marked{% endif %} fadeout" itemscope itemtype="http://schema.org/UserComments" id="p{{ post.id }}">
<div class="comment_body">
<div class="comment_top">
{% if topic.post.banned %}<span class="author banned" title="{% if post.banned_till>=now and post.banned_till!=4294967295 %}Изгнан пожизненно{% else %}Изгнан до {{ post.banned_till|shortdate }}{% endif %}">
{% else %}<span class="author" title="{{ post.user_title }}">{% endif %}
{{ macros.user(post.author,post.uid) }}</span>
<time itemprop="commentTime" class="blogpost_date" datetime="{{ post.postdate|date('Y-m-d\\TH:i') }}" content="{{ post.postdate|date('Y-m-dTG:i') }}">{{ post.postdate|shortdate }}</time>
<div class="postnumber"><a href="#">Предпросмотр</a></div>
{% include 'stdforum/postact.tpl' %}
</div>
<div class="comment_text" itemprop="commentText">{{ post.text|raw }}</div>
</div></div>

</div>
</div>
<!--##DEBUG#-->
</div>
</body>
