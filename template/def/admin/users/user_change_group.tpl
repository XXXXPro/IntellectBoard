{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="user_change_group">
<h1>Изменение прав доступа пользователя <span class="username">{{ userdata.basic.display_name }}</span></h1>
<form action="" class="ibform" method="post"><fieldset><legend>Група</legend>
<div><span>Выберите группу, к которой относится пользователь</span>{{ macros.select('level',userdata.ext_data.level,groups) }}</div>
<div class="submit"><button type="submit">Сохранить</button></div>
{{ macros.hidden('uid',userdata.basic.id) }}{{ macros.hidden('authkey',authkey) }}
</fieldset>
</form>
</div>
{% endblock %}