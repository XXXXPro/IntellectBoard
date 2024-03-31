{% import 'macro.tpl' as macros %}
<div class="anon_item post{% if item.marked %} marked{% endif %}" id="p{{ item.id }}">{% if get_opt('avatars','user') %}<div class="user_info">
{{ macros.avatar(item.uid,item.avatar) }}</div>{% endif %}
<div class="postin">
<div class="sender pu">
{% if item.uid>3 %}{{ macros.user(item.author,item.uid,'username') }}
{% else %}<span class="username">Аноним</span>{% endif %} написал {{ item.postdate|shortdate }} {% if is_moderator %} с IP: <a href="https://www.nic.ru/whois/?query={{ item.ip }}">{{ item.ip }}</a>{% endif %}
</div>
{% include 'stdforum/postact.tpl' with { 'post' : item } %}
<div class="ptext">{{ item.text|raw }}
{% if item.attach %}<div class="attach"><!--noindex-->Прикрепленные файлы:<ul>
{% for attach in item.attach %}
{% if attach.format=='image' and get_opt('pics','user') %}
<li class="attach_preview"><a class="lightbox" href="{{ url('f/up/1/'~attach.oid~'-'~attach.fkey~'/'~attach.filename) }}"><img src="{{ url('f/up/1/pr/'~get_opt('posts_preview_x')~'x'~get_opt('posts_preview_y')~'/'~attach.oid~'-'~attach.fkey~'.'~attach.extension) }}" alt="{{ attach.filename }}" /></a>
{% else %}<li class="attach_{{ attach.format }}"><a href="{{ url('f/up/1/'~attach.oid~'-'~attach.fkey~'/'~attach.filename) }}">{{ attach.filename }}</a> ({{ macro.filesize(attach.size) }}){% endif %}</li>
{% endfor 
%}</ul><!--/noindex--></div>{% endif %}
</div>
<div class="actions">
<a href="#reply">Ответить</a></div>
<br style="clear: both" />
</div></div>