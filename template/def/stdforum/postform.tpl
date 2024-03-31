{% import 'macro.tpl' as macros %}
<div class="preview"></div>
<form action="{{ editpost.action }}" method="post" enctype="multipart/form-data" class="postform {{ form_params.form_class }} noprint" id="replyform">
<fieldset><legend>{{ editpost.topmsg }}</legend>
{% if form_params.username %}
<div class="user_field"><div><label><span>Имя отправителя:</span>
{{ macros.input('post[author]', editpost.post.author,32,32,'required="required"') }}</label>
{% if form_params.rules %}При отправке сообщения соблюдайте, пожалуйста, <a href="{{ url('rules.htm') }}">правила форума</a>!{% endif %}
{% if form_params.social_login %}{% include 'user/social_small_ulogin.tpl' %}{% endif %}
</div></div>
{% endif  %}
{% if form_params.topic_block %}<div>
<div class="topic_fields"><label><span>Название темы:</span>
{{ macros.input('topic[title]', editpost.topic.title,60,80,'required="required"') }}</label></div>
{% if form_params.topic_descr %}<div><label><span>Краткое описание темы:</span>
{{ macros.input('topic[descr]',editpost.topic.descr,60,255) }}</label></div>{% endif %}
{% if form_params.topic_hurl %}
<div><label><span>Частичный URL темы:<br />
<small>Необязательное поле.</small></span>
{{ macros.input('topic[hurl]',editpost.topic.hurl,40,255,'pattern="[a-zA-Z][a-zA-Z0-9\-_]{0,254}"') }}</label></div>{% endif %}
</div>{% endif %}

<div class="maintext"><div>
<textarea name="post[comment]" rows="4" cols="60" id="p_comment" class="pseudo_comment"></textarea>
<label><span>Текст сообщения:</span><small class="rules_reminder">При отправке сообщения соблюдайте, пожалуйста, <a onclick="window.open('{{ url('rules.htm?onlytext=1') }}','IntB_rules','popup=1,width=400,height=300'); return false;" target="IntB_rules"  href="{{ url('rules.htm') }}">правила форума</a>{% if rules %} и <a onclick="window.open('{{ url(forum.hurl~'/rules.htm?onlytext=1') }}','IntB_rules','popup=1,width=400,height=300'); return false;" target="IntB_rules" href="{{ url(forum.hurl~'/rules.htm') }}">правила данного раздела</a>{% endif %}!</small><br />
<textarea name="post[text]" rows="{{ form_params.area_rows }}" cols="60" id="p_text" class="{{ form_params.area_class }}">{{ editpost.post.text }}</textarea>
</label></div></div>
{% if editpost.post.attach|length>0 %}
<div class="attach"><span>Уже прикреплены файлы:<br /><small>Поставьте галочку перед названием, если нужно удалить файл</small></span>
{% for item in editpost.post.attach %}{# <input type="hidden" name="preattach[{{ item.fkey }}]" value="{{ item.filename }}"/>#}<input type="checkbox" name="detach[]" value="{{ item.fkey }}"/><a href="{{ url('f/up/1/'~item.oid~'-'~item.fkey~'/'~attach.filename) }}">{{ item.filename }}</a> ({{ macros.filesize(item.size) }}) &nbsp;&nbsp; {% endfor %}
</div>{% endif %}
{% if form_params.attach %}
<div class="attach"><span>Прикрепить файлы:</span>
<input type="file" name="attach[]" multiple="multiple" /> <small>(не более {{ forum.max_attach|incline('%d файла','%d файлов','%d файлов') }}, не более {{ (upload_max_filesize/1024)|round }} Кб каждый, {{ (post_max_size/1024)|round }} Кб всего)</small>
</div>{% endif %}

<div class="extended">
{% if form_params.allowed %}
<div class="perms">
<b>HTML</b> {% if perms.html %}разрешен{% else %}запрещен{% endif %}.<br />
<b>BBCode</b> {% if perms.bcode %}разрешен{% else %}запрещен{% endif %}.<br />
<b>Смайлики</b> {% if perms.smiles %}разрешены{% else %}запрещены{% endif %}.<br />
Прикрепленные <b>файлы</b> {% if perms.attach %}разрешены{% else %}запрещены{% endif %}.<br />
</div>
{% endif %}
<div class="postfields">
{% if form_params.postdate %}<div><label class="backdate"><span>Время сообщения: </span>{{ macros.input('post[postdate]', editpost.post.postdate ? (editpost.post.postdate+get_opt('timezone','user'))|date('d.m.Y G:i') : '',20,20,'class="datetime"') }}</label></div>{% endif %}
{% if form_params.tags %}<div><label class="tagline"><span>Теги: </span>{{ macros.input('tagline', editpost.tagline,40,255 ) }}</label></div>{% endif %}
{% if form_params.value %}<div class="post_value"><span>Ценность сообщения: </span>
{{ macros.radio('post[value]',{'0':'Обычное','1':'Ценное','-1':'Флуд'},editpost.post.value) }}</div>{% endif %}
</div>
<div class="postboxes">
{% if perms.html %}<label>{{ macros.checkbox('post[html]',1,editpost.post.html) }} Использовать HTML</label><br />{% endif %}
{% if perms.bcode %}<label>{{ macros.checkbox('post[bcode]',1,editpost.post.bcode) }} Использовать <a href="../../help/bcode.htm" target="_blank">BoardCode</a></label><br />{% endif %}
{% if perms.smiles %}<label>{{ macros.checkbox('post[smiles]',1,editpost.post.smiles) }} Использовать <a href="../../help/smiles.htm" target="_blank">смайлики</a></label><br />{% endif %}
<label>{{ macros.checkbox('post[links]',1,editpost.post.links) }} Преобразовывать адреса в ссылки</label>
{% if form_params.subscribe %}<label>{{ macros.checkbox('subscribe',1,editpost.subscribe) }} Подписаться на тему</label>{% endif %}
{% if form_params.no_export %}<label>{{ macros.checkbox('no_export',1,0) }} Не отправлять в соцсети</label>{% endif %}
{% if form_params.bookmark %}<label>{{ macros.checkbox('bookmark',1,editpost.bookmark) }} Добавить в закладки</label>{% endif %}
{% if form_params.sticky %}<label>{{ macros.checkbox('topic[sticky]',1,editpost.topic.sticky) }} Сделать тему прикрепленной</label>{% endif %}
{% if form_params.sticky_post %}<label>{{ macros.checkbox('topic[sticky_post]',1,editpost.sticky_post) }} Первое сообщение на каждой странице</label>{% endif %}
{% if form_params.lock_post %}<label>{{ macros.checkbox('post[locked]',1,editpost.post.locked) }} Запретить редактирование сообщения</label>{% endif %}
{% if form_params.lock %}<label>{{ macros.checkbox('topic[locked]',1,editpost.topic.locked) }} Закрыть тему</label>{% endif %}
{% if form_params.favorites %}<label>{{ macros.checkbox('topic[favorites]',1,editpost.topic.favorites) }} В "Избранное"</label>{% endif %}
{% if form_params.delete %}<label class="danger">{{ macros.checkbox('delete',1,0) }} Удалить сообщение</label>{% endif %}
{# </div> #}
</div></div>
{% if captcha_key %}<div class="captcha"><label><span>Проверочные символы: <br /><small>Введите символы с картинки</small></span>
{{ macros.captcha(captcha_key,captcha_code,captcha_data) }}</label></div>{% endif %}
</fieldset>
{% if form_params.poll and editpost.edittopic %}
<fieldset><legend>Голосование в теме</legend>
{% if editpost.edittopic %}<label><input type="checkbox" name="create_poll" value="1" class="flipper" {% if editpost.poll %}checked="checked" readonly="readonly" disabled="disabled"{% endif %}/>Голосование в теме</label>
<div>
<div><label><span>Текст вопроса</span>{{ macros.input('poll[question]',editpost.poll.question) }}</label></div>
<div><span>Опрос действует</span>{{ macros.radio('poll[limit]',{'0':'Бессрочно','1':'В течение'},editpost.poll.endtime>0) }} <label>{{ macros.input('poll[period]',editpost.poll.period,4) }} дней</label></div>
{% if editpost.poll %}
<div>Редактировать варианты ответов:<br /><small>Оставьте поле пустым, если вариант ответа требуется удалить</small></div>
<input type="hidden" name="poll[edit]" value="1" />
{% for item in editpost.poll.variants %}
<div><label><span>{{ item.text }} ({{ item.count }})</span>{{ macros.input('vote['~item.id~'][text]',item.text) }}</label></div>
{% endfor %}
{% endif %}
<div>Добавить варианты ответов:</div>
{% for i in range(1,5) %}
<div><label><span>Вариант {{ i }}</span>{{ macros.input('vote[0][][text]','') }}</label></div>
{% endfor %}
</div>{% endif %}
{% if editpost.poll %}<label style="color: #c00">{{ macros.checkbox('delete_vote',1,0) }} Удалить голосование</label><br />{% endif %}
</fieldset>
{% endif %}
{% if form_params.warning %}
<fieldset><legend>Вынесение предупреждения</legend>
<label><input type="checkbox" name="warn_user" value="1" class="flipper" />Вынести предупреждение за данное сообщение</label>
<div>
{%  include 'user/warnform.tpl' %}
</div>
</fieldset>
{% endif %}
<fieldset><legend style="display: none"></legend>
<div class="submit"><button type="submit" name="sbm">Отправить</button> <input type="button" name="preview" value="Предпросмотр"/>
{% if authkey and not is_guest() %}<input type="hidden" name="authkey" value="{{ authkey }}" />
{% endif %}<input type="hidden" name="id" value="{{ editpost.post.id }}" /></div>
</fieldset></form>
