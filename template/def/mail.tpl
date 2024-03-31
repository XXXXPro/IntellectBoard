<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
{% block title %}<title>{{ mail_subject|striptags|raw }}</title>{% endblock %}
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{% block css %}{% endblock %}
</head><body>
{% block content %}
Если вы читаете это, значит, блок контента не вставился!
{% endblock %}
</body></html>