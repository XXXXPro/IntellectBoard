{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="settings_announce">
<h1>Объявления форумов</h1>
<p>Начиная с версии 3.02 режим показа объявлений задается в разделе «Вспомогательные блоки» (см. блок «Блок объявлений»).</p>
<form action="" method="get"><fieldset style="border: 0"><legend style="display: none"></legend>
<label>Выберите раздел для редактирования объявления: {{ macros.select('fid',fid,forum_list) }}</label><button type="submit">Перейти</button>
</fieldset></form>
<form action="" method="post" class="ibform"><fieldset><legend>Текст объявления</legend>
<div>
<textarea style="width: 98%; margin: auto" cols="60" rows="25" name="text" class="wysiwyg">{{ announce }}</textarea>
</div>
<div class="submit"><button type="submit">Сохранить</button>
<input type="hidden" name="authkey" value="{{ authkey }}" /><input type="hidden" name="fid" value="{{ fid }}" /></div>
</fieldset></form></div>
{% endblock %}