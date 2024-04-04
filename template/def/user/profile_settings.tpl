{% import 'macro.tpl' as macros %}
  <fieldset><legend>Настройки профиля</legend>
    <div><span>Скрывать мое присутствие на форуме</span>{{ macros.radio('settings[hidden]',yes_no,formdata.settings.hidden) }}</div>
{% if (allow_template) %}
    <div><label><span>Стиль сайта</span>{{ macros.select('settings[template]',formdata.settings.template,user_templates) }}</label></div>
{% endif %}
    <div><label><span>Часовой пояс</span>{{ macros.select('settings[timezone]',formdata.settings.timezone,timezones) }}</label></div>
    <div><label><span>Тем на страницу</span>{{ macros.input('settings[topics_per_page]',formdata.settings.topics_per_page,4) }}</label></div>
    <div><label><span>Сообщений на страницу</span>{{ macros.input('settings[posts_per_page]',formdata.settings.posts_per_page,4) }}</label></div>
    <div><span>Порядок вывода сообщений</span>{{ macros.radio('settings[msg_order]',{'ASC':'Сначала более старые','DESC':'Сначала более новые'},formdata.settings.msg_order) }}</div>
    <div><label><span>По умолчанию выводить темы, обновившиеся за</span>{{ macros.select('settings[topics_period]',formdata.settings.topics_period,{0:'все время',24:'последние сутки',72:'последние три дня',168:'последнюю неделю',720:'последний месяц',2160:'последние три месяца',4320:'последние полгода',8760:'последний год'}) }}</label></div>
    <div><label><span>Доля флуда, при превышении которой срабатывает фильтр зафлуженности</span>{{ macros.input('settings[flood_limit]',formdata.settings.flood_limit,3) }}%</label></div>
    <div><span>Показывать подписи пользователей</span>{{ macros.radio('settings[signatures]',yes_no,formdata.settings.signatures) }}</div>
    <div><span>Показывать аватары пользователей</span>{{ macros.radio('settings[avatars]',yes_no,formdata.settings.avatars) }}</div>
    <div><span>Показывать графические смайлики</span>{{ macros.radio('settings[smiles]',yes_no,formdata.settings.smiles) }}</div>
    <div><span>Показывать уменьшенные изображения для прикрепленных графических файлов</span>{{ macros.radio('settings[pics]',yes_no,formdata.settings.pics) }}</div>
    <div><span>Выводить сообщения в свернутом виде</span>{{ macros.radio('settings[longposts]',{'0':'Нет','1':'Все','2':'Только помеченные как флуд'},formdata.settings.longposts) }}</div>
    <div><span>Режим редактирования сообщений </span>{{ macros.radio('settings[wysiwyg]',{'0':'Простое поле ввода','1':'Режим ввода BBCode','2':'Визуальный режим'},formdata.settings.wysiwyg) }}</div>
  </fieldset>
