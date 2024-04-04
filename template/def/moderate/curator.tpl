{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<div id="moderate_curator">
<form action="" method="post" class="ibform"><fieldset style="border: 0"><legend>Назначение куратора темы</legend>
<div class="center">Куратор — это пользователь, обладающий возможностью редактировать, удалять и переносить сообщения в отдельной теме.<br />
У темы может быть только один куратор.
{% if not true_moderator %}<div class="msg_error">Внимание! Если вы передадите кураторство другому пользователю, вы лишитесь кураторских прав на эту тему сами!</div>{% endif %}
</div>
<div><label><span>Новый куратор:</span>{{ macros.input('owner',owner,32) }}</label></div>
<div class="submit"><button type="submit">Сохранить</button>
<input type="hidden" name="authkey" value="{{ authkey }}" /></div>
</fieldset></form>
</div>
{% endblock %}