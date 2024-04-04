{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="settings_create_style">
<h1>Создание нового стиля</h1>
<p><a href="edit_style.htm">&laquo; К редактированию стиля</a></p>
<form action="" method="post" class="ibform"><fieldset><legend>Параметры стиля</legend>
<div><label><span>Название каталога стиля<br /><small>Может содержать только символы латинского алфавита, цифры, подчеркивание и дефис.</small></span>{{ macros.input('style[filename]',style.filename,60,80) }}</label></div>
<div><label><span>Название стиля<br /><small>Это название показывается пользователям при выборе стиля</small></span>{{ macros.input('style[name]',style.name,60,80) }}</label></div>
<div><label><span>Заблокированный стиль<br /><small>Заблокированные стили доступны только администраторам, что полезно на этапе создания и тестирования.</small></span>{{ macros.checkbox('style[locked]',style.locked,1) }}</label></div>
<div class="submit"><button type="submit">Сохранить</button>{{ macros.hidden('authkey',authkey) }}</div>
</fieldset></form>
</div>
{% endblock %}