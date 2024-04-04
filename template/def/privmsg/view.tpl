{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="preload" as="style" href="{{ style('privmsg.css') }}" onload="this.rel='stylesheet'"/>
{% endblock %}
{% block content %}
<div id="privmsg_view">
<h1>Личные сообщения</h1>
<div>
<a href="mark_all.htm">Отметить все цепочки как прочитанные</a><br />
<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
<a class="actionbtn newtopic mainbtn" href="new.htm">Написать сообщение</a>
</div>

{{ macros.sub_block(IntB_subactions['action_start']) }}

<table class="ibtable thread_list">
<col /><col style="width: 8%" /><col style="width: 8%" /><col style="width: 32%" /><col style="width: 12.5em"/>
<thead><th>Тема сообщения</th><th>Новые сообщения</th><th>Всего сообщений</th><th>Участники</th><th>Последнее сообщение</th></thead>
<tbody>
{% for item in threads %}{% include 'privmsg/th_item.tpl' %}{% endfor %}
</tbody></table>
<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
<a class="actionbtn newtopic mainbtn" href="new.htm">Написать сообщение</a>

{{ macros.sub_block(IntB_subactions['action_end']) }}

<h5>Условные обозначения:</h5>
<dl class="t_legend">
<dt><div class="t_icon"><i class="far fa-envelope-open"></i></div></dt><dd>Обычная цепочка писем</dd>
<dt><div class="t_new"><i class="fas fa-envelope"></i></div></dt><dd>Цепочка с новыми сообщениями</dd>
</dl>

</div>
{% endblock %}
