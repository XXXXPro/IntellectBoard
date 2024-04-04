{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{# % import 'macro.tpl' as macros % #}
<form action="" method="post" class="ibform"><fieldset><legend>Правила форума</legend>
<div>
<textarea style="width: 98%; margin: auto" cols="60" rows="25" name="text" class="wysiwyg">{{ rules|raw }}</textarea>
</div>
<div class="submit"><button type="submit">Сохранить</button><input type="hidden" name="authkey" value="{{ authkey }}" /></div>
</fieldset></form>
{% endblock %}