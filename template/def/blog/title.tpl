{% import 'macro.tpl' as macros %}
{% if forum.is_new %}{% set forum_class = forum.icon_new ? forum.icon_new : 'fas fa-comments' %}
{% else %}{% set forum_class = forum.icon_nonew ? forum.icon_nonew : 'far fa-comments' %}
{% endif %}
<tr class="blog" id="title_f_{{ forum.id }}">
<td class="center">
<i class="forum_icon {{ forum_class }}"></i>
</td>
<td class="mainpage_forum" colspan="4">
<a class="mainpage_forumlink" rel="section" href="{{ url(forum.hurl~'/') }}">{{ forum.title }}</a>
<div>{{ forum.descr }}</div>
{% if forum.extdata.last_topics|length>0 %}
<ul>
{% for item in forum.extdata.last_topics  %}
<li><i class="far fa-calendar-alt"></i> {{ item.first_post_date|shortdate }} — <a href="{{ url(forum.hurl~'/'~item.t_hurl) }}">{{ item.title }}</a> {{ item.descr }}</li>
{% endfor %}
</ul>
{% endif %}
</td></tr>
