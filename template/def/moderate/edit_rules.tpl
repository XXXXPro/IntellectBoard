{% extends 'main.tpl' %}
{% block content %}
<div id="moderate_edit_rules">
<form action="" method="post" class="ibform"><fieldset style="border: 0"><legend>Редактирование правил раздела</legend>
<textarea rows="12" cols="60" name="text" class="wysiwyg" style="width: 99%; margin: auto">{{ static_text }}</textarea>
<div class="submit"><button type="submit">Сохранить</button>
<input type="hidden" name="authkey" value="{{ authkey }}" /></div>
</fieldset></form>
</div>
{% endblock %}