{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('topic.css') }}" />
<style type="text/css">
#ib_all #moderate_mod_forum .t_title { overflow: hidden; white-space: nowrap; text-overflow: ellipsis }
</style>
{% endblock %}
{% block content %}
<div id="moderate_mod_forum">
<!--noindex-->
<div class="right" style="text-align: right"><form action="params_forum.htm" method="get" class="smallform">
<legend>Настройки отображения раздела</legend>
<fieldset>
Показывать по {{ macros.input('perpage',opts.perpage,3) }} тем  с сортировкой {{ macros.select('order',opts.order,{last_post_time:'по последнему сообщению',first_post_id:'по дате создания',post_count:'количеству сообщений',valued_count:'количеству ценных сообщений',flood_coeff:'доле флуда'}) }} {{ macros.select('sort',opts.sort,{DESC:'по убыванию',ASC:'по возрастанию'}) }}.<br/>
Вывести {{ macros.select('filter',opts.filter,{all:'все темы',valued:'только темы с ценными сообщениями',unanswered:'только неотвеченные темы',noflood:'только темы, где менее 50% флуда',myposts:'только темы с моими сообщениями'}) }},<br />
созданных <input type="text" name="author_name" value="{{ opts.author_name }}" size="12" maxlength="32" placeholder="имя автора" />,
название которых содержит {{ macros.input('text',opts.text,20) }}
<button type="submit" name="submit">Показать</button>
<button type="submit" name="clear">Сброс</button>
</fieldset>
</form>
</div>
<div class="pages right" style="clear: both">{{ macros.pages(pages[0],"?page=") }}</div>
<!--/noindex-->

<form action="" method="post" style="clear:both"><fieldset style="border: 0"><legend style="display: none"></legend>
<table class="ibtable topic_list">
<col /><col style="width: 8em" /><col style="width:12em"/><col style="width: 4em"/><col  style="width: 4em" /><col  style="width: 4em" /><col  style="width: 4em" /><col style="width: 24em"/>
<thead><th>Название темы</th><th>Просмотры/<br/>Сообщения</th><th>Последнее сообщение</th><th>Избр.</th><th>Прикл.<br />тема</th><th>Прикл.<br />сообщ.</th><th>Тема<br />закрыта</th><th>Действия</th></thead>
<tbody>
{% for item in topics %}
<tr class="topic_item{% if item.valued_count>0 %} valued{% endif %}">
<td class="t_title"><a href="{{ url(forum.hurl) }}/{{ item.t_hurl }}">{{ item.title }}</a><br />{{ item.descr }}</td><td>{{ item.views }}/{{ item.post_count }}
<td>{{ macros.user(item.last_poster,item.last_poster_id) }}<br />{{ item.last_post_date|shortdate }}</td>
<td class="mod_favorites"><input type="hidden" name="old_favorites[{{ item.id }}]" value="{{ item.favorites }}" />{{ macros.checkbox('favorites['~item.id~']',1,item.favorites) }}</td>
<td class="mod_sticky"><input type="hidden" name="old_sticky[{{ item.id }}]" value="{{ item.sticky }}" />{{ macros.checkbox('sticky['~item.id~']',1,item.sticky) }}</td>
<td class="mod_post"><input type="hidden" name="old_sticky_post[{{ item.id }}]" value="{{ item.sticky_post }}" />{{ macros.checkbox('sticky_post['~item.id~']',1,item.sticky_post) }}</td>
<td class="mod_locked"><input type="hidden" name="old_locked[{{ item.id }}]" value="{{ item.locked }}">{{ macros.checkbox('locked['~item.id~']',1,item.locked) }}</td>
<td><label><input type="radio" value="" name="actions[{{ item.id }}]" checked="checked" />Оставить</label>
<label><input type="radio" value="m" name="actions[{{ item.id }}]" />Перенести</label> &nbsp;&nbsp;&nbsp;
<label class="msg_error"><input class="msg_error" type="radio" value="d" name="actions[{{ item.id }}]" />Удалить</label>
</td></tr>
{% endfor %}
</tbody></table>
<div class="pages right" style="clear: both">{{ macros.pages(pages[0],"?page=") }}</div>
{% if forumlist %}Выбранные для переноса темы отправить в раздел {{ macros.select('moveforum',0,forumlist) }}
{% else %}Нет доступных разделов для переноса выбранных тем{% endif %}
<input type="submit" value="Выполнить!" /><br />
<small>Примечание: у вас должны быть права на создание тем в том разделе, куда вы хотите перенести выбранное!</small>
<input type="hidden" name="authkey" value="{{ authkey }}" />
</fieldset></form>
</div>
{% endblock %}
