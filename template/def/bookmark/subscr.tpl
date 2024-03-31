{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('topic.css') }}" />
<style type="text/css"><!--
.f_item { padding: 4px; }
--></style>
{% endblock %}
{% block content %}
<div id="bookmark_subscribe">
<form action="../unsubscr.htm" method="post"><fieldset style="border: 0"><legend style="display: none"></legend>
<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
<h4>Подписки на темы</h4>
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

<form action="../unsubscr.htm" method="post"><fieldset style="border: 0"><legend style="display: none"></legend>
<h4>Подписка на форум целиком</h4>
<div style="font-size: 120%"><label>
<input type="checkbox" name="subscribe_forum[]" value="0" style="width: 24px; height: 24px" {% if subscribe_all %}checked="checked"{% endif %} />
Подписаться на уведомления о новых сообщениях во всех темах форума?<br />
<small>Внимание! Используйте опцию осторожно, так как ее включение на форуме с высокой активностью может привести к переполнению вашего почтового ящика.</small>
</label></div>
<h4>Подписка на разделы</h4>
{% for item in forums
%}<div class="f_item"><label><input type="checkbox" name="subscribe_forum[]" value="{{ item.id }}" {% if item.subscribe %}checked="checked"{% endif %} /> {{ item.title }}</label></div>
{% endfor %}
<div><input type="submit" value="Изменить настройки подписки" /></div><input type="hidden" name="authkey" value="{{ authkey }}" />
<input type="hidden" name="forums" value="1" />
</fieldset></form>

</div>
{% endblock %}
