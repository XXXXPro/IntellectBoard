{% extends intb.is_ajax ? 'ajax.tpl' : admin_edit_mode ? 'admin/main.tpl' : 'main.tpl' %}
{% block content %}{%
set yes_no = { 0: 'Нет', 1: 'Да' } %}
<div id="user_update">
<script type="text/javascript"><!--
  function change_img(data) {
    var elm = document.getElementById('img_'+data.name);
    if (elm) elm.src='file:///'+data.value;
    alert(data.value);
  }
--></script>
<form action="" method="post" class="ibform accordion" enctype="multipart/form-data">
  {% for tab in profile_tabs %}
  {% include "user/profile_"~tab~".tpl" %}
  {% endfor %}
    <div class="submit"><button type="submit">Сохранить изменения</button></div>
    <input type="hidden" name="basic[id]" value="{{ formdata.basic.id }}">
    <input type="hidden" name="referer" value="{{ referer }}">
{% if authkey %}<input type="hidden" name="authkey" value="{{ authkey }}">{% endif %}

</form>
</div>
{% endblock %}
