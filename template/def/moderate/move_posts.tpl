{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<div id="moderate_split_posts">
<form class="ibform" action="" method="post"><fieldset><legend>Разделение темы</legend>
<div><label><input type="radio" name="items" value="selected" checked="checked" />С выбранными сообщениями</label></div>
<div>В данный момент выбраны следующие сообщения:
{% for item in pids %}<a href="#" class="post_popup">#{{ item }}</a> {% endfor %}
{% if pids|length == 0 %}Ни одного сообщения не выбрано!{% endif %}
</div>
<div><label><input type="radio" name="items" value="all" />Со всеми сообщениями темы</label></div>
</fieldset><fieldset><legend>Выполнить следующее действие:</legend>
<div><label><input type="radio" name="subaction" value="split" checked="checked" />Отделить сообщения в новую тему</label></div>
<div><label><span>Название темы:</span>
{{ macros.input('topic[title]', editpost.topic.title,60,80,'oninput="document.querySelector(\'#moderate_split_posts input[value=split]\').checked=true"') }}</label></div>
<div><label><span>Краткое описание темы:</span>
{{ macros.input('topic[descr]',editpost.topic.descr,60,255,'oninput="document.querySelector(\'#moderate_split_posts input[value=split]\').checked=true"') }}</label></div>
<div><label><span>Частичный URL темы:<br />
<small>Необязательное поле.</small></span>
{{ macros.input('topic[hurl]',editpost.topic.hurl,40,255,'oninput="document.querySelector(\'#moderate_split_posts input[value=split]\').checked=true"') }}</label></div>
<div><label><input type="radio" name="subaction" value="join" />Присоединить к уже существующей теме</label></div>
<div><label><span>Номер темы для присоединения:</span><input type="text" name="new_tid" class="topic_search topic_id_finder" value="" size="40" maxlength="80" placeholder="Начните вводить номер, название или HURL темы" list="topic_search_list" oninput="document.querySelector('#moderate_split_posts input[value=join]').checked=true" />
<datalist id="topic_search_list">
</datalist></label></div>
<label><input type="radio" name="subaction" value="delete" />Удалить сообщения</label>
<div class="submit"><button type="submit">Выполнить</button></div>
<input type="hidden" name="authkey" value="{{ authkey }}" />
</fieldset></form>
</div>
{% endblock %}