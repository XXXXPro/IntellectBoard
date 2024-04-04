{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<h1>Редактор меню</h1>
<form action="menu_edit.htm" method="get" class="ibform"><fieldset><legend>Выберите меню для редактирования</legend>
{% for item in menus %}<div><label><input type="radio" name="id" value="{{ item.id }}" {% if loop.index==1 %}checked="checked" {% endif %}/>{{ item.descr }}</label></div>
{% endfor %}
<div class="sumbit"><button type="submit">Перейти к редактированию меню</button></div> 
</fieldset>
</form>
{% endblock %}