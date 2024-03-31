<!DOCTYPE html>
<html>
<head>
{% block title %}<title>{{ intb.title|striptags|raw }}</title>{% endblock %}
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preload" href="{{ url('fa/webfonts/fa-regular-400.woff2')}}" as="font" crossorigin />
<link rel="preload" href="{{ url('fa/webfonts/fa-solid-900.woff2')}}" as="font" crossorigin />
<style type="text/css">
#ib_all { margin: auto; padding: 0 50px; font-size: 1.25em; max-width: 1220px; min-width: 992px }
#ib_all #admin_title { text-align: center; line-height: 180%; background: #def; font-size: 1.4em; margin-bottom: 10px }
#ib_all #admin_menu { float: left; width: 220px;  }
#ib_all #admin_content { margin-left: 240px }
#ib_all .admin_menu_elm h3 { margin: 3px 0; cursor: pointer; }
#ib_all .admin_menu_elm ul { list-style: circle; padding-left: 20px; line-height: 156% }
#ib_all .admin_menu_elm li a { color: #3c5cc0 }
#ib_all .admin_menu_elm li a:hover { color: #283E7F }
#ib_all .admin_menu_elm li:hover { background-color: #def }
@media screen and (max-width: 992px) {
  #ib_all #admin_menu { position: static; width: auto }
  #ib_all #admin_content { margin-left: 0 }
}
#ib_all { margin: auto; padding: 0 50px; font-size: 1.25em; max-width: 1220px; min-width: 992px }
#ib_all .main_menu li, #ib_all .welcome li { display: inline-block; list-style: none}
</style>
{% if get_opt('javascript_cdn')=='yandex' %}{% set jquery_cdn="http://yastatic.net/jquery/2.1.1/jquery.min.js" %}
{% elseif get_opt('javascript_cdn')=='google' %}{% set jquery_cdn="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" %}
{% else %}{% set jquery_cdn=url('js/jquery.min.js') %}{% endif %}
{% if meta_redirect %}<meta http-equiv="refresh" content="1; url={{ meta_redirect }}" />{% endif %}
<script type="text/javascript">
IntB_params = {
   basedir:'{{ url('') }}',{%
   if draft_name %}draft: '{{ draft_name }}',{% endif %}{% if smiles %}
   emoticonsRoot : '{{ url('sm/') }}',
   emoticons : { {%
   if smiles.dropdown %}
    dropdown : { {% for item in smiles.dropdown %}"{{ item.code }}":"{{ item.file }}",{% endfor %} }, {% endif %}{%
   if smiles.more %}
    more : { {% for item in smiles.more %}"{{ item.code }}":"{{ item.file }}",{% endfor %} },{% endif %}{%
   if smiles.hidden %}
     hidden : { {% for item in smiles.hidden %}"{{ item.code }}":"{{ item.file }}",{% endfor %} },{%
   endif %}
   },{% endif %}
   wysiwyg: '{{ get_opt('wysiwyg','user') }}',
   longposts: {{ get_opt('longposts','user') }}
}
</script>
<script type="text/javascript" src="{{ url('js/head.load.min.js') }}" defer="defer"></script>
<script type="text/javascript" src="{{ jquery_cdn }}" defer="defer"></script>
<script type="text/javascript" src="{{ url('js/intb.js') }}" defer="defer"></script>
<link type="text/css" rel="preload" as="style" href="{{ url('fa/css/fontawesome-all.min.css') }}"  onload="this.rel='stylesheet'" />
<link type="text/css" href="{{ style('s.css') }}" rel="stylesheet" />
{% block css %}{% endblock %}
</head>
{% import 'macro.tpl' as macros %}

<body><div id="ib_all">
<div id="admin_title">{{ get_opt('site_title') }} &mdash; Центр Администрирования</div>

{% if not meta_redirect %}
<div id="admin_menu">
<div class="admin_menu_elm">
<h3>Общие настройки</h3>
<ul>
<li><a href="../settings/view.htm">Общее состояние форума</a></li>
<li><a href="../settings/settings.htm">Настройки форума</a></li>
<li><a href="../settings/edit_rules.htm">Правила форума</a></li>
<li><a href="../settings/edit_foreword.htm">Вводный текст</a></li>
<li><a href="../settings/menu.htm">Редактор меню</a></li>
<li><a href="../settings/subactions.htm">Вспомогательные блоки</a></li>
<li><a href="../settings/crontab.htm">Планировщик заданий</a></li>
<li><a href="../settings/libs.htm">Расширенные настройки</a></li>
</ul></div>

<div class="admin_menu_elm">
<h3>Разделы форума</h3>
<ul>
<li><a href="../forums/view.htm">Разделы и категории</a></li>
<li><a href="../forums/mass.htm">Групповая настройка разделов</a></li>
<li><a href="../settings/announce.htm">Объявления</a></li>
</ul></div>

<div class="admin_menu_elm">
<h3>Участники и группы</h3>
<ul>
<li><a href="../users/users.htm">Участники форума</a></li>
<li><a href="../users/groups.htm">Группы и права доступа</a></li>
<li><a href="../users/moderators.htm">Модераторы и эксперты</a></li>
<li><a href="../users/contacts.htm">Контакты и социальные сети</a></li>
<li><a href="../users/ip.htm">Запрет доступа по IP-адресу</a></li>
<li><a href="../users/user_edit.htm?uid=1">Настройки гостя</a></li>
<li><a href="../users/user_edit.htm?uid=3">Настройки нового пользователя</a></li>
</ul></div>

<div class="admin_menu_elm">
<h3>Стили и счетчики</h3>
<ul>
<li><a href="../settings/edit_style.htm">Редактор стилей</a></li>
<li><a href="../misc/counters.htm">Счетчики и Javascript</a></li>
</ul></div>

<div class="admin_menu_elm">
<h3>Прочее</h3>
<ul>
<li><a href="../misc/stats.htm">Общая статистика</a></li>
<li><a href="../misc/user_logs.htm">Журнал действий пользователей</a></li>
<li><a href="../misc/massmail.htm">Администраторская рассылка</a></li>
<li><a href="../misc/smiles.htm">Графические смайлики</a></li>
<li><a href="../misc/badwords.htm">Фильтр запрещенных слов</a></li>
<li><a href="../misc/resync.htm">Пересинхронизация</a></li>
<li><a href="../misc/cache_reset.htm">Сброс кеша</a></li>
<li><a href="../misc/trashbox.htm">Очистка корзины</a></li>
</ul>
</div>

{% if admin_menu %}<div class="admin_menu_elm">
<h3>Дополнения</h3>
{{ macros.menu(admin_menu) }}
</div>{% endif %}


<div class="admin_menu_elm">
<h3>Выход</h3>
<ul>
<li><a href="{{ url('') }}">Вернуться на форум</a></li>
<li><a href="../settings/logout.htm">Завершить работу с АЦ</a></li>
</ul>
</div>
</div>
{% endif %}

<div id="admin_content">
{# Сообщения об ошибках или уведомления, если таковые есть #}
{% block messages %}
{{ macros.messages(intb.messages) }}
{% endblock %}
{# Блок, вместо которого будет отображен контент соответствующей страницы! #}
{% block content %}
<p>{{ meta_messsage }}</p>
{% endblock %}

<!--##DEBUG#-->
</div></div></body>
</html>
