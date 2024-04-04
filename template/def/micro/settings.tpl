{% import 'macro.tpl' as macros %}
<fieldset><legend>Базовые настройки</legend>
<div><label><span>Название раздела</span>{{ macros.input('forum[title]',forumdata.title,40,80,'required="required"') }}</label></div>
<div><label><span>Описание</span>{{ macros.textarea('forum[descr]',forumdata.descr,3) }}</label></div>
<div><label><span>Относительный URL раздела<small>Только латинские буквы, цифры, знаки «/»,«_»,«-»,«.»</small></span>{{ macros.input('forum[hurl]',forumdata.hurl,40,255,'pattern="[a-zA-Z0-9\-_/.]{1,255}" required="required"') }}</label></div>
<div><label><span>Категория</span>{{ macros.select('forum[category_id]',forumdata.category_id,categories) }}</label></div>
<div><label><span>Родительский раздел</span>{{ macros.select('forum[parent_id]',forumdata.parent_id,parent_forums) }}</label></div>
<div><label><span>Максимальная длина сообщения</span>{{ macros.input('extdata[max_post_length]',extdata.max_post_length,5,10) }}</label></div>
</fieldset><fieldset><legend>Настройки отображения</legend>
<div><label><span>Шаблон для отображения раздела</span>{{ macros.select('forum[template]',forumdata.template,templates) }}</label></div>
<div><span>Игнорировать шаблон, выбранный пользователем</span>{{ macros.radio('forum[template_override]',{'0':'Нет','1':'Да'},forumdata.template_override) }}</div>
<div><span>Раздел учитывается в статистике сообщений</span>{{ macros.radio('forum[is_stats]',{'1':'Да','0':'Нет'},forumdata.is_stats) }}</div>
<div><span>Раздел выводится на главной странице</span>{{ macros.radio('forum[is_start]',{'1':'Да','0':'Нет'},forumdata.is_start) }}</div>
<div><span>Флуд-раздел (не показывается в "Обновишихся" и поиске)</span>{{ macros.radio('forum[is_flood]',{'0':'Нет','1':'Да'},forumdata.is_flood) }}</div>
<div><label><span>Картинка раздела<small>Файл должен находиться в каталоге www/s/имя_стиля/</small></span>{{ macros.input('forum[icon_nonew]',forumdata.icon_nonew,20) }}</label></div>
<div><label><span>Картинка раздела при наличии новых сообщений</span>{{ macros.input('forum[icon_new]',forumdata.icon_new,20) }}</label></div>
</fieldset><fieldset><legend>Доступные возможности</legend>
<div><span>Разрешить BBCode</span>{{ macros.radio('forum[bcode]',{'1':'Да','0':'Нет'},forumdata.bcode) }}</div>
<div><label><span>Максимальное число смайликов в сообщении</span>{{ macros.input('forum[max_smiles]',forumdata.max_smiles,3) }}</label></div>
</fieldset>
</fieldset><fieldset><legend>Рейтинги</legend>
<div><span>Разрешить рейтинги сообщений</span>{{ macros.radio('forum[rate]',{'1':'Да','0':'Нет','2':'Только положительные'},forumdata.rate) }}</div>
<div><label><span>Рейтинг для присвоения статуса "ценное" <small>0 — отключение автоматического присвоения статуса</small></span>{{ macros.input('forum[rate_value]',forumdata.rate_value,4) }}</label></div>
<div><label><span>Рейтинг для присвоения статуса "флуд" <small>0 — отключение автоматического присвоения статуса</small></span>{{ macros.input('forum[rate_flood]',forumdata.rate_flood,4) }}</label></div>
</fieldset><fieldset><legend>Прикрепление файлов</legend>
<div><label><span>Максимальное количество прикрепленных файлов</span>{{ macros.input('forum[max_attach]',forumdata.max_attach,3) }}</label></div>
<div><span>Типы файлов:<br /><br /><br /><br /><br /><br />&nbsp;</span>
<label><input type="checkbox" name="filetypes[]" value="1" {% if forumdata.attach_types b-and 1 %}checked="checked"{% endif %}/>Изображения</label><br />
<label><input type="checkbox" name="filetypes[]" value="2" {% if forumdata.attach_types b-and 2 %}checked="checked"{% endif %}/>Видео</label><br />
<label><input type="checkbox" name="filetypes[]" value="4" {% if forumdata.attach_types b-and 4 %}checked="checked"{% endif %}/>Аудио</label><br />
<label><input type="checkbox" name="filetypes[]" value="8" {% if forumdata.attach_types b-and 8 %}checked="checked"{% endif %}/>Текстовые</label><br />
<label><input type="checkbox" name="filetypes[]" value="240" {% if forumdata.attach_types b-and 240 %}checked="checked"{% endif %}/>Прочие</label><br />
</div>
</fieldset><fieldset><legend>Итеграция с VK и Telegram</legend>
<div><span>Уведомления в Telegram</span>{{ macros.select('extdata[telegram_mode]',extdata.telegram_mode,{'0':'Выключены','2':'Уведомления о новых темах с текстом первого сообщения'}) }}</div> 
<div><label><span>Ключ бота Telegram</span> {{ macros.input('extdata[telegram_key]',extdata.telegram_key,50,50) }}</label></div>
<div><span>ID канала или чата в Telegram для уведомлений</span>{{ macros.input('extdata[telegram_id]',extdata.telegram_id,21) }}</div>
</fieldset>
<fieldset><legend>Экспорт новых записей в LiveJournal</legend>
<div><label><span>Логин пользователя</span>{{ macros.input('extdata[lj_login]',extdata.lj_login) }}</label></div>
<div><label><span>PIN-код</span>{{ macros.input('extdata[lj_pin]',extdata.lj_pin) }}</label></div>
<div><label><small class="center">Для корректной работы экспорта вам необходимо задать PIN-код в <a href="https://www.livejournal.com/manage/emailpost.bml">настройках вашего LiveJournal</a> и внести адрес <strong>{{ get_opt('email_from') }}</strong> в список доверенных для публикации.</small></label></div>
<div><label><span>Ссылка на первоисточник</span>{{ macros.textarea('extdata[lj_text]',extdata.lj_text,2) }}</label></div>
<div><label><small class="center">Здесь можно указать текст со ссылкой на первоисточник, который будет добавляься в LJ-копию сообщения.
Ту часть, которая должна стать ссылкой, выделите двойными фигурными скобками, например «Первоисточник: мой блог на сайте {{ "{{"~get_opt('site_title')~"}}" }}.»</small></label></div>
<fieldset><legend>Экспорт в VK</legend>
<div><label><span>Числовой ID пользователя или группы VK.com</span>{{ macros.input('extdata[vk_user]',extdata.vk_user,) }} <a href="https://vk.com/linkapp" target="_blank">Узнать</a></label></div>
<div><label><span>Токен</span>{{ macros.input('extdata[vk_token]',extdata.vk_token,48,255,'id="vk_token"') }} {% if forumdata.hurl %}<button onclick="window.open('{{ url(forumdata.hurl) }}/vk_token.htm')">Получить</button>{% else %}Чтобы получить токен, завершите создание раздела{% endif %}</label></div>
</fieldset>
