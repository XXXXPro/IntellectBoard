<!DOCTYPE html>
<html lang="ru">
<head>
{% block title %}
<title>{{ intb.title|striptags|raw }}</title>
{% endblock %}

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
{% if get_opt('javascript_cdn')=='yandex' %}{% set jquery_cdn="http://yastatic.net/jquery/2.1.1/jquery.min.js" %}
{% elseif get_opt('javascript_cdn')=='google' %}{% set jquery_cdn="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" %}
{% else %}{% set jquery_cdn=url('js/jquery.min.js') %}{% endif %}
<link rel="preload" href="{{ jquery_cdn }}" as="script" />
<link rel="preload" href="{{ url('fa/webfonts/fa-regular-400.woff2') }}" as="font" crossorigin />
<link rel="preload" href="{{ url('fa/webfonts/fa-solid-900.woff2') }}" as="font" crossorigin />
{# <link rel="preload" href="{{ url('fa/webfonts/fa-brands-400.woff2') }}" as="font" crossorigin /> #}
<link type="text/css" rel="preload" as="style" href="{{ url('fa/css/fontawesome.min.css') }}"  onload="this.rel='stylesheet'" />
<link type="text/css" href="{{ style('s.css') }}" rel="stylesheet" />
<link type="image/png" href="{{ style('favicon.png') }}" rel="icon" />
{% for item,content in meta %}
<meta name="{{ item }}" content="{{ content }}" />
{% endfor %}
{% for item,content in meta_properties %}
<meta property="{{ item }}" content="{{ content }}" />
{% endfor %}
{% for item in link %}
<link rel="{{ item.rel }}" href="{{ item.href }}" {% if item.id %}id="{{ item.id }}"{% endif %}{% if item.type %} type="{{ item.type }}"{% endif %} />
{% endfor %}

{% block css %}{% endblock %}
{% block meta %}{% endblock %}

{% include 'counter_h.tpl' ignore missing %}
</head>
{% import 'macro.tpl' as macros %}
<body><div id="ib_all">
{% include 'counter_t.tpl' ignore missing %}

{% block header %}
<header class="header">
<a href="{{ url('#') }}"><img class="site_logo" src="{{ style('logo.gif') }}" alt="{{ get_opt('site_title') }}" /></a>
<div class="site_title">{{ get_opt('site_title') }}</div>
<div class="site_descr">{{ get_opt('site_description') }}</div>
{{ macros.sub_block(IntB_subactions['header']) }}
</header>
{% endblock %}

{% block topmenu %}
<nav class="hmenu main_menu">
    <input type="checkbox" class="sandwich fa fa-bars" id="intb_sandwich_main"/>
    <label for="intb_sandwich_main" class="fa fa-bars"></label>
{{ macros.menu(intb.mainmenu) }}
</nav>
{% endblock %}

{% block welcome %}
<div class="welcome">
{{ macros.sub_block(IntB_subactions['welcome_start']) }}
{% if is_guest() %}
<form action="{{ url('user/login.htm') }}" method="post"><fieldset><legend></legend>
<span id="greet">Привет, гость!</span> 
<ul class="hmenu usermenu">
<li><a href="{{ url('user/register.htm') }}" rel="nofollow">Регистрация</a></li>
<li><a href="{{ url('user/forgot.htm') }}" rel="nofollow">Забыли пароль?</a></li>
</ul><br />
<input type="text" name="login" maxlength="32" placeholder="логин" required="required"/>
<input type="password" name="password" maxlength="32" placeholder="пароль"  required="required"/>
<label title="Поставьте эту галочку, если хотите, чтобы форум сразу узнал вас при следующем заходе с этого компьютера.">
<input type="checkbox" name="long" value="1" />Запомнить </label>
<button type="submit">Войти</button>
{% if get_opt('site_social_lib') %}
{% include 'user/social_small_'~get_opt('site_social_lib')~'.tpl' %}
{% endif %}
</fieldset></form>
{% else %}
<a href="{{ url(sprintf(get_opt('user_hurl'),user.id)) }}">{{ macros.avatar(user.id,user.avatar,user.display_name) }}</a>
<div id="greet">Рады видеть вас, <a href="{{ url(sprintf(get_opt('user_hurl'),user.id)) }}" class="username">{{ user.display_name }}</a>! 
<a href="{{ url('user/logout.htm') }}" id="logout"><i class="fas fa-sign-out-alt"></i>Выйти</a></div>
<ul class="hmenu usermenu"><li><a href="{{ url('user/update.htm') }}">Профиль и настройки</a></li>
<li><a href="{{ url('bookmark/subscr/') }}">Подписка</a></li>
<li><a href="{{ url('newtopics/unread.htm') }}">Непрочитанные темы</a></li>
<li><a href="{{ url('bookmark/') }}">Закладки</a></li>
<li><a href="{{ url('bookmark/mytopics/') }}">Мои темы</a></li>
</ul><br />
<ul class="hmenu usermenu">{{ macros.sub_block(IntB_subactions['pm_notify']) }}
<li><a href="{{ url('address_book/') }}">Контакты</a></li>
<li><a href="{{ url('address_book/blacklist.htm') }}">Черный список</a></li></ul>
{% endif %}
<br style="clear: both"/>
{{ macros.sub_block(IntB_subactions['welcome_end']) }}
</div>
{% endblock %}

{{ macros.sub_block(IntB_subactions['page_top']) }}

{# Указатель текущего положения на форуме ("хлебные крошки") #}
{% block location %}
{{ macros.location(intb.location,intb.rss) }}
{% endblock %}
 
{{ macros.sub_block(IntB_subactions['page_location']) }}

{# Сообщения об ошибках или уведомления, если таковые есть #}
{% block messages %}
{{ macros.messages(intb.messages) }}
{% endblock %}

<main>
{# Блок, вместо которого будет отображен контент соответствующей страницы! #}
{% block content %}
Если вы читаете это, то нужный шаблон почему-то не подключился!<br />
{% endblock %}
</main>

{{ macros.sub_block(IntB_subactions['page_bottom']) }}

{% block footer %}
<footer>
<address class="copyright">&copy; {{ get_opt('site_copyright') }}<br />
{# Внимание! Удаление или изменение ссылки в строке ниже будет нарушением Лицензионного Соглашения. Будьте достойными людьми и не трогайте ее!  #}
Форум работает на <a href="https://intbpro.ru">Intellect Board Pro</a>
{{ intb.intb_version }} &copy; 2013-2024, 4X_Pro.
</address>
{% include 'counter_f.tpl' ignore missing %}
{# Вывод пустого div для того, чтобы вызвать планировщик заданий на случай, если он не висит на системном cron #}
{% if get_opt('cron_img') %}<div style="height: 1px; width: 1px; background: url('{{ url('cron.php') }}')"></div>{% endif %}
{{ macros.sub_block(IntB_subactions['footer']) }}
</footer>
{% endblock %}

<!--##DEBUG#-->
</div>
<!-- noindex -->
<ul id="quotemenu" class="invis">
<li id="quotemenu_quote"><i class="fas fa-quote-left"></i> Цитировать</li>
<li id="quotemenu_copy"><i class="fas fa-copy"></i> Копировать</li>
<li id="quotemenu_share"><i class="fas fa-share-alt"></i> Поделиться</li>
<li id="quotemenu_vk"><i class="fas fa-share"></i> Отправить ВК</li>
</ul>
<!-- /noindex -->
<script>
if (navigator.userAgent.indexOf("Firefox")>=0){
var elms = document.querySelectorAll('link[rel=preload][as=style]');
for (i=0; i<elms.length; i++){
elms[i].rel="stylesheet";}}
</script>
{% include 'intbjs.tpl' %}
</body>
</html>