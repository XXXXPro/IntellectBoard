{% if forum.is_new %}{% set forum_class = forum.icon_new ? forum.icon_new : 'fas fa-comments' %}
{% else %}{% set forum_class = forum.icon_nonew ? forum.icon_nonew : 'far fa-comments' %}
{% endif %}
<tr class="micro" id="title_f_{{ forum.id }}">
<td class="center">
<i class="forum_icon {{ forum_class }}"></i>
</td>
<td class="mainpage_forum" colspan="4">
<a class="mainpage_forumlink" rel="section" href="{{ url(forum.hurl~'/') }}">{{ forum.title }}</a>
<br />{{ forum.descr }}</td></tr>