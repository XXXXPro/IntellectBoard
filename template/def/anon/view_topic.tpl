{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('anon.css') }}" />
{% endblock %}
{% block content %}
<div id="anon_topic_view" class="forum{{ forum.id }} topic{{ topic.id }}">

{{ macros.sub_block(IntB_subactions['action_start']) }}

{% if pages.pages>1 %}<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>{% endif %}
{% for item in posts %}{% if loop.index>1 %}{% include 'anon/p_item.tpl' %}{% else %}{% include 'anon/first.tpl' %}{% endif %}{% endfor %}
<br style="clear: both" />
{% if pages.pages>1 %}<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>{% endif %}

{{ macros.sub_block(IntB_subactions['action_end']) }}

{% if is_moderator %}<div class="mod_actions">{%
if premod_count>0 %}<a href="{{ url('moderate/'~topic.full_hurl~'/premod.htm') }}">{{ premod_count|incline('Премодерация: <b>%d</b> сообщение', 'Премодерация: <b>%d</b> сообщения','Премодерация: <b>%d</b> сообщений')|raw }}</a>{%
else %}На премодерации нет сообщений{% endif %} |
<a href="edit.htm?id={{ topic.first_post_id }}">Редактировать тему</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'move_posts.htm') }}">Перенести/удалить сообщения</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'trashbox.htm') }}">Корзина</a> |
<a href="{{ url('moderate/'~topic.full_hurl~'view_log.htm') }}">Лог действий</a>
</div>{% endif %}

{% if perms.post %}<div><!--noindex-->
{% include 'anon/form.tpl' %}</div>
{% endif %}
</div>
{% endblock %}
