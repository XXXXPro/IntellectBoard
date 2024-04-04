{% import 'macro.tpl' as macros %}
<div class="preview"></div>
<form action="{{ editpost.action }}" method="post" enctype="multipart/form-data" class="postform" id="replyform" {% if (posts|length>0) %}id="comments"{% endif %}>
<fieldset><legend>{{ editpost.topmsg }}</legend>
{% if is_guest() %}
<div><label><span>Имя отправителя:</span>
{{ macros.input('post[author]', editpost.post.author,32,32) }}</label> При отправке сообщения соблюдайте, пожалуйста, <a href="{{ url('rules.htm') }}">правила форума</a>!</div>
{% endif  %}
{% if editpost.edittopic %}<div>
<div><label><span>Название темы:</span>
{{ macros.input('topic[title]', editpost.topic.title,60,80) }}</label></div>
<div><label><span>Краткое описание темы:</span>
{{ macros.input('topic[descr]',editpost.topic.descr,60,255) }}</label></div>
<div><label><span>Частичный URL темы:<br />
<small>Необязательное поле.</small></span>
{{ macros.input('topic[hurl]',editpost.topic.hurl,40,255) }}</label></div>
</div>{% endif %}

<div style="clear: both">
{# <div class="postavatar" style="padding-top: 2em">{{ macros.avatar(user.id,user.avatar,user.display_name) }}</div> #}
<div><label><span>Ваш комментарий:</span><br />
<textarea name="post[comment]" rows="4" cols="60" id="p_text" class="pseudo_comment"></textarea>
<textarea name="post[text]" rows="4" cols="60" id="p_text" class="mini_bbcode">{{ editpost.post.text }}</textarea>
</label></div>
</div>
{% if editpost.post.attach|length>0 %}
<div class="attach"><span>Уже прикреплены файлы:<br /><small>Поставьте галочку перед названием, если нужно удалить файл</small></span>
{% for item in editpost.post.attach %}{# <input type="hidden" name="preattach[{{ item.fkey }}]" value="{{ item.filename }}"/>#}<input type="checkbox" name="detach[]" value="{{ item.fkey }}"/><a href="{{ url('f/up/1/'~item.oid~'-'~item.fkey~'/'~attach.filename) }}">{{ item.filename }}</a> ({{ macros.filesize(item.size) }}) &nbsp;&nbsp; {% endfor %}
</div>{% endif %}
{% if perms.attach %}
<div class="attach"><span>Прикрепить файлы:</span>
<input type="file" name="attach[]" multiple="multiple" /> <small>(не более {{ forum.max_attach|incline('%d файла','%d файлов','%d файлов') }})</small>
</div>{% endif %}

<label><input class="flipper" type="checkbox" />Показать дополнительные настройки для комментирования</label>
<div class="extend">
<div class="left postavatar perms">
<b>HTML</b> {% if perms.html %}разрешен{% else %}запрещен{% endif %}.<br />
<b>BBCode</b> {% if perms.bcode %}разрешен{% else %}запрещен{% endif %}.<br />
<b>Смайлики</b> {% if perms.smiles %}разрешены{% else %}запрещены{% endif %}.<br />
Прикрепленные <b>файлы</b> {% if perms.attach %}разрешены{% else %}запрещены{% endif %}.<br />
</div>
{% if perms.value %}<div><span>Ценность сообщения: </span>
{{ macros.radio('post[value]',{'0':'Обычное','1':'Ценное','-1':'Флуд'},editpost.post.value) }}</div>{% endif %}
<div class="left" style="width: 25%">
{% if perms.html %}<label>{{ macros.checkbox('post[html]',1,editpost.post.html) }} Использовать HTML</label><br />{% endif %}
{% if perms.bcode %}<label>{{ macros.checkbox('post[bcode]',1,editpost.post.bcode) }} Использовать <a href="../../help/bcode.htm" target="_blank">BoardCode</a></label><br />{% endif %}
{% if perms.smiles %}<label>{{ macros.checkbox('post[smiles]',1,editpost.post.smiles) }} Использовать <a href="../../help/smiles.htm" target="_blank">смайлики</a></label><br />{% endif %}
<label>{{ macros.checkbox('post[links]',1,editpost.post.links) }} Преобразовывать адреса в ссылки</label><br />
</div>
<div class="left" style="width: 25%">{% if editpost.action!='edit.htm' %}
{% if not is_guest() %}<label>{{ macros.checkbox('subscribe',1,editpost.subscribe) }} Подписаться на тему</label><br />{% endif %}
{% if not is_guest() %}<label>{{ macros.checkbox('bookmark',1,editpost.bookmark) }} Добавить в закладки</label><br />{% endif %}
{% if editpost.edittopic and perms.sticky %}<label>{{ macros.checkbox('topic[sticky]',1,editpost.topic.sticky) }} Сделать тему прикрепленной</label><br />{% endif %}
{% if editpost.edittopic and perms.sticky_post %}<label>{{ macros.checkbox('topic[sticky_post]',1,editpost.sticky_post) }} Выводить первое сообщение на каждой странице</label><br />{% endif %}
{% endif %}</div>
<div class="left" style="width: 25%">
{% if perms.lock %}<label>{{ macros.checkbox('post[locked]',1,editpost.post.locked) }} Запретить редактирование сообщения</label><br />
<label>{{ macros.checkbox('topic[locked]',1,editpost.topic.locked) }} Закрыть тему</label><br />{% endif %}
{% if perms.delete and editpost.action=='edit.htm' %}<label style="color: #c00">{{ macros.checkbox('delete',1,0) }} Удалить сообщение</label><br />{% endif %}
</div>
</div>
{% if captcha_key %}<div style="clear: both"><label><span>Проверочные символы: <br /><small>Введите символы с картинки</small></span>
{{ macros.captcha(captcha_key,captcha_code,captcha_data) }}</label></div>{% endif %}
</fieldset>
{% if perms.poll and editpost.edittopic %}
{% endif %}
{% if allow_warning %}
<fieldset><legend>Вынесение предупреждения</legend>
<label><input type="checkbox" name="warn_user" value="1" class="flipper" />Вынести предупреждение за данное сообщение</label>
<div>
{%  include 'user/warnform.tpl' %}
</div>
</fieldset>
{% endif %}
<fieldset><legend style="display: none"></legend>
<div class="submit"><button type="submit" name="sbm">Отправить</button> <input type="submit" name="preview" value="Предпросмотр"/>
{% if authkey and not is_guest() %}<input type="hidden" name="authkey" value="{{ authkey }}" />
{% endif %}<input type="hidden" name="id" value="{{ editpost.post.id }}" /></div>
</fieldset></form>
