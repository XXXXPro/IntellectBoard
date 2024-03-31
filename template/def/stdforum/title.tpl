{% import 'macro.tpl' as macros %}
{% if forum.is_new %}{% set forum_class = forum.icon_new ? forum.icon_new : 'fas fa-comments' %}
{% else %}{% set forum_class = forum.icon_nonew ? forum.icon_nonew : 'far fa-comments' %}
{% endif %}
<tr class="stdforum" id="title_f_{{ forum.id }}">
<td class="center">
<i class="forum_icon {{ forum_class }}"></i>
</td>
<td class="mainpage_forum">
<a class="mainpage_forumlink" rel="section" href="{{ url(forum.hurl~'/') }}">{{ forum.title }}</a>
<div>{{ forum.descr }}</div></td>
<td class="center">{{ forum.views }}</td>
<td>{{ forum.topic_count|incline('%d тема','%d темы','%d тем') }}<br />
{{ forum.post_count|incline('%d сообщение','%d сообщения','%d сообщений') }}</td>
<td class="center">{% if forum.postdate %}
{{ macros.user(forum.author,forum.uid) }}
<br />{{ forum.postdate|shortdate }}
{% else %}
Нет сообщений
{% endif %}
</td></tr>
