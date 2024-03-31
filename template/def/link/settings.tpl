{% import 'macro.tpl' as macros %}
<fieldset><legend>Базовые настройки</legend>
<div><label><span>Название раздела</span>{{ macros.input('forum[title]',forumdata.title) }}</label></div>
<div><label><span>Описание</span>{{ macros.textarea('forum[descr]',forumdata.descr,3) }}</label></div>
<div><label><span>URL внешей ссылки</span>{{ macros.input('extdata[url]',extdata.url) }}</label></div> 
<div><label><span>Категория</span>{{ macros.select('forum[category_id]',forumdata.category_id,categories) }}</label></div>
<div><label><span>Родительский раздел</span>{{ macros.select('forum[parent_id]',forumdata.parent_id,parent_forums) }}</label></div>
</fieldset><fieldset><legend>Настройки отображения</legend>
<div><span>Раздел выводится на главной странице</span>{{ macros.radio('forum[is_start]',{'1':'Да','0':'Нет'},forumdata.is_start) }}</div>
<div><label><span>Картинка раздела<small>Файл должен находиться в каталоге www/s/имя_стиля/</small></span>{{ macros.input('forum[icon_nonew]',forumdata.icon_nonew,20) }}</label></div>
{{ macros.hidden('forum[hurl]',forumdata.hurl ? forumdata.hurl : 'link'~time) }}
</fieldset>