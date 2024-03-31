{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="forums_edit_forum">
<h1>Дополнительные настройки раздела &laquo;{{ forum.title }}&raquo;</h1>
<form action="" method="post" class="ibform">
<fieldset><legend>Экспорт в LiveJournal</legend>
<div><label><span>Логин пользователя</span>{{ macros.input('extdata[lj_login]',extdata.lj_login) }}</label></div>
<div><label><span>PIN-код</span>{{ macros.input('extdata[lj_pin]',extdata.lj_pin) }}</label></div>
<div><label><small class="center">Для корректной работы экспорта вам необходимо задать PIN-код в <a href="https://www.livejournal.com/manage/emailpost.bml">настройках вашего LiveJournal</a> и внести адрес <strong>{{ get_opt('email_from') }}</strong> в список доверенных для публикации.</small></label></div>
<div><label><span>Ссылка на первоисточник</span>{{ macros.textarea('extdata[lj_text]',extdata.lj_text,2) }}</label></div>
<div><label><small class="center">Здесь можно указать текст со ссылкой на первоисточник, который будет добавляься в LJ-копию сообщения.
Ту часть, которая должна стать ссылкой, выделите двойными фигурными скобками, например «Первоисточник: мой блог на сайте {{ "{{"~get_opt('site_title')~"}}" }}.»</small></label></div>
<fieldset><legend>Экспорт в VK</legend>
<div><label><span>Числовой ID пользователя или группы VK.com</span>{{ macros.input('extdata[vk_user]',extdata.vk_user,) }} <a href="https://vk.com/linkapp" target="_blank">Узнать</a></label></div>
<div><label><span>Токен</span>{{ macros.input('extdata[vk_token]',extdata.vk_token,64,255,'id="vk_token"') }} {% if forum.hurl %}<button onclick="window.open('{{ url(forum.hurl) }}/vk_token.htm')">Получить</button>{% else %}Чтобы получить токен, завершите создание раздела{% endif %}</label></div>
</fieldset>
<fieldset style="border: 0"><div class="submit"><button type="submit">Сохранить</button></div>
</fieldset></form>
</div>
<script>

</script>
{% endblock %}