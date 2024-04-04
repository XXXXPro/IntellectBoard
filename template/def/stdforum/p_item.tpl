{% import 'macro.tpl' as macros %}
<div class="fadeout post h-entry
{% if post.value==1 %} valued {% elseif post.value==-1 %} flood{% endif %}
{% if post.status==1 %} premod{% endif %}
{% if post.marked %} marked{% endif %}
{% if post.sticky %} sticky{% endif %}
{% if post.collapsed or post.sticky %} collapsed{% endif %}" id="p{{ post.id }}">
{% if post.uid!=2 and (post.relation!='ignore' or opts.filter=='nohide') %}
<div class="postin"><div class="pu h-card p-author{% if post.banned %} banned{% endif %}{% if post.uid==topic.first_post_uid %} topic_author{% endif %}{% if post.uid==topic.owner and topic.owner>0 and forum.selfmod>0 %} topic_curator{% endif %}">
{{ macros.user(post.author,post.uid,post.gender,"p-name") }}
{% if post.banned %}
<div class="group_banned">
   {% if post.banned_till>=now and post.banned_till!=4294967295 %}Изгнан до {{ post.banned_till|shortdate }}
   {% else %}Изгнан пожизненно{% endif %}
</div>
{% else  %}
<div class="group{{post.level}}">{{ post.user_title }}</div>
{% endif %}
{% if get_opt('avatars','user') %}
{% if post.uid>3 %}<a href="{{ url(sprintf(get_opt('user_hurl'),post.uid)) }}" class="avatar u-photo">{{ macros.avatar(post.uid,post.avatar) }}</a>
{% else %}{{ macros.avatar(post.uid,post.avatar) }}{% endif %}
{% endif %}
{% if post.uid>3 %}<!--noindex--><span class="puinfo">
Всего сообщений: {{ sprintf("%d",post.post_count) }}<br />
Зарегистрирован: {{ post.reg_date|shortdate }}<br />
{% if post.location %}Откуда: <span class="p-locality">{{ post.location }}</span><br />{% endif %}
Рейтинг пользователя: {{ sprintf("%d",post.user_rating) }}{%  if post.warnings %}
<br />Штрафных баллов: <span class="pwarn">{{ post.warnings }}</span>{% endif %}
</span>
{% if forum.rate and not premod_mode %}<br />
  {% include 'stdforum/rating.tpl' %}
{% endif %}
<!--/noindex-->
{% endif %}
</div>
<div class="pd">
<div class="ptop">
<time  datetime="{{ post.postdate|date('Y-m-dTH:i:sP') }}" class="dt-published">{{ post.postdate|longdate }}</time>{% if post.editcount>0 %}. <span class="edited">Редактировалось {{ post.editcount|incline('%d раз','%d раза','%d раз') }}, последний &mdash; <time datetime="{{ post.postdate|date('Y-m-dTH:i:sP') }}" class="dt-updated">{{ post.tx_lastmod|longdate }}</time></span>{% endif %}
{% if not post.preview and not post.t_title %}
<a href="post-{{post.id}}.htm" class="postnumber u-url u-uid">#{{ post.sticky ? 1 : loop.index+pages.start-has_sticky }}</a>
{% elseif post.preview %}<span class="postnumber">Предварительный просмотр</span>{% endif %}
</div>
{% if not post.preview %}{% include 'stdforum/postact.tpl' %}{% endif %}
<div class="pmain"><div class="ptext e-content{% if not post.t_title %} p-name{% endif %}">
{% if post.t_title %}<h2 class="pt_title p-name"><a href="{{ url(post.full_hurl)}}post-{{post.id}}.htm">{{ post.t_title }}</a></h2>{% endif %}
{{ post.text|raw }}</div>
{% if post.attach %}{% include 'stdforum/attach.tpl' %}{% endif%}
{% if post.signature and get_opt('signatures','user') %}
<hr />
<small class="psign">
{{ post.signature|raw }}
</small>{% endif %}</div>
<br style="clear: both"></div>
</div>
{% elseif post.relation=='ignore' %}
<div class="postsys">Сообщение пользователя {{ macros.user(post.author,post.uid) }} скрыто, так как он внесен вами в список игнорируемых. </div>
{% else %}
<div class="postsys">{{ post.text|raw }} </div>
{% endif %}
</div>


