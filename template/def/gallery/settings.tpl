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
<div><span>Порядок сортировки тем в разделе</span>{{ macros.radio('forum[sort_column]',{'last_post_time':'По последнему сообщению в теме','first_post_id':'По первому сообщению темы'},forumdata.sort_column) }}</div>
<div><span>Направление сортировки</span>{{ macros.radio('forum[sort_mode]',{'DESC':'По убыванию','ASC':'По возрастанию'},forumdata.sort_mode) }}</div>
<div><span>Раздел учитывается в статистике сообщений</span>{{ macros.radio('forum[is_stats]',{'1':'Да','0':'Нет'},forumdata.is_stats) }}</div>
<div><span>Раздел выводится на главной странице</span>{{ macros.radio('forum[is_start]',{'1':'Да','0':'Нет'},forumdata.is_start) }}</div>
<div><span>Флуд-раздел (не показывается в "Обновишихся" и поиске)</span>{{ macros.radio('forum[is_flood]',{'0':'Нет','1':'Да'},forumdata.is_flood) }}</div>
<div><label><span>CSS-класс раздела</span>{{ macros.input('forum[icon_nonew]',forumdata.icon_nonew,20) }}</label></div>
<div><label><span>CSS-класс раздела при наличии новых сообщений</span>{{ macros.input('forum[icon_new]',forumdata.icon_new,20) }}</label></div>
</fieldset><fieldset><legend>Доступные возможности</legend>
<div><span>Разрешить BBCode</span>{{ macros.radio('forum[bcode]',{'1':'Да','0':'Нет'},forumdata.bcode) }}</div>
<div><label><span>Максимальное число смайликов в сообщении</span>{{ macros.input('forum[max_smiles]',forumdata.max_smiles,3) }}</label></div>
<input type="hidden" name="forum[polls]" value="0"/>
<div><span>Теги для записей</span>{{ macros.radio('forum[tags]',{'1':'Да','0':'Нет'},forumdata.tags) }}</div>
<div><span>Кураторы тем</span>{{ macros.radio('forum[selfmod]',{'0':'Отсутствуют','1':'Куратор — создатель темы','2':'Назначаются модераторами вручную'},forumdata.selfmod) }}</div>
<input type="hidden" name="forum[sticky_post]" value="0"/>
<div><label><span>Выводить на главной странице обложки </span>{{ macros.input('extdata[mainpage_posts]',extdata.mainpage_posts,4) }} последних альбомов</label></div>
</fieldset><fieldset><legend>Рейтинги</legend>
<div><span>Разрешить рейтинги сообщений</span>{{ macros.radio('forum[rate]',{'1':'Да','0':'Нет','2':'Только положительные'},forumdata.rate) }}</div>
<div><label><span>Рейтинг для присвоения статуса "ценное" <small>0 — отключение автоматического присвоения статуса</small></span>{{ macros.input('forum[rate_value]',forumdata.rate_value,4) }}</label></div>
<div><label><span>Рейтинг для присвоения статуса "флуд" <small>0 — отключение автоматического присвоения статуса</small></span>{{ macros.input('forum[rate_flood]',forumdata.rate_flood,4) }}</label></div>
</fieldset><fieldset><legend>Прикрепление файлов</legend>
<div><label><span>Максимальное количество фото в серии</span>{{ macros.input('forum[max_attach]',forumdata.max_attach,3) }}</label></div>
<input type="hidden" name="filetypes[]" value="1"/>
<input type="hidden" name="filetypes[]" value="2"/>
</fieldset><fieldset><legend>Итеграция с VK и Telegram</legend>
<div><span>Уведомления в Telegram</span>{{ macros.select('extdata[telegram_mode]',extdata.telegram_mode,{'0':'Выключены','1':'Краткие уведомления о новых темах','3':'Краткие уведомления о всех сообщениях'}) }}</div> 
<div><label><span>Ключ бота Telegram</span> {{ macros.input('extdata[telegram_key]',extdata.telegram_key,50,50) }}</label></div>
<div><span>ID канала или чата в Telegram для уведомлений</span>{{ macros.input('extdata[telegram_id]',extdata.telegram_id,21) }}</div>
</fieldset>
