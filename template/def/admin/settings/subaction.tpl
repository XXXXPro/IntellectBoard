{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="settings_subaction">
<a href="subactions.htm">&laquo; К списку вспомогательных блоков</a>
<h1>Настройки вспомогательного блока</h1>
<p>Ддля модуля или действия можно указывать символ *, что означает «может выполняться для любого модуля/действия».
Для раздела и темы в этих же целях укажите нулевое значение.</p>
<form action="" method="post" class="ibform accordion"><fieldset><legend>Базовые настройки</legend>
  <div><label><span>Название блока для отображения результатов</span>{{ macros.input('subaction[block]',subaction.block,48,80) }}</label></div>
  <div><label><span>Описание блока</span>{{ macros.textarea('subaction[name]',subaction.name,3,60) }}</label></div>
  <div><label><span>Модуль</span>{{ macros.input('subaction[module]',subaction.module,20,80) }}</label></div>
  <div><label><span>Действие</span>{{ macros.input('subaction[action]',subaction.action,32,80) }}</label></div>
  <div><label><span>Номер раздела</span>{{ macros.input('subaction[fid]',subaction.fid,4,10) }}</label></div>
  <div><label><span>Номер темы</span>{{ macros.input('subaction[tid]',subaction.tid,4,10) }}</label></div>
  <div><label><span>Файл библиотеки</span>{{ macros.input('subaction[library]',subaction.library,32,80) }}</label></div>
  <div><label><span>Вызываемая процедура</span>{{ macros.input('subaction[proc]',subaction.proc,32,80) }}</label></div>
  <div><label><span>Параметры вызова</span>{{ macros.input('subaction[params]',subaction.params,60,255) }}</label></div>
  <div><label><span>Действие включено?</span>{{ macros.checkbox('subaction[active]',1,subaction.active) }}</label></div>
  <div><label><span>Приоритет</span>{{ macros.input('subaction[priority]',subaction.priority,4,4) }}</label></div>
<div class="submit"><button type="submit">Сохранить</button><input type="hidden" name="subaction[id]" value="{{ subaction.id }}" />
<input type="hidden" name="authkey" value="{{ authkey }}" />
{% if subaction.id %}<button class="right warnbtn confirm" name="delete" type="submit" value="1">Удалить</button>{% endif %}
</div>
</fieldset></form>
</div>
{% endblock %}
