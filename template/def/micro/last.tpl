<div class="microblog-main">
<a href="{{ url(data.hurl) }}/" class="link-right">Все новости</a>
<h4>{{ data.title }}</h4>
<ul class="clearfix">
  {% for item in data.extdata.last_topics %}
  <li>
    <div class="mb-item"><span class="mb-date">{{item.first_post_date|date('d.m') }} — </span>{{ item.post.text|raw }}</div>
  </li>
  {% endfor %}
  {% if data.extdata.last_topics|length==0 %}
  <li>Хорошая новость: у нас пока нет новостей!</li>
  {% endif %}
</ul>
</div>
