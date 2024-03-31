{% import 'macro.tpl' as macros %}
<div class="attach"><!--noindex-->Прикрепленные файлы:<ul>
{% for attach in post.attach %}{% if not attach.processed %}
{% if attach.format=='image' and get_opt('pics','user') and attach.fkey!='#' %}
<li class="attach_preview"><a class="lightbox" href="{{ url(attach.path) }}">
<img src="{{ url('f/up/1/pr/'~get_opt('posts_preview_x')~'x'~get_opt('posts_preview_y')~'/'~attach.oid~'-'~attach.fkey~'.'~attach.extension) }}" alt="{{ attach.filename }}" /><br />
{{ url(attach.filename) }}</a> ({{ macros.filesize(attach.size) }})
</li>
{% elseif attach.format=='audio' %}
<li class="attach_audio"><audio controls><source src="{{ url('f/up/1/'~attach.oid~'-'~attach.fkey~'/'~attach.filename) }}"/></audio> 
<a href="{{ url(attach.path) }}">{{ attach.filename }}</a> ({{ macros.filesize(attach.size) }})</li>
{% elseif attach.format=='video' %}
<li class="attach_video"><video controls><source src="{{ url('f/up/1/'~attach.oid~'-'~attach.fkey~'/'~attach.filename) }}"/></video> 
<a href="{{ url(attach.path) }}">{{ attach.filename }}</a> ({{ macros.filesize(attach.size) }})</li>
{% else %}
<li class="attach_{{ attach.format }}">
<i class="fas fa-file-alt"></i>
<a href="{{ url(attach.path) }}">{{ attach.filename }}</a> ({{ macros.filesize(attach.size) }}){% endif %}</li>
{% endif %}{% endfor %}
</ul><!--/noindex--></div>