{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block content %}
<div id="stdforum_vk_token">
<form action="" method="post" class="ibform"><fieldset style="border: 0"><legend>Получение токена доступа для VK.com</legend>
{% if received %}<div class="center"><a href="#" onclick="put_token('{{ result.access_token }}')">Закрыть окно и вернуться к редактированию раздела</a>
<script>
function put_token(token) {
  if (window.opener) {
    var elm = window.opener.document.getElementById('vk_token');
    if (elm) elm.value=token;
  }
  window.close();
  return false;
}
</script>
</div>{% else %}
<div><label><span>Client id приложения:</span>{{ macros.input('client_id',client_id,21) }}</label> <a href="https://vk.com/editapp?act=create" target="_blank">Создать приложение VK</a></div>
<div><label><span>Client secret для приложения:</span>{{ macros.input('client_secret',client_secret,64) }}</label></div>
<div class="submit"><button type="submit">Сохранить</button>
<input type="hidden" name="authkey" value="{{ authkey }}" /></div>
{% endif %}
</fieldset></form>
</div>
{% endblock %}