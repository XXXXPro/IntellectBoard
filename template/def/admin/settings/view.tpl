{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block css %}
<style>
#stat_block { display: flex; flex-wrap: wrap; justify-content: space-between }
#stat_block .item { border: #ccf 1px solid; list-style: none; padding: 5px; margin: 2px; min-width: 484px; box-sizing: border-box; align-self: start }
</style>
{% endblock %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="settings_view">
<h1>Состояние сайта</h1>

<div id="stat_block">
<ul class="item">
<li>Версия Intellect Board Pro: <b>{{ status.CMS_VERSION }}</b></li>
<li>Отладочный режим: <b>{{ (status.CMS_DEBUG>0 ? '<span class="msg_warn">Вкл. ('~status.CMS_DEBUG~')</span>' : '<span class="msg_ok">Выключен</span>')|raw }}</b>, 
SQL: <b>{{ (status.SQL_DEBUG>0 ? '<span class="msg_warn">Вкл. ('~status.SQL_DEBUG~')</span>' : '<span class="msg_ok">Выкл.</span>')|raw }}</b></li>
<li>Библиотека шаблонизатора: <b>{{ status.PARSER }}</b></li>
<li>Запрет кеширования: <b>{{ (status.CMS_NOCACHE ? '<span class="msg_warn">Вкл.</span>' : '<span class="msg_ok">Выкл.</span>')|raw }}</b></li>
<li>Путь для сохранения сессий: <b>{{ status.SESSION_PATH }}</b></li>
<li>Почтовые функции: <b>{{ (status.EMAIL ? '<span class="msg_ok">Вкл.</span>' : '<span class="msg_warn">Выкл.</span>')|raw }}</b></li>
</ul>
<ul class="item">
<li>Сервер: <b>{{ status.SERVER_SOFT }}</b></li>
<li>IP сервера: <b>{{ status.SERVER_IP }}</b></li>
<li>Версия PHP: <b>{{ status.PHP_VERSION }}</b></li>
<li>Версия GD: <b>{{ status.GD_VERSION }}</b></li>
<li>СУБД: <b>{{ db_version }}</b> (драйвер <b>{{ status.DB_DRIVER }}</b>)</li>
<li>Persistent connections: <b>{{ status.DB_PERSIST ? 'Вкл.' : 'Выкл.' }}</b></li>
</ul>
<ul class="item">
<li>Путь к форуму: <b>{{ status.CMS_PATH }}</b></li>
<li>Путь к временным файлам: <b>{{ status.SERVER_TEMP }}</b></li>
<li>Файл конфигурации (etc/ib_config.php): <b>{% if status.WRITABLE_config %}<span class="msg_ok">Доступен для записи</span>{% else %}<span class="msg_error">Не доступен для записи!</span>{% endif %}</b></li>
<li>Каталог файлов журнала (logs): <b>{% if status.WRITABLE_logs %}<span class="msg_ok">Доступен для записи</span>{% else %}<span class="msg_error">Не доступен для записи!</span>{% endif %}</b></li>
<li>Каталог аватаров (www/f/av): <b>{% if status.WRITABLE_av %}<span class="msg_ok">Доступен для записи</span>{% else %}<span class="msg_error">Не доступен для записи!</span>{% endif %}</b></li>
<li>Каталог фото (www/f/ph): <b>{% if status.WRITABLE_ph %}<span class="msg_ok">Доступен для записи</span>{% else %}<span class="msg_error">Не доступен для записи!</span>{% endif %}</b></li>
<li>Каталог CAPTCHA (www/f/cap): <b>{% if status.WRITABLE_cap %}<span class="msg_ok">Доступен для записи</span>{% else %}<span class="msg_error">Не доступен для записи!</span>{% endif %}</b></li>
<li>Каталог прикрепленных файлов (www/f/up/1): <b>{% if status.WRITABLE_up %}<span class="msg_ok">Доступен для записи</span>{% else %}<span class="msg_error">Не доступен для записи!</span>{% endif %}</b></li>
</ul>
<ul class="item">
<li>Вывод ошибок: <b>{{ (status.SCRIPT_ERRORS ? '<span class="msg_warn">Вкл.</span>' : '<span class="msg_ok">Выкл.</span>')|raw }}</b></li>
<li>Владелец файла скрипта: <b>{{ status.SCRIPT_OWNER_NAME }} ({{ status.SCRIPT_OWNER_UID }})</b></li>
<li>Скрипт выполняется от имени: <b>{{ status.SCRIPT_RUN_NAME }} ({{ status.SCRIPT_RUN_UID }})</b></li>
<li>Макс. время выполнения: <b>{{ status.LIMIT_TIME }}</b> с.</li>
<li>Макс. объем памяти: <b>{{ status.LIMIT_MEM }}</b></li>
<li>Свободно на диске: <b>{{ status.LIMIT_DISK }}</b></li>
<li>Загрузка файлов: <b>{{ (status.PHP_UPLOAD ? '<span class="msg_ok">Вкл.</span>' : '<span class="msg_warn">Выкл.</span>')|raw }}</b></li>
<li>Макс. загружаемый файл: <b>{{ status.LIMIT_SIZE }}</b></li>
</ul>
<ul class="item">
<li>Форум существует {% if forum_exists %}<b>{{ forum_exists|incline('%d день','%d дня','%d дней') }}</b>{% else %}меньше суток{% endif %}</li>
<li>Сообщения: <b>{{ post_active }}</b> доступно{% if post_premod %}, <b><span class="msg_warn">{{ post_premod }}</span></b> на премодерации{% endif %}{% if post_valued %}, из них <b><span class="msg_ok">{{ post_valued|incline('%d ценное','%d ценных','%d ценных') }}</span></b>{% endif %}</li>
<li>Темы:  <b>{{ topic_active }}</b> доступно{% if topic_premod %}, <b><span class="msg_warn">{{ topic_premod }}</span></b> на премодерации{% endif %}</li>
<li>Пользователи: <b>{{ users_active }}</b> зарегистрировано{% if users_inactive %}, <b><span class="msg_warn">{{ users_inactive }}</span></b> не подтверждено{% endif %}{% if users_banned %}, <b><span class="msg_error">{{ users_banned }}</span></b> изгнано{% endif %}</li>
<li>Активация пользователей: <b>{{ status.USER_ACTIVATE==2 ? 'только администратором' : status.USER_ACTIVATE==1 ? 'через Email' : 'выключена' }}</b></li>
<li><br />Администраторы форума: {% for item in admins %}{{ macros.user(item.display_name,item.id)}}{% if not loop.last %}, {% endif %}{% endfor %}</li>
</ul>
<ul class="item">
<li>Общее количество ожидающих выполнение асинхронных задач: <b><span class={% if tasks_count==0 %}"msg_ok"{% else %}"msg_warn"{% endif %}>{{ tasks_count }}</span></b></li>
<li>Из них на повторном выполнении из-за ошибок: <b><span class={% if tasks_errors==0 %}"msg_ok"{% else %}"msg_error"{% endif %}>{{ tasks_errors }}</span></b></li>
</ul>

<div class="item">
<table class="ib_table" style="table-layout: fixed; margin: auto"><col style="width: 25%" /><col style="width: 50%" />
<caption style="font-weight: bold; text-align: left">Индексация поисковыми системами:</caption>
<tbody>{% for bot in bots %}
<tr><td>{{ bot.bot_name }}</td><td>{% if bot.last_visit %}{{ bot.last_visit|longdate }}{% else %}не появлялся{% endif %}</td>
{% endfor %}</tbody>
</table>
</div>
</div>

</div>
{% endblock %}