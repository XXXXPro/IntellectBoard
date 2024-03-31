<!DOCTYPE html>
<html lang="ru">
<head>
{% block title %}
<title>{{ intb.title|striptags|raw }}</title>
{% endblock %}

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link type="text/css" href="{{ style('s.css') }}" rel="stylesheet" />
<style>
#ib_all { max-width: none; min-width: none; padding: 0 5px }
</style>

{% block css %}{% endblock %}
{% block meta %}{% endblock %}

{% include 'counter_h.tpl' ignore missing %}
</head>
{% import 'macro.tpl' as macros %}
<body><div id="ib_all">
{% include 'counter_t.tpl' ignore missing %}

<main>
{# Блок, вместо которого будет отображен контент соответствующей страницы! #}
{% block content %}
Если вы читаете это, то нужный шаблон почему-то не подключился!<br />
{% endblock %}
</main>

<!--##DEBUG#-->
</body>
</html>