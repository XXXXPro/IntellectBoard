{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style type="text/css">
#ib_all .category { display: inline-block; width: 32%; vertical-align: top; padding-right: 1%}
#ib_all .category div { font-weight: bold }
#ib_all .ibform div:nth-child(even) { background: none; }
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="forums_mass">
<a href="view.htm">&laquo; К списку разделов</a>
<h1>Групповая настройка параметров разделов</h1>
<form action="" method="post" class="ibform">
<fieldset><legend>Разделы</legend>
<div>
<label><input type="radio" name="range" value="all" />Применить настройки ко всем разделам (включая личные, не показанные тут)</label><br />
<label><input type="radio" name="range" value="selected" checked="checked"/>Применить настройки к выбранным разделам:</label><br />
</div>
{% for cat in categories %}
<div class="category"><div>&raquo; {{ cat.title }}</div>
{% for item in cat.forums %}
<label><input type="checkbox" name="ids[]" value="{{ item.id }}" />{{ item.title }}</label><br />
{% endfor %}</div>
{% endfor %}</fieldset> 

<fieldset><legend>Настройки</legend>
<div><label><span>Шаблон для отображения раздела</span>{{ macros.select('forum[template]','-1',templates) }}</label></div>
<div><span>Игнорировать шаблон, выбранный пользователем</span>{{ macros.radio('forum[template_override]',{'-1':'Без изменений','0':'Нет','1':'Да'},'-1') }}</div>
<div><span>Раздел учитывается в статистике сообщений</span>{{ macros.radio('forum[is_stats]',{'-1':'Без изменений','1':'Да','0':'Нет'},'-1') }}</div>
<div><span>Раздел выводится на главной странице</span>{{ macros.radio('forum[is_start]',{'-1':'Без изменений','1':'Да','0':'Нет'},'-1') }}</div>
<div><span>Флуд-раздел (не показывается в "Обновишихся" и поиске)</span>{{ macros.radio('forum[is_flood]',{'-1':'Без изменений','0':'Нет','1':'Да'},'-1') }}</div>
<div><span>Разрешить BBCode</span>{{ macros.radio('forum[bcode]',{'-1':'Без изменений','1':'Да','0':'Нет'},'-1') }}</div>
<div><label><span>Максимальное число смайликов в сообщении<small>Значение <strong>-1</strong> — оставить без изменений</small></span>{{ macros.input('forum[max_smiles]','-1',3) }}</label></div>
<div><span>Опросы</span>{{ macros.radio('forum[polls]',{'-1':'Без изменений','1':'Включены','0':'Выключены','2':'Только уже созданные, создание новых запрещено'},'-1') }}</div>
<div><span>Кураторы тем</span>{{ macros.radio('forum[selfmod]',{'-1':'Без изменений','0':'Отсутствуют','1':'Куратор — создатель темы','2':'Назначаются модераторами вручную'},'-1') }}</div>
<div><span>Выводить первое сообщение на всех страницах темы</span>{{ macros.radio('forum[sticky_post]',{'-1':'Без изменений','0':'Никогда','2':'Задается создателем темы','1':'Задается модератором','3':'Всегда'},'-1') }}</div>
<div><span>Разрешить рейтинги сообщений</span>{{ macros.radio('forum[rate]',{'-1':'Без изменений','1':'Да','0':'Нет','2':'Только положительные'},'-1') }}</div>
<div><label><span>Максимальное количество прикрепленных файлов<small>Значение <strong>-1</strong> — оставить без изменений</small></span>{{ macros.input('forum[max_attach]','-1',3) }}</label></div>
<div><label><span>Рейтинг для присвоения статуса "ценное" <small>0 — отключение автоматического присвоения статуса, -1 — оставить без изменений</small></span>{{ macros.input('forum[rate_value]',-1,4) }}</label></div>
<div><label><span>Рейтинг для присвоения статуса "флуд" <small>0 — отключение автоматического присвоения статуса, -1 — оставить без изменений</small></span>{{ macros.input('forum[rate_flood]',-1,4) }}</label></div>
</fieldset><fieldset><legend>Прикрепление файлов</legend>
<div><label><span>Максимальное количество прикрепленных файлов</span>{{ macros.input('forum[max_attach]',forumdata.max_attach,3) }}</label></div>
<div><span>Типы файлов:<br /><br /><br /><br /><br /><br /><br/>&nbsp;</span>
<label><input type="radio" name="attaches" value="-1" checked="checked"/>Не изменять</label> 
<label><input type="radio" name="attaches" value="change" />Выставить следующие значения:</label><br />
<label><input type="checkbox" name="filetypes[]" value="1" {% if forumdata.attach_types b-and 1 %}checked="checked"{% endif %}/>Изображения</label><br />
<label><input type="checkbox" name="filetypes[]" value="2" {% if forumdata.attach_types b-and 2 %}checked="checked"{% endif %}/>Видео</label><br />
<label><input type="checkbox" name="filetypes[]" value="4" {% if forumdata.attach_types b-and 4 %}checked="checked"{% endif %}/>Аудио</label><br />
<label><input type="checkbox" name="filetypes[]" value="8" {% if forumdata.attach_types b-and 8 %}checked="checked"{% endif %}/>Текстовые</label><br />
<label><input type="checkbox" name="filetypes[]" value="240" {% if forumdata.attach_types b-and 240 %}checked="checked"{% endif %}/>Прочие</label><br />
</div>
<div class="submit"><button type="submit">Сохранить</button></div>
{{ macros.hidden('authkey',authkey) }}
</fieldset></form>
</div>
{% endblock %}