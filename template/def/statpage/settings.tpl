{% import 'macro.tpl' as macros %}
<fieldset><legend>Базовые настройки</legend>
<div><label><span>Название раздела</span>{{ macros.input('forum[title]',forumdata.title,40,80,'required="required"') }}</label></div>
<div><label><span>Описание</span>{{ macros.textarea('forum[descr]',forumdata.descr,3) }}</label></div>
<div><label><span>Относительный URL раздела<small>Только латинские буквы, цифры, знаки «/»,«_»,«-»,«.»</small></span>{{ macros.input('forum[hurl]',forumdata.hurl,40,255,'pattern="[a-zA-Z0-9\-_/.]{1,255}" required="required"') }}</label></div>
<div><label><span>Категория</span>{{ macros.select('forum[category_id]',forumdata.category_id,categories) }}</label></div>
<div><label><span>Родительский раздел</span>{{ macros.select('forum[parent_id]',forumdata.parent_id,parent_forums) }}</label></div>
</fieldset><fieldset><legend>Настройки отображения</legend>
<div><label><span>Шаблон для отображения раздела</span>{{ macros.select('forum[template]',forumdata.template,templates) }}</label></div>
<div><span>Игнорировать шаблон, выбранный пользователем</span>{{ macros.radio('forum[template_override]',{'0':'Нет','1':'Да'},forumdata.template_override) }}</div>
<div><span>Раздел выводится на главной странице</span>{{ macros.radio('forum[is_start]',{'1':'Да','0':'Нет'},forumdata.is_start) }}</div>
<div><span>Флуд-раздел (не показывается в "Обновишихся" и поиске)</span>{{ macros.radio('forum[is_flood]',{'0':'Нет','1':'Да'},forumdata.is_flood) }}</div>
<div><label><span>Картинка раздела<small>Файл должен находиться в каталоге www/s/имя_стиля/</small></span>{{ macros.input('forum[icon_nonew]',forumdata.icon_nonew,20) }}</label></div>
</fieldset><fieldset><legend>Доступные возможности</legend>
<div><span>Разрешить BBCode</span>{{ macros.radio('forum[bcode]',{'1':'Да','0':'Нет'},forumdata.bcode) }}</div>
<div><label><span>Максимальное число смайликов в сообщении</span>{{ macros.input('forum[max_smiles]',forumdata.max_smiles,3) }}</label></div>
</fieldset>