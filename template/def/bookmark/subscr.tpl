{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('topic.css') }}" />
<style type="text/css">
#ib_all .grid { display: grid; flex-wrap: wrap; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
#ib_all .ibform div.no_background { background: none }
#ib_all .f_item label { display: block }
#ib_all h4 { margin-top: 2.5em; }
#ib_all h4:first-child { margin-top: initial; }
#ib_all h5 { margin-bottom: 0.3em; }
</style>
{% endblock %}
{% block content %}
<div id="bookmark_subscribe">
{% if not only_ignored %}
<form action="../unsubscr.htm" method="post" class="ibform"><fieldset style="border: 0"><legend style="display: none"></legend>
Отписаться от темы по названию или id: <input type="text" name="subscribe[]" size="60" class="topic_search topic_id_finder" placeholder="Начните набирать название темы, частичный URL или введите номер" list="topic_search_list"/> <button type="submit" class="actionbtn">Отписаться</button>
<input type="hidden" name="authkey" value="{{ authkey }}" />
<datalist id="topic_search_list"></datalist>
</fieldset></form>
<form action="../unsubscr.htm" method="post"><fieldset style="border: 0"><legend style="display: none"></legend>
<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
<h4>Действующие подписки на темы</h4>
<table class="ibtable topic_list">
<col /><col style="width: 22%" /><col style="width: 8%" /><col style="width: 10%" /><col style="width: 12.5em"/><col style="width: 2em"/>
<thead><tr><th>Название темы</th><th>Раздел</th><th>Сообщения</th><th>Автор</th><th>Последнее сообщение</th><th></th></tr></thead>
<tbody>
{% for item in topics %}
<tr class="topic_item"><td class="t_title">
<a href="{{ url(item.full_hurl) }}">{{ item.title }}</a></td>
<td><a href="{{ url(item.forum_hurl) }}/">{{ item.forum_title }}</a></td>
<td>{{ item.post_count }}</td>
<td>{{ macros.user(item.starter,item.starter_id) }}<td><a class="t_last" href="{{ url(item.full_hurl) }}last.htm">{{ item.last_post_date|shortdate }}</a></td>
<td><input type="checkbox" name="subscribe[]" value="{{ item.id }}" /></td>
</tr>{% endfor %}
</tbody></table>
<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
<input type="submit" value="Отписаться от выбранных тем"  class="actionbtn mainbtn" /><input type="hidden" name="authkey" value="{{ authkey }}" />
</fieldset></form>

<form action="../unsubscr.htm" method="post" class="ibform"><fieldset style="border: 0"><legend style="display: none"></legend>
<h4>Подписка на форум целиком</h4>
<div style="font-size: 120%"><label>
<input type="checkbox" name="subscribe_forum[]" value="0" style="width: 24px; height: 24px" {% if subscribe_all %}checked="checked"{% endif %} />
Подписаться на уведомления о новых сообщениях во всех темах форума?<br />
<small>Внимание! Используйте опцию осторожно, так как ее включение на форуме с высокой активностью может привести к переполнению вашего почтового ящика.</small>
</label></div>
<h4>Подписка на разделы</h4>
<div class="grid">
{% set prev_category=item.ct_id %}
{% for item in forums %}
{% if prev_category!=item.ct_id %}
{% if loop.index>1 %}</div>{% endif %}
<div class="f_item no_background">
<h5>{% if item.ct_title %}{{ item.ct_title }}{% else %}&lt; без категории &gt;{% endif %}</h5>
{% set prev_category=item.ct_id %}
{% endif %}
<label><input type="checkbox" name="subscribe_forum[]" value="{{ item.id }}" {% if item.subscribe %}checked="checked"{% endif %} /> {{ item.title }}</label>
{% endfor %}
</div>
</div>
<div class="no_background"><input type="submit" value="Изменить настройки подписки" class="actionbtn mainbtn" /></div><input type="hidden" name="authkey" value="{{ authkey }}" />
<input type="hidden" name="forums" value="1" />
</fieldset></form>
{% endif %}

<form action="../unignore.htm" method="post" class="ibform"><fieldset style="border: 0"><legend style="display: none"></legend>
<h4>Темы-исключения</h4>
<p><small>Для тем-исключений уведомления не будут приходить даже в том случае, если вы подписаны на раздел или форум целиком.</small></p>
<table class="ibtable topic_list">
<col /><col style="width: 22%" /><col style="width: 8%" /><col style="width: 10%" /><col style="width: 12.5em"/><col style="width: 2em"/>
<thead><tr><th>Название темы</th><th>Раздел</th><th>Сообщения</th><th>Автор</th><th>Последнее сообщение</th><th></th></tr></thead>
<tbody>
{% for item in ignored_topics %}
<tr class="topic_item"><td class="t_title">
<a href="{{ url(item.full_hurl) }}">{{ item.title }}</a></td>
<td><a href="{{ url(item.forum_hurl) }}/">{{ item.forum_title }}</a></td>
<td>{{ item.post_count }}</td>
<td>{{ macros.user(item.starter,item.starter_id) }}<td><a class="t_last" href="{{ url(item.full_hurl) }}last.htm">{{ item.last_post_date|shortdate }}</a></td>
<td><input type="checkbox" name="subscribe[]" value="{{ item.id }}" /></td>
</tr>{% endfor %}
</tbody></table>
{% if more_ignored %}
<p><a href="?more_ignored=1" class="more">Показать все темы</a></p>
{% endif %}
<input type="submit" value="Удалить из списка исключений"  class="actionbtn mainbtn" /><input type="hidden" name="authkey" value="{{ authkey_unignore }}" />
</form>

</div>
{% endblock %}
