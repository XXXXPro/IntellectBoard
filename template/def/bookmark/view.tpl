{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('topic.css') }}" />
{% endblock %}
{% block content %}
<div id="bookmark_view">
  
{{ macros.sub_block(IntB_subactions['action_start']) }}  

{% if delete_items %}<form action="delete.htm" method="post"><fieldset style="border: 0"><legend style="display: none"></legend>{%
endif %}
<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
<table class="ibtable topic_list">
<col /><col style="width: 22%" /><col style="width: 8%" /><col style="width: 10%" />
<col style="width: 12%" /><col style="width: 12%"/>{%
if delete_items %}<col style="width: 5%"/>{% endif %}
<thead><tr><th>Название темы</th><th>Раздел</th><th>Просмотры</th><th>Сообщения</th><th>Автор</th><th>Последнее сообщение</th>{%
if delete_items %}<th></th>{% endif %}</tr></thead>
<tbody>
{% for item in topics %}{% include 'bookmark/tf_item.tpl' %}{% endfor %}
{% if topics|length==0 %}<tr><td class="center" colspan="{%if delete_items %}7{% else %}6{% endif %}">Нет ни одной темы.</td></tr>{% endif %}
</tbody></table>
<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
{% if delete_items %}<input type="submit" value="Удалить выбранные темы из закладок" class="actionbtn mainbtn"/>
</fieldset></form>{%
endif %}

{{ macros.sub_block(IntB_subactions['action_end']) }}

<!--noindex--><h5>Условные обозначения:</h5>
<div class="right t_legend_r"><dl>
<dt><div class="t_sticky fas fa-thumbtack"></div></dt><dd>Приклеенная тема</dd>
<dt><div class="t_locked fas fa-lock"></div></dt><dd>Тема закрыта</dd>
<dt><div class="t_posted far fa-edit"></div></dt><dd>Тема, в которой вы оставляли сообщения</dd>
<dt><div class="t_poll far fa-question-circle"></div></dt><dd>Тема с опросом</dd>
</dl></div>

<dl class="t_legend">
<dt><div class="t_icon far fa-comment"></div></dt><dd>Обычная тема</dd>
<dt><div class="t_new fas fa-comment"></div></dt><dd>Тема с новыми сообщениями</dd>
<dt><div class="t_hot fas fa-comment-dots"></div></dt><dd>Активно обсуждаемая тема с новыми сообщениями</dd>
</dl>
<!--/noindex-->

</div>
{% endblock %}
