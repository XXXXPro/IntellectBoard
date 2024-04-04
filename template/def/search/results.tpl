{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<style type="text/css">
#ib_all #search_view .ibform div { background: none }
.search_col { display: inline-block; vertical-align: top; width: 30%; margin-right: 2% }
#search_third label { display: block }
</style>
{% if search.output_mode == 'posts' %}
<link rel="stylesheet" type="text/css" href="{{ style('post.css') }}" />
{% else %}
<link rel="stylesheet" type="text/css" href="{{ style('topic.css') }}" />{% endif %}
{% endblock %}
{% block content %}
<div id="search_results">
{% if search.search_type < 2 %}{% include 'search/form.tpl' %}{% endif %}
{% if search.output_mode == 'posts' %}
{% if search.search_type==2 %}<h1>Сообщения, отправленные пользователем <span class="username">{{ search.query }}</span></h1>
{% elseif search.search_type==3 %}<h1>Сообщения пользователя <span class="username">{{ search.query }}</span>, признанные ценными</h1>
{% else %}<h1>Сообщения, содержащие &laquo;{{ search.query }}&raquo;</h1>{% endif %}
<div class="pages right">{{ macros.pages(pages) }}</div>
<div class="posts">
{% for post in posts %}
{% include 'stdforum/p_item.tpl' %}
{% endfor %}
{% if posts|length==0%}<p>По вашему запросу ничего не найдено!</p>{% endif %}
</div>
<div class="pages right">{{ macros.pages(pages) }}</div>
{% else %}
{% if search.search_type==4 %}<h1>Темы, созданные пользователем <span class="username">{{ search.query }}</span></h1>
{% else %}<h1>Темы про &laquo;{{ search.query }}&raquo;</h1>{% endif %}
<div class="pages right">{{ macros.pages(pages) }}</div>
<table class="ibtable topic_list">
<col /><col style="width: 22%" /><col style="width: 8%" /><col style="width: 10%" /><col style="width: 12.5em" /><col style="width: 12.5em"/>{%
if delete_items %}<col style="width: 2em"/>{% endif %}
<thead><th>Название темы</th><th>Раздел</th><th>Просмотры</th><th>Сообщения</th><th>Автор</th><th>Последнее сообщение</th>{%
if delete_items %}<th></th>{% endif %}</thead>
<tbody>
{% for item in topics %}
{% include 'bookmark/tf_item.tpl' %}
{% endfor %}
{% if topics|length==0%}<p>По вашему запросу ничего не найдено!</p>{% endif %}
</tbody></table>
<div class="pages right">{{ macros.pages(pages) }}</div>
{% endif %}
</div>
<!--/noindex-->
{% endblock %}
