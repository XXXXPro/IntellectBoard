{% import 'macro.tpl' as macros %}
<form id="reply" action="{{ editpost.action }}" method="post" enctype="multipart/form-data" class="ibform postform">
<fieldset><legend style="display: none"></legend>
{% if editpost.edittopic %}<input type="hidden" name="topic[title]" value="{{editpost.topic.title}}" />{% endif %}

<textarea name="post[comment]" rows="4" cols="60" id="p_text" class="pseudo_comment"></textarea>
<textarea name="post[text]" rows="6" cols="60" id="p_text" class="bbcode">{{ editpost.post.text }}</textarea>

{% if captcha_key %}<div style="clear: both"><label><span>Проверочные символы: <br /><small>Введите символы с картинки</small></span>
{{ macros.captcha(captcha_key,captcha_code,captcha_data) }}</label></div>{% endif %}

{% if not is_guest() and get_opt('email_enabled') %}<div><label>{{ macros.checkbox('subscribe',1,editpost.subscribe) }} Подписаться на тему</label></div>{% endif %}

{% if editpost.post.attach|length>0 %}
<div class="attach"><span>Уже прикреплены файлы:<br /><small>Поставьте галочку перед названием, если нужно удалить файл</small></span>
{% for item in editpost.post.attach %}{# <input type="hidden" name="preattach[{{ item.fkey }}]" value="{{ item.filename }}"/>#}<input type="checkbox" name="detach[]" value="{{ item.fkey }}"/><a href="{{ url('f/up/1/'~item.oid~'-'~item.fkey~'/'~attach.filename) }}">{{ item.filename }}</a> ({{ macros.filesize(item.size) }}) &nbsp;&nbsp; {% endfor %}
</div>{% endif %}
{% if perms.attach %}
<div class="attach"><span>Прикрепить файлы:</span>
<input type="file" name="attach[]" multiple="multiple" /> <small>(не более {{ forum.max_attach|incline('%d файла','%d файлов','%d файлов') }})</small>
</div>{% endif %}
{% if not is_guest() %}<div><label>{{ macros.checkbox('anonymous',1,0) }} Отправить анонимно</label></div>{% endif %}

<div class="submit"><button type="submit">Отправить</button>
<input type="hidden" name="id" value="{{ editpost.post.id }}" /></div>
{% if authkey and not is_guest() %}<input type="hidden" name="authkey" value="{{ authkey }}" />{% endif %}
<input type="hidden" name="post[bcode]" value="1" />
<input type="hidden" name="post[smiles]" value="1" />
<input type="hidden" name="topic[descr]" value="" />
<input type="hidden" name="topic[hurl]" value="" />
</fieldset></form>