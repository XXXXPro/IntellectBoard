{% import 'macro.tpl' as macros %}
<div class="preview"></div>
<form action="{{ editpost.action }}" method="post" enctype="multipart/form-data" class="postform" id="replyform">
<fieldset><legend>Отправить сообщение</legend>
{% if is_guest() %}
<div><label><span>Имя отправителя:</span>
{{ macros.input('post[author]', editpost.post.author,32,32) }}</label> При отправке сообщения соблюдайте, пожалуйста, <a href="{{ url('rules.htm') }}">правила форума</a>!</div>
{% endif  %}

<div style="clear: both">
{# <div class="postavatar" style="padding-top: 2em">{{ macros.avatar(user.id,user.avatar,user.display_name) }}</div> #}
<div><label><span>Текст сообщения:</span><small class="rules_reminder">При отправке сообщения соблюдайте, пожалуйста, <a onclick="window.open('{{ url('rules.htm?onlytext=1') }}','IntB_rules','popup=1,width=400,height=300'); return false;" target="IntB_rules"  href="{{ url('rules.htm') }}">правила форума</a>{% if rules %} и <a onclick="window.open('{{ url(forum.hurl~'/rules.htm?onlytext=1') }}','IntB_rules','popup=1,width=400,height=300'); return false;" target="IntB_rules" href="{{ url(forum.hurl~'/rules.htm') }}">правила данного раздела</a>{% endif %}!</small><br />
<textarea name="post[text]" rows="3" cols="60" id="p_text" class="mini_bbcode">{{ editpost.post.text }}</textarea>
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

<div class="extend">
{% if perms.value %}<div><span>Ценность сообщения: </span>
{{ macros.radio('post[value]',{'0':'Обычное','1':'Ценное','-1':'Флуд'},editpost.post.value) }}</div>{% endif %}
<div class="left">
{% if perms.html %}<label>{{ macros.checkbox('post[html]',1,editpost.post.html) }} Использовать HTML</label> {% endif %}
{% if perms.bcode %}<label>{{ macros.checkbox('post[bcode]',1,editpost.post.bcode) }} Использовать <a href="../../help/bcode.htm" target="_blank">BoardCode</a></label> {% endif %}
{% if perms.smiles %}<label>{{ macros.checkbox('post[smiles]',1,editpost.post.smiles) }} Использовать <a href="../../help/smiles.htm" target="_blank">смайлики</a></label> {% endif %}
<label>{{ macros.checkbox('post[links]',1,editpost.post.links) }} Преобразовывать адреса в ссылки</label>
</div>
</div>
{% if captcha_key %}<div style="clear: both"><label><span>Проверочные символы: <br /><small>Введите символы с картинки</small></span>
{{ macros.captcha(captcha_key) }}</label></div>{% endif %}
</fieldset>

{% if allow_warning %}
<fieldset><legend>Вынесение предупреждения</legend>
<label><input type="checkbox" name="warn_user" value="1" class="flipper" />Вынести предупреждение за данное сообщение</label>
<div>
{%  include 'user/warnform.tpl' %}
</div>
</fieldset>
{% endif %}
<fieldset><legend style="display: none"></legend>
<div class="submit"><button type="submit" name="sbm">Отправить</button>
{% if authkey and not is_guest() %}<input type="hidden" name="authkey" value="{{ authkey }}" />
{% endif %}<input type="hidden" name="id" value="{{ editpost.post.id }}" /></div>
</fieldset></form>
