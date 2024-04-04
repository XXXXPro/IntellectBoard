{% if forum.is_new %}{% set forum_class = forum.icon_new ? forum.icon_new : 'fas fa-comments' %}
{% else %}{% set forum_class = forum.icon_nonew ? forum.icon_nonew : 'far fa-comments' %}
{% endif %}
<tr class="gallery" id="title_f_{{ forum.id }}">
<td class="center">
<i class="forum_icon {{ forum_class }}"></i>
</td>
<td class="mainpage_forum">
<a class="mainpage_forumlink" rel="section" href="{{ url(forum.hurl~'/') }}">{{ forum.title }}</a>
<div>{{ forum.descr }}</div></td>
<td class="center">{{ forum.views }}</td>
<td>
{{ forum.topic_count|incline('%d альбом','%d альбома','%d альбомов') }}<br />
{{ (forum.post_count-forum.topic_count)|incline('%d комментарий','%d комментария','%d комментариев') }}
</td><td class="center">
{% if forum.extdata.last_topics %}
{% for item in forum.extdata.last_topics  %}
<a href="{{ url(forum.hurl~'/'~item.t_hurl) }}" title="{{ item.title }}"><img src="{{ url('f/up/1/pr/'~get_opt('gallery_mainpage_x')~'x'~get_opt('gallery_mainpage_y')~'/'~item.first_post_id~'-'~item.fkey~'.'~item.extension) }}" alt="{{ item.title }}" /></a> 
{% endfor %}
{% else %}
Нет альбомов
{% endif %}
</td></tr>
