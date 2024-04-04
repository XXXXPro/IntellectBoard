{% extends "main.htm" %}
{% block content %}
А теперь включение прошло корректно!<br />

Проверим вывод даты: {{ testdate|shortdate }}<br />

Проверяем getopt: {{ get_opt('debug') }}
{% endblock %}
