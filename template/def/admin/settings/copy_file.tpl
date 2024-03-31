{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="settings_copy_file">
<h1>Копирование файлов из шаблона по умолчанию</h1>
<a href="edit_style.htm?style={{ style }}">&laquo; К редактированию стиля</a>
<p>Данная опция позволяет скопировать файлы в текущий стиль из стиля по умолчанию для последующего редактирования.</p>
<form action="" method="post" class="ibform"><fieldset><legend>Файлы шаблона</legend>
{% for item in tpl_files %}
<div>{{ macros.checkbox('tpl['~item~']',tpl[item],1) }}{{ item }}</div>
{% endfor %}
<fieldset><legend>Файлы статики (графика, CSS, JavaScript и прочее)</legend>
{% for item in css_files %}
<div>{{ macros.checkbox('css['~item~']',css[item],1) }}{{ item }}</div>
{% endfor %}
<fieldset><legend style="display: none"></legend>
<div class="submit"><button type="submit">Скопировать</button>{{ macros.hidden('authkey',authkey) }}{{ macros.hidden('style',style) }}</div>
</fieldset></form>
</div>
{% endblock %}