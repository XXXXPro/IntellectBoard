{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="users_new_group">
<h1>Создание нового уровня</h1>
<p><a href="groups.htm">&laquo; К списку уровней</a></p>
<form action="" class="ibform" method="post"><fieldset><legend>Общие настройки группы</legend>
<div><label><span>Уровень доступа</span><b>{{ macros.input('group[level]',group.level,4) }}</b></label></div>
{% include 'admin/users/group.tpl' %}</fieldset>
<fieldset><legend>Права доступа на разделы</legend>
<div><label><span>Скопировать права доступа группы</span>{{ macros.select('parent_group',0,parent_groups) }}</label></div>
<div class="submit"><button type="submit">Сохранить</button>
{{ macros.hidden('authkey',authkey)}}</div>
</fieldset></form>

<p>Внимание! При создании нового уровня доступа рекомендуется всегда выставлять ему статус "Особый", 
производить настройку прав доступа, и только после этого снимать статус "Особый" (если это нужно).
Это позволит избежать временного попадания пользователей в непредназначенные для них разделы.</p>  

<h4>Обозначение прав доступа</h4>
<ul>
<li><b>view</b> &mdash; право видеть форум на главной странице, в списке разделов и т.п.</li>
<li><b>read</b> &mdash; право просмотра списка тем в форуме и самих тем</li>
<li><b>post</b> &mdash; право отвечать в уже существующих темах</li>
<li><b>topic</b> &mdash; право создавать новые темы</li>
<li><b>poll</b> &mdash; право создавать опросы (имеет смысл только вместе с правом на создание темы)</li>
<li><b>vote</b> &mdash; право голосовать в опросах</li>
<li><b>rate</b> &mdash; право влиять на рейтинг сообщений</li>
<li><b>edit</b> &mdash; право редактировать свои сообщения</li>
<li><b>nopremod</b> &mdash; право писать без премодерации</li>
<li><b>attach</b> &mdash; право прикреплять файлы к сообщениям</li>
<li><b>html</b> &mdash; право использовать HTML-код (потенциально небезопасно, ставьте только проверенным пользователям)</li>
</ul>
</div>
{% endblock %}