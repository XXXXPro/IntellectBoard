{% import 'macro.tpl' as macros %}
<tr class="topic_item{% if item.valued_count>0 %} valued{% endif %}"><td class="t_title">
<a class="{% if item.new %}t_new fas fa-comment{% else %}t_icon far fa-comment{% endif %}" href="{{ item.t_hurl }}new.htm">
{%if item.sticky %}<span class="t_sticky fas fa-thumbtack"></span>{% endif %}
{%if item.locked %}<span class="t_locked fas fa-lock"></span>{% endif %}
{%if item.posted %}<span class="t_posted far fa-edit"></span>{% endif %}
{%if item.poll %}<span class="t_poll far fa-question-circle"></span>{% endif %}
</a>
<a href="{{ item.t_hurl }}">{{ item.title }}</a> {{ macros.pages(item.pages,item.t_hurl,1) }}<br />
{{ item.descr }}
{% if item.curator_id %}<br /><small>Куратор: {{ macros.user(item.curator_display_name,item.curator_id) }}</small>{% endif %}</td><td>{{ item.views }}</td>
<td>{{ item.post_count }}<br />{% if item.valued_count or item.flood_count>0 %}<small>{% 
if item.valued_count %}{{ item.valued_count|incline("%d ценное","%d ценных","%d ценных") }}{% endif %}{% 
if item.flood_count>0 %}&nbsp; {{ sprintf("%.0f%% флуда",100*item.flood_count/item.post_count) }}{% endif %}</small>{% endif %}</td>
<td>{{ macros.user(item.starter,item.starter_id) }}<br />{{ item.first_post_date|shortdate }}</td>
<td>{{ macros.user(item.last_poster,item.last_poster_id) }}<br />
<a class="t_last" href="{{ item.t_hurl }}last.htm">{{ item.last_post_date|shortdate }} <i class="fas fa-arrow-circle-right"></i></a></td></tr>
