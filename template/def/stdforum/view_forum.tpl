{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}{% if subforums %}
<link rel="preload" as="style" type="text/css" href="{{ style('forums.css') }}" onload="this.rel='stylesheet'"/>
{% endif %}<link rel="preload" as="style" type="text/css" href="{{ style('topic.css') }}" onload="this.rel='stylesheet'"/>
{% endblock %}
{% block content %}
<div id="stdforum_view_forum" class="forum{{ forum.id }}">
<h1>{{ forum.title }}</h1>
{% if start_text %}
<div class="start_text">
{{ start_text|raw }}
{% if rules %} <a href="rules.htm" class="rules">Правила раздела</a>{% endif %}
</div>
{% else %}
<p class="descr">{{ forum.descr }}</p>
{% if rules %} <a href="rules.htm" class="rules">Правила раздела</a>{% endif %}
{% endif %}

{{ macros.sub_block(IntB_subactions['action_start']) }}

<!--noindex-->
{% if subforums %}
<table class="ibtable categories"><col /><col /><col /><col /><col />
<thead><tr><th class="cat_icon"></th><th class="cat_name">Название</th><th class="cat_views">Просмотров</th>
<th class="cat_topics">Тем и сообщений</th><th class="cat_last">Последнее сообщение</th></tr></thead>
<tbody>
{% for forum in subforums %}
{% include var ~ forum.module ~ '/title.tpl' %}
{% endfor %}
</tbody>
</table>
{% endif %}
<div class="right" style="text-align: right"><form action="params_forum.htm" method="get" class="smallform">
<fieldset><legend>Настройки отображения раздела</legend>
Показывать по {{ macros.input('perpage',opts.perpage,3) }} тем  с сортировкой {{ macros.select('order',opts.order,{last_post_time:'по последнему сообщению',first_post_id:'по дате создания',post_count:'количеству сообщений',valued_count:'количеству ценных сообщений',flood_coeff:'доле флуда'}) }} {{ macros.select('sort',opts.sort,{DESC:'по убыванию',ASC:'по возрастанию'}) }}.<br/>
Вывести {{ macros.select('filter',opts.filter,{all:'все темы',valued:'только темы с ценными сообщениями',unanswered:'только неотвеченные темы',noflood:'только темы, где менее 50% флуда',myposts:'только темы с моими сообщениями'}) }}
за {{ macros.select('period',opts.period,{0:'все время',2208:'последние 3 месяца',744:'последний месяц',168:'последнюю неделю',72:'последние 3 дня',24:'последние сутки'}) }},<br />
созданных <input type="text" name="author_name" value="{{ opts.author_name }}" size="12" maxlength="32" placeholder="имя автора" />,
название которых содержит {{ macros.input('text',opts.text,20) }}
<button type="submit" name="submit">Показать</button>
<button type="submit" name="clear">Сброс</button>
</fieldset>
</form>
</div>
<div>
{% if not is_guest() %}<span class="lastvisit">
{% if forum.visit2 %}Последний раз вы заходили в этот раздел {{ forum.visit2|longdate }}.{% else %}
Вы впервые в этом разделе. Добро пожаловать!{% endif %}</span><br />
{% endif %}
{% if roles.moderator|length>0 %}Модераторы: {% for user in roles.moderator %}{% if not loop.first %}, {% endif %}{{ macros.user(user.display_name,user.id) }}{% endfor %}<br />{% endif %}
{% if roles.expert|length>0 %}Эксперты: {% for user in roles.expert %}{% if not loop.first %}, {% endif %}{{ macros.user(user.display_name,user.id) }}{% endfor %}<br />{% endif %}
{% if not is_guest() %} <a class="small_link" href="mark_all.htm">Отметить все темы раздела как прочитанные</a>{% endif %}
<br style="clear: both"/>
<div class="pages right">{{ macros.pages(pages) }}</div>
{% if perms.topic %}<a class="actionbtn newtopic mainbtn" href="newtopic.htm"><i class="far fa-edit"></i> Новая тема</a>{% endif %}
</div>
<!--/noindex-->

<table class="ibtable topic_list">
<col /><col style="width: 8%" /><col style="width: 12%" /><col style="width: 15%" /><col style="width: 15%"/>
<thead><tr><th>Название темы</th><th>Просмотры</th><th>Сообщения</th><th>Автор</th><th>Последнее сообщение</th></tr></thead>
<tbody>{% if sticky %}
{% for item in sticky %}{% include 'stdforum/t_item.tpl' %}{% endfor %}
<tr class="topic_item"><td colspan="5"></td></tr>
{% endif %}
{% for item in topics %}{% include 'stdforum/t_item.tpl' %}{% endfor %}
{% if topics|length==0 and sticky|length==0 %}<tr><td colspan="5" class="notopics">В данном разделе еще нет ни одной темы. У вас есть шанс стать первым автором!</td></tr>{% endif %}
</tbody></table>
<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>
{% if perms.topic %}<a class="actionbtn newtopic mainbtn" href="newtopic.htm"><i class="far fa-edit"></i> Новая тема</a>{% endif %}

{{ macros.sub_block(IntB_subactions['action_end']) }}

{% if is_moderator %}<div class="mod_actions">
{% if user.id==forum.owner and not is_guest() %}<a href="owner_settings.htm">Настройки раздела</a> | {% endif %}
{% if intb.is_admin or get_opt('moder_edit_rules') %}<a href="{{ url('moderate/'~forum.hurl~'/edit_rules.htm') }}">Правила</a> | {% endif %}
{% if intb.is_admin or get_opt('moder_edit_foreword') %}<a href="{{ url('moderate/'~forum.hurl~'/edit_foreword.htm') }}">Вступительное слово</a> | 
{% endif %}{% if premod_count>0 %}<a href="{{ url('moderate/'~forum.hurl~'/premod.htm') }}">{{ premod_count|incline('<b>%d</b> сообщение на премодерации', '<b>%d</b> сообщения на премодерации','<b>%d</b> сообщений на премодерации')|raw }}</a>
{% else %}На премодерации нет сообщений{% endif %} |
<a href="{{ url('moderate/'~forum.hurl~'/mod_forum.htm') }}">Модерировать раздел</a> |
<a href="{{ url('moderate/'~forum.hurl~'/trashbox.htm') }}">Корзина</a> |
<a href="{{ url('moderate/'~forum.hurl~'/view_log.htm') }}">Лог действий</a>
</div>{% endif %}

<!--noindex--><h5>Условные обозначения:</h5>
<div class="right t_perms">
<b>Отправка сообщений</b> {% if perms.post %}разрешена{% else %}запрещена{% endif %}.<br />
<b>Редактирование сообщений</b> {% if perms.post %}разрешено{% else %}запрещено{% endif %}.<br />
<b>Создание тем</b> {% if perms.post %}разрешено{% else %}запрещено{% endif %}.<br />
<b>Создание опросов</b> {% if perms.post %}разрешено{% else %}запрещено{% endif %}.<br />
<b>Голосование в опросах</b> {% if perms.post %}разрешено{% else %}запрещено{% endif %}.<br />
<b>Изменение рейтинга сообщений</b> {% if perms.post %}разрешено{% else %}запрещено{% endif %}.<br />
</div>
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
</dl></div>
<!--/noindex-->
{% endblock %}
