<!DOCTYPE html>
<head>
<link rel="stylesheet" href="{{ style('s.css') }}"/>
<link rel="stylesheet" href="{{ style('blog.css') }}" />
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
</style>
<title>Предварительный просмотр сообщения</title>
</head>
<body>
<div id="ib_all">
<div id="stdforum_view_topic"  class="forum{{ forum.id }} topic{{ topic.id }}">
<h1>{{ topic.title }}</h1>
<p class="descr">{{ topic.descr }}</p>

<ul class="microblog h-feed">
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
</ul>

</div>
<!--##DEBUG#-->
</div>
</body>
