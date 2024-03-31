{% if data.tags|length>0 %}
<ul class="topic_tags">
{% for item,value in data.tags %}
<li style="font-size: {{100-((data.max-value)*50/data.max)|round}}%"><a href="{{ url(data.forum.hurl) }}/?tags={{ item|url_encode }}">{{ item }}</a></li>
{% endfor %}
</ul>
{% if data.tags|length<data.limit %}<a class="right" href="{{ url(data.forum.hurl) }}/tags.htm">Все теги</a>{% endif %}
{% endif %}