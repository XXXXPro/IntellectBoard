{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="preload" as="style" href="{{ style('privmsg.css') }}" onload="this.rel='stylesheet'" />
{% endblock %}
{% block content %}
{% include 'privmsg/pm_form.tpl' %}
{% endblock %}
