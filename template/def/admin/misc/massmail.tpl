{% extends intb.is_ajax ? 'ajax.tpl' : 'admin/main.tpl' %}
{% block content %}
{% import 'macro.tpl' as macros %}
<div id="misc_massmail">
<h1>Массовая рассылка</h1>
<p>Последовательность %username% будет автоматически заменена на имя пользователя.
В конец сообщения будет добавлено уведомление о том, что пользователь получил это сообщение
потому что зарегистрировался на форуме.</p>
<form action="" method="post" class="ibform"><fieldset><legend>Массовая рассылка</legend>
<div><span style="line-height: {{ (groups|length)*140 }}%">Группы, которые получат сообщение:</span>
{% for group in groups %}<label><input type="checkbox" name="mail[groups][{{ group.level }}]" value="{{ group.level}}" {% if mail.groups[group.level] or is_new %}checked="checked"{% endif %} />{{ group.name }}</label><br />{% endfor %}
</div>
<div><label><span>Тема письма</span>{{ macros.input('mail[subj]',mail.subj,40) }}</label></div>
<div><label><span>Количество писем за один шаг</span>{{ macros.input('mail[step]',mail.step,5) }}</label></div>
<div>Текст сообщения:
<textarea name="mail[text]" class="wysiwyg" rows="15" cols="60" style="width: 96%; margin: auto">{{ mail.text }}</textarea>
<div class="submit"><button type="submit">Отправить!</button>{{ macros.hidden('authkey',authkey) }}</div>
</div>
</fieldset></form> 
</div>
{% endblock %}