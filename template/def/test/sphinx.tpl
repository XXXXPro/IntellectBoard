{% extends 'main.tpl' %}
{% block content %}
<form action=""><fieldset><legend>Текст для поиска</legend>
<input type="text" name="q" value="{{ query }}" /><button type="submit">Искать</button>
</fieldset></form>
<h1>Результаты поиска</h1>
{% for pid,item in found %}
<p>Сообщение #{{pid}}. Вес: {{ item.weight }}, UID: {{ item.attrs.uid }}, дата: {{ item.attrs.postdate|longdate }}, раздел: {{ item.attrs.fid }}</p>
{% endfor %}
{% endblock %} 