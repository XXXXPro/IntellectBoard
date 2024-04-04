{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block meta %}
{% endblock %}
{% block css %}
<link rel="preload" as="style" href="{{ style('forums.css') }}" onload="this.rel='stylesheet'" />
{% endblock %}
{% block content %}
<div id="mainpage_view">
{% if start_text %}
<div class="start_text">
{{ start_text|raw }}
</div>
{% endif %}
{% if get_opt('mainpage_stats')==1 and last_user %}
{% include 'mainpage/stats.tpl' %}
{% endif %}

{{ macros.sub_block(IntB_subactions['action_start']) }}

<table class="ibtable categories"><col /><col /><col /><col /><col />
<thead><tr><th class="cat_icon"></th><th class="cat_name">Название</th><th class="cat_views">Просмотров</th>
<th class="cat_topics">Тем и сообщений</th><th class="cat_last">Последнее сообщение</th></tr></thead>
<tbody>
{% set shown = 0 %}
{% for curcat in cat_list %}
{% if curcat.forums %}
<tr><td colspan="5" class="category"><a href="{{ url('category/'~curcat.id~'.htm') }}">{{ curcat.title }}</a></td></tr>
{% for forum in curcat.forums %}
{% set shown = 1 %}
{% include var ~ forum.module ~ '/title.tpl' %}
{% endfor %}
{% endif %}
{% endfor %}
{% if not shown %}
<tr><td colspan="5" class="noitems noforums">На данный момент на форуме не создано ни одного раздела.</td></tr>
{% endif %}
</tbody>
</table>
{% if get_opt('mainpage_stats')==2 %}
{% include 'mainpage/stats.tpl' %}
{% endif %}

{{ macros.sub_block(IntB_subactions['action_end']) }}

<div id="mainpage_subtext">
{% if user.is_guest() %}
Зарегистрируйтесь, чтобы иметь возможность отслеживать появление новых сообщений!
{% else %}
<a class="small_link" href="{{ url('mark_all.htm') }}">Отметить все как прочитанное</a>
<i class="forum_icon far fa-comments"></i> В разделе нет новых сообщений<br />
<i class="forum_icon fas fa-comments"></i> В разделе появились новые сообщения с момента вашего последнего визита
{% endif %}</div>
</div>
{% endblock %}