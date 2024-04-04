{% if data %}
<div class="infoblock"><h4>Активные темы</h4>
<ul>{% for topic in data.topics %}
<li><a href="{{ url(topic.full_hurl) }}">{{ topic.title}}</a> — <span class="username">{{ topic.starter }}</span>, {{ topic.first_post_date|shortdate }}
{% endfor %}
</ul></div>
{% endif %}