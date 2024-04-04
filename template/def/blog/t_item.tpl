{% import 'macro.tpl' as macros %}

<div class="blogpost" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
          <h3><a href="{{ url(topic.full_hurl) }}">{{ topic.title }}</a></h3>
<div class="blogpost_text">
{{ topic.text|raw }}
</div>
          <ul class="tags"><li>JS</li> <li>internationalization</li> <li>PHP</li> </ul>
          <div class="post-info">

            <span><i class="fa fa-folder-open"></i> JS </span>
            <span><i class="fa fa-comment"></i> {{ topic.post_count-1 }} </span>
            <span>{{ topic.first_post_date|long_date }}</span>
          </div>
</div>

<div class="anon_item post"><div class="user_info">
{{ macros.avatar(item.post.uid,item.post.avatar) }}</div>
{% if is_moderator %}<div class="modlink"><a class="confirm" href="{{ url('moderate/'~forum.hurl~'/'~item.t_hurl~'delete_topic.htm?authkey='~item.del_key) }}">&cross;</a></div>{% endif %}
{{ item.post.text|raw }}
{% if item.post.attach %}<div class="attach"><!--noindex-->Прикрепленные файлы:<ul>
{% for attach in item.post.attach %}
{% if attach.format=='image' and get_opt('pics','user') %}
<li class="attach_preview"><a class="lightbox" href="{{ url('f/up/1/'~attach.oid~'-'~attach.fkey~'/'~attach.filename) }}"><img src="{{ url('f/up/1/pr/'~get_opt('posts_preview_x')~'x'~get_opt('posts_preview_y')~'/'~attach.oid~'-'~attach.fkey~'.'~attach.extension) }}" alt="{{ attach.filename }}" /></a>
{% else %}<li class="attach_{{ attach.format }}"><a href="{{ url('f/up/1/'~attach.oid~'-'~attach.fkey~'/'~attach.filename) }}">{{ attach.filename }}</a> ({{ macro.filesize(attach.size) }}){% endif %}</li>
{% endfor
%}</ul><!--/noindex--></div>{% endif %}
<div class="sender">
{{ item.post.postdate|shortdate }} от
{% if item.post.uid>3 %}{{ macros.user(item.post.author,item.post.uid) }}
{% else %}анонима{% endif %}
</div>
<div class="actions">
{% if item.views %}{{ item.views|incline('%d просмотр','%d просмотра','%d просмотров') }} &nbsp;&nbsp;&nbsp; {% endif %}
{% if item.post_count > 1 %}<a href="{{ item.t_hurl }}">{{ (item.post_count-1)|incline('%d комментарий','%d комментария', '%d комментариев') }}</a> &nbsp;&nbsp;&nbsp; {% endif %}
<a href="{{ item.t_hurl }}#reply">Комментировать</a></div>
<br style="clear: both" />
</div>
