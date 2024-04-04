{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all #access_table { border-collapse: collapse; margin: -2px }
#ib_all #access_table td, #ib_all #access_table th { border: #eee 1px solid }
#ib_all .forum_row { background: #F2F8FF }
#ib_all .access_row { text-align: center }
#ib_all .access_row td { padding: 10px 0 }
#ib_all .new_row { font-size: 130%; font-weight: bold; background: #BFDDFF }
#ib_all .ibform div.inherited { font-size: 80%; color: #444; background: none }
#ib_all .inherited a { color: #444 }
#ib_all .forum_row a.f_title { font-size: 125%; font-weight: bold }
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="users_edit_group">
<h1>Настройки группы &laquo;{{ group.name }}&raquo;</h1>
<p><a href="groups.htm">&laquo; К списку групп</a></p>
<form action="" class="ibform" method="post"><fieldset><legend>Общие настройки группы</legend>
{% if not founder %}<div class="msg_warn">Вы не являетесь основателем форума, поэтому не можете изменять общие свойства группы, а можете задавать только права доступа!</div>{% else %}
<div><label><span>Уровень доступа</span><b>{{ group.level }}</b></label></div>
{% include 'admin/users/group.tpl' %}{% endif %}
</fieldset>
<fieldset><legend>Права доступа</legend>
<table id="access_table" class="ibtable">
<thead><tr>{% for item in fields %}<th>{{ item }}</th>{% endfor %}</tr></thead>
<tbody>
{% for item in acl %}
<tr class="forum_row"><td colspan="{{ fields|length }}">&raquo; <a class="f_title" href="{{ url(item.hurl) }}">{{ item.title }}</a>
{% if item.fid!=0 %}<label class="right"><input type="checkbox" name="delete[{{ item.fid}}]" value="1" />Удалить</label>{% endif %}
{% if item.subforums %}<div class="inherited">Наследуются разделами: {% for subforum in item.subforums %}<a href="{{ url(subforum.hurl) }}/">{{ subforum.title }}</a>{% if not loop.last %}, {% endif %}{% endfor %}</div>{% endif %}
</td></tr>
<tr class="access_row">{% for field in fields %}<td>{{ macros.checkbox('access['~item.fid~']['~field~']',1,item[field]) }}</td>{% endfor %}
{% endfor %}
<tr class="clone new_row"><td colspan="{{ fields|length }}">&raquo; Добавить права на раздел: {{ macros.select('new[0][fid]',0,new_forums) }}</td></tr>
<tr class="access_row">{% for field in fields %}<td>{{ macros.checkbox('new[0]['~field~']',1,0) }}</td>{% endfor %}
</tbody>
<tfoot><tr>{% for item in fields %}<th>{{ item }}</th>{% endfor %}</tr></tfoot>
</table>
 <div class="submit"><button type="submit">Сохранить</button>
{{ macros.hidden('level',group.level) }}{{ macros.hidden('authkey',authkey)}}</div>
</fieldset></form>

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