{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="stylesheet" type="text/css" href="{{ style('anon.css') }}" />
{% endblock %}
{% block content %}
<div id="anon_forum_view" class="forum{{ forum.id }}">
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

<div>
{% if perms.topic %}<!--noindex-->
{% include 'stdforum/postform.tpl' %}
<!--/noindex-->{% endif %}
</div>
{% if forum.topic_count %}<p class="topic_count">Всего вопросов: {{ forum.topic_count }}</p>{% endif %}
{% for item in topics %}{% include 'anon/t_item.tpl' %}{% endfor %}
{% if topics|length==0 %}<p class="topic_count">Пока нет ни одного вопроса. У вас есть шанс спросить первым!</p>{% endif %}
<br style="clear: both" />
{% if pages.pages>1 %}<div class="pages right" style="clear: both">{{ macros.pages(pages) }}</div>{% endif %}
</div>

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
{% endblock %}
