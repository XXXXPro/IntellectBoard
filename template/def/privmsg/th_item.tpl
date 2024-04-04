{% import 'macro.tpl' as macros %}
<tr class="thread_item{% if item.unread %} thread_unread{% endif %}"><td class="t_title">
<a href="{{ item.id }}/new.htm">{% if item.unread %}<i class="fas fa-envelope"></i>{% else %}<i class="far fa-envelope-open"></i>{% endif %}</a>
<a href="{{ item.id }}/">{{ item.title }}</a></td><td>{{ item.unread }}</td>
<td>{{ item.total }}</td>
<td>{% for user in item.users %}{% if loop.index>1 %}, {% endif %}{{ macros.user(user.display_name,user.id,user.avatar) }}{% endfor %}</td>
<td><a class="t_last" href="{{ item.id }}/last.htm">{{ item.last_post_date|shortdate }}</a></td></tr>
