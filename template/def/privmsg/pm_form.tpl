{% import 'macro.tpl' as macros %}
<form action="send.htm" method="post" enctype="form-data/multipart" class="postform">
<fieldset><legend>Отправка личного сообщения</legend>
{% if newpost %}<div>
<div><label><span>Получатели сообщения:</span>
<input type="text" name="recepients" value="{{ recepients }}" size="40" maxlength="255" list="friends" />
{% if friends %}<datalist id="friends">
{% for item in friends %}<option>{{ item.display_name }}</option>{% endfor %}
</datalist>
{% endif %}</div>
<div><label><span>Название темы:</span>
{{ macros.input('thread[title]', thread.title,40,80) }}</label></div>
</div>{% endif %}

<div style="clear: both">
<div><label><span>Текст сообщения:</span><br />
<textarea name="post[text]" rows="12" cols="40" id="p_text" class="bbcode">{{ post.text }}</textarea>
</label></div>
</div>

<div class="extend">
<div class="left postavatar perms">
<b>HTML</b> {% if perms.html %}разрешен{% else %}запрещен{% endif %}.<br />
<b>BoardCode</b> {% if perms.bcode %}разрешен{% else %}запрещен{% endif %}.<br />
<b>Смайлики</b> {% if perms.smiles %}разрешены{% else %}запрещены{% endif %}.<br />
{% if max_pms > 0 %}Вы можете создавать не более <b>{{ max_pms|incline('%d темы','%d тем','%d тем') }}</b> в час.<br />У каждой темы может быть не более <b>{{ (max_pms-1)|incline('%d получатель','%d получателей','%d получателей') }}</b>.
{% else %}У вас нет ограничений на количество отправляемых сообщений{% endif %}
</div>
<div class="left">
{% if perms.html %}<label>{{ macros.checkbox('post[html]',1,post.html) }} Использовать HTML</label><br />{% endif %}
{% if perms.bcode %}<label>{{ macros.checkbox('post[bcode]',1,post.bcode) }} Использовать <a href="../../help/bcode.htm" target="_blank">BoardCode</a></label><br />{% endif %}
{% if perms.smiles %}<label>{{ macros.checkbox('post[smiles]',1,post.smiles) }} Использовать <a href="../../help/smiles.htm" target="_blank">смайлики</a></label><br />{% endif %}
<label>{{ macros.checkbox('post[links]',1,post.links) }} Преобразовывать адреса в ссылки</label><br />
</div>
</div>
{% if captcha_key %}<div style="clear: both"><label><span>Проверочные символы: <br /><small>Введите символы с картинки</small></span>
{{ macros.captcha(captcha_key,captcha_code,captcha_data) }}</label></div>{% endif %}
</fieldset>

<fieldset><legend style="display: none"></legend>
<div class="submit">{% if authkey %}<input type="hidden" name="authkey" value="{{ authkey }}" />
{% endif %}<button type="submit">Отправить</button></div>
</fieldset></form>
