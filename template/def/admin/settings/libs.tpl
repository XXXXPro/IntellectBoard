{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<h1>Задаваемые библиотеки и расширенные настройки форума</h1>
<p>Внимание! Внесение неправильных настроек на данной странице может привести к нарушению работы форума.
Изменяйте их только в том случае, если вы понимаете, что и с какой целью вы делаете!</p> 
<form action="" method="post" class="ibform"><fieldset><legend>Задаваемые библиотеки</legend>
<div><label><span>Библиотека шаблонизатора<small>По умолчанию — twig</small></span>{{ macros.input('config[site_template_lib]',config.site_template_lib,24) }}</label></div>    
<div><label><span>Библиотека уведомлений о событиях<small>По умолчанию — notify</small></span>{{ macros.input('config[site_notify_lib]',config.site_notify_lib,24) }}</label></div>
<div><label><span>Библиотека авторизации через социальные сети<small>По умолчанию — ulogin, оставьте поле пустым, чтобы отключить</small></span>{{ macros.input('config[site_social_lib]',config.site_social_lib,24) }}</label></div>
<div><label><span>Библиотека серверного кеширования<small>По умолчанию не используется, возможны варианты xchache и filecache</small></span>{{ macros.input('config[site_cache_lib]',config.site_cache_lib,24) }}</label></div>
</fieldset><fieldset><legend>Пути и настройки кеша</legend>
<div><label><span>Каталог сессий<small>Оставьте пустым, чтобы использовать настройки PHP по умолчанию</small></span>{{ macros.input('config[session_path]',config.session_path,40) }}</label></div>
<div><label><span>Каталог кеша шаблонизатора<small>По умолчанию ../tmp/template</small></span>{{ macros.input('config[cache_template_dir]',config.cache_template_dir,40) }}</label></div>
<div><label><span>Каталог для filecache<small>По умолчанию ../tmp/cache</small></span>{{ macros.input('config[cache_file_dir]',config.cache_file_dir,40) }}</label></div>
</fieldset><fieldset><legend>Журнал действий</legend>
<div><span>Запись в журнал действий в Центре Администрирования</span>{{ macros.radio('config[enable_log_admins]', {'0':'Выключена','1':'Включена'}, config.enable_log_admins)}}</div>
</fieldset><fieldset><legend>Прочие настройки</legend>
<div><label><span>Ключи $_SERVER, из которых берется значение IP-адреса<small>Можно указать несколько, например, REMOTE_ADDR,HTTP_X_FORWARDED_FOR</small></span>{{ macros.input('config[ip_address_source]',config.ip_address_source,40) }}</label></div>    
<div><span>Разрешить администраторам (не основателям) управлять вспомогательными блоками</span>{{ macros.radio('config[enable_admin_subactions]', {'0':'Нет','1':'Да'}, config.enable_admin_subactions)}}</div>
<div class="submit"><button type="submit">Сохранить</button><input type="hidden" name="authkey" value="{{ authkey }}" /></div>
</fieldset></form>
{% endblock %}