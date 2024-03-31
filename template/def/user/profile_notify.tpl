{% import 'macro.tpl' as macros %}
  <fieldset><legend>Уведомления на электронную почту</legend>
    <div><span>Режим подписки на темы <small>Данный параметр влияет на то, будет ли предлагаться подписка по умолчанию. Перед отправкой вы сможете поменять этот параметр для каждого конкретного сообщения.</small>
</span>{{ macros.radio('settings[subscribe]',{'No':'Не предлагать','My':'Только на созданные мной темы','All':'На все темы, в которые я отвечаю'},formdata.settings.subscribe) }}</div>
    <div><span>Режим отправки уведомлений о новых сообщениях на форуме</span>{{ macros.radio('settings[subscribe_mode]',{'0':'Выключены','1':'Сразу'},formdata.settings.subscribe_mode) }}</div>
    <div><span>Отправлять в письме полный текст сообщения</span>{{ macros.radio('settings[email_fulltext]',yes_no,formdata.settings.email_fulltext) }}</div>
    <div><span>Уведомлять о новых личных сообщениях</span>{{ macros.radio('settings[email_pm]',yes_no,formdata.settings.email_pm) }}</div>
    <div><span>Разрешить отправку сообщения через форму на сайте</span>{{ macros.radio('settings[email_message]',yes_no,formdata.settings.email_message) }}</div>
    <div><span>Получать рассылки от администрации форума</span>{{ macros.radio('settings[email_broadcasts]',yes_no,formdata.settings.email_broadcasts) }}</div>
  </fieldset>
