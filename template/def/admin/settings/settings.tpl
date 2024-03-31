{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<h1>Настройки форума</h1>
<form action="" method="post" class="ibform accordion"><fieldset><legend>Базовые настройки</legend>
  <div><label><span>Название форума</span>{{ macros.input('config[site_title]',config.site_title,48,80) }}</label></div>
  <div><label><span>Описание форума</span>{{ macros.input('config[site_description]',config.site_description,48,255) }}</label></div>
  <div><label><span>Краткое название<small>Выводится в строке навигации как указатель на главную</small></span>{{ macros.input('config[site_start]',config.site_start,40,60) }}</label></div>
  <div><label><span>Заголовок главной страницы</span>{{ macros.input('config[mainpage_title]',config.mainpage_title,48,80) }}</label></div>
  <div><label><span>URL логотипа или главного изображения<small>Выводится в OpenGraph-разметке</small></span>{{ macros.input('config[site_picture]',config.site_picture,40,60) }}</label></div>
  <div><label><span>Copyright-сообщение</span>{{ macros.input('config[site_copyright]',config.site_copyright,48,80) }}</label></div>
  <div><label><span>Шаблон форума</span>{{ macros.select('config[site_template]',config.site_template,templates) }}</label></div>
  <div><label><span>Контактный Email администратора форума</span> {{ macros.input('config[email]',config.email,40) }}</label></div>
  <div><label><span>Email, с которого отправляются рассылки и уведомления</span> {{ macros.input('config[email_from]',config.email_from,40) }}</label></div>
  <div><label><span>Email для уведомлений об ошибках доставки почты<small>Рекомендуется указывать адрес, на котором вся почта автоматически удаляется</small></span> {{ macros.input('config[email_return]',config.email_return,40) }}</label></div>
</fieldset><fieldset><legend>Режимы работы и служебные настройки</legend>
  <div><span>Показывать версию Intellect Board</span>{{ macros.radio('config[site_version]',{'1':'Да','0':'Нет'},config.site_version) }}</div>
  <div><label><span>Отладочный режим</span>{{ macros.select('config[debug]',config.debug,{'0':'Выключен',
'1':'Вывод предупреждений и информации, переданной функции _dbg','2':'Сообщения об ошибках с номером строки и файла',
'3':'Сообщения об ошибках с полным стеком вызовов','4':'Данные о времени выполнения и памяти'}) }}</label></div>
  <div><label><span>Отладочный режим запросов SQL</span>{{ macros.select('config[sql_debug]',
  config.sql_debug,{'0':'Выключен','1':'Выводить все выполняемые запросы и время выполнения','2':'Делать EXPLAIN для каждого запроса'}) }}</label></div>
  <div><span>Сжатие GZIP</span> {{ macros.radio('config[gzip]', {'1':'Включено','0':'Выключено'}, config.gzip) }}</div>
  <div><span>Использовать минификацию HTML</span> {{ macros.radio('config[minify_html]', {'1':'Включено','0':'Выключено'}, config.minify_html) }}</div>
  <div><span>Кеширование страниц форума и шаблонов</span> {{ macros.radio('config[nocache]', {'0':'Кеширование используется (рекомендуется)','1':'Кеширование заблокировано'}, config.nocache) }}</div>
  <div><label><span>Идентификатор сессии</span> {{ macros.input('config[session]',config.session,12) }}</label></div>
  <div><label><span>Корректировка времени сервера</span> {{ macros.input('config[timezone_correction]',config.timezone_correction,5) }} секунд</label></div>
  <div><label><span>Время присутствия после последнего действия</span> {{ macros.input('config[online_time]',config.online_time,3) }} минут</label></div>
  <div><span>Запуск планировщика заданий форума</span> {{ macros.radio('config[cron_img]', {'0':'Через системный cron','1':'Запуск через невидимый img'}, config.cron_img) }}</div>
  <div><span>Вывод "Вчера" и "Сегодня" в датах</span> {{ macros.radio('config[date_today]', {'0':'Выключено','1':'Включено'}, config.date_today) }}</div>
  <div><span>Вторая навигационная строка после сообщений в теме</span> {{ macros.radio('config[bottom_location]', {'0':'Выключена','1':'Включена'}, config.bottom_location) }}</div>
  <div><label><span>Минимальная длина сообщения</span> {{ macros.input('config[post_minlength]',config.post_minlength,6) }} символов</label></div>
  <div><label><span>Максимальная длина сообщения</span> {{ macros.input('config[post_maxlength]',config.post_maxlength,6) }} символов</label></div>
{# <div><span>Использовать библиотеку социальных сетей</span> {{ macros.radio('config[javascript_share]', {'':'Не использовать','share42':'Share42'}, config.javascript_share) }}</div> #}
  <div><span>Относительный URL списка разделов<small>Оставьте пустым, чтобы список разделов был на главной</small></span> {{ macros.input('config[forum_mainpage]',config.forum_mainpage,30) }}</div>
  <div><span>Адрес форума всегда указывается с протоколом https<small>Включите эту опцию, если протокол определяется неправильно.</small></span> {{ macros.radio('config[force_https]', {'0':'Выключено','1':'Включено'}, config.force_https) }}</div>

</fieldset><fieldset><legend>Функции форума и производительность</legend>
  <div><span>Поддержка разметки OpenGraph</span> {{ macros.radio('config[opengraph]', {'0':'Выключена','1':'Включена'}, config.opengraph) }}</div>
  <div><span>Почтовые функции</span> {{ macros.radio('config[email_enabled]', {'0':'Выключены','1':'Включены'}, config.email_enabled) }}</div>
  <div><span>Вложенные разделы</span> {{ macros.radio('config[subforums_enabled]', {'0':'Отключены','1':'Включены'}, config.enable_subforums) }}</div>
  <div><span>Личные сообщения</span> {{ macros.radio('config[enable_privmsg]', {'0':'Выключены','1':'Включены'}, config.enable_privmsg) }}</div>
  <div><span>Отслеживание времени последнего визита</span> {{ macros.radio('config[visits_mode]', {'0':'Только по разделам','1':'По разделам и темам (рекомендуется)'}, config.visits_mode) }}</div>
  <div><span>Статистика на главной странице</span> {{ macros.radio('config[mainpage_stats]', {'0':'Выключена','1':'Над списком разделов','2':'Под списком разделов'}, config.mainpage_stats) }}</div>
  <div><span>Макс. количество сообщений, выдаваемых в RSS</span> {{ macros.input('config[rss_max_items]',config.rss_max_items,5) }}</label></div>

</fieldset><fieldset><legend>Настройки безопасности</legend>
  <div><span>Проверка поля Referer для POST-запросов</span> {{ macros.radio('config[check_referer]', {'0':'Выключена','1':'Включена (рекомендуется)'}, config.check_referer) }}</div>
  <div><span>Проверка неизменности User-Agent</span> {{ macros.radio('config[check_user_agent]', {'0':'Выключена','1':'Включена (рекомендуется)'}, config.check_user_agent) }}</div>
  <div><span>Режим CAPTCHA:</span> {{ macros.radio('config[captcha]', {'0':'Выключена','1':'KCAPTCHA (цифры)','2':'ReCAPTCHA от Google'}, config.captcha) }}</div>
  <div><span>Ключ сайта для ReCAPTCHA:</span> {{ macros.input('config[captcha_public_key]',config.captcha_public_key) }}</div>
  <div><span>Секретный ключ для ReCAPTCHA:</span> {{ macros.input('config[captcha_secret_key]',config.captcha_secret_key) }}</div>
  <div><span>Модераторам разрешено редактировать правила разделов</span> {{ macros.radio('config[moder_edit_rules]', {'1':'Да','0':'Нет'}, config.moder_edit_rules) }}</div>
  <div><span>Модераторам разрешено редактировать вступительное слово</span> {{ macros.radio('config[moder_edit_foreword]', {'1':'Да','0':'Нет'}, config.moder_foreword) }}</div>
  <div><label><span>Секретный ключ (для генерации поля authkey)</span> {{ macros.input('config[site_secret]',config.site_secret,30) }}</label></div>

</fieldset><fieldset><legend>Регистрация и профили пользователей пользователей</legend>
  <div><label><span>Контрольный вопрос при регистрации<small>Используется, чтобы отличить пользователей от ботов. Оставьте пустым, если не требуется.</small></span> {{ macros.input('config[userlib_reg_question]',config.userlib_reg_question,40) }}</label></div>
  <div><label><span>Ответы на контрольный вопрос<small>Можно перечислить несколько вариантов через запятую</small></span> {{ macros.input('config[userlib_reg_answers]',userlib_reg_answers,48) }}</label></div>
  <div><span>Режим активации зарегистрированных пользователей</span> {{ macros.radio('config[userlib_activation]', {'0':'Не требуется','1':'Пользователем через Email','2':'Администрацией форума через АЦ'}, config.userlib_activation) }}</div>
  <div><label><span>Таймаут между регистрациями пользователей</span> {{ macros.input('config[userlib_register_timeout]',config.userlib_register_timeout,3) }} секунд</label></div>
  <div><label><span>Таймаут между попытками входа пользователя</span> {{ macros.input('config[userlib_login_timeout]',config.userlib_login_timeout,3) }} секунд</label></div>
  <div><span>Уведомлять администраторов о регистрации пользователей</span> {{ macros.radio('config[userlib_newuser_mail]', {'0':'Выключено','1':'Включено'}, config.userlib_newuser_mail) }}</div>
  <div><span>Проверять существование MX-записи для почты</span> {{ macros.radio('config[userlib_check_mx]', {'0':'Выключено','1':'Включено'}, config.userlib_check_mx) }}</div>
  <div><span>Позволять пользователям выбирать шаблон форума в профиле</span> {{ macros.radio('config[userlib_allow_template]', {'0':'Нет','1':'Да'}, config.userlib_allow_template) }}</div>
  <div><label><span>Допустимые символы в имени</span> {{  macros.select('config[userlib_name_mode]',config.userlib_name_mode,{'0':'Все, кроме разделителей и кавычек','1':'Буквы кириллицы и латиницы, пробел, цифры, знаки []*().+-/?!',
  '2':'Буквы кириллицы и латиницы, цифры и пробелы','3':'Только буквы латиницы и пробелы','4':'Имя должно быть правильным идентификатором','5':'Задается регулярным выражением'}) }}</label></div>
  <div><label><span>Регулярное выражение для проверки допустимости имени <small>Задается в формате, используемом preg_match, включая ограничительные символы.</small></span> {{ macros.input('config[userlib_name_regexp]',config.userlib_name_regexp,40) }}</label></div>
  <div><label><span>Журнал действий с профилями пользователей</span>{{ macros.select('config[userlib_logs]',config.userlib_logs,
  {'0':'Выключен','1':'Только регистрация','2':'Регистрация и смена базовых параметров','3':'Регистрация и все изменения профиля','4':'Регистрация, редактирование профиля, восстановление пароля, удачный вход','5':'Все действия над профилем, включая неудачные попытки входа'}) }}</label></div>
  <div><label><span>Формат профиля пользователя <small>Используйте %s там, где должен быть идентификатор</small></span> {{ macros.input('config[user_hurl]',config.user_hurl,40) }}</label></div>
  <div><label><span>Максимальное количество штрафных баллов <small>При достижении указанного количества баллов в действующих предупреждениях пользователь автоматически изгоняется с форума</small></span> {{ macros.input('config[user_max_warnings]',config.user_max_warnings,3) }}</label></div>

</fieldset><fieldset><legend>Настройки поиска по форуму</legend>
  <div><label><span>Поиск по форуму</span> {{ macros.select('config[search_mode]', config.search_mode, {'0':'Выключен','yandex':'Поисковая форма Яндекса','google':'Поисковая форма Google','fulltext':'FULLTEXT-поиск средствами СУБД','sphinx':'Использование системы Sphinx'}) }}</label></div>
  <div><label><span>Идентификатор поисковой формы Яндекс</span> {{ macros.input('config[search_yandex_id]',config.search_yandex_id,10) }}</label></div>
  <div><label><span>Идентификатор поисковой формы Google</span> {{ macros.input('config[search_google_id]',config.search_google_id,40) }}</label></div>
  <div><label><span>Индекс Sphinx для поиска по сообщениям</span> {{ macros.input('config[search_sphinx_post_index]',config.search_sphinx_post_index,40) }}</label></div>
  <div><label><span>Индекс Sphinx для поиска по темам</span> {{ macros.input('config[search_sphinx_topic_index]',config.search_sphinx_topic_index,40) }}</label></div>
  <div><label><span>Таймаут между поисковыми запросами</span> {{ macros.input('config[search_timeout]',config.search_timeout,3) }} секунд</label></div>

</fieldset><fieldset><legend>Отслеживание действий пользователей</legend>
  <div><small>Внимание: в законодательствах некоторых стран могут быть требования и ограничения, связанные со сбором информации о пользователях.</small></div>
  <div><span>Запись в журнал действий гостей</span>{{ macros.radio('config[enable_log_guests]', {'0':'Выключена','1':'Включена'}, config.enable_log_guests)}}</div>
  <div><span>Запись в журнал действий зарегистрированных пользователей</span> {{ macros.radio('config[enable_log_users]', {'0':'Выключена','1':'Включена'},config.enable_log_users)}}</div>
  <div><span>Установка отслеживающих cookie</span> {{ macros.radio('config[enable_user_cookies]', {'0':'Выключена','1':'Включена'},config.enable_user_cookies)}}</div>
  <div><span>Сохранение описаний действий пользователя (делает журнал более информативным, но существенно увеличивает объем)</span> {{ macros.radio('config[enable_log_action]', {'0':'Выключено','1':'Включено'},config.enable_log_action)}}</div>
  <div><span>Сохранение User-Agent пользователя (также увеличивает объем журнала)</span> {{ macros.radio('config[enable_log_useragent]', {'0':'Выключено','1':'Включено'},config.enable_log_useragent)}}</div>

</fieldset><fieldset><legend>Графические настройки</legend>
  <div><label><span>Размеры уменьшенных изображений в сообщениях</span> {{ macros.input('config[posts_preview_x]',config.posts_preview_x,4) }}×{{ macros.input('config[posts_preview_y]',config.posts_preview_y,4) }} пикселей</label></div>
  <div><label><span>Качество JPEG при генерации уменьшенной картинки</span> {{ macros.input('config[posts_preview_jpeg_qty]',config.posts_preview_jpeg_qty,3) }}</label></div>
  <div><label><span>Максимальные размеры аватара</span> {{ macros.input('config[userlib_avatar_x]',config.userlib_avatar_x,4) }}×{{ macros.input('config[userlib_avatar_y]',config.userlib_avatar_y,4) }} пикселей</label></div>
  <div><label><span>Качество JPEG для обработки аватара</span> {{ macros.input('config[userlib_avatar_jpeg_qty]',config.userlib_avatar_jpeg_qty,3) }}</label></div>
  <div><label><span>Максимальные размеры фотографии профиля</span> {{ macros.input('config[userlib_photo_x]',config.userlib_photo_x,4) }}×{{ macros.input('config[userlib_photo_y]',config.userlib_photo_y,4) }} пикселей</label></div>
  <div><label><span>Размеры уменьшенных изображений в разделе-галерее<small>Если оставить эту настройку пустой, будут использованы настройки для обычных сообщений</small></span> {{ macros.input('config[gallery_preview_x]',config.gallery_preview_x,4) }}×{{ macros.input('config[gallery_preview_y]',config.gallery_preview_y,4) }} пикселей</label></div>
  <div><label><span>Качество JPEG для обработки фотографий</span> {{ macros.input('config[userlib_photo_jpeg_qty]',config.userlib_photo_jpeg_qty,3) }}</label></div>  
  <div><label><span>Уменьшать загружаемые полноразмерные изображения до</span> {{ macros.input('config[attach_max_x]',config.attach_max_x,4) }}×{{ macros.input('config[attach_max_y]',config.attach_max_y,4) }} пикселей</label></div>
  <div><label><span>Размеры уменьшенных альбомов на главной</span> {{ macros.input('config[gallery_mainpage_x]',config.gallery_mainpage_x,4) }}×{{ macros.input('config[gallery_mainpage_y]',config.gallery_mainpage_y,4) }} пикселей</label></div>

</fieldset><fieldset><legend>Интеграция и децентрализация</legend>
<div><span>Загружать jQuery</span> {{ macros.radio('config[javascript_cdn]', {'':'Локально','yandex':'из CDN Яндекса','google':'из CDN Google'}, config.javascript_cdn) }}</div>
<div><span>Авторизация через OAuth/IndieAuth</span> {{ macros.radio('config[oauth_server_enable]', {'0':'Выключена','1':'Включена'}, config.oauth_server_enable) }}</div>
{# <div><label><span>Ключ бота Telegram</span> {{ macros.input('config[telegram_key]',config.telegram_key,50,50) }}</label></div> #}
{#   <div><span>Поддержка взаимодействия с Fediverse</span> {{ macros.radio('config[fediverse_enable]', {'0':'Выключена','1':'Включена'}, config.fediverse_enable) }}</div>   #}

<div class="submit"><button type="submit">Сохранить</button><input type="hidden" name="authkey" value="{{ authkey }}" /></div>
</fieldset></form>
{% endblock %}
