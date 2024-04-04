<!DOCTYPE html>
<html>
<head>
{% block title %}<title>{{ intb.title|striptags|raw }}</title>{% endblock %}
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<noscript><link type="text/css" href="{{ style('s.css') }}" rel="stylesheet" /></noscript>
<script type="text/javascript">
function IntB_css(url) {
	var css = document.createElement('link');
	css.type="text/css"; css.href=url; css.rel="stylesheet";
	document.getElementsByTagName("head")[0].appendChild(css);	
}
function IntB_init() {
var script = document.createElement("script");
script.src="{{ url('js/head.load.min.js') }}";
script.async=true;
script.onload=function() {
 head.load(["{{ url('js/jquery.min.js') }}","{{ url('js/intb.js') }}", "http://typologies.ru/newsline.js"], function() {
  new IntB_main({
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
  });
 });
}
document.getElementsByTagName("head")[0].appendChild(script);
}

IntB_css('{{ style('s.css') }}');
intb_loader = new IntB_init;
</script>
{% block css %}{% endblock %}
{% for item in meta %}
<meta name="{{ item.name }}" content="{{ item.content }}" />
{% endfor %}
{% for item in link %}
<link rel="{{ item.rel }}" href="{{ item.href }}" {% if item.id %}id="{{ item.id }}"{% endif %} />
{% endfor %}
</head>
{% import 'macro.tpl' as macros %}

<body><div id="ib_all">
{% block header %}<div class="header">
<img class="site_logo" src="{{ style('logo.jpg') }}" alt="{{ get_opt('site_title') }}" />
<div id="topmenu">
<ul>
<li><a href="http://sociomodel.ru">Основы соционики <small>SOCIOMODEL.RU</small></a></li>
<li><a href="http://soctype.ru">Социотипы <small>SOCTYPE.RU</small></a></li>
<li><a href="http://typtest.ru">Тесты <small>TYPTEST.RU</small></a></li>
<li><a href="http://typologies.ru">Исследования <small>TYPOLOGIES.RU</small></a></li>
</ul>
</div>
<div id="topsep"></div>
<div id="newsline"><div id="newsblock"></div></div>
</div>
{% endblock %}
{% block welcome %}
{% if intb.announce_text %}
<div class="block announce"><div class="welcome"><div class="headline">Объявление</div>
<div>{{ intb.announce_text }}</div></div>{% endif %}
{% endblock %}

<div id="content">
<div id="topline">

<ul>
<li><a href="{{ url('') }}">Главная</a></li>
<li><a href="{{ url('newtopics/') }}">Обновления</a></li>
{% if not is_guest() %}<li><a href="{{ url('bookmark/subscr.htm') }}">Подписка</a></li>{% endif %}
<li><a href="{{ url('search/') }}">Поиск</a></li>
</ul>

<a id="twitter" rel="nofollow" title="Новости проекта в Twitter" href="http://twitter.com/typologies_ru/"></a>
<a id="vkontakte" rel="nofollow" title="Наша страница ВКонтакте" href="http://vk.com/typologies"></a>

</div>
{# Сообщения об ошибках или уведомления, если таковые есть #}
{% block messages %}
{{ macros.messages(intb.messages) }}
{% endblock %}

{# Блок, вместо которого будет отображен контент соответствующей страницы! #}
{% block content %}
Если вы читаете это, то нужный шаблон почему-то не подключился!<br />
{% endblock %}

{% block user_login %}<div class="userlink">
{% if is_guest() %}
Для тех, кто все же не любит анонимность:
<a href="{{ url('user/login.htm') }}" rel="nofollow">Войти</a> |  
<a href="{{ url('user/register.htm') }}" rel="nofollow">Зарегистрироваться</a>
{% else %}
Рады видеть вас, <span class="username">{{ user.display_name }}</span>! 
<a href="{{ url('user/update.htm') }}">Профиль и настройки</a> | <a href="{{ url('user/logout.htm') }}">Выйти</a>
{% endif %}</div>
{% endblock %}
</div>

{% block footer %}
<address class="copyright">&copy; {{ get_opt('site_copyright')|raw }}<br />
{# Внимание! Удаление или изменение строки ниже будет нарушением Лицензионного Соглашения. Будьте достойными людьми и не трогайте ее!  #}
Форум работает на <a href="https://intbpro.ru">Intellect Board Pro</a>
{{ intb.intb_version }} &copy; 2007, 2010, 2012&ndash;2014—2023, 4X_Pro.
</address>
{# Вывод пустого div для того, чтобы вызвать планировщик заданий на случай, если он не висит на системном cron #}
{% if get_opt('cron_img') %}<div style="height: 1px; width: 1px; background: url('{{ url('cron.php') }}')"></div>{% endif %}
{% endblock %}

<!--##DEBUG#-->
</div></body>
</html>